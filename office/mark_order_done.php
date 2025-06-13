<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    
    // Check if this is a material order
    $is_material_order = $conn->query("
        SELECT 1 FROM orders o
        JOIN quotations q ON o.quotation_id = q.id
        WHERE o.id = $order_id 
        AND q.type = 'raw_materials'
        AND o.status = 'pending'
    ")->fetch_assoc();

    if ($is_material_order) {
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'done',
                admin_approved = 1,
                admin_approved_by = ?,
                admin_approved_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param("ii", $_SESSION['user_id'], $order_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Order marked as done successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating order status.";
        }
    }
    
    header('Location: pending_orders.php');
    exit();
}
