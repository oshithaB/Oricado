<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Add this function at the top after require statements
function formatOrderNumber($orderId, $createdAt) {
    $date = new DateTime($createdAt);
    return sprintf(
        "SO/%s/%s/%s/%05d",
        $date->format('d'),
        $date->format('m'),
        $date->format('y'),
        $orderId
    );
}

// Get search parameters
$search = $_GET['search'] ?? '';

// Modify query to check for final invoice
$query = "
    SELECT o.*, 
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           COALESCE(SUM(CASE WHEN i.invoice_type = 'advance' THEN i.amount ELSE 0 END), 0) as advance_paid,
           o.total_price - COALESCE(SUM(CASE WHEN i.invoice_type = 'advance' THEN i.amount ELSE 0 END), 0) as balance_amount,
           EXISTS(SELECT 1 FROM invoices i2 WHERE i2.order_id = o.id AND i2.invoice_type = 'final') as has_final_invoice,
           q.type as quotation_type
    FROM orders o
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN invoices i ON o.id = i.order_id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.status = 'pending'
    GROUP BY o.id";

// Add search condition if search term exists
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (o.id LIKE '%$search%' 
                OR o.customer_name LIKE '%$search%'
                OR o.customer_contact LIKE '%$search%')";
}

$query .= " ORDER BY o.created_at DESC";

