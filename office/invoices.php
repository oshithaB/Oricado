<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$type = $_GET['type'] ?? 'advance';

// Validate invoice type
if (!in_array($type, ['advance', 'final'])) {
    $type = 'advance';
}

// Fix the invoice type in the query
$query = "
    SELECT i.*, o.customer_name, o.customer_contact, o.customer_address,
           o.total_price, o.paid_amount, o.balance_amount
    FROM invoices i
    JOIN orders o ON i.order_id = o.id
    WHERE i.invoice_type = ?
    ORDER BY i.created_at DESC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param('s', $type);
if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
$invoices = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo ucfirst($type); ?> Invoices</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <div class="invoice-header">
                    <h2><?php echo ucfirst($type); ?> Invoices</h2>
                    <div class="invoice-tabs">
                        <a href="?type=advance" class="<?php echo $type == 'advance' ? 'active' : ''; ?>">
                            Advance Invoices
                        </a>
                        <a href="?type=final" class="<?php echo $type == 'final' ? 'active' : ''; ?>">
                            Final Invoices
                        </a>
                    </div>
                </div>

                <div class="invoices-list">
                    <?php foreach ($invoices as $invoice): ?>
                    <div class="invoice-card">
                        <div class="invoice-info">
                            <h3>Invoice #<?php echo $invoice['id']; ?></h3>
                            <p><strong>Order #:</strong> <?php echo $invoice['order_id']; ?></p>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($invoice['customer_name']); ?></p>
                            <p><strong>Amount:</strong> Rs. <?php echo number_format($invoice['amount'], 2); ?></p>
                            <?php if ($type == 'advance'): ?>
                                <p><strong>Balance:</strong> Rs. <?php echo number_format($invoice['balance_amount'], 2); ?></p>
                                <?php if ($invoice['balance_amount'] > 0): ?>
                                    <a href="create_invoice.php?id=<?php echo $invoice['order_id']; ?>&type=final" class="button invoice-btn">
                                        Create Final Invoice
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <p><strong>Advance Paid:</strong> Rs. <?php echo number_format($invoice['advance_amount'], 2); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="invoice-actions">
                            <a href="download_invoice.php?id=<?php echo $invoice['id']; ?>" class="button">
                                Download Invoice
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
