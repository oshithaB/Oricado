<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: reviewed_orders.php');
    exit();
}

// Get order details including quotation amount
$order = $conn->query("
    SELECT o.*, q.total_amount as quotation_amount
    FROM orders o
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.id = $order_id
")->fetch_assoc();

if (!$order) {
    $_SESSION['error_message'] = "Order not found";
    header('Location: reviewed_orders.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Use quotation amount if available, otherwise use POST value
        $total_price = $order['quotation_amount'] ?? $_POST['total_price'];

        // Get order materials
        $materials = $conn->query("
            SELECT om.material_id, om.quantity, m.quantity as current_stock
            FROM order_materials om
            JOIN materials m ON om.material_id = m.id
            WHERE om.order_id = $order_id
        ")->fetch_all(MYSQLI_ASSOC);

        // Check stock availability
        foreach ($materials as $material) {
            if ($material['current_stock'] < $material['quantity']) {
                throw new Exception("Insufficient stock for material ID: " . $material['material_id']);
            }
        }

        // Deduct materials from stock
        foreach ($materials as $material) {
            $new_quantity = $material['current_stock'] - $material['quantity'];
            $material_id = $material['material_id'];
            
            $stmt = $conn->prepare("UPDATE materials SET quantity = ? WHERE id = ?");
            $stmt->bind_param("di", $new_quantity, $material_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating stock for material ID: " . $material_id);
            }
        }

        // Update order status
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'confirmed', 
                total_price = ?,
                material_cost = (
                    SELECT SUM(m.price * om.quantity)
                    FROM order_materials om
                    JOIN materials m ON om.material_id = m.id
                    WHERE om.order_id = ?
                )
            WHERE id = ?
        ");
        
        $stmt->bind_param("dii", $total_price, $order_id, $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Error confirming order");
        }

        $conn->commit();
        $_SESSION['success_message'] = "Order confirmed and materials deducted from stock.";
        header('Location: confirmed_orders.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: confirm_order.php?id=' . $order_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm Order</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Confirm Order #<?php echo $order_id; ?></h2>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert error">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="confirm-form">
                    <div class="form-group">
                        <label>Total Price (Rs.):</label>
                        <input type="number" name="total_price" 
                               value="<?php echo $order['quotation_amount'] ?? ''; ?>" 
                               step="0.01" required <?php echo $order['quotation_amount'] ? 'readonly' : ''; ?>>
                        <?php if ($order['quotation_amount']): ?>
                            <small>Price automatically set from quotation</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <a href="reviewed_orders.php" class="button">Cancel</a>
                        <button type="submit" class="button primary">Confirm Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
