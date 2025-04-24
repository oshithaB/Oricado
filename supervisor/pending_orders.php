<?php
require_once '../config/config.php';
checkAuth(['supervisor']);

// Get all pending orders that haven't been reviewed yet
$orders = $conn->query("
    SELECT o.*, 
           rdm.section1, rdm.section2, rdm.door_width, rdm.tower_height,
           rdm.tower_type, rdm.coil_color,
           wdm.point1,  /* For checking if wicket door exists */
           u.name as prepared_by_name,
           u.contact as prepared_by_contact,
           q.total_amount as quotation_amount
    FROM orders o
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u ON o.prepared_by = u.id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.status = 'pending'
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h2>Pending Orders</h2>
            <?php if (empty($orders)): ?>
                <p>No pending orders found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Details</th>
                            <th>Measurements</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($order['customer_contact']); ?><br>
                                    <?php echo htmlspecialchars($order['customer_address']); ?>
                                </td>
                                <td>
                                    <strong>Door:</strong><br>
                                    Height: <?php echo $order['section1'] + $order['section2']; ?><br>
                                    Width: <?php echo $order['door_width']; ?><br>
                                    Type: <?php echo ucfirst($order['tower_type']); ?><br>
                                    Color: <?php echo str_replace('_', ' ', ucfirst($order['coil_color'])); ?>
                                    <?php if ($order['point1']): ?>
                                        <br><strong>Has Wicket Door</strong>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['prepared_by_name']); ?><br>
                                    <?php echo htmlspecialchars($order['prepared_by_contact']); ?><br>
                                    <small>Created: <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></small>
                                </td>
                                <td>
    <div class="button-group">
        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button view-btn">
            <i class="fas fa-eye"></i> View Details
        </a>
        <a href="review_order.php?id=<?php echo $order['id']; ?>" class="button add-materials-btn">
            <i class="fas fa-plus"></i> Add Materials
        </a>
        <a href="download_order.php?id=<?php echo $order['id']; ?>" class="button download-btn">
            <i class="fas fa-download"></i> Download
        </a>
    </div>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
