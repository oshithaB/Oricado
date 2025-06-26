<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

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

// Get search parameters
$search = $_GET['search'] ?? '';

// Get invoice type
$type = $_GET['type'] ?? 'advance';

// Validate invoice type
if (!in_array($type, ['advance', 'final'])) {
    $type = 'advance';
}

// Build the query with search conditions
$query = "
    SELECT i.*, i.created_at as invoice_created_at,
           o.*, o.created_at as order_created_at,
           u.name as created_by_name,
           COALESCE(SUM(CASE WHEN inv.invoice_type = 'advance' THEN inv.amount ELSE 0 END), 0) as advance_paid,
           o.total_price - COALESCE(SUM(CASE WHEN inv.invoice_type = 'advance' THEN inv.amount ELSE 0 END), 0) as balance_amount
    FROM invoices i
    JOIN orders o ON i.order_id = o.id
    LEFT JOIN users u ON i.created_by = u.id
    LEFT JOIN invoices inv ON o.id = inv.order_id
    WHERE 1=1";

// Add search condition if search term exists
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (o.id LIKE '%$search%' 
                OR o.customer_name LIKE '%$search%'
                OR o.customer_contact LIKE '%$search%')";
}

$query .= " AND i.invoice_type = ? GROUP BY i.id ORDER BY i.created_at DESC";

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
            gap: 8px;
            margin-top: 15px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
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
        .search-section {
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 600px;
        }
        .search-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .search-input {
            flex: 1;
            height: 7px;
            padding: 0 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 13px;
        }
        .search-button {
            height: 35px;
            padding: 0 14px ;
            font-size: 13px;
            border-radius: 4px;
            border: none;
            background: #0d6efd;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 3;
        }
        .btn-clear {
            background-color: #6c757d;
            color: white;
            border: 1px solid #565e64;
        }
        .btn-clear:hover {
            background-color: #5c636a;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Invoice Action Buttons */
        .invoice-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 15px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        .invoice-btn {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            width: 200px;
            transition: all 0.2s ease;
        }

        .btn-advance {
            background-color: #0d6efd;
            border: 1px solid #0a58ca;
            color: white;
        }

        .btn-advance:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            color: white;
        }

        .btn-final {
            background-color: #198754;
            border: 1px solid #146c43;
            color: white;
        }

        .btn-final:hover {
            background-color: #157347;
            border-color: #146c43;
            color: white;
        }

        .btn-download {
            background-color: #6c757d;
            border: 1px solid #565e64;
            color: white;
        }

        .btn-download:hover {
            background-color: #5c636a;
            border-color: #565e64;
            color: white;
        }

        .current-advance {
            background: rgba(255,255,255,0.2);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge {
            font-size: 0.9em;
            padding: 5px 8px;
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

                <!-- Search Section -->
                <div class="search-section">
                    <form method="GET" class="search-form">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <input type="text" 
                               name="search" 
                               class="search-input" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by Order ID, Customer Name or Contact">
                        <button type="submit" class="search-button">Search</button>
                        <?php if (!empty($search)): ?>
                            <button type="button" onclick="window.location.href='?type=<?php echo $type; ?>'" class="search-button btn-clear">Clear</button>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="invoices-list">
                    <?php
                    // Build a map of order_id => has_final_invoice for fast lookup
                    $orderHasFinalInvoice = [];
                    foreach ($invoices as $inv) {
                        if ($inv['invoice_type'] === 'final') {
                            $orderHasFinalInvoice[$inv['order_id']] = true;
                        }
                    }
                    ?>
                    <?php foreach ($invoices as $invoice): ?>
                    <div class="invoice-card">
                        <div class="invoice-info">
                            <h3>Invoice #<?php echo formatInvoiceNumber($invoice['id'], $invoice['invoice_created_at']); ?></h3>
                            <p><strong>Order #:</strong> <?php echo formatOrderNumber($invoice['order_id'], $invoice['order_created_at']); ?></p>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($invoice['customer_name']); ?></p>
                            <p><strong>Total Order Amount:</strong> Rs. <?php echo number_format($invoice['total_price'], 2); ?></p>
                            <?php if ($invoice['invoice_type'] == 'advance'): ?>
                                <p><strong>Advance Amount:</strong> Rs. <?php echo number_format($invoice['amount'], 2); ?></p>
                                <p><strong>Balance:</strong> Rs. <?php echo number_format($invoice['balance_amount'], 2); ?></p>
                            <?php else: ?>
                                <p><strong>Previous Advance:</strong> Rs. <?php echo number_format($invoice['advance_amount'], 2); ?></p>
                                <p><strong>Final Amount:</strong> Rs. <?php echo number_format($invoice['amount'], 2); ?></p>
                                <p><strong>Total Paid:</strong> Rs. <?php echo number_format($invoice['advance_amount'] + $invoice['amount'], 2); ?></p>
                                <p><strong>Remaining:</strong> Rs. 0.00</p>
                            <?php endif; ?>
                        </div>
                        <div class="invoice-actions">
                            <?php
                            $has_final_invoice = isset($orderHasFinalInvoice[$invoice['order_id']]);
                            $has_balance = $invoice['balance_amount'] > 0;
                            ?>
                            <?php if (
                                $invoice['invoice_type'] === 'advance' 
                                && !$has_final_invoice
                                && $has_balance
                            ): ?>
                                <?php if ($type !== 'final'): ?>
                                    <a href="create_invoice.php?id=<?php echo $invoice['order_id']; ?>&type=advance" 
                                       class="invoice-btn btn-advance">
                                        <span>Add to Advance</span>
                                        <?php if ($invoice['advance_paid'] > 0): ?>
                                            <span class="current-advance">Rs. <?php echo number_format($invoice['advance_paid'], 2); ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <a href="create_invoice.php?id=<?php echo $invoice['order_id']; ?>&type=final" 
                                       class="invoice-btn btn-final">Create Final Invoice</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <a href="download_invoice.php?id=<?php echo $invoice['id']; ?>" 
                               class="invoice-btn btn-download">Download Invoice</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
