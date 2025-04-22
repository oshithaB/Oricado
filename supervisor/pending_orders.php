<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

$pendingOrders = $conn->query("
    SELECT o.*, 
           rdm.*,
           wdm.*,
           u.name as prepared_by_name,
           u.contact as prepared_by_contact
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u ON o.prepared_by = u.id
    WHERE o.status = 'pending'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Pending Orders</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Prepared By</th>
                        <th>Created Date</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['prepared_by_name']); ?><br>
                            <?php echo htmlspecialchars($order['prepared_by_contact']); ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <strong>Roller Door:</strong><br>
                            Width: <?php echo $order['door_width']; ?><br>
                            Height: <?php echo $order['tower_height']; ?><br>
                            Type: <?php echo ucfirst($order['tower_type']); ?>
                            <?php if ($order['point1']): ?>
                                <br><strong>Wicket Door:</strong> Yes
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="button-group">
                                <button type="button" onclick="toggleDetails(<?php echo $order['id']; ?>)" class="button view-btn">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <a href="download_details.php?id=<?php echo $order['id']; ?>" class="button download-btn">
                                    <i class="fas fa-download"></i> Download Details
                                </a>
                                <a href="review_order.php?id=<?php echo $order['id']; ?>" class="button add-materials-btn">
                                    <i class="fas fa-plus"></i> Add Materials
                                </a>
                            </div>

                            <!-- Order Details Collapsible Section -->
                            <div id="order-details-<?php echo $order['id']; ?>" class="order-details-section" style="display: none;">
                                <div class="section">
                                    <h4>Order Details</h4>
                                    <div class="measurements-grid">
                                        <div class="measurement-item">
                                            <strong>Outside Width:</strong> <?php echo $order['outside_width']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Inside Width:</strong> <?php echo $order['inside_width']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Door Width:</strong> <?php echo $order['door_width']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Tower Height:</strong> <?php echo $order['tower_height']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Tower Type:</strong> <?php echo ucfirst($order['tower_type']); ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Coil Color:</strong> <?php echo str_replace('_', ' ', ucfirst($order['coil_color'])); ?>
                                        </div>
                                    </div>

                                    <?php if ($order['point1']): ?>
                                    <h4>Wicket Door Measurements</h4>
                                    <div class="measurements-grid">
                                        <div class="measurement-item">
                                            <strong>Point 1:</strong> <?php echo $order['point1']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Point 2:</strong> <?php echo $order['point2']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Point 3:</strong> <?php echo $order['point3']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Point 4:</strong> <?php echo $order['point4']; ?>
                                        </div>
                                        <div class="measurement-item">
                                            <strong>Point 5:</strong> <?php echo $order['point5']; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function toggleDetails(orderId) {
        const detailsSection = document.getElementById('order-details-' + orderId);
        if (detailsSection.style.display === 'none') {
            detailsSection.style.display = 'block';
        } else {
            detailsSection.style.display = 'none';
        }
    }
    </script>
</body>
</html>
