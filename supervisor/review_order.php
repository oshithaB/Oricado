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
        $stmt = $conn->prepare("DELETE FROM order_materials WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Handle coil selection
        if (!empty($_POST['coil_id']) && $_POST['coil_quantity'] > 0) {
            $stmt = $conn->prepare("INSERT INTO order_materials (order_id, material_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $order_id, $_POST['coil_id'], $_POST['coil_quantity']);
            $stmt->execute();
        }
        
        // Handle other materials
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

// Get all coils for dropdown
$coils = $conn->query("SELECT * FROM materials WHERE type = 'coil' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get materials by type (motor, pulley, spring, angle, paint, iron)
$type_materials = $conn->query("
    SELECT * FROM materials 
    WHERE type IN ('motor', 'pulley', 'spring', 'angle', 'paint', 'iron')
    ORDER BY type, name
")->fetch_all(MYSQLI_ASSOC);

// Get materials by specific IDs
$specific_ids = [59, 22, 23, 149, 139, 16, 17, 140, 121, 82, 24, 25, 26, 27, 28, 129, 
                35, 36, 135, 32, 33, 41, 123, 39, 55, 56, 110, 111, 112, 141, 107, 108, 
                31, 130, 47, 48, 73, 74, 61, 70, 154, 97, 38, 131, 132, 20, 34, 69];

$id_list = implode(',', $specific_ids);
$id_materials = $conn->query("
    SELECT * FROM materials 
    WHERE id IN ($id_list)
    ORDER BY name
")->fetch_all(MYSQLI_ASSOC);

// Combine and remove duplicates
$all_materials = array_merge($type_materials, $id_materials);
$unique_materials = [];
$seen_ids = [];

foreach ($all_materials as $material) {
    if (!in_array($material['id'], $seen_ids)) {
        $unique_materials[] = $material;
        $seen_ids[] = $material['id'];
    }
}

// Sort materials by type for better organization
usort($unique_materials, function($a, $b) {
    if ($a['type'] == $b['type']) {
        return strcmp($a['name'], $b['name']);
    }
    return strcmp($a['type'], $b['type']);
});

// Group materials by type
$grouped_materials = [];
foreach ($unique_materials as $material) {
    $grouped_materials[$material['type']][] = $material;
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, rdm.*, wdm.*
    FROM orders o
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Materials</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .materials-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .materials-table th,
        .materials-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .materials-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .material-type-header {
            background-color: #e8f4f8;
            font-weight: bold;
            text-align: center;
        }
        
        .coil-section {
            background-color: #f0f8ff;
        }
        
        .coil-select {
            width: 300px;
            margin-right: 10px;
        }
        
        input[type="number"] {
            width: 80px;
            padding: 5px;
        }
        
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        .button {
            padding: 10px 20px;
            margin-left: 10px;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .button.primary {
            background-color: #007cba;
            color: white;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .debug-info {
            background: #f0f0f0;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Add Materials for Order #<?php echo $order_id; ?></h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <!-- Debug info -->
                <div class="debug-info">
                    <strong>Debug Info:</strong><br>
                    Coils found: <?php echo count($coils); ?><br>
                    Type materials found: <?php echo count($type_materials); ?><br>
                    ID materials found: <?php echo count($id_materials); ?><br>
                    Unique materials total: <?php echo count($unique_materials); ?><br>
                    Material types: <?php echo implode(', ', array_keys($grouped_materials)); ?>
                </div>
                
                <div class="order-summary">
                    <h3>Order Details</h3>
                    <p><strong>Door Width:</strong> <?php echo $order['door_width'] ?? 'N/A'; ?></p>
                    <p><strong>Total Height:</strong> <?php echo ($order['section1'] ?? 0) + ($order['section2'] ?? 0); ?></p>
                    <p><strong>Total Sqft:</strong> <?php echo $order['total_sqft'] ?? 'N/A'; ?></p>
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
                            <!-- Coil Selection Section -->
                            <tr class="coil-section">
                                <td colspan="4" class="material-type-header">
                                    <strong>COIL SELECTION</strong>
                                </td>
                            </tr>
                            <tr class="coil-section">
                                <td>Coil</td>
                                <td>
                                    <select name="coil_id" class="coil-select">
                                        <option value="">Select a coil</option>
                                        <?php foreach ($coils as $coil): ?>
                                        <option value="<?php echo $coil['id']; ?>">
                                            <?php echo htmlspecialchars($coil['name']); ?> - 
                                            Color: <?php echo str_replace('_', ' ', $coil['color'] ?? 'N/A'); ?>, 
                                            Thickness: <?php echo $coil['thickness'] ?? 'N/A'; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>LFT</td>
                                <td>
                                    <input type="number" 
                                           name="coil_quantity" 
                                           step="0.01" 
                                           min="0" 
                                           value="0"
                                           placeholder="Quantity">
                                </td>
                            </tr>
                            
                            <!-- Other Materials Grouped by Type -->
                            <?php if (!empty($grouped_materials)): ?>
                                <?php foreach ($grouped_materials as $type => $type_materials): ?>
                                    <tr>
                                        <td colspan="4" class="material-type-header">
                                            <strong><?php echo strtoupper($type); ?>S</strong>
                                        </td>
                                    </tr>
                                    <?php foreach ($type_materials as $material): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($material['name']); ?></td>
                                        <td>
                                            Type: <?php echo ucfirst($material['type']); ?>
                                            <?php if (!empty($material['thickness'])): ?>
                                                <br>Thickness: <?php echo $material['thickness']; ?>
                                            <?php endif; ?>
                                            <?php if (!empty($material['color']) && $material['color'] != 'N/A'): ?>
                                                <br>Color: <?php echo str_replace('_', ' ', $material['color']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($material['unit'] ?? 'pcs'); ?></td>
                                        <td>
                                            <input type="number" 
                                                   name="quantities[<?php echo $material['id']; ?>]" 
                                                   step="0.01" 
                                                   min="0" 
                                                   value="0"
                                                   placeholder="Qty">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
                                        No materials found. Please check your database configuration.
                                    </td>
                                </tr>
                            <?php endif; ?>
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