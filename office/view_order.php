<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

// Get complete order details with all measurements and pricing
$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u1.name as prepared_by_name,
           u1.contact as prepared_by_contact,
           u2.name as checked_by_name,
           u3.name as admin_approved_by_name,
           o.admin_approved_at,
           q.id as quotation_id,
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

// Get materials with costs
$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity,
           (m.price * om.quantity) as material_cost
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

$total_material_cost = array_sum(array_column($materials, 'material_cost'));
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Order</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <style>
/* General Page Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
    color: #333;
}

.dashboard {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Section Styling */
.section {
    margin-bottom: 20px;
    padding: 20px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section h3 {
    margin-top: 0;
    font-size: 18px;
    color: rgb(255, 179, 0); /* Yellow color for headings */
    border-bottom: 2px solid rgb(255, 179, 0);
    padding-bottom: 5px;
}

/* Measurements Grid */
.measurements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.measurements-grid p {
    background-color: #f9f9f9;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

table th {
    background-color: rgb(255, 179, 0); /* Yellow background */
    color: black;
    text-align: left;
    padding: 12px;
    font-size: 14px;
    font-weight: bold;
    border-bottom: 2px solid black;
}

table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    color: #333;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: rgb(255, 230, 128); /* Light yellow on hover */
}

/* Buttons Styling */

.button {
    padding: 10px 20px;
    background-color: rgb(255, 179, 0); /* Yellow background */
    color: black;
    border: 2px solid black;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
    display: inline-block;
    margin-right: 10px;
}

.button:hover {
    background-color: black; /* Black background on hover */
    color: white; /* White text on hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .measurements-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    table th, table td {
        font-size: 12px;
        padding: 8px;
    }

    .button {
        font-size: 12px;
        padding: 8px 15px;
    }
}
</style>
    <div class="dashboard">
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        
        <div class="content">
            <h2>Order Details</h2>
            <div class="order-reference">
                <p>Order #<?php echo $order['id']; ?></p>
                <?php if ($order['quotation_id']): ?>
                    <p>Quotation #<?php echo $order['quotation_id']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Customer Information -->
            <div class="section">
                <h3>Customer Information</h3>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Order Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                <p><strong>Created Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                <?php if ($order['total_price']): ?>
                    <p><strong>Total Price:</strong> Rs. <?php echo number_format($order['total_price'], 2); ?></p>
                <?php endif; ?>
            </div>

            <!-- Roller Door Measurements -->
            <div class="section">
                <h3>Roller Door Measurements</h3>
                <div class="measurements-grid">
                    <p><strong>Section 1:</strong> <?php echo $order['section1']; ?></p>
                    <p><strong>Section 2:</strong> <?php echo $order['section2']; ?></p>
                    <p><strong>Outside Width:</strong> <?php echo $order['outside_width']; ?></p>
                    <p><strong>Inside Width:</strong> <?php echo $order['inside_width']; ?></p>
                    <p><strong>Door Width:</strong> <?php echo $order['door_width']; ?></p>
                    <p><strong>Tower Height:</strong> <?php echo $order['tower_height']; ?></p>
                    <p><strong>Tower Type:</strong> <?php echo ucfirst($order['tower_type']); ?></p>
                    <p><strong>Coil Color:</strong> <?php echo str_replace('_', ' ', ucfirst($order['coil_color'])); ?></p>
                    <p><strong>Thickness:</strong> <?php echo $order['thickness']; ?></p>
                    <p><strong>Covering:</strong> <?php echo ucfirst($order['covering']); ?></p>
                    <p><strong>Side Lock:</strong> <?php echo $order['side_lock'] ? 'Yes' : 'No'; ?></p>
                    <p><strong>Motor:</strong> <?php echo $order['motor'] == 'manual' ? 'Manual' : $order['motor']; ?></p>
                    <p><strong>Fixing:</strong> <?php echo ucfirst($order['fixing']); ?></p>
                    <p><strong>Down Lock:</strong> <?php echo $order['down_lock'] ? 'Yes' : 'No'; ?></p>
                </div>
            </div>

            <!-- Wicket Door Measurements if exists -->
            <?php if ($order['point1']): ?>
            <div class="section">
                <h3>Wicket Door Measurements</h3>
                <div class="measurements-grid">
                    <p><strong>Point 1:</strong> <?php echo $order['point1']; ?></p>
                    <p><strong>Point 2:</strong> <?php echo $order['point2']; ?></p>
                    <p><strong>Point 3:</strong> <?php echo $order['point3']; ?></p>
                    <p><strong>Point 4:</strong> <?php echo $order['point4']; ?></p>
                    <p><strong>Point 5:</strong> <?php echo $order['point5']; ?></p>
                    <p><strong>Thickness:</strong> <?php echo $order['thickness']; ?></p>
                    <p><strong>Door Opening:</strong> <?php echo str_replace('_', ' ', ucfirst($order['door_opening'])); ?></p>
                    <p><strong>Handle:</strong> <?php echo $order['handle'] ? 'Yes' : 'No'; ?></p>
                    <p><strong>Letter Box:</strong> <?php echo $order['letter_box'] ? 'Yes' : 'No'; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Materials and Costs -->
            <div class="section">
                <h3>Materials and Costs</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                            <td><?php echo $material['used_quantity'] . ' ' . $material['unit']; ?></td>
                            <td>Rs. <?php echo number_format($material['price'], 2); ?></td>
                            <td>Rs. <?php echo number_format($material['material_cost'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3" align="right"><strong>Total Material Cost:</strong></td>
                            <td><strong>Rs. <?php echo number_format($total_material_cost, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="actions">
                <a href="dashboard.php" class="button">Back to Dashboard</a>
                <?php if ($order['status'] == 'reviewed'): ?>
                <a href="confirm_order.php?id=<?php echo $order_id; ?>" class="button primary">Confirm Order</a>
                <?php endif; ?>
                <a href="download_order.php?id=<?php echo $order_id; ?>" class="button">Download Order Details</a>
            </div>
        </div>
    </div>
</body>
</html>
