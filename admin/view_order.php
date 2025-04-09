<?php
require_once '../config/config.php';
checkAuth(['admin']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: orders.php');
    exit();
}

$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           u3.name as approved_by_name
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN users u3 ON o.approved_by = u3.id
    WHERE o.id = $order_id")->fetch_assoc();

$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Order</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Order Details #<?php echo $order_id; ?></h2>
            <a href="orders.php">Back to Orders</a>
        </nav>

        <div class="content">
            <!-- Order Details -->
            <div class="order-section">
                <h3>Order Information</h3>
                <p>Customer: <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p>Status: <?php echo ucfirst($order['status']); ?></p>
                <p>Total Price: Rs. <?php echo number_format($order['total_price'], 2); ?></p>
                <p>Created: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
            </div>

            <!-- Materials Used -->
            <div class="materials-section">
                <h3>Materials Used</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Details</th>
                            <th>Quantity Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                            <td>
                                <?php if ($material['type'] == 'coil'): ?>
                                    Color: <?php echo $material['color']; ?><br>
                                    Thickness: <?php echo $material['thickness']; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $material['used_quantity'] . ' ' . $material['unit']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
