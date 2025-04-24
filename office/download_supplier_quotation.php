<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
if (!$quotation_id) {
    header('Location: supplier_quotations.php');
    exit();
}

// Get complete quotation data
$stmt = $conn->prepare("
    SELECT sq.*, q.*, u.name as created_by_name
    FROM supplier_quotations sq
    JOIN quotations q ON sq.quotation_id = q.id
    LEFT JOIN users u ON sq.created_by = u.id
    WHERE sq.id = ?
");

$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$quotation = $stmt->get_result()->fetch_assoc();

if (!$quotation) {
    $_SESSION['error_message'] = "Quotation not found";
    header('Location: supplier_quotations.php');
    exit();
}

// Get items
$stmt = $conn->prepare("
    SELECT qi.*, m.color, m.thickness, m.type
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = ?
");

$stmt->bind_param("i", $quotation['quotation_id']);
$stmt->execute();
$quotation['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Generate PDF
$pdf = new PDFGenerator($quotation_id);
$pdf->generateSupplierQuotationPDF($quotation);
