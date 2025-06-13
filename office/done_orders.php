<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Modify query to match pending_orders.php structure
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
    WHERE o.status = 'done'
    GROUP BY o.id
    ORDER BY o.created_at DESC";

$orders = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Done Orders</title>
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
        .modal-content {
            border-radius: 12px;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .amount-details {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        .amount-details div {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="container-fluid py-4">
                <h2 class="pending-title">Done Orders</h2>

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
                                <span class="order-word">Order</span> #<?php echo $order['id']; ?>
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
                                            <div>Total Amount: Rs. <?php echo number_format($order['total_price'], 2); ?></div>
                                            <?php if ($order['advance_paid'] > 0): ?>
                                                <div>Advance Paid: Rs. <?php echo number_format($order['advance_paid'], 2); ?></div>
                                            <?php endif; ?>
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
                                                <div>Total Amount: Rs. <?php echo number_format($order['total_price'], 2); ?></div>
                                                <?php if ($order['advance_paid'] > 0): ?>
                                                    <div>Advance Paid: Rs. <?php echo number_format($order['advance_paid'], 2); ?></div>
                                                <?php endif; ?>
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
</script>
</body>
</html>
