<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotations = $conn->query("
    SELECT q.*, u.name as prepared_by_name 
    FROM quotations q
    LEFT JOIN users u ON q.created_by = u.id
    ORDER BY q.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
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
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotations as $quotation): ?>
                        <tr>
                            <td>#<?php echo $quotation['id']; ?></td>
                            <td><?php echo ucfirst($quotation['type']); ?></td>
                            <td><?php echo htmlspecialchars($quotation['customer_name']); ?></td>
                            <td>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></td>
                            <td>
                                <a href="download_quotation.php?id=<?php echo $quotation['id']; ?>" 
                                   class="button">Download</a>
                                <?php if ($quotation['type'] == 'order'): ?>
                                    <a href="create_order.php?quotation_id=<?php echo $quotation['id']; ?>" 
                                       class="button">Add Measurements</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>