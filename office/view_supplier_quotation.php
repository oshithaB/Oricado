<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
if (!$quotation_id) {
    header('Location: supplier_quotations.php');
    exit();
}

// Get quotation details
$stmt = $conn->prepare("
    SELECT sq.*, q.*, u.name as created_by_name
    FROM supplier_quotations sq
    JOIN quotations q ON sq.quotation_id = q.id
    LEFT JOIN users u ON sq.created_by = u.id
    WHERE sq.id = ?
");

$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$quotation = $stmt->get_result()->fetch_assoc();

if (!$quotation) {
    $_SESSION['error_message'] = "Quotation not found";
    header('Location: supplier_quotations.php');
    exit();
}

// Get items
$stmt = $conn->prepare("
    SELECT qi.*, m.color, m.thickness, m.type
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = ?
");

$stmt->bind_param("i", $quotation['quotation_id']);
$stmt->execute();
$quotation['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Supplier Quotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .no-print { display: none; }
            .dashboard { display: block; }
            nav { display: none; }
            .content { margin: 0; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <div class="actions no-print">
                    <button onclick="window.print()" class="button">Print Quotation</button>
                    <a href="download_supplier_quotation.php?id=<?php echo $quotation_id; ?>" 
                       class="button">Download PDF</a>
                    <a href="supplier_quotations.php" class="button">Back to List</a>
                </div>

                <div class="quotation-header">
                    <h2>Supplier Purchase Quotation #<?php echo $quotation['id']; ?></h2>
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
                            <td colspan="5" align="right"><strong>Total Amount:</strong></td>
                            <td><strong>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
