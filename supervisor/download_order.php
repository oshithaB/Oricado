<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['supervisor']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: confirmed_orders.php');
    exit();
}

// Get complete order data
$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u.name as prepared_by_name,
           u.contact as prepared_by_contact,
           q.id as quotation_id,
           q.total_amount as quotation_amount
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u ON o.prepared_by = u.id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.id = $order_id
")->fetch_assoc();

// Get materials list
$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

$order['materials'] = $materials;

// Generate PDF
$pdf = new PDFGenerator($order_id);
$pdf->generatePDF($order, 'supervisor_order');
