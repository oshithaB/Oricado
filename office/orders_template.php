<?php
require_once '../config/config.php';
require_once '../includes/order_status.php';
checkAuth(['office_staff']);

$status = $status ?? ORDER_STATUS_PENDING;
$title = ucfirst($status) . ' Orders';

$orders = $conn->query("
    SELECT o.*, u1.name as prepared_by_name, u2.name as checked_by_name 
    FROM orders o 
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    WHERE o.status = '$status'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h3><?php echo $title; ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <?php if ($status == ORDER_STATUS_REVIEWED): ?>
                                <div class="order-detail-header">
                                    <div class="customer-quick-info">
                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                        <span><?php echo htmlspecialchars($order['customer_contact']); ?></span>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <button type="button" onclick="toggleSection('materials-<?php echo $order['id']; ?>')" class="button">View Materials</button>
                                    <button type="button" onclick="toggleSection('measurements-<?php echo $order['id']; ?>')" class="button">View Measurements</button>
                                    <a href="download_materials.php?id=<?php echo $order['id']; ?>" class="button">Download Materials List</a>
                                </div>
                                
                                <div class="price-confirmation">
                                    <form method="POST" action="confirm_order.php">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <div class="form-group">
                                            <label>Enter Total Price:</label>
                                            <input type="number" name="total_price" step="0.01" required>
                                        </div>
                                        <button type="submit" name="action" value="confirm" class="button">Confirm Order</button>
                                    </form>
                                </div>

                                <!-- Materials List Section -->
                                <div id="materials-<?php echo $order['id']; ?>" class="collapsible-section" style="display: none;">
                                    <h4>Materials Required</h4>
                                    <div class="materials-list scrollable">
                                        <table class="mini-table">
                                            <?php
                                            $materials = $conn->query("
                                                SELECT m.name, m.type, om.quantity, m.unit 
                                                FROM order_materials om 
                                                JOIN materials m ON om.material_id = m.id 
                                                WHERE om.order_id = {$order['id']}
                                            ")->fetch_all(MYSQLI_ASSOC);
                                            
                                            foreach ($materials as $material): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($material['name']); ?></td>
                                                <td><?php echo $material['quantity'] . ' ' . $material['unit']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </div>
                                </div>

                                <!-- Measurements Section -->
                                <div id="measurements-<?php echo $order['id']; ?>" class="collapsible-section" style="display: none;">
                                    <h4>Measurements</h4>
                                    <?php
                                    $measurements = $conn->query("
                                        SELECT rdm.*, wdm.* 
                                        FROM orders o
                                        LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
                                        LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
                                        WHERE o.id = {$order['id']}
                                    ")->fetch_assoc();
                                    ?>
                                    <strong>Roller Door:</strong>
                                    <ul class="measurement-list">
                                        <li>Outside Width: <?php echo $measurements['outside_width']; ?></li>
                                        <li>Inside Width: <?php echo $measurements['inside_width']; ?></li>
                                        <li>Door Width: <?php echo $measurements['door_width']; ?></li>
                                        <li>Tower Height: <?php echo $measurements['tower_height']; ?></li>
                                        <li>Tower Type: <?php echo ucfirst($measurements['tower_type']); ?></li>
                                    </ul>
                                    <?php if ($measurements['point1']): ?>
                                    <strong>Wicket Door:</strong>
                                    <ul class="measurement-list">
                                        <li>Point 1: <?php echo $measurements['point1']; ?></li>
                                        <li>Point 2: <?php echo $measurements['point2']; ?></li>
                                        <li>Point 3: <?php echo $measurements['point3']; ?></li>
                                        <li>Point 4: <?php echo $measurements['point4']; ?></li>
                                        <li>Point 5: <?php echo $measurements['point5']; ?></li>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($status == ORDER_STATUS_DONE): ?>
                                <div class="order-actions">
                                    <a href="download_order.php?id=<?php echo $order['id']; ?>" class="button">Download Order</a>
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                                </div>
                            <?php elseif ($status == ORDER_STATUS_COMPLETED): ?>
                                <!-- For completed orders, show mark as done option -->
                                <div class="order-actions">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="mark_done" value="1">
                                        <button type="submit" onclick="return confirm('Mark this order as done?')" class="button">Mark as Done</button>
                                    </form>
                                </div>
                            <?php elseif ($status == ORDER_STATUS_PENDING): ?>
                                <!-- For pending orders, show edit option -->
                                <div class="order-actions">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View</a>
                                    <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="button">Edit</a>
                                </div>
                            <?php else: ?>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View Details</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section.style.display === 'none' || section.style.display === '') {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    }
    </script>
</body>
</html>
