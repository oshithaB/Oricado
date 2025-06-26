<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Add this function at the top after require statements
function formatOrderNumber($orderId, $createdAt) {
    $date = new DateTime($createdAt);
    return sprintf(
        "SO/%s/%s/%s/%05d",
        $date->format('d'),
        $date->format('m'),
        $date->format('y'),
        $orderId
    );
}

// Get confirmed orders with balance
$orders = $conn->query("
    SELECT o.*, 
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           o.total_price - COALESCE(SUM(i.amount), 0) as balance_amount
    FROM orders o
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN invoices i ON o.id = i.order_id
    WHERE o.status = 'confirmed'
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$status = 'confirmed';
$formatOrderNumber = 'formatOrderNumber';
require 'orders_template.php';
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
