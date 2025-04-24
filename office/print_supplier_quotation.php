<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
if (!$quotation_id) {
    header('Location: supplier_quotations.php');
    exit();
}

// Get quotation details
$quotation = $conn->query("
    SELECT sq.*, q.*, u.name as created_by_name
    FROM supplier_quotations sq
    JOIN quotations q ON sq.quotation_id = q.id
    LEFT JOIN users u ON sq.created_by = u.id
    WHERE q.id = $quotation_id
")->fetch_assoc();

if (!$quotation) {
    header('Location: supplier_quotations.php');
    exit();
}

// Get items
$quotation['items'] = $conn->query("
    SELECT qi.*, m.color, m.thickness, m.type
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = $quotation_id
")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Quotation #<?php echo $quotation['id']; ?></title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
        }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; }
        .total-row { font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supplier Purchase Quotation</h1>
        <h2>Quotation #<?php echo $quotation['id']; ?></h2>
    </div>

    <div class="supplier-info">
        <p><strong>Supplier:</strong> <?php echo htmlspecialchars($quotation['supplier_name']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($quotation['supplier_contact']); ?></p>
        <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></p>
        <p><strong>Created By:</strong> <?php echo htmlspecialchars($quotation['created_by_name']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Material</th>
                <th>Specifications</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quotation['items'] as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>
                    <?php if ($item['type'] == 'coil'): ?>
                        Color: <?php echo str_replace('_', ' ', ucfirst($item['color'])); ?><br>
                        Thickness: <?php echo $item['thickness']; ?>
                    <?php endif; ?>
                </td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                <td>Rs. <?php echo number_format($item['amount'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="5" align="right">Total Amount:</td>
                <td>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></td>
            </tr>
        </tbody>
    </table>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
