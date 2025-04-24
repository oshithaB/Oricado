<?php
require_once '../config/config.php';
checkAuth(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_order'])) {
    $order_id = $_POST['order_id'];
    
    $conn->begin_transaction();
    try {
        // Calculate material cost
        $materials_query = $conn->prepare("
            SELECT SUM(om.quantity * m.price) as total_cost
            FROM order_materials om
            JOIN materials m ON om.material_id = m.id
            WHERE om.order_id = ?
        ");
        $materials_query->bind_param("i", $order_id);
        $materials_query->execute();
        $material_cost = $materials_query->get_result()->fetch_assoc()['total_cost'];

        // Update order with approval
        $stmt = $conn->prepare("
            UPDATE orders 
            SET admin_approved = 1,
                admin_approved_by = ?,
                admin_approved_at = NOW(),
                material_cost = ?
            WHERE id = ? AND status = 'reviewed'
        ");
        $stmt->bind_param("idi", $_SESSION['user_id'], $material_cost, $order_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success_message'] = "Order #$order_id approved successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error approving order: " . $e->getMessage();
    }
    
    header('Location: reviewed_orders.php');
    exit();
}

// Get reviewed orders that need admin approval
$orders = $conn->query("
    SELECT o.*, 
           rdm.section1, rdm.section2, rdm.door_width,
           q.id as quotation_id, q.total_amount as quotation_amount,
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           (SELECT SUM(om2.quantity * m2.price) 
            FROM order_materials om2 
            JOIN materials m2 ON om2.material_id = m2.id 
            WHERE om2.order_id = o.id) as material_cost
    FROM orders o
    LEFT JOIN quotations q ON o.quotation_id = q.id
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    WHERE o.status = 'reviewed'
    AND (o.admin_approved = 0 OR o.admin_approved IS NULL)
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Orders</title>
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

.button {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: bold;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    display: inline-block;
    text-align: center;
    cursor: pointer;
}

.view-btn {
    background-color: #28a745; /* Green */
    border: 1px solid #218838;
}

.view-btn:hover {
    background-color: #218838;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.download-btn {
    background-color: #007bff; /* Blue */
    border: 1px solid #0056b3;
}

.download-btn:hover {
    background-color: #0056b3;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.approve-btn {
    background-color: #d4af37; /* Gold */
    border: 1px solid #b8860b;
}

.approve-btn:hover {
    background-color: #b8860b;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-card {
        padding: 15px;
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
            <h2>Review Orders</h2>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <p>No orders pending for review.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="reference-header">
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <?php if ($order['quotation_id']): ?>
                                <h4>Quotation #<?php echo $order['quotation_id']; ?></h4>
                            <?php endif; ?>
                        </div>

                        <div class="order-details">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p><strong>Prepared by:</strong> <?php echo htmlspecialchars($order['prepared_by_name']); ?></p>
                            <p><strong>Reviewed by:</strong> <?php echo htmlspecialchars($order['checked_by_name']); ?></p>
                            <p><strong>Created:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                        </div>

                        <div class="financial-summary">
                            <p><strong>Quotation Amount:</strong> Rs. <?php echo number_format($order['quotation_amount'], 2); ?></p>
                            <p><strong>Material Cost:</strong> Rs. <?php echo number_format($order['material_cost'], 2); ?></p>
                            <p><strong>Profit Margin:</strong> Rs. <?php echo number_format($order['quotation_amount'] - $order['material_cost'], 2); ?></p>
                        </div>

                        <div class="order-actions">
                            <a href="view_order_details.php?id=<?php echo $order['id']; ?>" class="button view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="download_order_details.php?id=<?php echo $order['id']; ?>" class="button download-btn">
                                <i class="fas fa-download"></i> Download Details
                            </a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="approve_order" 
                                        onclick="return confirm('Are you sure you want to approve this order?')"
                                        class="button approve-btn">
                                    <i class="fas fa-check"></i> Approve Order
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
