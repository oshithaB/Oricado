<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if (!isset($_GET['id'])) {
    die('No ID provided');
}

$id = $conn->real_escape_string($_GET['id']);

// Get quotation details
$quotation = $conn->query("
    SELECT sq.*, q.*, u.name as created_by_name
    FROM supplier_quotations sq
    JOIN quotations q ON sq.quotation_id = q.id
    LEFT JOIN users u ON sq.created_by = u.id
    WHERE q.id = $id
")->fetch_assoc();

// Get items
$items = $conn->query("
    SELECT qi.*, m.color, m.thickness, m.type
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = $id
")->fetch_all(MYSQLI_ASSOC);

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="material_request_'.$id.'.html"');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Material Request #<?php echo $id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .header { margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Material Request</h1>
        <p><strong>Request #:</strong> <?php echo $quotation['id']; ?></p>
        <p><strong>Supplier:</strong> <?php echo htmlspecialchars($quotation['supplier_name']); ?></p>
        <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Specifications</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
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
            <tr>
                <td colspan="5" align="right"><strong>Total Amount:</strong></td>
                <td><strong>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
