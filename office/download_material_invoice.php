<?php
require_once '../config/config.php';
require_once '../includes/InvoiceGenerator.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
if (!$quotation_id) {
    header('Location: quotations.php');
    exit();
}

// Get quotation details with items
$quotation = $conn->query("
    SELECT q.*, u.name as created_by_name 
    FROM quotations q
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

// Initialize invoice generator and generate invoice
$generator = new InvoiceGenerator($quotation_id);
$generator->generateInvoicePDF($quotation, 'material');
