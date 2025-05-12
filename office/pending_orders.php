<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Get pending orders
$orders = $conn->query("
    SELECT o.*, 
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           CASE WHEN i.id IS NOT NULL THEN 1 ELSE 0 END as has_advance_invoice
    FROM orders o
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN invoices i ON o.id = i.order_id AND i.invoice_type = 'advance'
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
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h2>Pending Orders</h2>
            
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <h3>Order #<?php echo $order['id']; ?></h3>
                <div class="order-details">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                    <p><strong>Prepared By:</strong> <?php echo htmlspecialchars($order['prepared_by_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                </div>

                <div class="order-actions">
                    <?php if ($order['total_price'] > 0 && !$order['has_advance_invoice']): ?>
                        <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=advance" class="button invoice-btn">
                            Create Advance Invoice
                        </a>
                    <?php endif; ?>
                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button view-btn">
                        View Details
                    </a>
                    <a href="download_order.php?id=<?php echo $order['id']; ?>" class="button download-btn">
                        Download Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
