<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Get all materials with their current stock levels
$materials = $conn->query("
    SELECT m.*, 
           COALESCE(SUM(om.quantity), 0) as used_quantity,
           COUNT(DISTINCT o.id) as times_used
    FROM materials m
    LEFT JOIN order_materials om ON m.id = om.material_id
    LEFT JOIN orders o ON om.order_id = o.id
    GROUP BY m.id
    ORDER BY m.type, m.name
")->fetch_all(MYSQLI_ASSOC);

// Group materials by type
$grouped_materials = [];
foreach ($materials as $material) {
    $grouped_materials[$material['type']][] = $material;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Material Report</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <div class="report-header">
                    <h2>Material Stock Report</h2>
                    <div class="actions">
                        <button onclick="window.print()" class="button">Print Report</button>
                        <a href="download_material_report.php" class="button">Download Report</a>
                    </div>
                </div>

                <?php foreach ($grouped_materials as $type => $type_materials): ?>
                    <div class="material-group">
                        <h3><?php echo ucfirst($type); ?> Materials</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Material Name</th>
                                    <?php if ($type == 'coil'): ?>
                                        <th>Color</th>
                                        <th>Thickness</th>
                                    <?php endif; ?>
                                    <th>Current Stock</th>
                                    <th>Unit</th>
                                    <th>Times Used</th>
                                    <th>Total Used</th>
                                    <th>Current Price</th>
                                    <th>Stock Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($type_materials as $material): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($material['name']); ?></td>
                                        <?php if ($type == 'coil'): ?>
                                            <td><?php echo str_replace('_', ' ', ucfirst($material['color'])); ?></td>
                                            <td><?php echo $material['thickness']; ?></td>
                                        <?php endif; ?>
                                        <td><?php echo number_format($material['quantity'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($material['unit']); ?></td>
                                        <td><?php echo $material['times_used']; ?></td>
                                        <td><?php echo number_format($material['used_quantity'], 2); ?></td>
                                        <td>Rs. <?php echo number_format($material['price'], 2); ?></td>
                                        <td>Rs. <?php echo number_format($material['quantity'] * $material['price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
    @media print {
        .dashboard nav, .actions { display: none; }
        .content { margin: 0; padding: 20px; }
        .report-table { page-break-inside: avoid; }
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }
    .report-table th, .report-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .report-table th {
        background: #f5f5f5;
        font-weight: bold;
    }
    .material-group {
        margin-bottom: 30px;
    }
    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    </style>
</body>
</html>
