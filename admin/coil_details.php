<?php
require_once '../config/config.php';
checkAuth(['admin']);

$material_id = $_GET['id'] ?? null;
if (!$material_id) {
    header('Location: dashboard.php');
    exit();
}

// Fetch material (not just coil)
$material = $conn->query("
    SELECT id, name, type, thickness, color, quantity, unit, price, saleprice 
    FROM materials WHERE id = $material_id
")->fetch_assoc();

if (!$material) {
    header('Location: dashboard.php');
    exit();
}

// Usage and revenue for all materials
$usage = $conn->query("
    SELECT o.id, o.customer_name, o.total_price, o.created_at, om.quantity
    FROM orders o
    JOIN order_materials om ON o.id = om.order_id
    WHERE om.material_id = $material_id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$total_usage = array_sum(array_column($usage, 'quantity'));
$total_revenue = array_sum(array_column($usage, 'total_price'));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Material Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Material Details</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <div class="coil-info">
                <h3><?php echo htmlspecialchars($material['name']); ?></h3>
                <?php if ($material['type'] === 'coil'): ?>
                    <p>Width: 914mm</p>
                <?php endif; ?>
                <?php if (!empty($material['color'])): ?>
                    <p>Color: <?php echo htmlspecialchars($material['color']); ?></p>
                <?php endif; ?>
                <?php if (!empty($material['thickness'])): ?>
                    <p>Thickness: <?php echo $material['thickness']; ?>mm</p>
                <?php endif; ?>
                <p>Type: <?php echo htmlspecialchars($material['type']); ?></p>
                <p>Current Stock: <?php echo number_format($material['quantity'], 2) . ' ' . $material['unit']; ?></p>
                <p>Purchase Price: Rs. <?php echo number_format($material['price'], 2); ?></p>
                <p>Sale Price: Rs. <?php echo number_format($material['saleprice'], 2); ?></p>
                <p>Total Usage: <?php echo number_format($total_usage, 2) . ' ' . $material['unit']; ?></p>
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
                        <td><?php echo number_format($use['quantity'], 2) . ' ' . $material['unit']; ?></td>
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
