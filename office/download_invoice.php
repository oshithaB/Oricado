<?php
require_once '../config/config.php';
require_once '../includes/InvoiceGenerator.php';
checkAuth(['office_staff']);

$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) {
    header('Location: invoices.php');
    exit();
}

// Get only necessary invoice details
$query = "
    SELECT i.*, o.customer_name, o.customer_contact, o.customer_address,
           o.total_price, u.name as created_by_name
    FROM invoices i
    JOIN orders o ON i.order_id = o.id
    LEFT JOIN users u ON i.created_by = u.id
    WHERE i.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("Invoice not found");
}

// Generate PDF using new InvoiceGenerator
$generator = new InvoiceGenerator($invoice_id);
$generator->generateInvoicePDF($invoice);
