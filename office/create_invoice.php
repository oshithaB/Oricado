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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        if ($invoice_type == 'final') {
            // For final invoice
            $amount = $order['balance_amount'];
            $advance_amount = $order['paid_amount'];
            
            // Update order payment status
            $stmt = $conn->prepare("
                UPDATE orders 
                SET paid_amount = total_price,
                    balance_amount = 0
                WHERE id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            // Create final invoice record
            $stmt = $conn->prepare("
                INSERT INTO invoices (
                    order_id, invoice_type, amount, advance_amount, balance_amount, created_by
                ) VALUES (?, 'final', ?, ?, 0, ?)
            ");
            $stmt->bind_param("iddi", 
                $order_id, 
                $amount, 
                $advance_amount,
                $_SESSION['user_id']
            );
            $stmt->execute();
        } else {
            $amount = floatval($_POST['advance_amount']);
            $balance = $order['total_price'] - $amount;
            
            // Update order with payment details
            $stmt = $conn->prepare("
                UPDATE orders 
                SET paid_amount = ?,
                    balance_amount = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ddi", $amount, $balance, $order_id);
            $stmt->execute();

            // Create advance invoice record
            $stmt = $conn->prepare("
                INSERT INTO invoices (
                    order_id, invoice_type, amount, balance_amount, created_by
                ) VALUES (?, 'advance', ?, ?, ?)
            ");
            $stmt->bind_param("iddi", $order_id, $amount, $balance, $_SESSION['user_id']);
            $stmt->execute();
        }

        $invoice_id = $conn->insert_id;
        $conn->commit();
        
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
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Create <?php echo ucfirst($type); ?> Invoice</h2>
                
                <form method="POST" class="invoice-form">
                    <input type="hidden" name="invoice_type" value="<?php echo $invoice_type; ?>">

                    <div class="form-group">
                        <label>Total Amount:</label>
                        <input type="text" value="Rs. <?php echo number_format($order['total_price'], 2); ?>" readonly>
                    </div>

                    <?php if ($type == 'advance'): ?>
                        <div class="form-group">
                            <label>Advance Amount:</label>
                            <input type="number" name="advance_amount" step="0.01" 
                                   max="<?php echo $order['total_price']; ?>" required
                                   onchange="updateBalance(this.value)">
                        </div>
                        <div class="form-group">
                            <label>Balance Amount:</label>
                            <input type="text" id="balance_display" readonly>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Advance Paid:</label>
                            <input type="text" value="Rs. <?php echo number_format($order['paid_amount'], 2); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Balance Payment:</label>
                            <input type="text" value="Rs. <?php echo number_format($order['balance_amount'], 2); ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <button type="submit">Create Invoice</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function updateBalance(advanceAmount) {
        const totalAmount = <?php echo $order['total_price']; ?>;
        const balance = totalAmount - parseFloat(advanceAmount || 0);
        document.getElementById('balance_display').value = 'Rs. ' + balance.toFixed(2);
    }
    </script>
</body>
</html>
