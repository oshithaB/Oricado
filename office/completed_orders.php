<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Handle mark as done action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_done'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'done' WHERE id = ? AND status = 'completed'");
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order #$order_id marked as done successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating order status.";
    }
    
    header('Location: completed_orders.php');
    exit();
}

// Get completed orders with balance and quotation type
$orders = $conn->query("
    SELECT o.*, 
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           o.total_price - COALESCE(SUM(i.amount), 0) as balance_amount,
           COALESCE(SUM(i.amount), 0) as paid_amount,
           q.type as quotation_type
    FROM orders o
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN invoices i ON o.id = i.order_id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.status = 'completed'
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Completed Orders</title>
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
        .modal-content {
            border-radius: 12px;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="container-fluid py-4">
                <h2 class="pending-title">Completed Orders</h2>

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
                                    <?php if ($order['balance_amount'] > 0): ?>
                                        Balance: Rs. <?php echo number_format($order['balance_amount'], 2); ?>
                                    <?php else: ?>
                                        PAID
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="order-actions d-flex flex-column gap-2">
                                <?php if ($order['quotation_type'] === 'raw_materials'): ?>
                                    <button type="button" onclick="showMaterials(<?php echo $order['quotation_id']; ?>)" 
                                            class="btn btn-material w-100">Show Materials</button>
                                <?php else: ?>
                                    <a href="download_order.php?id=<?php echo $order['id']; ?>" 
                                       class="btn btn-secondary w-100">Download Order</a>
                                    <?php if ($order['balance_amount'] > 0): ?>
                                        <a href="create_invoice.php?id=<?php echo $order['id']; ?>" 
                                           class="btn btn-primary w-100">Create Final Invoice</a>
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
            // AJAX request to fetch materials based on quotation ID
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_materials.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    var response = JSON.parse(xhr.responseText);
                    var materialsList = document.getElementById('materialsList');
                    materialsList.innerHTML = '';

                    if (response.success) {
                        response.materials.forEach(function(material) {
                            var div = document.createElement('div');
                            div.classList.add('material-item', 'd-flex', 'justify-content-between', 'align-items-center', 'py-2', 'border-bottom');
                            div.innerHTML = '<div><strong>' + material.name + '</strong> (x' + material.quantity + ')</div>' +
                                            '<div>Rs. ' + parseFloat(material.price).toFixed(2) + '</div>';
                            materialsList.appendChild(div);
                        });
                    } else {
                        materialsList.innerHTML = '<div class="text-center py-3">No materials found for this order.</div>';
                    }

                    var myModal = new bootstrap.Modal(document.getElementById('materialsModal'));
                    myModal.show();
                }
            };
            xhr.send('quotation_id=' + quotationId);
        }
    </script>
</body>
</html>
