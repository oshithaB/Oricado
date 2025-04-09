<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

$pendingOrders = $conn->query("
    SELECT o.*, 
           rdm.*,
           wdm.*,
           u.name as prepared_by_name,
           u.contact as prepared_by_contact
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u ON o.prepared_by = u.id
    WHERE o.status = 'pending'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Pending Orders</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Prepared By</th>
                        <th>Created Date</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['prepared_by_name']); ?><br>
                            <?php echo htmlspecialchars($order['prepared_by_contact']); ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <strong>Roller Door:</strong><br>
                            Width: <?php echo $order['door_width']; ?><br>
                            Height: <?php echo $order['tower_height']; ?><br>
                            Type: <?php echo ucfirst($order['tower_type']); ?>
                            <?php if ($order['point1']): ?>
                                <br><strong>Wicket Door:</strong> Yes
                            <?php endif; ?>
                        </td>
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