$orders = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pending-title {
            text-align: center;
            color: #d4af37;
            font-size: 2.5em;
            font-weight: bold;
            margin: 30px 0 40px 0;
            letter-spacing: 2px;
            text-shadow: 0 2px 8px rgba(212,175,55,0.15);
        }
        .order-card {
            margin-bottom: 32px;
            border: 2px solid #e0e0e0;
            border-radius: 14px;
            background: #fafbfc;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            padding: 24px 28px;
            transition: box-shadow 0.2s;
        }
        .order-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .order-title {
            font-size: 1.5em;
            margin: 0 0 12px 0;
        }
        .order-title .order-word {
            font-family: 'Segoe Script', 'Comic Sans MS', cursive;
            color: #007bff;
            font-weight: 600;
        }
        .order-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .balance-status {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
            display: inline-block;
            margin-top: 10px;
        }
        .balance-pending {
            color: #dc3545;
            background-color: #fff3cd;
        }
        .balance-paid {
            color: #28a745;
            background-color: #d4edda;
        }
        .btn-material {
            background-color: #28a745;
            color: white;
        }
        .btn-material:hover {
            background-color: #218838;
            color: white;
        }
        .btn-done {
            background-color: #007bff;
            color: white;
        }
        .modal-content {
            border-radius: 12px;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        
        /* Search bar styling */
        .search-container {
            margin: 20px auto;
            max-width: 800px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .search-button {
            padding: 10px 20px;
            background-color: rgb(255, 179, 0);
            color: black;
            border: 2px solid black;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .search-button:hover {
            background-color: black;
            color: white;
        }

        .amount-details {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        .amount-details div {
            margin: 2px 0;
        }

        .order-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }

        .order-actions .btn {
            width: 100%;
            text-align: center;
            padding: 10px 15px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 200px;
        }

        .btn.btn-material,
        .btn.btn-secondary,
        .btn.btn-info,
        .btn.btn-primary,
        .btn.btn-success {
            width: 100%;
            margin: 0;
        }

        .current-advance {
            font-size: 12px;
            padding: 4px 8px;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="container-fluid py-4">
                <h2 class="pending-title">Pending Orders</h2>

                <!-- Add search section -->
                <div class="search-container">
                    <form method="GET" class="search-form">
                        <input type="text" 
                               name="search" 
                               class="search-input"
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by Order ID, Customer Name or Contact">
                        <button type="submit" class="search-button">Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="pending_orders.php" class="search-button">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="text-center mt-4">
                        <p>No pending orders found<?php echo !empty($search) ? ' for your search' : ''; ?>.</p>
                    </div>
                <?php endif; ?>

                <!-- Materials Modal -->
                <div class="modal fade" id="materialsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Order Materials</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="materialsList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="order-title">
                                <span class="order-word">Order</span> #<?php echo formatOrderNumber($order['id'], $order['created_at']); ?>
                            </h3>
                            <div class="order-info">
                                <p class="mb-2"><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p class="mb-2"><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                                <p class="mb-2"><strong>Prepared By:</strong> <?php echo htmlspecialchars($order['prepared_by_name']); ?></p>
                                <p class="mb-2"><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                                
                                <div class="balance-status <?php echo $order['balance_amount'] > 0 ? 'balance-pending' : 'balance-paid'; ?>">
                                    <?php if ($order['has_final_invoice']): ?>
                                        <span class="badge bg-success">PAID</span>
                                        <div class="amount-details mt-1">
                                            Total Amount: Rs. <?php echo number_format($order['total_price'], 2); ?>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($order['balance_amount'] > 0): ?>
                                            <div>Balance: Rs. <?php echo number_format($order['balance_amount'], 2); ?></div>
                                            <div class="amount-details mt-1">
                                                <div>Order Amount: Rs. <?php echo number_format($order['total_price'], 2); ?></div>
                                                <?php if ($order['advance_paid'] > 0): ?>
                                                    <div>Advance Paid: Rs. <?php echo number_format($order['advance_paid'], 2); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            PAID
                                            <div class="amount-details mt-1">
                                                Total Amount: Rs. <?php echo number_format($order['total_price'], 2); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="order-actions d-flex flex-column gap-2 justify-content-start align-items-stretch">
                                <?php if ($order['quotation_type'] === 'raw_materials'): ?>
                                    <button type="button" onclick="showMaterials(<?php echo $order['quotation_id']; ?>)" 
                                            class="btn btn-material">Show Materials</button>
                                    
                                    <?php if (!$order['has_final_invoice']): ?>
                                        <?php if ($order['advance_paid'] > 0): ?>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=advance" 
                                               class="btn btn-info">
                                                Add to Advance (Current: Rs. <?php echo number_format($order['advance_paid'], 2); ?>)
                                            </a>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=final" 
                                               class="btn btn-primary">Create Final Invoice</a>
                                        <?php else: ?>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=advance" 
                                               class="btn btn-primary">Create Advance Invoice</a>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=final" 
                                               class="btn btn-success">Create Final Invoice</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <!-- Add Done button -->
                                    <button type="button" onclick="markOrderAsDone(<?php echo $order['id']; ?>)" 
                                            class="btn btn-warning">Mark as Done</button>
                                <?php else: ?>
                                    <a href="download_order.php?id=<?php echo $order['id']; ?>" 
                                       class="btn btn-secondary w-100">Download Order</a>
                                    <?php if (!$order['has_final_invoice']): ?>
                                        <?php if ($order['advance_paid'] > 0): ?>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=advance" 
                                               class="btn btn-info">
                                                Add to Advance (Current: Rs. <?php echo number_format($order['advance_paid'], 2); ?>)
                                            </a>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=final" 
                                               class="btn btn-primary">Create Final Invoice</a>
                                        <?php else: ?>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=advance" 
                                               class="btn btn-primary">Create Advance Invoice</a>
                                            <a href="create_invoice.php?id=<?php echo $order['id']; ?>&type=final" 
                                               class="btn btn-success">Create Final Invoice</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showMaterials(quotationId) {
        fetch(`get_quotation_materials.php?quotation_id=${quotationId}`)
            .then(response => response.json())
            .then(data => {
                let html = '<table class="table">';
                html += '<thead><tr><th>Item</th><th>Quantity</th><th>Unit</th><th>Price</th><th>Amount</th></tr></thead>';
                html += '<tbody>';
                data.forEach(item => {
                    html += `<tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>${item.unit}</td>
                        <td>Rs. ${parseFloat(item.price).toFixed(2)}</td>
                        <td>Rs. ${parseFloat(item.amount).toFixed(2)}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('materialsList').innerHTML = html;
                
                // Use Bootstrap's modal method
                const modal = new bootstrap.Modal(document.getElementById('materialsModal'));
                modal.show();
            });
    }

    function markOrderAsDone(orderId) {
        if (confirm('Are you sure you want to mark this order as done?')) {
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&status=done`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order marked as done successfully');
                    location.reload();
                } else {
                    alert('Error updating order status');
                }
            });
        }
    }
    </script>
</body>
</html>
