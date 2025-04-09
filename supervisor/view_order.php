<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u1.name as prepared_by_name,
           u1.contact as prepared_by_contact
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    WHERE o.id = $order_id
")->fetch_assoc();

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
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <div class="order-section">
                <h3>Order Information</h3>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                <p><strong>Prepared By:</strong> <?php echo htmlspecialchars($order['prepared_by_name']); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['prepared_by_contact']); ?></p>
                
                <?php if ($order['status'] == 'confirmed'): ?>
                <form method="POST" action="completed_orders.php">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <input type="hidden" name="complete_order" value="1">
                    <button type="submit" class="button">Mark as Completed</button>
                </form>
                <?php endif; ?>
            </div>

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
