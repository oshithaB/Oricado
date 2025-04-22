<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$supervisors = $conn->query("SELECT * FROM users WHERE role = 'supervisor'")->fetch_all(MYSQLI_ASSOC);
$coils = $conn->query("SELECT * FROM materials WHERE type = 'coil'")->fetch_all(MYSQLI_ASSOC);

// Check if this is from a quotation
$quotation_id = $_GET['quotation_id'] ?? null;
if ($quotation_id) {
    // Get quotation details
    $quotation = $conn->query("
        SELECT * FROM quotations 
        WHERE id = $quotation_id AND type = 'order'
    ")->fetch_assoc();

    if ($quotation) {
        // Pre-fill customer details
        $customer_name = $quotation['customer_name'];
        $customer_contact = $quotation['customer_contact'];
        
        // Get customer address from contacts
        $customer = $conn->query("
            SELECT address FROM contacts 
            WHERE name = '$customer_name' AND mobile = '$customer_contact'
        ")->fetch_assoc();
        
        $customer_address = $customer['address'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Insert order with customer details
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_contact, customer_address, prepared_by, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssi", $_POST['customer_name'], $_POST['customer_contact'], $_POST['customer_address'], $_SESSION['user_id']);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Insert roller door measurements
        $stmt = $conn->prepare("INSERT INTO roller_door_measurements (
            order_id, outside_width, inside_width, door_width, tower_height,
            tower_type, coil_color, thickness, covering, side_lock, motor,
            fixing, down_lock
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $sideLock = isset($_POST['side_lock']) ? 1 : 0;
        $downLock = isset($_POST['down_lock']) ? 1 : 0;
        
        $stmt->bind_param("iddddssdsissi", 
            $order_id, $_POST['outside_width'], $_POST['inside_width'],
            $_POST['door_width'], $_POST['tower_height'], $_POST['tower_type'],
            $_POST['coil_color'], $_POST['thickness'], $_POST['covering'],
            $sideLock, $_POST['motor'], $_POST['fixing'],
            $downLock
        );
        $stmt->execute();

        // Insert wicket door measurements if exists
        if (isset($_POST['has_wicket_door']) && $_POST['has_wicket_door'] == 'on') {
            $stmt = $conn->prepare("INSERT INTO wicket_door_measurements (
                order_id, point1, point2, point3, point4, point5,
                thickness, door_opening, handle, letter_box, coil_color
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $handle = isset($_POST['handle']) ? 1 : 0;
            $letterBox = isset($_POST['letter_box']) ? 1 : 0;
            
            $stmt->bind_param("idddddssiss",
                $order_id, $_POST['point1'], $_POST['point2'], $_POST['point3'],
                $_POST['point4'], $_POST['point5'], $_POST['thickness'],
                $_POST['door_opening'], $handle, $letterBox,
                $_POST['coil_color']
            );
            $stmt->execute();
        }

        // Commit the transaction first
        $conn->commit();

        // After successful commit, get complete order data for PDF
        $order = $conn->query("
            SELECT o.*, rdm.*, wdm.*,
                   u1.name as prepared_by_name,
                   u1.contact as prepared_by_contact
            FROM orders o 
            LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
            LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
            LEFT JOIN users u1 ON o.prepared_by = u1.id
            WHERE o.id = $order_id
        ")->fetch_assoc();

        // Generate PDF
        require_once '../includes/PDFGenerator.php';
        $pdf = new PDFGenerator($order_id);
        $pdf->generatePDF($order, 'new_order');
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error creating order: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Order</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                        <input type="text" name="prepared_by" value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Contact Number:</label>
                        <input type="text" name="contact" value="<?php echo isset($_SESSION['contact']) ? htmlspecialchars($_SESSION['contact']) : ''; ?>" readonly>
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
                            <label>Section 1:</label>
                            <input type="number" name="section1" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Section 2:</label>
                            <input type="number" name="section2" step="0.01" required>
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
                        <label>Door Width:</label>
                        <input type="number" name="door_width" step="0.01" required>
                    </div>
                    
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

                <button type="submit">Create Order</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/order-form.js"></script>
</body>
</html>
