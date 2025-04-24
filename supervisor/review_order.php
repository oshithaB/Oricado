<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: pending_orders.php');
    exit();
}

// Handle material submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // First delete existing materials for this order
        $conn->query("DELETE FROM order_materials WHERE order_id = $order_id");
        
        // Insert new materials
        foreach ($_POST['quantities'] as $material_id => $quantity) {
            if ($quantity > 0) {
                $stmt = $conn->prepare("INSERT INTO order_materials (order_id, material_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iid", $order_id, $material_id, $quantity);
                $stmt->execute();
            }
        }

        // Update order status to reviewed
        $stmt = $conn->prepare("UPDATE orders SET status = 'reviewed', checked_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $order_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success_message'] = "Materials added successfully";
        header('Location: pending_orders.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Get all available materials
$materials = $conn->query("
    SELECT * FROM materials 
    ORDER BY CASE 
        WHEN type = 'coil' THEN 1 
        ELSE 2 
    END, name
")->fetch_all(MYSQLI_ASSOC);

// Get order details
$order = $conn->query("
    SELECT o.*, rdm.*, wdm.*
    FROM orders o
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    WHERE o.id = $order_id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Materials</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Add Materials for Order #<?php echo $order_id; ?></h2>
                
                <div class="order-summary">
                    <h3>Order Details</h3>
                    <p><strong>Door Width:</strong> <?php echo $order['door_width']; ?></p>
                    <p><strong>Total Height:</strong> <?php echo $order['section1'] + $order['section2']; ?></p>
                    <p><strong>Total Sqft:</strong> <?php echo $order['total_sqft']; ?></p>
                </div>

                <form method="POST" class="materials-form">
                    <table class="materials-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($material['name']); ?></td>
                                <td>
                                    <?php if ($material['type'] == 'coil'): ?>
                                        Color: <?php echo str_replace('_', ' ', $material['color']); ?><br>
                                        Thickness: <?php echo $material['thickness']; ?>
                                    <?php else: ?>
                                        <?php echo ucfirst($material['type']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($material['unit']); ?></td>
                                <td>
                                    <input type="number" 
                                           name="quantities[<?php echo $material['id']; ?>]" 
                                           step="0.01" 
                                           min="0" 
                                           value="0">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="form-actions">
                        <a href="pending_orders.php" class="button">Cancel</a>
                        <button type="submit" class="button primary">Submit Materials List</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
