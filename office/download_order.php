<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

// Get complete order data
$order = $conn->query("
    SELECT o.*, rdm.*, wdm.*,
           u1.name as prepared_by_name,
           u1.contact as prepared_by_contact,
           u2.name as checked_by_name,
           u3.name as admin_approved_by_name,
           o.admin_approved_at,
           q.total_amount as order_value
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN users u3 ON o.admin_approved_by = u3.id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.id = $order_id
")->fetch_assoc();

// Get materials if any
$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

if ($materials) {
    $order['materials'] = $materials;
}

// Get order status
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_status = $result->fetch_assoc()['status'] ?? null;

// Only show signatures for reviewed, completed, or done orders
$showSignature = in_array($order_status, ['reviewed', 'completed', 'done']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order #<?php echo $order_id; ?> Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            padding: 40px 0;
        }
        .order-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .logo-section img {
            max-width: 200px;
            height: auto;
        }
        .section-title {
            background-color: #f8f9fa;
            color: #333;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            border-left: 5px solid rgb(255, 179, 0);
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 200px;
        }
        .materials-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 20px 0;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .materials-table th {
            background-color: rgb(255, 179, 0);
            color: black;
            padding: 12px;
            font-weight: bold;
        }
        .materials-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .materials-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .signature-section {
            margin-top: 50px;
            text-align: center;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        .signature-section img {
            max-width: 150px;
            margin: 10px auto;
        }
        .signature-text {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        @media print {
            body { padding: 0; }
            .order-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="order-container">
        <!-- Logo Section -->
        <div class="logo-section">
            <img src="../logo.jpg" alt="Company Logo" class="mb-3">
            <h1 class="fs-2 text-center mb-0">Order #<?php echo $order_id; ?></h1>
        </div>

        <!-- Customer Information -->
        <div class="mb-4">
            <h2 class="section-title">Customer Information</h2>
            <table class="info-table">
                <tr>
                    <td>Name:</td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                </tr>
                <tr>
                    <td>Contact:</td>
                    <td><?php echo htmlspecialchars($order['customer_contact']); ?></td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td><?php echo htmlspecialchars($order['customer_address']); ?></td>
                </tr>
            </table>
        </div>

        <!-- Roller Door Measurements -->
        <div class="mb-4">
            <h2 class="section-title">Roller Door Specifications</h2>
            <div class="row">
                <div class="col-md-6">
                    <table class="info-table">
                        <tr>
                            <td>Outside Width:</td>
                            <td><?php echo $order['outside_width'] ? $order['outside_width'] . ' inches' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Inside Width:</td>
                            <td><?php echo $order['inside_width'] ? $order['inside_width'] . ' inches' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Door Width:</td>
                            <td><?php echo $order['door_width'] ? $order['door_width'] . ' inches' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Tower Height:</td>
                            <td><?php echo $order['tower_height'] ? $order['tower_height'] . ' inches' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Total Square Feet:</td>
                            <td><?php echo $order['total_sqft'] ? $order['total_sqft'] : 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="info-table">
                        <tr>
                            <td>Coil Color:</td>
                            <td><?php echo $order['coil_color'] ? str_replace('_', ' ', ucfirst($order['coil_color'])) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Tower Type:</td>
                            <td><?php echo $order['tower_type'] ? ucfirst($order['tower_type']) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Thickness:</td>
                            <td><?php echo $order['thickness'] ? $order['thickness'] : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Motor:</td>
                            <td><?php echo $order['motor'] ? ($order['motor'] === 'L' ? 'Left' : ($order['motor'] === 'R' ? 'Right' : 'Manual')) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Fixing:</td>
                            <td><?php echo $order['fixing'] ? ucfirst($order['fixing']) : 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Wicket Door if exists -->
        <?php if ($order['point1']): ?>
        <div class="mb-4">
            <h2 class="section-title">Wicket Door Specifications</h2>
            <div class="row">
                <div class="col-md-6">
                    <table class="info-table">
                        <tr><td>Point 1:</td><td><?php echo $order['point1']; ?> inches</td></tr>
                        <tr><td>Point 2:</td><td><?php echo $order['point2']; ?> inches</td></tr>
                        <tr><td>Point 3:</td><td><?php echo $order['point3']; ?> inches</td></tr>
                        <tr><td>Point 4:</td><td><?php echo $order['point4']; ?> inches</td></tr>
                        <tr><td>Point 5:</td><td><?php echo $order['point5']; ?> inches</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="info-table">
                        <tr><td>Door Opening:</td><td><?php echo str_replace('_', ' ', ucfirst($order['door_opening'])); ?></td></tr>
                        <tr><td>Handle:</td><td><?php echo $order['handle'] ? 'Yes' : 'No'; ?></td></tr>
                        <tr><td>Letter Box:</td><td><?php echo $order['letter_box'] ? 'Yes' : 'No'; ?></td></tr>
                        <tr><td>Door Type:</td><td><?php echo $order['door_type'] ? ucfirst($order['door_type']) : 'N/A'; ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Materials Section if exists -->
        <?php if (!empty($materials)): ?>
        <div class="mb-4">
            <h2 class="section-title">Materials</h2>
            <table class="materials-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Specifications</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($material['name']); ?></td>
                        <td>
                            <?php if (isset($material['type']) && $material['type'] == 'coil'): ?>
                                Color: <?php echo str_replace('_', ' ', ucfirst($material['color'] ?? 'N/A')); ?><br>
                                Thickness: <?php echo $material['thickness'] ?? 'N/A'; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $material['used_quantity']; ?></td>
                        <td><?php echo htmlspecialchars($material['unit']); ?></td>
                        <td>Rs. <?php echo number_format($material['price'], 2); ?></td>
                        <td>Rs. <?php echo number_format($material['price'] * $material['used_quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Signature Section -->
        <?php if ($showSignature): ?>
        <div class="signature-section">
            <img src="../esign.jpg" alt="Signature">
            <div class="signature-text">Authorized Signature</div>
        </div>
        <?php endif; ?>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
