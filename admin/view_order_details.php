<?php
require_once '../config/config.php';
checkAuth(['admin']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: reviewed_orders.php');
    exit();
}

// Get complete order details
$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           q.id as quotation_id,
           q.total_amount as quotation_amount
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.id = $order_id
")->fetch_assoc();

// Get materials list with costs
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
    <title>Order Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
    .navigation {
    background: #333; /* Black background for navigation */
    color: white;
    padding: 20px;
}

.logo-container {
    text-align: center;
    margin-bottom: 20px;
}

.navigation-logo {
    max-width: 150px;
    height: auto;
    display: block;
    margin: 0 auto;
    border-radius: 50%; /* Makes the logo circular */
    border: 2px solid #FFD700; /* Gold color for the border */
    box-shadow: 0 0 10px 2px #FFD700; /* Optional: Add a glowing effect */
}
/* General Page Styling */
body {
    font-family: Arial, sans-serif;
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

/* Section Header */
h2 {
    color: #d4af37; /* Gold color for headings */
    border-bottom: 2px solid #d4af37;
    padding-bottom: 5px;
    margin-bottom: 20px;
}

/* Order Card Styling */
.order-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.order-card h3, .order-card h4 {
    margin: 0;
    color: #d4af37; /* Gold color for order titles */
}

.order-details, .financial-summary {
    margin-top: 15px;
}

.order-details p, .financial-summary p {
    margin: 5px 0;
    font-size: 14px;
    color: #333;
}

/* Button Group Styling */
.order-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

/* General Button Styling */
.button {
    padding: 12px 20px; /* Consistent padding for all buttons */
    font-size: 16px; /* Consistent font size for all buttons */
    font-weight: bold;
    border-radius: 8px; /* Rounded corners for a modern look */
    text-decoration: none;
    color: white;
    transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
    display: inline-block;
    text-align: center;
    cursor: pointer;
    min-width: 200px; /* Ensures all buttons have the same width */
}

/* Back to Orders Button */
.back-btn {
    background-color: #007bff; /* Blue */
    border: 2px solid #0056b3;
}

.back-btn:hover {
    background-color: #0056b3;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transform: scale(1.05); /* Slight zoom effect on hover */
}

/* Download Details Button */
.download-btn {
    background-color: #28a745; /* Green */
    border: 2px solid #218838;
}

.download-btn:hover {
    background-color: #218838;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transform: scale(1.05); /* Slight zoom effect on hover */
}

/* Approve Order Button */
.approve-btn {
    background-color: #d4af37; /* Gold */
    border: 2px solid #b8860b;
}

.approve-btn:hover {
    background-color: #b8860b;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transform: scale(1.05); /* Slight zoom effect on hover */
}
/* Responsive Design */
@media (max-width: 768px) {
    .order-card {
        padding: 15px;
    }

    .button {
        font-size: 12px;
        padding: 5px 10px;
    }
}
    </style>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="reference-header">
                <h2>Order #<?php echo $order['id']; ?></h2>
                <?php if ($order['quotation_id']): ?>
                    <h3>Quotation #<?php echo $order['quotation_id']; ?></h3>
                <?php endif; ?>
            </div>

            <h2>Order Details #<?php echo $order_id; ?></h2>

            <div class="section">
                <h3>Customer Information</h3>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
            </div>

            <div class="section">
                <h3>Measurements</h3>
                <h4>Roller Door</h4>
                <div class="measurements-grid">
                    <p><strong>Section 1:</strong> <?php echo $order['section1']; ?></p>
                    <p><strong>Section 2:</strong> <?php echo $order['section2']; ?></p>
                    <p><strong>Door Width:</strong> <?php echo $order['door_width']; ?></p>
                    <!-- Add other measurements -->
                </div>

                <?php if ($order['point1']): ?>
                <h4>Wicket Door</h4>
                <div class="measurements-grid">
                    <p><strong>Point 1:</strong> <?php echo $order['point1']; ?></p>
                    <!-- Add other wicket door measurements -->
                </div>
                <?php endif; ?>
            </div>

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

                <div class="profit-analysis">
                    <p><strong>Quotation Amount:</strong> Rs. <?php echo number_format($order['quotation_amount'], 2); ?></p>
                    <p><strong>Material Cost:</strong> Rs. <?php echo number_format($total_material_cost, 2); ?></p>
                    <p><strong>Profit Margin:</strong> Rs. <?php echo number_format($order['quotation_amount'] - $total_material_cost, 2); ?></p>
                </div>
            </div>

            <div class="actions">
                <a href="reviewed_orders.php" class="button">Back to Orders</a>
                <a href="download_order_details.php?id=<?php echo $order_id; ?>" class="button">Download Details</a>
                <?php if (!$order['admin_approved']): ?>
                <form method="POST" action="reviewed_orders.php" style="display: inline;">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <button type="submit" name="approve_order" class="button approve-btn">Approve Order</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
