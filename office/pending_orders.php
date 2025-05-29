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
    <style>
        .pending-title {
            text-align: center;
            color: #d4af37; /* Gold highlight */
            font-size: 2.5em;
            font-weight: bold;
            margin: 30px 0 40px 0;
            letter-spacing: 2px;
            text-shadow: 0 2px 8px rgba(212,175,55,0.15);
        }
        .order-card {
            margin-bottom: 32px; /* Space between orders */
            border: 2px solid #e0e0e0; /* Light border */
            border-radius: 14px;        /* Rounded corners */
            background: #fafbfc;        /* Subtle background */
            box-shadow: 0 2px 12px rgba(0,0,0,0.04); /* Soft shadow */
            padding: 24px 28px;         /* Padding inside card */
            transition: box-shadow 0.2s;
        }
        .order-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.18); /* Stronger, darker shadow on hover */
        }
        .order-title {
            font-size: 1.5em;
            margin: 0 0 12px 0; /* Add space below order number */
        }
        .order-title .order-word {
            font-family: 'Segoe Script', 'Comic Sans MS', cursive;
            color: #007bff;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .order-details p {
            margin: 4px 0; /* Reduce space between order details */
        }
        .order-actions {
            margin-top: 18px; /* Space above buttons */
            display: flex;
            gap: 16px; /* Space between buttons */
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h2 class="pending-title">Pending Orders</h2>
            
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <h3 class="order-title">
                    <span class="order-word">Order</span> #<?php echo $order['id']; ?>
                </h3>
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
