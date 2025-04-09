<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

// Get order and materials details
$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u1.name as prepared_by_name,
           u2.name as checked_by_name
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    WHERE o.id = $order_id")->fetch_assoc();

$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        $new_status = $_POST['action'] == 'done' ? 'done' : 'confirmed';
        
        // Update order status and price
        $stmt = $conn->prepare("UPDATE orders SET status = ?, total_price = ? WHERE id = ?");
        $stmt->bind_param("sdi", $new_status, $_POST['total_price'], $order_id);
        $stmt->execute();

        if ($new_status == 'confirmed') {
            // Deduct materials from stock
            foreach ($materials as $material) {
                $stmt = $conn->prepare("
                    UPDATE materials 
                    SET quantity = quantity - ? 
                    WHERE id = ?
                ");
                $stmt->bind_param("di", $material['used_quantity'], $material['id']);
                $stmt->execute();
            }
        }

        $conn->commit();
        header('Location: dashboard.php?success=1');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating order: " . $e->getMessage();
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
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        
        <div class="content">
            <h2>Order #<?php echo $order_id; ?></h2>
            
            <div class="materials-section">
                <h3>Materials List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Details</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                            <td>
                                <?php if ($material['type'] == 'coil'): ?>
                                    Color: <?php echo htmlspecialchars($material['color']); ?><br>
                                    Thickness: <?php echo $material['thickness']; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $material['used_quantity'] . ' ' . $material['unit']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($order['status'] == 'reviewed'): ?>
            <form method="POST" class="confirm-form" onsubmit="return confirm('Are you sure? This will deduct materials from stock.');">
                <div class="form-group">
                    <label>Total Price:</label>
                    <input type="number" name="total_price" step="0.01" required>
                </div>
                <input type="hidden" name="action" value="confirm">
                <button type="submit">Confirm Order</button>
            </form>
            <?php elseif ($order['status'] == 'confirmed'): ?>
            <form method="POST" class="mark-done-form">
                <input type="hidden" name="action" value="done">
                <input type="hidden" name="total_price" value="<?php echo $order['total_price']; ?>">
                <button type="submit">Mark as Done</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
