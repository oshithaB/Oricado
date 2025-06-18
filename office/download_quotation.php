<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['office_staff']);

function formatQuotationNumber($quotationId, $createdAt) {
    $date = new DateTime($createdAt);
    return sprintf(
        "QT/%s/%s/%s/%d",
        $date->format('d'),
        $date->format('m'),
        $date->format('y'),
        $quotationId
    );
}

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

$quotation_id = $_GET['id'] ?? null;
if (!$quotation_id) {
    header('Location: quotations.php');
    exit();
}

// Get complete quotation data with all items
$quotation = $conn->query("
    SELECT q.*, q.created_at, o.created_at as order_created_at, 
           o.id as order_id, u.name as prepared_by_name 
    FROM quotations q
    LEFT JOIN orders o ON q.order_id = o.id
    LEFT JOIN users u ON q.created_by = u.id
    WHERE q.id = $quotation_id
")->fetch_assoc();

if (!$quotation) {
    die("Quotation not found");
}

// Get all items with their details
$quotation['items'] = $conn->query("
    SELECT qi.*, m.name as material_name, m.type, m.color, m.thickness
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = $quotation_id
")->fetch_all(MYSQLI_ASSOC);

// Add formatted numbers
$quotation['formatted_number'] = formatQuotationNumber($quotation['id'], $quotation['created_at']);
if ($quotation['order_id']) {
    $quotation['formatted_order_number'] = formatOrderNumber($quotation['order_id'], $quotation['order_created_at']);
}

// Initialize PDF generator and generate PDF
$pdf = new PDFGenerator($quotation_id);
$pdf->generatePDF($quotation, 'quotation');
