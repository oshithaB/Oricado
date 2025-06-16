<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$report_type = $_POST['report_type'] ?? 'payment';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

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

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="report_' . $report_type . '_' . date('Y-m-d') . '.csv"');

// Create output handle
$output = fopen('php://output', 'w');

// Add headers row
if ($report_type === 'profit') {
    fputcsv($output, ['Order ID', 'Customer', 'Created By', 'Date', 'Total Amount', 
                      'Material Cost', 'Profit']);
} else {
    fputcsv($output, ['Order ID', 'Customer', 'Created By', 'Date', 'Total Amount', 
                      'Paid Amount', 'Balance', 'Status']);
}

// Add data rows
foreach ($orders as $order) {
    $row = [
        $order['id'],
        $order['customer_name'],
        $order['created_by_name'],
        date('Y-m-d', strtotime($order['created_at'])),
        number_format($order['total_price'], 2)
    ];

    if ($report_type === 'profit') {
        $row[] = number_format($order['material_cost'], 2);
        $row[] = number_format($order['profit'], 2);
    } else {
        $row[] = number_format($order['paid_amount'], 2);
        $row[] = number_format($order['balance_amount'], 2);
        $row[] = ucfirst($order['status']);
    }

    fputcsv($output, $row);
}

// Add summary row
fputcsv($output, []); // Empty row
fputcsv($output, ['Summary']);
if ($report_type === 'profit') {
    $total_sales = array_sum(array_column($orders, 'total_price'));
    $total_cost = array_sum(array_column($orders, 'material_cost'));
    $total_profit = array_sum(array_column($orders, 'profit'));
    
    fputcsv($output, ['Total Sales', number_format($total_sales, 2)]);
    fputcsv($output, ['Total Material Cost', number_format($total_cost, 2)]);
    fputcsv($output, ['Total Profit', number_format($total_profit, 2)]);
} else {
    $total_order = array_sum(array_column($orders, 'total_price'));
    $total_paid = array_sum(array_column($orders, 'paid_amount'));
    
    fputcsv($output, ['Total Order Value', number_format($total_order, 2)]);
    fputcsv($output, ['Total Received Amount', number_format($total_paid, 2)]);
    fputcsv($output, ['Total Outstanding', number_format($total_order - $total_paid, 2)]);
}

fclose($output);
exit;
