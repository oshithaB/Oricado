<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Handle search
$search = $_GET['search'] ?? '';
$searchCondition = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $searchCondition = "WHERE m.name LIKE '%$search%' OR m.type LIKE '%$search%' OR m.color LIKE '%$search%'";
}

// Get all materials with their current stock levels
$materials = $conn->query("
    SELECT m.*, 
           m.price as purchase_price,
           m.saleprice as selling_price,
           COALESCE(SUM(om.quantity), 0) as used_quantity,
           COUNT(DISTINCT o.id) as times_used
    FROM materials m
    LEFT JOIN order_materials om ON m.id = om.material_id
    LEFT JOIN orders o ON om.order_id = o.id
    $searchCondition
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add print specific styles -->
    <style>
        /* Regular styles stay the same */
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

        /* Print specific styles */
        @media print {
            /* Hide non-printable elements */
            .dashboard nav,
            .search-actions,
            .no-print {
                display: none !important;
            }

            /* Reset margins and padding for print */
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            .content {
                margin: 0 !important;
                padding: 15px !important;
                width: 100% !important;
            }

            /* Ensure tables break properly */
            .material-group {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }

            .report-table {
                width: 100% !important;
                margin: 10px 0;
                border: 1px solid #000;
            }

            .report-table th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .report-table th,
            .report-table td {
                border: 1px solid #000;
            }

            /* Ensure text is black for better printing */
            * {
                color: black !important;
            }

            /* Format headers nicely */
            h2, h3 {
                margin: 10px 0;
                page-break-after: avoid;
            }

            /* Remove shadows and unnecessary styles */
            .section {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <div class="report-header">
                    <h2>Material Stock Report</h2>
                    <div class="search-actions d-flex gap-3 align-items-center">
                        <form class="d-flex" method="GET">
                            <input type="search" 
                                   name="search" 
                                   class="form-control me-2" 
                                   placeholder="Search materials..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="material_report.php" class="btn btn-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                        <button onclick="printReport()" class="btn btn-success no-print">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button onclick="downloadCsv()" class="btn btn-primary no-print">
                            <i class="fas fa-download"></i> Download CSV
                        </button>
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
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
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
                                        <td>Rs. <?php echo number_format($material['purchase_price'], 2); ?></td>
                                        <td>Rs. <?php echo number_format($material['selling_price'], 2); ?></td>
                                        <td>Rs. <?php echo number_format($material['quantity'] * $material['purchase_price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    function printReport() {
        // Remove existing print styles if any
        const existingStyle = document.getElementById('dynamic-print-style');
        if (existingStyle) {
            existingStyle.remove();
        }

        // Create title for print
        const style = document.createElement('style');
        style.id = 'dynamic-print-style';
        style.innerHTML = `
            @media print {
                @page {
                    size: landscape;
                    margin: 1cm;
                }
                body::before {
                    content: "Material Stock Report - ${new Date().toLocaleDateString()}";
                    display: block;
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 20px;
                }
            }
        `;
        document.head.appendChild(style);

        window.print();
    }

    function downloadCsv() {
        window.location.href = 'download_material_report.php<?php echo !empty($search) ? "?search=" . urlencode($search) : ""; ?>';
    }
    </script>
</body>
</html>
