<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Get quotations with items
$quotations = $conn->query("
    SELECT q.*, u.name as prepared_by_name 
    FROM quotations q
    LEFT JOIN users u ON q.created_by = u.id
    ORDER BY q.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get items for each quotation
foreach ($quotations as &$quotation) {
    $quotation['items'] = $conn->query("
        SELECT qi.*, m.color, m.thickness, m.type
        FROM quotation_items qi
        LEFT JOIN materials m ON qi.material_id = m.id
        WHERE qi.quotation_id = {$quotation['id']}
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quotations</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="section">
                <h2>Quotations</h2>
                <?php foreach ($quotations as $quotation): ?>
                <div class="quotation-card">
                    <div class="quotation-header">
                        <h3>Quotation #<?php echo $quotation['id']; ?></h3>
                        <div class="quotation-info">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($quotation['customer_name']); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($quotation['customer_contact']); ?></p>
                            <p><strong>Type:</strong> <?php echo ucfirst($quotation['type']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></p>
                        </div>
                    </div>

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Details</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Taxes</th>
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
                                <td><?php echo $item['discount']; ?>%</td>
                                <td><?php echo $item['taxes']; ?>%</td>
                                <td>Rs. <?php echo number_format($item['amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="7" align="right"><strong>Total Amount:</strong></td>
                                <td><strong>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="quotation-actions">
                        <a href="download_quotation.php?id=<?php echo $quotation['id']; ?>" 
                           class="button download-btn">Download</a>
                        <?php if ($quotation['type'] == 'order'): ?>
                            <a href="create_order.php?quotation_id=<?php echo $quotation['id']; ?>" 
                               class="button add-measurements-btn">Add Measurements</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>