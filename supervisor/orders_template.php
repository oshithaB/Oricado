<?php
require_once '../config/config.php';
require_once '../includes/order_status.php';
checkAuth(['supervisor']);

$status = $status ?? ORDER_STATUS_PENDING;
$title = ucfirst($status) . ' Orders';

$orders = $conn->query("
    SELECT o.*, 
           rdm.*,
           wdm.*,
           u1.name as prepared_by_name,
           u1.contact as prepared_by_contact
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    WHERE o.status = '$status'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['complete_order'])) {
        $order_id = $_POST['order_id'];
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        header("Location: {$status}_orders.php");
        exit();
    }
}
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
            <div class="status-header">
                <h3><?php echo $title; ?> (<?php echo count($orders); ?>)</h3>
                <div class="order-status-counts">
                    <?php
                    $statuses = ['pending', 'reviewed', 'confirmed', 'completed', 'done'];
                    foreach ($statuses as $statusType) {
                        $count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = '$statusType'")->fetch_assoc()['count'];
                        echo "<a href='{$statusType}_orders.php' class='status-link " . ($status == $statusType ? 'active' : '') . "'>";
                        echo ucfirst($statusType) . " ($count)";
                        echo "</a>";
                    }
                    ?>
                </div>
            </div>
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
                    <?php foreach ($orders as $order): ?>
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
                            <?php if ($status == ORDER_STATUS_PENDING): ?>
                                <div class="order-actions">
                                    <a href="review_order.php?id=<?php echo $order['id']; ?>" class="button">Add Materials</a>
                                    <form method="POST" class="materials-quick-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="button" onclick="showMaterialForm(<?php echo $order['id']; ?>)" class="button">Quick Add Materials</button>
                                    </form>
                                </div>
                            <?php elseif ($status == ORDER_STATUS_CONFIRMED): ?>
                                <div class="order-actions">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="complete_order" value="1">
                                        <button type="submit" onclick="return confirm('Mark this order as completed?')" class="button">Mark as Completed</button>
                                    </form>
                                </div>
                                <div class="materials-list">
                                    <h4>Materials Added:</h4>
                                    <?php
                                    $materials = $conn->query("
                                        SELECT m.name, m.type, om.quantity, m.unit 
                                        FROM order_materials om 
                                        JOIN materials m ON om.material_id = m.id 
                                        WHERE om.order_id = {$order['id']}
                                    ")->fetch_all(MYSQLI_ASSOC);
                                    ?>           
                                    <table class="mini-table">
                                        <?php foreach ($materials as $material): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                                            <td><?php echo $material['quantity'] . ' ' . $material['unit']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            <?php elseif ($status == ORDER_STATUS_REVIEWED): ?>
                                <div class="order-actions">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                                    <a href="edit_materials.php?id=<?php echo $order['id']; ?>" class="button">Edit Materials</a>
                                </div>
                                <span class="status-note">Waiting for office staff confirmation</span>
                            <?php else: ?>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
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
