<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$report_type = $_GET['report_type'] ?? 'payment'; // Add report type selection

// Format dates for MySQL if provided
$formatted_start = !empty($start_date) ? date('Y-m-d 00:00:00', strtotime($start_date)) : null;
$formatted_end = !empty($end_date) ? date('Y-m-d 23:59:59', strtotime($end_date)) : null;

// Get orders data if date range is selected
$orders_data = null;
$total_order_amount = 0;
$total_paid_amount = 0;

// Get orders data with material cost for profit report
if ($formatted_start && $formatted_end && $report_type === 'profit') {
    $query = "SELECT o.*, 
                     u.name as created_by_name,
                     o.material_cost,
                     (o.total_price - COALESCE(o.material_cost, 0)) as profit
              FROM orders o
              LEFT JOIN users u ON o.prepared_by = u.id
              WHERE o.created_at BETWEEN ? AND ?
              ORDER BY o.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $formatted_start, $formatted_end);
    $stmt->execute();
    $orders_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calculate totals for profit report
    $total_sales = 0;
    $total_cost = 0;
    $total_profit = 0;
    foreach ($orders_data as $order) {
        $total_sales += $order['total_price'];
        $total_cost += $order['material_cost'] ?? 0;
        $total_profit += $order['profit'] ?? 0;
    }
} elseif ($formatted_start && $formatted_end) {
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-control" required>
                                <option value="payment" <?php echo $report_type === 'payment' ? 'selected' : ''; ?>>Payment Report</option>
                                <option value="profit" <?php echo $report_type === 'profit' ? 'selected' : ''; ?>>Profit Report</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="startDate" class="form-control" 
                                   value="<?php echo $start_date; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="endDate" class="form-control" 
                                   value="<?php echo $end_date; ?>" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Generate Report</button>
                            <?php if (!empty($start_date) && !empty($end_date)): ?>
                                <a href="reports.php" class="btn btn-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <?php if ($orders_data): ?>
                    <!-- Summary Section -->
                    <div class="summary-box">
                        <h4>Summary</h4>
                        <?php if ($report_type === 'payment'): ?>
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
                        <?php else: ?>
                            <!-- Profit report summary -->
                            <div class="summary-item">
                                <strong>Total Sales:</strong>
                                <span>Rs. <?php echo number_format($total_sales, 2); ?></span>
                            </div>
                            <div class="summary-item">
                                <strong>Total Material Cost:</strong>
                                <span>Rs. <?php echo number_format($total_cost, 2); ?></span>
                            </div>
                            <div class="summary-item">
                                <strong>Total Profit:</strong>
                                <span>Rs. <?php echo number_format($total_profit, 2); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Add Download Button -->
                        <?php if ($orders_data): ?>
                            <div class="mt-3">
                                <button onclick="downloadCsv()" class="btn btn-success">
                                    <i class="fas fa-download"></i> Download CSV Report
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Orders Table -->
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <?php if ($report_type === 'payment'): ?>
                                    <th>Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                <?php else: ?>
                                    <th>Material Cost</th>
                                    <th>Profit</th>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders_data as $order): ?>
                            <tr>
                                <td><?php echo $order['id'] ?? 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['created_by_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                                <td>Rs. <?php echo number_format($order['total_price'] ?? 0, 2); ?></td>
                                <?php if ($report_type === 'payment'): ?>
                                    <td>Rs. <?php echo number_format($order['paid_amount'] ?? 0, 2); ?></td>
                                    <td>Rs. <?php echo number_format($order['balance_amount'] ?? 0, 2); ?></td>
                                    <td><?php echo ucfirst($order['status'] ?? 'N/A'); ?></td>
                                <?php else: ?>
                                    <td>Rs. <?php echo number_format($order['material_cost'] ?? 0, 2); ?></td>
                                    <td>Rs. <?php echo number_format($order['profit'] ?? 0, 2); ?></td>
                                    <td>
                                        <button onclick="viewMaterials(<?php echo $order['id']; ?>)" 
                                                class="btn btn-sm btn-info">
                                            View Materials
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Materials Modal -->
                    <div class="modal fade" id="materialsModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Order Materials Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Material</th>
                                                <th>Specifications</th>
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Cost Price</th>
                                                <th>Selling Price</th>
                                            </tr>
                                        </thead>
                                        <tbody id="materialsTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    function viewMaterials(orderId) {
                        const tbody = document.getElementById('materialsTableBody');
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
                        
                        fetch(`get_order_materials.php?id=${orderId}`)
                            .then(response => response.json())
                            .then(data => {
                                tbody.innerHTML = '';
                                
                                if (!Array.isArray(data) || data.length === 0) {
                                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No materials found</td></tr>';
                                    return;
                                }

                                data.forEach(material => {
                                    let specs = [];
                                    if (material.type === 'coil') {
                                        if (material.color) specs.push(`Color: ${material.color.replace('_', ' ')}`);
                                        if (material.thickness) specs.push(`Thickness: ${material.thickness}`);
                                    }
                                    
                                    tbody.innerHTML += `
                                        <tr>
                                            <td>${material.name || 'N/A'}</td>
                                            <td>${specs.length ? specs.join('<br>') : 'N/A'}</td>
                                            <td>${material.quantity || '0'}</td>
                                            <td>${material.unit || 'N/A'}</td>
                                            <td>Rs. ${Number(material.cost || 0).toFixed(2)}</td>
                                            <td>Rs. ${Number(material.selling_price || 0).toFixed(2)}</td>
                                        </tr>
                                    `;
                                });
                                
                                new bootstrap.Modal(document.getElementById('materialsModal')).show();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading materials</td></tr>';
                            });
                    }

                    function downloadCsv() {
                        const reportType = '<?php echo $report_type; ?>';
                        const startDate = '<?php echo $start_date; ?>';
                        const endDate = '<?php echo $end_date; ?>';
                        
                        // Create form element
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'download_report.php';
                        
                        // Add hidden fields
                        const fields = {
                            report_type: reportType,
                            start_date: startDate,
                            end_date: endDate
                        };
                        
                        for (const [key, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }
                        
                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);
                    }
                    </script>
                <?php endif; ?>
            </div>
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

    function downloadReport(format) {
        let url = format === 'excel' ? 'download_report_excel.php' : 'download_report.php';
        url += '?report_type=<?php echo $report_type; ?>';
        url += '&start_date=<?php echo $start_date; ?>';
        url += '&end_date=<?php echo $end_date; ?>';
        window.location.href = url;
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
