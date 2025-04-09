<?php
require_once '../config/config.php';
checkAuth(['admin']);

$orders = $conn->query("
    SELECT o.*, 
    u1.name as prepared_by_name,
    u2.name as checked_by_name,
    COUNT(om.id) as materials_count,
    SUM(CASE WHEN m.type = 'coil' THEN om.quantity ELSE 0 END) as total_coil_sqft
    FROM orders o
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN order_materials om ON o.id = om.order_id
    LEFT JOIN materials m ON om.material_id = m.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders Overview</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Orders Overview</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total Coil (sqft)</th>
                        <th>Total Price</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo ucfirst($order['status']); ?></td>
                        <td><?php echo number_format($order['total_coil_sqft'], 2); ?></td>
                        <td>Rs. <?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
