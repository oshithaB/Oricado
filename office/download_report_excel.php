<?php
require_once '../config/config.php';
require 'vendor/autoload.php';
checkAuth(['office_staff']);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$report_type = $_GET['report_type'] ?? 'payment';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$formatted_start = !empty($start_date) ? date('Y-m-d 00:00:00', strtotime($start_date)) : null;
$formatted_end = !empty($end_date) ? date('Y-m-d 23:59:59', strtotime($end_date)) : null;

// Get data based on report type
if ($report_type === 'profit') {
    $query = "SELECT o.*, 
                     u.name as created_by_name,
                     o.material_cost,
                     (o.total_price - COALESCE(o.material_cost, 0)) as profit
              FROM orders o
              LEFT JOIN users u ON o.prepared_by = u.id
              WHERE o.created_at BETWEEN ? AND ?
              ORDER BY o.created_at DESC";
} else {
    $query = "SELECT o.*, 
                     u.name as created_by_name,
                     COALESCE(SUM(i.amount), 0) as paid_amount,
                     o.total_price - COALESCE(SUM(i.amount), 0) as balance_amount
              FROM orders o
              LEFT JOIN users u ON o.prepared_by = u.id
              LEFT JOIN invoices i ON o.id = i.order_id
              WHERE o.created_at BETWEEN ? AND ?
              GROUP BY o.id
              ORDER BY o.created_at DESC";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $formatted_start, $formatted_end);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers based on report type
if ($report_type === 'profit') {
    $headers = ['Order ID', 'Customer', 'Created By', 'Date', 'Total Amount', 
                'Material Cost', 'Profit'];
} else {
    $headers = ['Order ID', 'Customer', 'Created By', 'Date', 'Total Amount', 
                'Paid Amount', 'Balance', 'Status'];
}

$sheet->fromArray([$headers], NULL, 'A1');

// Add data
$row = 2;
foreach ($orders as $order) {
    $data = [
        $order['id'],
        $order['customer_name'],
        $order['created_by_name'],
        date('Y-m-d', strtotime($order['created_at'])),
        number_format($order['total_price'], 2)
    ];

    if ($report_type === 'profit') {
        $data[] = number_format($order['material_cost'], 2);
        $data[] = number_format($order['profit'], 2);
    } else {
        $data[] = number_format($order['paid_amount'], 2);
        $data[] = number_format($order['balance_amount'], 2);
        $data[] = ucfirst($order['status']);
    }

    $sheet->fromArray([$data], NULL, "A$row");
    $row++;
}

// Style the sheet
$sheet->getStyle('A1:' . ($report_type === 'profit' ? 'G1' : 'H1'))->getFont()->setBold(true);
foreach(range('A', ($report_type === 'profit' ? 'G' : 'H')) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set filename and headers
$filename = 'orders_report_' . $report_type . '_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Save file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
