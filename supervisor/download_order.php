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
           rdm.outside_width, rdm.inside_width, rdm.door_width, rdm.tower_height,
           rdm.tower_type, rdm.coil_color, rdm.thickness, rdm.covering,
           rdm.side_lock, rdm.motor, rdm.fixing, rdm.down_lock,
           rdm.section1, rdm.section2,
           wdm.point1, wdm.point2, wdm.point3, wdm.point4, wdm.point5,
           wdm.thickness as wicket_thickness, wdm.door_opening, wdm.handle,
           wdm.letter_box, wdm.coil_color as wicket_color,
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

// Handle null values
array_walk_recursive($order, function(&$value) {
    $value = $value ?? 'N/A';
});

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
