<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$orders = $conn->query("
    SELECT o.*, q.id as quotation_id, q.total_amount as order_value,
           u1.name as prepared_by_name,
           u2.name as admin_approved_by_name,
           o.admin_approved_at,
           o.material_cost
    FROM orders o
    LEFT JOIN quotations q ON o.quotation_id = q.id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.admin_approved_by = u2.id
    WHERE o.status = 'reviewed'
    AND o.admin_approved = 1
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reviewed Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .reviewed-title {
            text-align: center;
            color: #d4af37;
            font-size: 2.5em;
            font-weight: bold;
            margin: 30px 0 40px 0;
            letter-spacing: 2px;
            /* Removed black outline */
            /* -webkit-text-stroke: 2px #000; */
            /* text-stroke: 2px #000; */
            text-shadow: 0 2px 8px rgba(212,175,55,0.15);
        }
        .order-card {
            margin-bottom: 32px;
            border: 2px solid #e0e0e0;
            border-radius: 14px;
            background: #fafbfc;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            padding: 24px 28px;
            transition: box-shadow 0.2s;
        }
        .order-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .order-header h3 {
            font-size: 1.5em;
            margin: 0 0 12px 0;
        }
        .order-header .order-word {
            font-family: 'Segoe Script', 'Comic Sans MS', cursive;
            color: #007bff;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .order-details p, .cost-summary p {
            margin: 4px 0;
        }
        .actions {
            margin-top: 18px;
            display: flex;
            gap: 16px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2 class="reviewed-title">Reviewed Orders</h2>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="reference-numbers">
                                <h3>
                                    <span class="order-word">Order</span> #<?php echo $order['id']; ?>
                                </h3>
                                <?php if ($order['quotation_id']): ?>
                                    <h4>Quotation #<?php echo $order['quotation_id']; ?></h4>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="approval-info">
                            <p class="admin-approved">
                                ✓ Approved by <?php echo htmlspecialchars($order['admin_approved_by_name']); ?>
                                on <?php echo date('Y-m-d H:i', strtotime($order['admin_approved_at'])); ?>
                            </p>
                        </div>

                        <div class="order-details">
                            <p>Customer: <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p>Contact: <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                        </div>

                        <div class="cost-summary">
                            <p>Total Material Cost: Rs. <?php echo number_format($order['material_cost'], 2); ?></p>
                            <p>Order Value: Rs. <?php echo number_format($order['order_value'], 2); ?></p>
                            <p>Profit Margin: Rs. <?php echo number_format($order['order_value'] - $order['material_cost'], 2); ?></p>
                        </div>

                        <div class="actions">
                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                            <a href="confirm_order.php?id=<?php echo $order['id']; ?>" class="button primary">Confirm Order</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
