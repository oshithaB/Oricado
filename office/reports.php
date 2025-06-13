<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Format dates for MySQL if provided
$formatted_start = !empty($start_date) ? date('Y-m-d 00:00:00', strtotime($start_date)) : null;
$formatted_end = !empty($end_date) ? date('Y-m-d 23:59:59', strtotime($end_date)) : null;

// Get orders data if date range is selected
$orders_data = null;
$total_order_amount = 0;
$total_paid_amount = 0;

if ($formatted_start && $formatted_end) {
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

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $formatted_start, $formatted_end);
    $stmt->execute();
    $orders_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calculate totals
    foreach ($orders_data as $order) {
        $total_order_amount += $order['total_price'];
        $total_paid_amount += $order['paid_amount'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add DateRangePicker CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .report-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .date-range-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .report-actions {
            margin: 20px 0;
        }
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="report-container">
                <h2 class="mb-4">Reports</h2>

                <!-- Date Range Selection Form -->
                <div class="date-range-form">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="startDate" class="form-control" 
                                   value="<?php echo $start_date; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="endDate" class="form-control" 
                                   value="<?php echo $end_date; ?>" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                        <?php if (!empty($start_date) && !empty($end_date)): ?>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="reports.php" class="btn btn-secondary">Clear</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if ($orders_data): ?>
                    <!-- Summary Section -->
                    <div class="summary-box">
                        <h4>Summary</h4>
                        <div class="summary-item">
                            <strong>Total Order Value:</strong>
                            <span>Rs. <?php echo number_format($total_order_amount, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <strong>Total Received Amount:</strong>
                            <span>Rs. <?php echo number_format($total_paid_amount, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <strong>Total Outstanding:</strong>
                            <span>Rs. <?php echo number_format($total_order_amount - $total_paid_amount, 2); ?></span>
                        </div>
                    </div>

                    <!-- Download Button -->
                    <div class="report-actions">
                        <button onclick="downloadReport()" class="btn btn-success">
                            <i class="fas fa-download"></i> Download Report
                        </button>
                    </div>

                    <!-- Orders Table -->
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Paid Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders_data as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_contact']); ?></td>
                                <td><?php echo htmlspecialchars($order['created_by_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                                <td>Rs. <?php echo number_format($order['total_price'], 2); ?></td>
                                <td>Rs. <?php echo number_format($order['paid_amount'], 2); ?></td>
                                <td>Rs. <?php echo number_format($order['balance_amount'], 2); ?></td>
                                <td><?php echo ucfirst($order['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function() {
            $('#dateRange').daterangepicker({
                opens: 'left',
                autoUpdateInput: false, // Disable auto-update
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });

            // Handle apply event
            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $('#startDate').val(picker.startDate.format('YYYY-MM-DD'));
                $('#endDate').val(picker.endDate.format('YYYY-MM-DD'));
            });

            // Handle cancel event
            $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#startDate').val('');
                $('#endDate').val('');
            });
        });

        function downloadReport() {
            // Get the table HTML
            let table = document.getElementById('ordersTable');
            
            // Convert table to CSV
            let csv = [];
            let rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    // Clean the text content
                    let text = cols[j].textContent.replace(/\s+/g, ' ').trim();
                    // Quote the text and escape existing quotes
                    row.push('"' + text.replace(/"/g, '""') + '"');
                }
                
                csv.push(row.join(','));
            }

            // Add summary data
            csv.push(''); // Empty line
            csv.push('"Summary"');
            csv.push('"Total Order Value","<?php echo number_format($total_order_amount, 2); ?>"');
            csv.push('"Total Received Amount","<?php echo number_format($total_paid_amount, 2); ?>"');
            csv.push('"Total Outstanding","<?php echo number_format($total_order_amount - $total_paid_amount, 2); ?>"');
            
            // Create CSV file
            let csvFile = csv.join('\n');
            let blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
            let link = document.createElement("a");
            let url = URL.createObjectURL(blob);
            
            // Set file name with date range
            let fileName = 'orders_report_<?php echo $start_date; ?>_to_<?php echo $end_date; ?>.csv';
            
            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Get date input elements
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

            // Set max date to today for both calendars
            const today = new Date().toISOString().split('T')[0];
            startDate.max = today;
            endDate.max = today;

            // Update end date min value when start date changes
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
            });

            // Update start date max value when end date changes
            endDate.addEventListener('change', function() {
                startDate.max = this.value;
                if (startDate.value && startDate.value > this.value) {
                    startDate.value = this.value;
                }
            });
        });
    </script>
</body>
</html>
