<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Get search parameter
$search = $_GET['search'] ?? '';
$searchCondition = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $searchCondition = "WHERE m.name LIKE '%$search%' OR m.type LIKE '%$search%' OR m.color LIKE '%$search%'";
}

// Get materials data
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

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="material_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add headers row
fputcsv($output, [
    'Material Type',
    'Material Name',
    'Color',
    'Thickness',
    'Current Stock',
    'Unit',
    'Times Used',
    'Total Used',
    'Purchase Price (Rs.)',
    'Selling Price (Rs.)',
    'Stock Value (Rs.)'
]);

// Add data rows
foreach ($materials as $material) {
    $row = [
        ucfirst($material['type']),
        $material['name'],
        $material['type'] == 'coil' ? str_replace('_', ' ', ucfirst($material['color'])) : '',
        $material['type'] == 'coil' ? $material['thickness'] : '',
        number_format($material['quantity'], 2),
        $material['unit'],
        $material['times_used'],
        number_format($material['used_quantity'], 2),
        number_format($material['purchase_price'], 2),
        number_format($material['selling_price'], 2),
        number_format($material['quantity'] * $material['purchase_price'], 2)
    ];
    fputcsv($output, $row);
}

// Add totals
$total_stock_value = array_sum(array_map(function($m) {
    return $m['quantity'] * $m['purchase_price'];
}, $materials));

fputcsv($output, []); // Empty row
fputcsv($output, ['', '', '', '', '', '', '', '', '', 'Total Stock Value:', number_format($total_stock_value, 2)]);

fclose($output);
exit;
