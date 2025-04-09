<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_order'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    header('Location: completed_orders.php');
    exit();
}

$completedOrders = $conn->query("
    SELECT o.*, 
           COUNT(om.id) as materials_count,
           u1.name as prepared_by_name,
           u2.name as checked_by_name
    FROM orders o
    LEFT JOIN order_materials om ON o.id = om.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    WHERE o.status IN ('reviewed', 'confirmed', 'completed', 'done')
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Completed Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Completed Orders</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Materials Used</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completedOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo ucfirst($order['status']); ?></td>
                        <td><?php echo $order['materials_count']; ?> items</td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                            <?php if ($order['status'] == 'confirmed'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="complete_order" value="1">
                                <button type="submit" class="button">Mark as Completed</button>
                            </form>
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
