<?php
require_once '../config/config.php';
checkAuth(['admin']);

$coil_id = $_GET['id'] ?? null;
if (!$coil_id) {
    header('Location: dashboard.php');
    exit();
}

$coil = $conn->query("
    SELECT * FROM materials WHERE id = $coil_id AND type = 'coil'
")->fetch_assoc();

$usage = $conn->query("
    SELECT o.id, o.customer_name, o.total_price, o.created_at, om.quantity
    FROM orders o
    JOIN order_materials om ON o.id = om.order_id
    WHERE om.material_id = $coil_id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$total_usage = array_sum(array_column($usage, 'quantity'));
$total_revenue = array_sum(array_column($usage, 'total_price'));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coil Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Coil Details</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <div class="coil-info">
                <h3><?php echo htmlspecialchars($coil['name']); ?></h3>
                <p>Color: <?php echo htmlspecialchars($coil['color']); ?></p>
                <p>Thickness: <?php echo $coil['thickness']; ?></p>
                <p>Current Stock: <?php echo $coil['quantity'] . ' ' . $coil['unit']; ?></p>
                <p>Total Usage: <?php echo number_format($total_usage, 2) . ' ' . $coil['unit']; ?></p>
                <p>Total Revenue: Rs. <?php echo number_format($total_revenue, 2); ?></p>
            </div>

            <h3>Usage History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Quantity Used</th>
                        <th>Order Total</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usage as $use): ?>
                    <tr>
                        <td>#<?php echo $use['id']; ?></td>
                        <td><?php echo htmlspecialchars($use['customer_name']); ?></td>
                        <td><?php echo number_format($use['quantity'], 2) . ' ' . $coil['unit']; ?></td>
                        <td>Rs. <?php echo number_format($use['total_price'], 2); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($use['created_at'])); ?></td>
                        <td>
                            <a href="view_order.php?id=<?php echo $use['id']; ?>" class="button">View Order</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
