<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

// Handle completing an order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_order'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order marked as completed successfully.";
    }
    header('Location: confirmed_orders.php');
    exit();
}

// Get confirmed orders
$orders = $conn->query("
    SELECT o.*, 
           q.id as quotation_id,
           rdm.section1, rdm.section2, rdm.door_width, rdm.tower_height,
           rdm.tower_type, rdm.coil_color,
           wdm.point1,
           u.name as prepared_by_name,
           u.contact as prepared_by_contact
    FROM orders o
    LEFT JOIN quotations q ON o.quotation_id = q.id
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u ON o.prepared_by = u.id
    WHERE o.status = 'confirmed'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmed Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h2>Confirmed Orders</h2>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <p>No confirmed orders found.</p>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?php echo $order['id']; ?></h3>
                                <?php if ($order['quotation_id']): ?>
                                    <p>Quotation #<?php echo $order['quotation_id']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="customer-info">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                            </div>

                            <div class="order-measurements">
                                <p><strong>Total Height:</strong> <?php echo $order['section1'] + $order['section2']; ?></p>
                                <p><strong>Width:</strong> <?php echo $order['door_width']; ?></p>
                                <p><strong>Type:</strong> <?php echo ucfirst($order['tower_type']); ?></p>
                                <?php if ($order['point1']): ?>
                                    <p><strong>Includes Wicket Door</strong></p>
                                <?php endif; ?>
                            </div>

                            <div class="order-actions">
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button view-btn">
                                    View Details
                                </a>
                                <a href="download_order.php?id=<?php echo $order['id']; ?>" class="button download-btn">
                                    Download Details
                                </a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to mark this order as completed?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="complete_order" class="button complete-btn">
                                        Mark as Completed
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
