<?php
require_once '../config/config.php';
require_once '../includes/InvoiceGenerator.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
if (!$quotation_id) {
    header('Location: supplier_quotations.php');
    exit();
}

// Get quotation details
$stmt = $conn->prepare("
    SELECT sq.*, q.*, u.name as created_by_name
    FROM supplier_quotations sq
    JOIN quotations q ON sq.quotation_id = q.id
    LEFT JOIN users u ON sq.created_by = u.id
    WHERE q.id = ?
");

$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("Invoice not found");
}

// Get items
$stmt = $conn->prepare("
    SELECT qi.*, m.color, m.thickness, m.type
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = ?
");

$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$invoice['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Generate PDF
$generator = new InvoiceGenerator($quotation_id);
$generator->generateSupplierInvoicePDF($invoice);
