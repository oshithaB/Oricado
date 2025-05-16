<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
$calculated_sqft = $_GET['calculated_sqft'] ?? 0;

if (!$quotation_id) {
    header('Location: quotations.php');
    exit();
}

// Get quotation item details with better error handling
$query = "
    SELECT q.*, qi.*, m.price as base_price, qi.name as material_name
    FROM quotations q
    JOIN quotation_items qi ON q.id = qi.quotation_id
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE q.id = ? AND qi.name LIKE '%Roller Door%'
    LIMIT 1
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$result = $stmt->get_result();
$quotation = $result->fetch_assoc();

if (!$quotation) {
    $_SESSION['error_message'] = "Quotation or coil item not found";
    header('Location: quotations.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        if (isset($_POST['create_order']) && isset($_SESSION['order_data'])) {
            // Create order using updated quotation data and stored measurements
            $orderData = $_SESSION['order_data'];
            $calculated_sqft = $_SESSION['calculated_sqft'];

            $stmt = $conn->prepare("
                INSERT INTO orders (
                    customer_name, customer_contact, customer_address, 
                    prepared_by, status, quotation_id, total_sqft, total_price,
                    balance_amount
                ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?)
            ");

            // Get updated quotation total
            $quotation = $conn->query("SELECT total_amount FROM quotations WHERE id = $quotation_id")->fetch_assoc();
            $total_price = $quotation['total_amount'];

            $stmt->bind_param("sssisddd", 
                $orderData['customer_name'],
                $orderData['customer_contact'],
                $orderData['customer_address'],
                $_SESSION['user_id'],
                $quotation_id,
                $calculated_sqft,
                $total_price,
                $total_price // Initial balance equals total price
            );
            
            $stmt->execute();
            $order_id = $conn->insert_id;

            // Insert measurements
            $measureQuery = $conn->prepare("INSERT INTO roller_door_measurements (
                order_id, section1, section2, outside_width, inside_width, door_width, 
                tower_height, tower_type, coil_color, thickness, covering, 
                side_lock, motor, fixing, down_lock
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare variables for bind_param
            $section1 = $orderData['section1'];
            $section2 = $orderData['section2'];
            $outsideWidth = $orderData['outside_width'];
            $insideWidth = $orderData['inside_width'];
            $doorWidth = $orderData['door_width'];
            $towerHeight = $orderData['tower_height'];
            $towerType = $orderData['tower_type'];
            $coilColor = $orderData['coil_color'];
            $thickness = $orderData['thickness'];
            $covering = $orderData['covering'];
            $sideLock = $orderData['side_lock'];
            $motor = $orderData['motor'];
            $fixing = $orderData['fixing'];
            $downLock = intval($orderData['down_lock']);

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

            // Handle wicket door if exists
            if (isset($orderData['has_wicket_door'])) {
                $wicketQuery = $conn->prepare("INSERT INTO wicket_door_measurements (
                    order_id, point1, point2, point3, point4, point5,
                    thickness, door_opening, handle, letter_box, coil_color
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                // Prepare variables for bind_param
                $point1 = floatval($orderData['point1']);
                $point2 = floatval($orderData['point2']);
                $point3 = floatval($orderData['point3']);
                $point4 = floatval($orderData['point4']);
                $point5 = floatval($orderData['point5']);
                $wThickness = $orderData['thickness'];
                $doorOpening = $orderData['door_opening'];
                $handle = isset($orderData['handle']) ? 1 : 0;
                $letterBox = isset($orderData['letter_box']) ? 1 : 0;
                $wCoilColor = $orderData['coil_color'];

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

            $conn->commit();
            unset($_SESSION['order_data'], $_SESSION['calculated_sqft']);
            $_SESSION['success_message'] = "Order #$order_id created successfully!";
            header('Location: pending_orders.php');
            exit();
        } else {
            // Update quotation item with new square footage
            $stmt = $conn->prepare("
                UPDATE quotation_items 
                SET quantity = ?, 
                    price = ?, 
                    discount = ?, 
                    taxes = ?, 
                    amount = ?
                WHERE quotation_id = ? AND material_id = ?
            ");

            if (!$stmt) {
                throw new Exception("Error preparing update statement: " . $conn->error);
            }

            $quantity = floatval($_POST['quantity']);
            $price = floatval($_POST['price']);
            $discount = floatval($_POST['discount']);
            $taxes = floatval($_POST['taxes']);
            
            // Calculate new amount
            $amount = $quantity * $price;
            $amount = $amount * (1 - ($discount/100));
            $amount = $amount * (1 + ($taxes/100));

            $material_id = intval($_POST['material_id']);

            if (!$stmt->bind_param("dddddii", 
                $quantity, $price, $discount, $taxes, $amount,
                $quotation_id, $material_id
            )) {
                throw new Exception("Error binding parameters: " . $stmt->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Error updating quotation item: " . $stmt->error);
            }

            // Update quotation total
            $stmt = $conn->prepare("
                UPDATE quotations 
                SET total_amount = (
                    SELECT SUM(amount) FROM quotation_items WHERE quotation_id = ?
                ),
                is_updated = 1
                WHERE id = ?
            ");

            if (!$stmt->bind_param("ii", $quotation_id, $quotation_id)) {
                throw new Exception("Error binding parameters for total update: " . $stmt->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Error updating quotation total: " . $stmt->error);
            }

            $_SESSION['quotation_updated'] = true;
            $conn->commit();
            
            // Refresh page to show create order button
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quotation_id . "&calculated_sqft=" . $_SESSION['calculated_sqft']);
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        // Log the error
        error_log("Error in edit_quotation.php: " . $e->getMessage());
        // Refresh page to show error
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quotation_id . "&calculated_sqft=" . $calculated_sqft);
        exit();
    }
}
?>
                
<!DOCTYPE html>
<html>
<head>
    <title>Edit Quotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Edit Quotation</h2>
                <div class="alert warning">
                    <p>Square feet mismatch detected:</p>
                    <ul>
                        <li>Calculated from measurements: <strong><?php echo number_format($calculated_sqft, 2); ?> sqft</strong></li>
                        <li>Current in quotation: <strong><?php echo number_format($quotation['quantity'] ?? 0, 2); ?> sqft</strong></li>
                        <li>Material: <strong><?php echo htmlspecialchars($quotation['material_name'] ?? 'Unknown'); ?></strong></li>
                    </ul>
                </div>
                <form method="POST">
                    <input type="hidden" name="material_id" value="<?php echo $quotation['material_id']; ?>">
                    
                    <div class="form-group">
                        <label>Square Feet:</label>
                        <input type="number" name="quantity" step="0.01" 
                               value="<?php echo $calculated_sqft; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Price per Unit:</label>
                        <input type="number" name="price" step="0.01" 
                               value="<?php echo $quotation['price']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Discount (%):</label>
                        <input type="number" name="discount" step="0.01" 
                               value="<?php echo $quotation['discount']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Taxes (%):</label>
                        <input type="number" name="taxes" step="0.01" 
                               value="<?php echo $quotation['taxes']; ?>" required>
                    </div>

                    <div class="form-group">
                        <button type="submit">Update Quotation</button>
                        <?php if (isset($_SESSION['quotation_updated'])): ?>
                            <button type="submit" name="create_order">Create Order</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
