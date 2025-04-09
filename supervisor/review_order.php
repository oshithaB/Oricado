<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

$order = $conn->query("SELECT o.*, 
    rdm.*, wdm.*, 
    u1.name as prepared_by_name,
    u2.name as checked_by_name
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    WHERE o.id = $order_id")->fetch_assoc();

$materials = $conn->query("SELECT * FROM materials ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        foreach ($_POST['materials'] as $material_id => $quantity) {
            if ($quantity > 0) {
                $stmt = $conn->prepare("INSERT INTO order_materials (order_id, material_id, quantity) 
                    VALUES (?, ?, ?)");
                $stmt->bind_param("iid", $order_id, $material_id, $quantity);
                $stmt->execute();
            }
        }

        $stmt = $conn->prepare("UPDATE orders SET status = 'reviewed', checked_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $order_id);
        $stmt->execute();

        $conn->commit();
        header('Location: dashboard.php?success=1');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating order: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Order</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        <div class="content">
            <h2>Review Order #<?php echo $order_id; ?></h2>
            
            <!-- Display order details -->
            <div class="order-details">
                <!-- ... Show order measurements ... -->
            </div>

            <!-- Materials Form -->
            <form method="POST" class="materials-form">
                <h3>Required Materials</h3>
                <table class="materials-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>DIMENSIONS & DESCRIPTIONS</th>
                            <th>QUANTITY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                            <td>
                                <?php if ($material['type'] == 'coil'): ?>
                                    Color: <?php echo htmlspecialchars($material['color']); ?><br>
                                    Thickness: <?php echo $material['thickness']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" 
                                    name="materials[<?php echo $material['id']; ?>]" 
                                    step="0.01" 
                                    min="0" 
                                    value="0">
                                <?php echo htmlspecialchars($material['unit']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Submit Materials List</button>
            </form>
        </div>
    </div>
</body>
</html>
