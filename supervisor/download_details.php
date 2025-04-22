<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['supervisor']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: pending_orders.php');
    exit();
}

// Get complete order data
$order = $conn->query("
    SELECT o.*, rdm.*, wdm.*,
           u1.name as prepared_by_name,
           u1.contact as prepared_by_contact
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    WHERE o.id = $order_id
")->fetch_assoc();

// Generate PDF using existing PDFGenerator
$pdf = new PDFGenerator($order_id);
$pdf->generatePDF($order, 'new_order');
