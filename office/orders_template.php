<?php
require_once '../config/config.php';
require_once '../includes/order_status.php';
checkAuth(['office_staff']);

$status = $status ?? ORDER_STATUS_PENDING;
$title = ucfirst($status) . ' Orders';

$orders = $conn->query("
    SELECT o.id as order_id, o.*, u1.name as prepared_by_name, u2.name as checked_by_name 
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
    <title>
        <?php
            if ($status === 'confirmed') {
                echo 'Confirmed Orders';
            } else if ($status === 'reviewed') {
                echo 'Reviewed Orders';
            } else if ($status === 'completed') {
                echo 'Completed Orders';
            } else {
                echo 'Orders';
            }
        ?>
    </title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .orders-title {
            text-align: center;
            color: #d4af37;
            font-size: 2.5em;
            font-weight: bold;
            margin: 30px 0 40px 0;
            letter-spacing: 2px;
            text-shadow: 0 2px 8px rgba(212,175,55,0.15);
        }
        .order-card {
            margin-bottom: 32px;
            border: 2px solid #e0e0e0;
            border-radius: 14px;
            background: #fafbfc;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            padding: 24px 28px;
            transition: box-shadow 0.2s;
        }
        .order-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .order-header h3 {
            font-size: 1.5em;
            margin: 0 0 12px 0;
        }
        .order-header .order-word {
            font-family: 'Segoe Script', 'Comic Sans MS', cursive;
            color: #007bff;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .order-details p, .cost-summary p {
            margin: 4px 0;
        }
        .actions {
            margin-top: 18px;
            display: flex;
            gap: 16px;
        }
        .order-actions {
            margin-top: 18px;
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .order-actions form {
            margin: 0;
            display: inline;
        }
        .order-actions form button {
            margin: 0;
            vertical-align: middle;
        }

        .button {
            background: #2196f3;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 12px 28px;
            font-size: 1.1em;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(33,150,243,0.08);
            outline: none;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }

        .button:hover, .button:focus {
            background: #1769aa;
            box-shadow: 0 4px 16px rgba(33,150,243,0.18);
        }

        .button:active {
            background: #0d47a1;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2 class="orders-title">
                    <?php
                        if ($status === 'confirmed') {
                            echo 'Confirmed Orders';
                        } else if ($status === 'reviewed') {
                            echo 'Reviewed Orders';
                        } else if ($status === 'completed') {
                            echo 'Completed Orders';
                        } else if ($status === 'done') {
                            echo 'Done Orders';
                        } else {
                            echo 'Orders';
                        }
                    ?>
                </h2>
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
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                            <td>
                                <?php if ($status == ORDER_STATUS_REVIEWED): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="reference-numbers">
                                                <h3>Order #<?php echo $order['order_id']; ?></h3>
                                                <?php if ($order['quotation_id']): ?>
                                                    <h4>Quotation #<?php echo $order['quotation_id']; ?></h4>
                                                <?php endif; ?>
                                            </div>
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

                                            if ($measurements): ?>
                                                <strong>Roller Door:</strong>
                                                <ul class="measurement-list">
                                                    <li>Section 1: <?php echo $measurements['section1']; ?></li>
                                                    <li>Section 2: <?php echo $measurements['section2']; ?></li>
                                                    <li>Total Height: <?php echo $measurements['section1'] + $measurements['section2']; ?></li>
                                                    <li>Outside Width: <?php echo $measurements['outside_width']; ?></li>
                                                    <li>Inside Width: <?php echo $measurements['inside_width']; ?></li>
                                                    <li>Door Width: <?php echo $measurements['door_width']; ?></li>
                                                    <li>Tower Height: <?php echo $measurements['tower_height']; ?></li>
                                                    <li>Tower Type: <?php echo ucfirst($measurements['tower_type']); ?></li>
                                                    <li>Coil Color: <?php echo str_replace('_', ' ', ucfirst($measurements['coil_color'])); ?></li>
                                                    <li>Thickness: <?php echo $measurements['thickness']; ?></li>
                                                    <li>Covering: <?php echo ucfirst($measurements['covering']); ?></li>
                                                    <li>Side Lock: <?php echo $measurements['side_lock'] ? 'Yes' : 'No'; ?></li>
                                                    <li>Motor: <?php echo $measurements['motor']; ?></li>
                                                    <li>Fixing: <?php echo ucfirst($measurements['fixing']); ?></li>
                                                    <li>Down Lock: <?php echo $measurements['down_lock'] ? 'Yes' : 'No'; ?></li>
                                                </ul>

                                                <?php if ($measurements['point1']): ?>
                                                    <strong>Wicket Door:</strong>
                                                    <ul class="measurement-list">
                                                        <li>Point 1: <?php echo $measurements['point1']; ?></li>
                                                        <li>Point 2: <?php echo $measurements['point2']; ?></li>
                                                        <li>Point 3: <?php echo $measurements['point3']; ?></li>
                                                        <li>Point 4: <?php echo $measurements['point4']; ?></li>
                                                        <li>Point 5: <?php echo $measurements['point5']; ?></li>
                                                        <li>Door Opening: <?php echo str_replace('_', ' ', ucfirst($measurements['door_opening'])); ?></li>
                                                        <li>Handle: <?php echo $measurements['handle'] ? 'Yes' : 'No'; ?></li>
                                                        <li>Letter Box: <?php echo $measurements['letter_box'] ? 'Yes' : 'No'; ?></li>
                                                        <li>Color: <?php echo str_replace('_', ' ', ucfirst($measurements['coil_color'])); ?></li>
                                                    </ul>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
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
