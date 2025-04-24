<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: confirmed_orders.php');
    exit();
}

// Get complete order details
$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u.name as prepared_by_name,
           u.contact as prepared_by_contact,
           q.id as quotation_id,
           q.total_amount as quotation_amount
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u ON o.prepared_by = u.id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.id = $order_id
")->fetch_assoc();

// Get materials list
$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Order Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
/* General Page Styling */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4; /* Light gray background */
    margin: 0;
    padding: 0;
    color: #333;
}

.dashboard {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Section Styling */
.section {
    margin-bottom: 20px;
    padding: 20px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.section h3 {
    margin-top: 0;
    font-size: 20px;
    color: #d4af37; /* Gold color for headings */
    border-bottom: 2px solid #d4af37;
    padding-bottom: 5px;
}

/* Info Grid Styling */
.info-grid, .measurements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.info-grid p, .measurements-grid p {
    background-color: #f9f9f9;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    font-size: 14px;
    color: #333;
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
    background-color: #d4af37; /* Gold background */
    color: white;
    text-align: left;
    padding: 12px;
    font-size: 14px;
    font-weight: bold;
    border-bottom: 2px solid #b8860b;
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
    background-color: #f7e8c1; /* Light gold on hover */
}

/* Buttons Styling */
.button {
    padding: 10px 20px;
    background-color: #d4af37; /* Gold background */
    color: white;
    border: 2px solid #b8860b;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
    display: inline-block;
    margin-right: 10px;
}

.button:hover {
    background-color: #b8860b; /* Darker gold on hover */
    color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .info-grid, .measurements-grid {
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
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="order-header">
                <h2>Order #<?php echo $order_id; ?></h2>
                <?php if ($order['quotation_id']): ?>
                    <h3>Quotation #<?php echo $order['quotation_id']; ?></h3>
                <?php endif; ?>
            </div>

            <div class="section">
                <h3>Customer Information</h3>
                <div class="info-grid">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                </div>
            </div>

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
                    <p><strong>Color:</strong> <?php echo str_replace('_', ' ', ucfirst($order['coil_color'])); ?></p>
                    <p><strong>Thickness:</strong> <?php echo $order['thickness']; ?></p>
                </div>
            </div>

            <?php if ($order['point1']): ?>
            <div class="section">
                <h3>Wicket Door Measurements</h3>
                <div class="measurements-grid">
                    <p><strong>Point 1:</strong> <?php echo $order['point1']; ?></p>
                    <p><strong>Point 2:</strong> <?php echo $order['point2']; ?></p>
                    <p><strong>Point 3:</strong> <?php echo $order['point3']; ?></p>
                    <p><strong>Point 4:</strong> <?php echo $order['point4']; ?></p>
                    <p><strong>Point 5:</strong> <?php echo $order['point5']; ?></p>
                    <p><strong>Door Opening:</strong> <?php echo str_replace('_', ' ', ucfirst($order['door_opening'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="section">
                <h3>Materials Used</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Specifications</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                            <td>
                                <?php if ($material['type'] == 'coil'): ?>
                                    Color: <?php echo str_replace('_', ' ', ucfirst($material['color'])); ?><br>
                                    Thickness: <?php echo $material['thickness']; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $material['used_quantity']; ?></td>
                            <td><?php echo $material['unit']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="actions">
                <a href="confirmed_orders.php" class="button">Back to Orders</a>
                <a href="download_order.php?id=<?php echo $order_id; ?>" class="button download-btn">Download Details</a>
                <?php if ($order['status'] == 'confirmed'): ?>
                    <form method="POST" action="confirmed_orders.php" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <button type="submit" name="complete_order" class="button complete-btn"
                                onclick="return confirm('Are you sure you want to mark this order as completed?')">
                            Mark as Completed
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
