<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['office_staff']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

$pdf = new PDFGenerator($order_id);
$pdf->generatePDF($materials, 'materials');
