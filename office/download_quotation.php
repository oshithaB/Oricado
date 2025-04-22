<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
if (!$quotation_id) {
    header('Location: quotations.php');
    exit();
}

$quotation = $conn->query("
    SELECT q.*, u.name as prepared_by_name 
    FROM quotations q
    LEFT JOIN users u ON q.created_by = u.id
    WHERE q.id = $quotation_id
")->fetch_assoc();

$items = $conn->query("
    SELECT * FROM quotation_items 
    WHERE quotation_id = $quotation_id
")->fetch_all(MYSQLI_ASSOC);

$quotation['items'] = $items;
$pdf = new PDFGenerator();
$pdf->generateQuotation($quotation);
