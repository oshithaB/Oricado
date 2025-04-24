<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Handle mark as done action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_done'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'done' WHERE id = ? AND status = 'completed'");
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order #$order_id marked as done successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating order status.";
    }
    
    header('Location: completed_orders.php');
    exit();
}

// Set status for template
$status = 'completed';
require 'orders_template.php';
