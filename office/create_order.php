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
        $outside_width_inches = floatval($_POST['outside_width']);
        
        // Convert to feet
        $section1_feet = $section1_inches / 12;
        $section2_feet = $section2_inches / 12;
        $outside_width_feet = $outside_width_inches / 12;
        
        // Calculate total height and square feet
        $height = $section1_feet + $section2_feet;
        $calculated_sqft = $height * $outside_width_feet;

        // Get quotation VAT information
        $quotation_details = $conn->query("
            SELECT is_vat_quotation FROM quotations WHERE id = $quotation_id
        ")->fetch_assoc();

        // Store all form data in session
        $_SESSION['order_data'] = $_POST;
        $_SESSION['calculated_sqft'] = $calculated_sqft;
        $_SESSION['is_vat_quotation'] = ($quotation_details['is_vat_quotation'] ?? false);

        // Redirect to edit quotation
        header("Location: edit_quotation.php?id=$quotation_id&calculated_sqft=$calculated_sqft");
        exit();
    }

    $conn->begin_transaction();
    try {
        // Calculate measurements
        $section1_inches = floatval($_POST['section1']);
        $section2_inches = floatval($_POST['section2']);
        $outside_width_inches = floatval($_POST['outside_width']);
        
        // Convert to feet
        $section1_feet = $section1_inches / 12;
        $section2_feet = $section2_inches / 12;
        $outside_width_feet = $outside_width_inches / 12;
        
        // Calculate square feet
        $height = $section1_feet + $section2_feet;
        $calculated_sqft = $height * $outside_width_feet;

        // Set initial status
        $status = 'pending';

        // Prepare statement based on quotation existence
        if (!$quotation_id) {
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    customer_name, customer_contact, customer_address, 
                    prepared_by, status, total_sqft, total_price, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $customer_name = $_POST['customer_name'];
            $customer_contact = $_POST['customer_contact'];
            $customer_address = $_POST['customer_address'];
            $user_id = $_SESSION['user_id'];
            $total_price = 0;

            $stmt->bind_param("sssissd", 
                $customer_name, $customer_contact, $customer_address,
                $user_id, $status, $calculated_sqft, $total_price
            );
        } else {
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    customer_name, customer_contact, customer_address, 
                    prepared_by, status, quotation_id, total_sqft, total_price, balance_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $customer_name = $_POST['customer_name'];
            $customer_contact = $_POST['customer_contact'];
            $customer_address = $_POST['customer_address'];
            $user_id = $_SESSION['user_id'];
            $total_price = $quotation['total_amount'] ?? 0;
            $balance = $total_price;

            $stmt->bind_param("sssisiddd", 
                $customer_name, $customer_contact, $customer_address,
                $user_id, $status, $quotation_id, $calculated_sqft, $total_price, $balance
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
            motor, fixing
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $section1 = $section1_inches;
        $section2 = $section2_inches;
        $outsideWidth = floatval($_POST['outside_width']);
        $insideWidth = floatval($_POST['inside_width']);
        $doorWidth = floatval($_POST['door_width']);
        $towerHeight = floatval($_POST['tower_height']);
        $towerType = $_POST['tower_type'];
        $coilColor = ($_POST['coil_color'] === 'custom') ? $_POST['custom_coil_color'] : $_POST['coil_color'];
        $thickness = $_POST['thickness'];
        $covering = $_POST['covering'];
        $motor = $_POST['motor'];
        $fixing = $_POST['fixing'];

        $measureQuery->bind_param('iddddddssssss',
            $order_id, $section1, $section2, $outsideWidth, $insideWidth, $doorWidth,
            $towerHeight, $towerType, $coilColor, $thickness, $covering, 
            $motor, $fixing
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
                $order_id, $point1, $point2, $point3, $point4, $point5, 
                $wThickness, $doorOpening, $handle, $letterBox, $wCoilColor
            );

            $wicketQuery->execute();
        }

        // After successfully creating order and saving measurements, check quotation if exists
        if ($quotation_id) {
            // First get quotation data with VAT status
            $quotation_data = $conn->query("
                SELECT q.is_vat_quotation, q.id, q.type
                FROM quotations q
                WHERE q.id = $quotation_id
            ")->fetch_assoc();

            // Get roller door item
            $roller_door = $conn->query("
                SELECT qi.* 
                FROM quotation_items qi
                WHERE qi.quotation_id = $quotation_id 
                AND qi.name LIKE '%Powder-Coated Roller Door%'
                LIMIT 1
            ")->fetch_assoc();

            if ($roller_door && abs($calculated_sqft - $roller_door['quantity']) > 0.01) {
                error_log("Starting update process - Quotation ID: $quotation_id");

                try {
                    $conn->begin_transaction();

                    // 1. Update roller door quantity and amount
                    $price_per_sqft = $roller_door['price'] ?? 0;
                    $new_door_amount = $calculated_sqft * $price_per_sqft;
                    
                    $update_door = $conn->prepare("
                        UPDATE quotation_items 
                        SET quantity = ?, amount = ?
                        WHERE quotation_id = ? AND name LIKE '%Powder-Coated Roller Door%'
                    ");
                    
                    if (!$update_door->bind_param("ddi", $calculated_sqft, $new_door_amount, $quotation_id)) {
                        throw new Exception("Failed to bind door parameters");
                    }
                    
                    if (!$update_door->execute()) {
                        throw new Exception("Failed to update door: " . $update_door->error);
                    }

                    // 2. Calculate new subtotal from updated items
                    $get_items = $conn->query("
                        SELECT SUM(amount) as new_subtotal 
                        FROM quotation_items 
                        WHERE quotation_id = $quotation_id
                    ");
                    $subtotal_row = $get_items->fetch_assoc();
                    $new_subtotal = round(floatval($subtotal_row['new_subtotal']), 2);

                    // 3. Calculate VAT if applicable
                    $is_vat_quotation = (bool)$quotation_data['is_vat_quotation'];
                    $new_vat = $is_vat_quotation ? round($new_subtotal * 0.18, 2) : 0;
                    $new_total_amount = $new_subtotal + $new_vat;

                    error_log("Updating quotation - Subtotal: $new_subtotal, VAT: $new_vat, Total: $new_total_amount");

                    // 4. Update quotation with new values - THIS IS THE KEY PART
                    $update_quotation = $conn->prepare("
                        UPDATE quotations 
                        SET subtotal = ?,
                            vat = ?,
                            total_amount = ?,
                            is_updated = 1
                        WHERE id = ?
                    ");
                    
                    if (!$update_quotation->bind_param("dddi", 
                        $new_subtotal,
                        $new_vat,
                        $new_total_amount,
                        $quotation_id
                    )) {
                        throw new Exception("Failed to bind quotation parameters");
                    }
                    
                    if (!$update_quotation->execute()) {
                        throw new Exception("Failed to update quotation: " . $update_quotation->error);
                    }

                    // 5. Verify the update immediately
                    $verify = $conn->query("
                        SELECT subtotal, vat, total_amount, is_vat_quotation 
                        FROM quotations 
                        WHERE id = $quotation_id
                    ")->fetch_assoc();

                    if ($verify['subtotal'] != $new_subtotal || 
                        $verify['vat'] != $new_vat || 
                        $verify['total_amount'] != $new_total_amount) {
                        throw new Exception("Values not updated correctly in database");
                    }

                    // 6. Update order with final amount
                    $update_order = $conn->prepare("
                        UPDATE orders 
                        SET total_price = ?,
                            balance_amount = ? 
                        WHERE id = ?
                    ");
                    
                    if (!$update_order->bind_param("ddi", $new_total_amount, $new_total_amount, $order_id)) {
                        throw new Exception("Failed to bind order parameters");
                    }
                    
                    if (!$update_order->execute()) {
                        throw new Exception("Failed to update order: " . $update_order->error);
                    }

                    // 7. Commit all changes
                    $conn->commit();

                    $_SESSION['success_message'] = "Order created and quotation updated successfully! " .
                        "Subtotal: Rs. " . number_format($new_subtotal, 2) . 
                        ($is_vat_quotation ? ", VAT (18%): Rs. " . number_format($new_vat, 2) : "") . 
                        ", Total: Rs. " . number_format($new_total_amount, 2);

                    header("Location: quotations.php");
                    exit();

                } catch (Exception $e) {
                    $conn->rollback();
                    error_log("Error updating quotation: " . $e->getMessage());
                    throw new Exception("Failed to update quotation: " . $e->getMessage());
                }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
    function calculateSquareFeet() {
        const section1 = parseFloat(document.getElementsByName('section1')[0].value) || 0;
        const section2 = parseFloat(document.getElementsByName('section2')[0].value) || 0;
        const outsideWidth = parseFloat(document.getElementsByName('outside_width')[0].value) || 0;
        const heightFeet = (section1 + section2) / 12;
        const widthFeet = outsideWidth / 12;
        const squareFeet = heightFeet * widthFeet;
        if (!isNaN(squareFeet)) {
            document.getElementById('sqft_display').textContent = 
                `Square Feet: ${squareFeet.toFixed(2)} (Height: ${heightFeet.toFixed(2)}' × Width: ${widthFeet.toFixed(2)}')`;
        }
    }

    function checkCustomColor(value) {
        const customInput = document.getElementById('custom_coil_color');
        if (value === 'custom') {
            customInput.style.display = 'block';
            customInput.required = true;
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
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
                    font-family: 'Roman Classic', serif;
                    font-size: 30px;
                    font-weight: bold;
                    color: rgb(249, 243, 243);
                    text-align: center;
                    margin-bottom: 20px;
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
                    <div class="form-group">
                        <label>Outside Width:</label>
                        <input type="number" name="outside_width" step="0.01" required onchange="calculateSquareFeet()">
                    </div>
                    <div class="form-group">
                        <label>Inside Width:</label>
                        <input type="number" name="inside_width" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Door Width (inches):</label>
                        <input type="number" name="door_width" step="0.01" required>
                    </div>
                    <div id="sqft_display" style="margin: 10px 0; font-weight: bold; color: #007bff;"></div>
                    <div class="form-group">
                        <label>Tower Height:</label>
                        <input type="number" name="tower_height" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Tower Type:</label>
                        <select name="tower_type" required>
                            <option value="none">None</option>
                            <option value="small">Small</option>
                            <option value="large">Large</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Coil Color:</label>
                        <select name="coil_color" id="coil_color" onchange="checkCustomColor(this.value)">
                            <option value="coffee_brown">Coffee Brown</option>
                            <option value="black_shine">Black Shine</option>
                            <option value="blue_color">Blue Color</option>
                            <option value="butter_milk">Butter Milk</option>
                            <option value="chocolate_brown">Chocolate Brown</option>
                            <option value="black_mate">Black Mate</option>
                            <option value="beige">Beige</option>
                            <option value="custom">Custom Color</option>
                        </select>
                        <input type="text" name="custom_coil_color" id="custom_coil_color" 
                               style="display:none; margin-top:5px;" placeholder="Enter custom color">
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
        </div>form>
    </div>div>
    <script src="../assets/js/order-form.js"></script>
</body>ript src="../assets/js/order-form.js"></script>
</html>
</html>