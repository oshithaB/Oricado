<?php
require_once '../config/config.php';
require_once '../includes/order_status.php';
checkAuth(['office_staff']);

$status = $status ?? ORDER_STATUS_PENDING;
$title = ucfirst($status) . ' Orders';

$orders = $conn->query("
    SELECT o.*, u1.name as prepared_by_name, u2.name as checked_by_name 
    FROM orders o 
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    WHERE o.status = '$status'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h3><?php echo $title; ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <?php if ($status == ORDER_STATUS_REVIEWED): ?>
                                <!-- For reviewed orders, show price confirmation form -->
                                <div class="price-confirmation">
                                    <form method="POST" action="confirm_order.php">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <div class="form-group">
                                            <label>Enter Total Price:</label>
                                            <input type="number" name="total_price" step="0.01" required>
                                        </div>
                                        <button type="submit" name="action" value="confirm" class="button">Confirm Order</button>
                                    </form>
                                </div>
                            <?php elseif ($status == ORDER_STATUS_COMPLETED): ?>
                                <!-- For completed orders, show mark as done option -->
                                <div class="order-actions">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="mark_done" value="1">
                                        <button type="submit" onclick="return confirm('Mark this order as done?')" class="button">Mark as Done</button>
                                    </form>
                                </div>
                            <?php elseif ($status == ORDER_STATUS_PENDING): ?>
                                <!-- For pending orders, show edit option -->
                                <div class="order-actions">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View</a>
                                    <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="button">Edit</a>
                                </div>
                            <?php else: ?>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                            <?php endif; ?>

                            <?php if (in_array($status, [ORDER_STATUS_REVIEWED, ORDER_STATUS_CONFIRMED, ORDER_STATUS_COMPLETED])): ?>
                                <!-- Show materials list for these statuses -->
                                <div class="materials-list">
                                    <h4>Materials Required:</h4>
                                    <table class="mini-table">
                                        <?php
                                        $materials = $conn->query("
                                            SELECT m.name, m.type, om.quantity, m.unit 
                                            FROM order_materials om 
                                            JOIN materials m ON om.material_id = m.id 
                                            WHERE om.order_id = {$order['id']}
                                        ")->fetch_all(MYSQLI_ASSOC);
                                        
                                        foreach ($materials as $material): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                                            <td><?php echo $material['quantity'] . ' ' . $material['unit']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
