<?php
require_once '../config/config.php';
require_once '../includes/InvoiceGenerator.php';
checkAuth(['office_staff']);

function formatInvoiceNumber($invoiceId, $createdAt) {
    $date = new DateTime($createdAt);
    return sprintf(
        "INV/%s/%s/%s/%d",
        $date->format('d'),
        $date->format('m'),
        $date->format('y'),
        $invoiceId
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

$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) {
    header('Location: invoices.php');
    exit();
}

// Get only necessary invoice details
$query = "
    SELECT i.*, i.created_at as invoice_created_at,
           o.*, o.created_at as order_created_at,
           u.name as created_by_name
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

// Update the formatted numbers with correct date fields
$invoice['formatted_number'] = formatInvoiceNumber($invoice['id'], $invoice['invoice_created_at']);
$invoice['formatted_order_number'] = formatOrderNumber($invoice['order_id'], $invoice['order_created_at']);
$generator = new InvoiceGenerator($invoice_id);
$generator->generateInvoicePDF($invoice);
