<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Get supplier quotations
$quotations = $conn->query("
    SELECT sq.*, q.*, u.name as created_by_name
    FROM supplier_quotations sq
    JOIN quotations q ON sq.quotation_id = q.id
    LEFT JOIN users u ON sq.created_by = u.id
    ORDER BY sq.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get items for each quotation
foreach ($quotations as &$quotation) {
    $quotation['items'] = $conn->query("
        SELECT qi.*, m.color, m.thickness, m.type
        FROM quotation_items qi
        LEFT JOIN materials m ON qi.material_id = m.id
        WHERE qi.quotation_id = {$quotation['quotation_id']}
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Quotations</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h2>Supplier Quotations</h2>
            
            <?php foreach ($quotations as $quotation): ?>
                <div class="quotation-card">
                    <div class="quotation-header">
                        <h3>Quotation #<?php echo $quotation['id']; ?></h3>
                        <div class="supplier-info">
                            <p><strong>Supplier:</strong> <?php echo htmlspecialchars($quotation['supplier_name']); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($quotation['supplier_contact']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></p>
                            <p><strong>Created By:</strong> <?php echo htmlspecialchars($quotation['created_by_name']); ?></p>
                        </div>
                    </div>

                    <table class="items-table">
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
                                <td colspan="5" align="right"><strong>Total Amount:</strong></td>
                                <td><strong>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="quotation-actions">
                        <a href="print_supplier_quotation.php?id=<?php echo $quotation['quotation_id']; ?>" 
                           class="button download-btn">Print Quotation</a>
                        
                        <a href="download_supplier_invoice.php?id=<?php echo $quotation['quotation_id']; ?>" 
                           class="button invoice-btn">Download Invoice</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
