<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['office_staff']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

// Get complete order data
$order = $conn->query("
    SELECT o.*, rdm.*, wdm.*,
           u1.name as prepared_by_name,
           u1.contact as prepared_by_contact,
           u2.name as checked_by_name,
           o.total_price
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    WHERE o.id = $order_id
")->fetch_assoc();

$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

$order['materials'] = $materials;
$pdf = new PDFGenerator($order_id);
$pdf->generatePDF($order, 'order');
