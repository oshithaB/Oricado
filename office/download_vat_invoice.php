<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if (!isset($_GET['id'])) {
    die('Quotation ID required');
}

$id = $_GET['id'];
$stmt = $conn->prepare("
    SELECT 
        q.*,
        COALESCE(c.address, 'Not Provided') as customer_address,
        COALESCE(c.tax_number, 'Not Provided') as customer_tax_id 
    FROM quotations q 
    LEFT JOIN contacts c ON q.customer_name = c.name 
    WHERE q.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$quotation = $stmt->get_result()->fetch_assoc();

if (!$quotation) {
    die('Quotation not found');
}

$stmt = $conn->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>VAT Invoice #<?php echo $id; ?></title>
    <style>
        @media print {
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 20px; }
            .company-info { margin-bottom: 20px; }
            .customer-info { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f0f0f0; }
            .totals { text-align: right; margin: 20px 0; }
            .signatures { margin-top: 50px; }
            .signature-line { border-top: 1px solid #000; width: 200px; display: inline-block; margin: 0 20px; }
            .no-print { display: none; }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>Riyon International Pvt(Ltd)</h1>
        <h2>ORICADO</h2>
    </div>

    <div class="company-info">
        <p><strong>VAT Number:</strong> 174924198-7000</p>
        <p><strong>Address:</strong> 456/A, MDH Jayawardhane Mawatha, kaduwela</p>
    </div>

    <div class="customer-info">
        <h3>Customer Details:</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($quotation['customer_name']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($quotation['customer_address']); ?></p>
        <p><strong>Tax ID:</strong> <?php echo htmlspecialchars($quotation['customer_tax_id']); ?></p>
    </div>

    <table>
        <tr>
            <th>Item</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Amount</th>
        </tr>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
            <td>Rs. <?php echo number_format($item['amount'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="totals">
        <?php
        // Calculate totals correctly
        $subtotal = $quotation['total_amount'] - $quotation['vat']; // Remove VAT from total to get subtotal
        $vat = $quotation['vat'];
        $grandTotal = $quotation['total_amount'];
        ?>
        <p><strong>Sub Total:</strong> Rs. <?php echo number_format($subtotal, 2); ?></p>
        <p><strong>VAT (18%):</strong> Rs. <?php echo number_format($vat, 2); ?></p>
        <p><strong>Grand Total:</strong> Rs. <?php echo number_format($grandTotal, 2); ?></p>
    </div>

    <div class="signatures">
        <div style="text-align: center;">
            <div class="signature-line"></div>
            <div class="signature-line"></div>
            <br>
            <span style="margin: 0 50px;">Authorized Signature</span>
            <span style="margin: 0 50px;">Customer Signature</span>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print Invoice</button>
    </div>
</body>
</html>

