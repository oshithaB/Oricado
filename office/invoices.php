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
    <style>
        .invoices-title {
            text-align: center;
            color: #d4af37;
            font-size: 2.5em;
            font-weight: bold;
            margin: 30px 0 40px 0;
            letter-spacing: 2px;
            text-shadow: 0 2px 8px rgba(212,175,55,0.15);
        }
        .invoice-card {
            margin-bottom: 32px;
            border: 2px solid #e0e0e0;
            border-radius: 14px;
            background: #fafbfc;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            padding: 24px 28px;
            transition: box-shadow 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .invoice-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .invoice-info h3 {
            font-size: 1.3em;
            margin: 0 0 8px 0;
        }
        .invoice-info p {
            margin: 4px 0;
        }
        .invoice-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .button, .invoice-btn {
            background: #2196f3;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 12px 28px;
            font-size: 1.1em;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(33,150,243,0.08);
            outline: none;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }
        .button:hover, .invoice-btn:hover, .button:focus, .invoice-btn:focus {
            background: #1769aa;
            box-shadow: 0 4px 16px rgba(33,150,243,0.18);
        }
        .invoice-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
        }
        .invoice-tabs {
            margin-top: 10px;
            display: flex;
            gap: 16px;
        }
        .invoice-tabs a {
            padding: 8px 22px;
            border-radius: 6px;
            background: #f3f3f3;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .invoice-tabs a.active, .invoice-tabs a:hover {
            background: #d4af37;
            color: #fff;
        }
        .button.invoice-btn {
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <div class="invoice-header">
                    <h2 class="invoices-title"><?php echo ucfirst($type); ?> Invoices</h2>
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
