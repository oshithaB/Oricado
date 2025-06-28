<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$order_id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'advance';
$invoice_type = ($type == 'final') ? 'final' : 'advance';

// Get order details
$order = $conn->query("
    SELECT o.*, o.total_price as quotation_amount
    FROM orders o 
    WHERE o.id = $order_id
")->fetch_assoc();

// Get current advance amount and balance
$payment_info = $conn->query("
    SELECT 
        o.total_price,
        COALESCE(SUM(CASE WHEN i.invoice_type = 'advance' THEN i.amount ELSE 0 END), 0) as advance_paid,
        o.total_price - COALESCE(SUM(CASE WHEN i.invoice_type = 'advance' THEN i.amount ELSE 0 END), 0) as balance_amount
    FROM orders o
    LEFT JOIN invoices i ON o.id = i.order_id
    WHERE o.id = $order_id
    GROUP BY o.id
")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        if ($invoice_type == 'final') {
            // Use current advance as advance_amount, and only charge the remaining balance
            $advance = floatval($payment_info['advance_paid']);
            $final_amount = $order['total_price'] - $advance;
            if ($final_amount < 0) $final_amount = 0;

            // Update order - set paid_amount to total_price and balance to 0
            $stmt = $conn->prepare("
                UPDATE orders 
                SET paid_amount = total_price,
                    balance_amount = 0,
                    status = 'completed'
                WHERE id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            // Create final invoice with advance and final amount
            $stmt = $conn->prepare("
                INSERT INTO invoices (
                    order_id, invoice_type, amount, advance_amount, balance_amount, created_by
                ) VALUES (?, 'final', ?, ?, 0, ?)
            ");
            $stmt->bind_param("iddi", 
                $order_id, 
                $final_amount,
                $advance,
                $_SESSION['user_id']
            );
            $stmt->execute();
            $invoice_id = $conn->insert_id;

        } else {
            $new_advance_amount = floatval($_POST['advance_amount']);
            
            // Get current invoice for this order
            $current_invoice = $conn->query("
                SELECT id, amount as current_advance 
                FROM invoices 
                WHERE order_id = $order_id AND invoice_type = 'advance'
                ORDER BY id DESC LIMIT 1
            ")->fetch_assoc();

            $total_advance = $new_advance_amount;
            if ($current_invoice) {
                // Update existing invoice
                $total_advance += $current_invoice['current_advance'];
                $balance = $order['total_price'] - $total_advance;
                
                $stmt = $conn->prepare("
                    UPDATE invoices 
                    SET amount = ?,
                        balance_amount = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("ddi", $total_advance, $balance, $current_invoice['id']);
                $stmt->execute();

                $invoice_id = $current_invoice['id'];
            } else {
                // Create new invoice record if none exists
                $balance = $order['total_price'] - $total_advance;
                $stmt = $conn->prepare("
                    INSERT INTO invoices (
                        order_id, invoice_type, amount, balance_amount, created_by
                    ) VALUES (?, 'advance', ?, ?, ?)
                ");
                $stmt->bind_param("iddi", $order_id, $total_advance, $balance, $_SESSION['user_id']);
                $stmt->execute();
                $invoice_id = $conn->insert_id;
            }
            
            // Update order payment details
            $stmt = $conn->prepare("
                UPDATE orders 
                SET paid_amount = ?,
                    balance_amount = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ddi", $total_advance, $balance, $order_id);
            $stmt->execute();
        }

        $conn->commit();
        $_SESSION['success_message'] = "Invoice created successfully!";
        header("Location: download_invoice.php?id=" . $invoice_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Invoice</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-header h2 {
            color: #2c3e50;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 500;
            color: #34495e;
        }
        .readonly-field {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        .amount-display {
            font-size: 1.1rem;
            font-weight: 500;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .balance-info {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #e9ecef;
        }
        .btn-submit {
            padding: 12px 30px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="container">
                <div class="invoice-container">
                    <div class="invoice-header">
                        <h2 class="text-center mb-4">Create <?php echo ucfirst($type); ?> Invoice</h2>
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="invoice_type" value="<?php echo $invoice_type; ?>">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Total Amount:</label>
                                    <input type="text" class="form-control readonly-field" 
                                           value="Rs. <?php echo number_format($order['total_price'], 2); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <?php if ($type == 'advance'): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if ($payment_info['advance_paid'] > 0): ?>
                                        <div class="alert alert-info">
                                            <p><strong>Current Advance Paid:</strong> Rs. <?php echo number_format($payment_info['advance_paid'], 2); ?></p>
                                            <p><strong>Current Balance:</strong> Rs. <?php echo number_format($payment_info['balance_amount'], 2); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label class="form-label"><?php echo $payment_info['advance_paid'] > 0 ? 'Additional' : ''; ?> Advance Amount:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" 
                                                   name="advance_amount" 
                                                   class="form-control" 
                                                   step="0.01" 
                                                   max="<?php echo $payment_info['balance_amount']; ?>" 
                                                   required
                                                   onchange="updateTotalAdvance(this.value)">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Total After This Payment:</label>
                                        <input type="text" 
                                               id="totalAdvanceDisplay" 
                                               class="form-control" 
                                               value="Rs. <?php echo number_format($payment_info['advance_paid'], 2); ?>" 
                                               readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Remaining Balance:</label>
                                        <input type="text" 
                                               id="balanceDisplay" 
                                               class="form-control" 
                                               value="Rs. <?php echo number_format($payment_info['balance_amount'], 2); ?>" 
                                               readonly>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="balance-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="amount-display">
                                            <label>Advance Paid:</label>
                                            <div>Rs. <?php echo number_format($payment_info['advance_paid'], 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="amount-display">
                                            <label>Final Amount:</label>
                                            <div>Rs. <?php
                                                $final = $order['total_price'] - $payment_info['advance_paid'];
                                                if ($final < 0) $final = 0;
                                                echo number_format($final, 2);
                                            ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="amount-display">
                                            <label>Total Paid:</label>
                                            <div>Rs. <?php echo number_format($order['total_price'], 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-submit">Create Invoice</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function updateTotalAdvance(newAmount) {
        const currentAdvance = <?php echo $payment_info['advance_paid']; ?>;
        const totalPrice = <?php echo $payment_info['total_price']; ?>;
        
        newAmount = parseFloat(newAmount) || 0;
        const totalAdvance = currentAdvance + newAmount;
        const remainingBalance = totalPrice - totalAdvance;

        document.getElementById('totalAdvanceDisplay').value = 'Rs. ' + totalAdvance.toFixed(2);
        document.getElementById('balanceDisplay').value = 'Rs. ' + remainingBalance.toFixed(2);
    }
    </script>
</body>
</html>
