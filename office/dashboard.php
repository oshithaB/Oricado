<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Get recent orders with creator info
$recent_orders = $conn->query("
    SELECT o.*, 
           u.name as prepared_by_name,
           u.contact as prepared_by_contact,
           rdm.door_width, rdm.tower_height, rdm.tower_type, rdm.coil_color
    FROM orders o
    LEFT JOIN users u ON o.prepared_by = u.id
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Office Staff Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h2>Dashboard</h2>

            <div class="section">
                <h3>Recent Orders</h3>
                <div class="orders-grid">
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h4>Order #<?php echo $order['id']; ?></h4>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                                <p><strong>Created By:</strong> <?php echo htmlspecialchars($order['prepared_by_name']); ?></p>
                                <p><strong>Staff Contact:</strong> <?php echo htmlspecialchars($order['prepared_by_contact']); ?></p>
                                <p><strong>Created:</strong> <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                                <?php if ($order['door_width']): ?>
                                    <p><strong>Specifications:</strong></p>
                                    <ul>
                                        <li>Width: <?php echo $order['door_width']; ?></li>
                                        <li>Height: <?php echo $order['tower_height']; ?></li>
                                        <li>Type: <?php echo ucfirst($order['tower_type']); ?></li>
                                        <li>Color: <?php echo str_replace('_', ' ', ucfirst($order['coil_color'])); ?></li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .order-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        text-transform: uppercase;
    }

    .status-badge.pending { background: #ffd700; }
    .status-badge.reviewed { background: #87ceeb; }
    .status-badge.confirmed { background: #98fb98; }
    .status-badge.completed { background: #dda0dd; }
    .status-badge.done { background: #90ee90; }

    .order-details p {
        margin: 5px 0;
    }

    .order-details ul {
        margin: 5px 0;
        padding-left: 20px;
    }
    </style>
</body>
</html>
