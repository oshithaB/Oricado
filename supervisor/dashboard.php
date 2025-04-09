<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

$pendingOrders = $conn->query("
    SELECT o.*, u.name as prepared_by_name
    FROM orders o 
    LEFT JOIN users u ON o.prepared_by = u.id
    WHERE o.status = 'pending'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        <div class="content">
            <div class="status-header">
                <h3>Order Overview</h3>
                <div class="order-status-counts">
                    <?php
                    $statuses = ['pending', 'reviewed', 'confirmed', 'completed', 'done'];
                    foreach ($statuses as $status) {
                        $count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = '$status'")->fetch_assoc()['count'];
                        echo "<a href='{$status}_orders.php' class='status-link'>";
                        echo ucfirst($status) . " ($count)";
                        echo "</a>";
                    }
                    ?>
                </div>
            </div>

            <h3>Pending Orders</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Prepared By</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['prepared_by_name']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="review_order.php?id=<?php echo $order['id']; ?>" class="button">Review</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
