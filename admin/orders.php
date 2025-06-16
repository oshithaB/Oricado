<?php
require_once '../config/config.php';
checkAuth(['admin']);

$orders = $conn->query("
    SELECT o.*, 
    u1.name as prepared_by_name,
    u2.name as checked_by_name,
    COUNT(om.id) as materials_count,
    SUM(CASE WHEN m.type = 'coil' THEN om.quantity ELSE 0 END) as total_coil_sqft
    FROM orders o
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN order_materials om ON o.id = om.order_id
    LEFT JOIN materials m ON om.material_id = m.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders Overview</title>
    <!-- Add Bootstrap and Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .content {
            padding: 30px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            width: 100%;
            margin-top: 20px;
        }
        
        th {
            background-color: #f8f9fa;
            padding: 15px;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
            text-align: center;
        }
        
        .status-pending { background-color: #ffc107; color: #000; }
        .status-processing { background-color: #17a2b8; color: #fff; }
        .status-done { background-color: #28a745; color: #fff; }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Keep existing navigation -->
        <nav>
            <h2>Orders Overview</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total Coil (sqft)</th>
                            <th>Total Price</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            switch ($order['status']) {
                                case 'pending':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'processing':
                                    $statusClass = 'status-processing';
                                    break;
                                case 'done':
                                    $statusClass = 'status-done';
                                    break;
                                default:
                                    $statusClass = '';
                                    break;
                            }
                        ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo number_format($order['total_coil_sqft'], 2); ?></td>
                            <td><strong>Rs. <?php echo number_format($order['total_price'], 2); ?></strong></td>
                            <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
