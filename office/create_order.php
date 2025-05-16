<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if (isset($_SESSION['order_data']) && isset($_SESSION['order_from_quotation'])) {
    $_POST = $_SESSION['order_data'];
    unset($_SESSION['order_data']);
    unset($_SESSION['order_from_quotation']);
}

$supervisors = $conn->query("SELECT * FROM users WHERE role = 'supervisor'")->fetch_all(MYSQLI_ASSOC);
$coils = $conn->query("SELECT * FROM materials WHERE type = 'coil'")->fetch_all(MYSQLI_ASSOC);

// Check if this is from a quotation
$quotation_id = $_GET['quotation_id'] ?? null;
if ($quotation_id) {
    // Get quotation details
    $quotation = $conn->query("
        SELECT q.*, u.name as created_by_name, u.contact as created_by_contact
        FROM quotations q 
        LEFT JOIN users u ON q.created_by = u.id
        WHERE q.id = $quotation_id AND q.type = 'order'
    ")->fetch_assoc();

    if ($quotation) {
        // Pre-fill customer details
        $customer_name = $quotation['customer_name'];
        $customer_contact = $quotation['customer_contact'];
        
        // Get customer address from contacts
        $customer = $conn->query("
            SELECT * FROM contacts 
            WHERE name = '$customer_name' AND mobile = '$customer_contact'
            LIMIT 1
        ")->fetch_assoc();
        
        $customer_address = $customer['address'] ?? '';
        $prepared_by_name = $quotation['created_by_name'];
        $prepared_by_contact = $quotation['created_by_contact'];
    }

    // Check if quotation already has an order
    $existing_order = $conn->query("
        SELECT id FROM orders WHERE quotation_id = $quotation_id
    ")->fetch_assoc();

    if ($existing_order) {
        $_SESSION['error_message'] = "This quotation already has an order.";
        header('Location: quotations.php');
        exit();
    }
}

// Get measurements from URL if they exist
$measurements = null;
if (isset($_GET['measurements'])) {
    $measurements = json_decode(base64_decode($_GET['measurements']), true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['check_quotation']) && $quotation_id) {
        // Calculate square feet
        $section1_inches = floatval($_POST['section1']);
        $section2_inches = floatval($_POST['section2']);
        $door_width_inches = floatval($_POST['door_width']);
        
        $section1_feet = $section1_inches / 12;
        $section2_feet = $section2_inches / 12;
        $door_width_feet = $door_width_inches / 12;
        
        $height = $section1_feet + $section2_feet;
        $calculated_sqft = $height * $door_width_feet;

        // Store all form data in session
        $_SESSION['order_data'] = $_POST;
        $_SESSION['calculated_sqft'] = $calculated_sqft;

        // Redirect to edit quotation first
        header("Location: edit_quotation.php?id=$quotation_id&calculated_sqft=$calculated_sqft");
        exit();
    }

    $conn->begin_transaction();
    try {
        // Convert inches to feet
        $section1_inches = floatval($_POST['section1']);
        $section2_inches = floatval($_POST['section2']);
        $door_width_inches = floatval($_POST['door_width']);
        
        // Convert to feet (1 foot = 12 inches)
        $section1_feet = $section1_inches / 12;
        $section2_feet = $section2_inches / 12;
        $door_width_feet = $door_width_inches / 12;
        
        // Calculate total height in feet
        $height = $section1_feet + $section2_feet;
        
        // Calculate square feet
        $calculated_sqft = $height * $door_width_feet;

        // Set initial status
        $status = 'pending';  // Make sure this is set to 'pending'
        
        // Basic insert without quotation_id
        if (!$quotation_id) {
            $query = $conn->prepare("
                INSERT INTO orders (
                    customer_name, customer_contact, customer_address, 
                    prepared_by, status, total_sqft, total_price, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $customer_name = $_POST['customer_name'];
            $customer_contact = $_POST['customer_contact'];
            $customer_address = $_POST['customer_address'];
            $user_id = $_SESSION['user_id'];
            $total_price = 0; // Default to 0 if no quotation

            $query->bind_param("sssissd", 
                $customer_name,
                $customer_contact,
                $customer_address,
                $user_id,
                $status,      // Make sure 'pending' is being passed here
                $calculated_sqft,
                $total_price
            );
        } 
        // Insert with quotation_id
        else {
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    customer_name, customer_contact, customer_address, 
                    prepared_by, status, quotation_id, total_sqft, total_price,
                    balance_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $customer_name = $_POST['customer_name'];
            $customer_contact = $_POST['customer_contact'];
            $customer_address = $_POST['customer_address'];
            $user_id = $_SESSION['user_id'];
            $total_price = $quotation['total_amount'] ?? 0;
            $balance = $total_price; // Set initial balance equal to total price

            $stmt->bind_param("sssisiddd", 
                $customer_name,
                $customer_contact,
                $customer_address,
                $user_id,
                $status,
                $quotation_id,
                $calculated_sqft,
                $total_price,
                $balance
            );
        }

        if (!$stmt->execute()) {
            throw new Exception("Error inserting order: " . $stmt->error);
        }

        $order_id = $conn->insert_id;

        // Insert roller door measurements
        $measureQuery = $conn->prepare("INSERT INTO roller_door_measurements (
            order_id, section1, section2, outside_width, inside_width, door_width, 
            tower_height, tower_type, coil_color, thickness, covering, 
            side_lock, motor, fixing, down_lock
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Use original inch measurements for storage
        $section1 = $section1_inches;
        $section2 = $section2_inches;
        $outsideWidth = floatval($_POST['outside_width']);
        $insideWidth = floatval($_POST['inside_width']);
        $doorWidth = $door_width_inches;
        $towerHeight = floatval($_POST['tower_height']);
        $towerType = $_POST['tower_type'];
        $coilColor = $_POST['coil_color'];
        $thickness = $_POST['thickness'];
        $covering = $_POST['covering'];
        $sideLock = $_POST['side_lock'];
        $motor = $_POST['motor'];
        $fixing = $_POST['fixing'];
        $downLock = intval($_POST['down_lock']);

        $measureQuery->bind_param('iddddddsssssssi',
            $order_id,
            $section1,
            $section2,
            $outsideWidth,
            $insideWidth,
            $doorWidth,
            $towerHeight,
            $towerType,
            $coilColor,
            $thickness,
            $covering,
            $sideLock,
            $motor,
            $fixing,
            $downLock
        );

        $measureQuery->execute();

        // Insert wicket door if exists
        if (isset($_POST['has_wicket_door'])) {
            $wicketQuery = $conn->prepare("INSERT INTO wicket_door_measurements (
                order_id, point1, point2, point3, point4, point5,
                thickness, door_opening, handle, letter_box, coil_color
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $point1 = floatval($_POST['point1']);
            $point2 = floatval($_POST['point2']);
            $point3 = floatval($_POST['point3']);
            $point4 = floatval($_POST['point4']);
            $point5 = floatval($_POST['point5']);
            $wThickness = $_POST['thickness'];
            $doorOpening = $_POST['door_opening'];
            $handle = isset($_POST['handle']) ? 1 : 0;
            $letterBox = isset($_POST['letter_box']) ? 1 : 0;
            $wCoilColor = $_POST['coil_color'];

            $wicketQuery->bind_param('idddddssiss',
                $order_id,
                $point1,
                $point2,
                $point3,
                $point4,
                $point5,
                $wThickness,
                $doorOpening,
                $handle,
                $letterBox,
                $wCoilColor
            );

            $wicketQuery->execute();
        }

        // After successfully creating order and saving measurements, check quotation if exists
        if ($quotation_id) {
            $roller_door_item = $conn->query("
                SELECT qi.* FROM quotation_items qi
                WHERE qi.quotation_id = $quotation_id 
                AND qi.name LIKE '%Roller Door%'
                LIMIT 1
            ")->fetch_assoc();

            if ($roller_door_item && abs($calculated_sqft - $roller_door_item['quantity']) > 0.01) {
                $_SESSION['order_id'] = $order_id; // Save the order ID
                $_SESSION['calculated_sqft'] = $calculated_sqft;
                $conn->commit(); // Commit the transaction since order is created

                // Redirect to edit quotation
                header("Location: edit_quotation.php?id=$quotation_id&calculated_sqft=$calculated_sqft&order_id=$order_id");
                exit();
            }
        }

        $conn->commit();
        $_SESSION['success_message'] = "Order #$order_id created successfully!";
        header("Location: pending_orders.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in create_order.php: " . $e->getMessage());
        $_SESSION['error_message'] = "Error creating order: " . $e->getMessage();
        header("Location: create_order.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Order</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
    function calculateSquareFeet() {
        const section1 = parseFloat(document.getElementsByName('section1')[0].value) || 0;
        const section2 = parseFloat(document.getElementsByName('section2')[0].value) || 0;
        const doorWidth = parseFloat(document.getElementsByName('door_width')[0].value) || 0;
        
        // Convert inches to feet
        const heightFeet = (section1 + section2) / 12;
        const widthFeet = doorWidth / 12;
        
        // Calculate square feet
        const squareFeet = heightFeet * widthFeet;
        
        // Display the calculation if you want to show it to the user
        if (!isNaN(squareFeet)) {
            document.getElementById('sqft_display').textContent = 
                `Square Feet: ${squareFeet.toFixed(2)} (Height: ${heightFeet.toFixed(2)}' × Width: ${widthFeet.toFixed(2)}')`;
        }
    }
    </script>
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        <div class="content">
            <h2 style="color: black;">Create New Order</h2>
            <style>
                h2 {
    font-family: 'Roman Classic', serif; /* Use Roman Classic font */
    font-size: 30px; /* Adjust font size */
    font-weight: bold; /* Make the text bold */
    color:rgb(249, 243, 243); /* Set a nice color */
    text-align: center; /* Center the text */
    margin-bottom: 20px; /* Add spacing below the text */
}
                </style>
            <form method="POST" class="order-form">
                <!-- Customer Details -->
                <div class="section">
                    <h3>Customer Details</h3>
                    <div class="form-group">
                        <label>Customer Name:</label>
                        <input type="text" name="customer_name" 
                               value="<?php echo htmlspecialchars($customer_name ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Customer Contact Number:</label>
                        <input type="text" name="customer_contact" 
                               value="<?php echo htmlspecialchars($customer_contact ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Customer Address:</label>
                        <textarea name="customer_address" required rows="3"><?php echo htmlspecialchars($customer_address ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Prepared By:</label>
                        <input type="text" name="prepared_by" 
                               value="<?php echo htmlspecialchars($prepared_by_name ?? $_SESSION['name']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Contact Number:</label>
                        <input type="text" name="contact" 
                               value="<?php echo htmlspecialchars($prepared_by_contact ?? $_SESSION['contact']); ?>" readonly>
                    </div>
                </div>

                <!-- Roller Door Measurements -->
                <div class="section">
                    <h3>Roller Door Measurements</h3>
                    <div class="measurement-guide">
                        <img src="../rollerdoor.jpg" alt="Roller Door Measurement Guide" class="guide-image">
                    </div>
                    <!-- Add Section 1 and 2 measurements -->
                    <div class="measurement-sections">
                        <div class="form-group">
                            <label>Section 1 (inches):</label>
                            <input type="number" name="section1" step="0.01" 
                                   value="<?php echo htmlspecialchars($measurements['section1'] ?? ''); ?>" 
                                   required onchange="calculateSquareFeet()">
                        </div>
                        <div class="form-group">
                            <label>Section 2 (inches):</label>
                            <input type="number" name="section2" step="0.01" 
                                   value="<?php echo htmlspecialchars($measurements['section2'] ?? ''); ?>" 
                                   required onchange="calculateSquareFeet()">
                        </div>
                    </div>

                    <!-- Add remaining roller door fields -->
                    <div class="form-group">
                        <label>Outside Width:</label>
                        <input type="number" name="outside_width" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Inside Width:</label>
                        <input type="number" name="inside_width" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Door Width (inches):</label>
                        <input type="number" name="door_width" step="0.01" required onchange="calculateSquareFeet()">
                    </div>
                    
                    <div id="sqft_display" style="margin: 10px 0; font-weight: bold; color: #007bff;"></div>
                    
                    <div class="form-group">
                        <label>Tower Height:</label>
                        <input type="number" name="tower_height" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tower Type:</label>
                        <select name="tower_type" required>
                            <option value="small">Small</option>
                            <option value="large">Large</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Coil Color:</label>
                        <select name="coil_color" required>
                            <option value="coffee_brown">Coffee Brown</option>
                            <option value="black_shine">Black Shine</option>
                            <option value="blue_color">Blue Color</option>
                            <option value="butter_milk">Butter Milk</option>
                            <option value="chocolate_brown">Chocolate Brown</option>
                            <option value="black_mate">Black Mate</option>
                            <option value="beige">Beige</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Thickness:</label>
                        <select name="thickness" id="thickness" required>
                            <option value="0.6">0.6</option>
                            <option value="0.47">0.47</option>
                            <option value="custom">Custom</option>
                        </select>
                        <input type="number" name="custom_thickness" id="custom_thickness" style="display:none" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label>Covering:</label>
                        <select name="covering" required>
                            <option value="full">Full</option>
                            <option value="side">Side</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Side Lock:</label>
                        <select name="side_lock" required>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Motor:</label>
                        <select name="motor" required>
                            <option value="R">Right</option>
                            <option value="L">Left</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Fixing:</label>
                        <select name="fixing" required>
                            <option value="inside">Inside</option>
                            <option value="outside">Outside</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Down Lock:</label>
                        <select name="down_lock" required>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <!-- Wicket Door Measurements -->
                <div class="section">
                    <h3>Wicket Door</h3>
                    <div class="measurement-guide">
                        <img src="../wicketdoor.jpg" alt="Wicket Door Measurement Guide" class="guide-image">
                    </div>
                    <div class="form-group">
                        <label>Include Wicket Door:</label>
                        <input type="checkbox" name="has_wicket_door" id="has_wicket_door">
                    </div>
                    <div id="wicket-door-fields" style="display:none;">
                        <div class="form-group">
                            <label>Point 1:</label>
                            <input type="number" name="point1" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Point 2:</label>
                            <input type="number" name="point2" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Point 3:</label>
                            <input type="number" name="point3" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Point 4:</label>
                            <input type="number" name="point4" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Point 5:</label>
                            <input type="number" name="point5" step="0.01">
                        </div>
                        
                        <div class="form-group">
                            <label>Door Opening:</label>
                            <select name="door_opening">
                                <option value="inside_left">Inside Left</option>
                                <option value="inside_right">Inside Right</option>
                                <option value="outside_left">Outside Left</option>
                                <option value="outside_right">Outside Right</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Handle:</label>
                            <select name="handle">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Letter Box:</label>
                            <select name="letter_box">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" name="<?php echo $quotation_id ? 'check_quotation' : 'submit'; ?>">
                    <?php echo $quotation_id ? 'Check Measurements' : 'Create Order'; ?>
                </button>
            </form>
        </div>
    </div>

    <script src="../assets/js/order-form.js"></script>
</body>
</html>
