<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['office_staff']);

// Get materials data
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
$report_data = ['materials' => []];
foreach ($materials as $material) {
    $report_data['materials'][$material['type']][] = $material;
}

// Generate PDF
$pdf = new PDFGenerator('material_report');
$pdf->generatePDF($report_data, 'material_report');
