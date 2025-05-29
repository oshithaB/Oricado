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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order #<?php echo $order_id; ?> Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 200px;
            height: auto;
        }
        h1 {
            color: #CC8800;
            text-align: center;
            margin: 20px 0;
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #CC8800;
            border-radius: 5px;
        }
        h2 {
            background: #CC8800;
            color: white;
            padding: 8px 15px;
            margin: 0 0 15px 0;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th {
            background: #CC8800;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px;
            border: 1px solid #CC8800;
        }
        tr:nth-child(even) {
            background: #FFF0D9;
        }
        tr:hover {
            background: #FFE0B3;
        }
        .materials-header {
            background: #CC8800;
            color: white;
            padding: 10px;
            font-weight: bold;
        }
        .total-row {
            background: #CC8800;
            color: white;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 50px;
            text-align: center;
            border-top: 2px solid #CC8800;
            padding-top: 20px;
        }
        .signature-section img {
            width: 150px;
            margin: 10px auto;
            display: block;
        }
        .signature-text {
            color: black;
            font-weight: bold;
            margin-top: 10px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .section { border-color: black; }
            h2 { background: white; color: black; border-bottom: 2px solid #CC8800; }
            th { background: white !important; color: black; border: 1px solid black; }
            td { border-color: black; }
            .signature-section { border-top-color: black; }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="logo">
        <img src="../logo.jpg" alt="Company Logo">
    </div>

    <h1>Order #<?php echo $order_id; ?></h1>
    
    <!-- Customer Information -->
    <div class="section">
        <h2>Customer Information</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
    </div>

    <!-- Roller Door Measurements -->
    <div class="section">
        <h2>Roller Door Measurements</h2>
        <p><strong>Section 1:</strong> <?php echo $order['section1']; ?> inches</p>
        <p><strong>Section 2:</strong> <?php echo $order['section2']; ?> inches</p>
        <p><strong>Door Width:</strong> <?php echo $order['door_width']; ?> inches</p>
        <p><strong>Total Square Feet:</strong> <?php echo $order['total_sqft']; ?></p>
    </div>

    <!-- Wicket Door if exists -->
    <?php if ($order['point1']): ?>
    <div class="section">
        <h2>Wicket Door Measurements</h2>
        <p><strong>Point 1:</strong> <?php echo $order['point1']; ?></p>
        <p><strong>Point 2:</strong> <?php echo $order['point2']; ?></p>
        <p><strong>Point 3:</strong> <?php echo $order['point3']; ?></p>
        <p><strong>Point 4:</strong> <?php echo $order['point4']; ?></p>
        <p><strong>Point 5:</strong> <?php echo $order['point5']; ?></p>
        <p><strong>Door Opening:</strong> <?php echo str_replace('_', ' ', ucfirst($order['door_opening'])); ?></p>
        <p><strong>Handle:</strong> <?php echo $order['handle'] ? 'Yes' : 'No'; ?></p>
        <p><strong>Letter Box:</strong> <?php echo $order['letter_box'] ? 'Yes' : 'No'; ?></p>
    </div>
    <?php endif; ?>

    <!-- Materials -->
    <?php if (!empty($materials)): ?>
    <div class="section">
        <h2>Materials</h2>
        <table>
            <tr>
                <th>Material</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
            <?php foreach ($materials as $material): ?>
            <tr>
                <td><?php echo htmlspecialchars($material['name']); ?></td>
                <td><?php echo $material['used_quantity']; ?></td>
                <td><?php echo htmlspecialchars($material['unit']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Total -->
    <div class="section">
        <h2>Order Summary</h2>
        <p><strong>Total Price:</strong> Rs. <?php echo number_format($order['total_price'], 2); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        <p><strong>Created Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
    </div>

    <!-- Signature -->
    <div class="signature-section">
        <img src="../esign.jpg" alt="Signature">
        <div class="signature-text">Sign</div>
    </div>
</body>
</html>
