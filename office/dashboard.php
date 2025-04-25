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
/* Orders Grid Styling */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

/* Order Card Styling */
.order-card {
    background: white;
    border-radius: 12px; /* Slightly more rounded corners */
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transition for hover effects */
    cursor: pointer; /* Pointer cursor for interactivity */
}

.order-card:hover {
    transform: translateY(-10px); /* Slight jump effect */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
}

/* Order Header Styling */
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.status-badge {
    padding: 6px 12px; /* Slightly larger padding for better visibility */
    border-radius: 20px; /* Fully rounded badges */
    font-size: 0.9em; /* Slightly larger font size */
    text-transform: uppercase;
    font-weight: bold;
    color: white; /* White text for better contrast */
}

.status-badge.pending { background: #ffd700; }
.status-badge.reviewed { background: #87ceeb; }
.status-badge.confirmed { background: #98fb98; }
.status-badge.completed { background: #dda0dd; }
.status-badge.done { background: #90ee90; }

/* Order Details Styling */
.order-details p {
    margin: 8px 0; /* Increased spacing for better readability */
    font-size: 14px; /* Slightly larger font size */
    color: #555; /* Softer text color */
}

.order-details ul {
    margin: 8px 0;
    padding-left: 20px;
    list-style-type: disc; /* Add bullet points for better structure */
    color: #555; /* Softer text color */
}

.order-details ul li {
    margin-bottom: 5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .orders-grid {
        grid-template-columns: 1fr; /* Single column layout for smaller screens */
    }

    .order-card {
        padding: 15px;
    }
}
/* Order Card Styling */
.order-card {
    background: white;
    border-radius: 12px; /* Slightly more rounded corners */
    padding: 20px;
    border: 2px solid transparent; /* Default transparent border */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease; /* Smooth transition for hover effects */
    cursor: pointer; /* Pointer cursor for interactivity */
}

.order-card:hover {
    transform: translateY(-10px); /* Slight jump effect */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
    border-color: #d4af37; /* Gold border on hover */
}

/* Order Header Styling */
.order-header h4 {
    font-size: 18px; /* Larger font size for the order title */
    font-weight: bold; /* Bold for emphasis */
    color: #333; /* Darker text color for better readability */
    margin: 0; /* Remove default margin */
}

.order-header .status-badge {
    padding: 8px 15px; /* Slightly larger padding for better visibility */
    font-size: 12px; /* Adjusted font size */
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 20px; /* Fully rounded badges */
    color: white; /* White text for contrast */
}

/* Order Details Styling */
.order-details p {
    margin: 6px 0; /* Reduced spacing for compactness */
    font-size: 14px; /* Standard font size for readability */
    color: #555; /* Softer text color */
    line-height: 1.6; /* Improved line spacing for readability */
}

.order-details p strong {
    color: #333; /* Darker color for labels */
    font-weight: bold; /* Bold for emphasis */
}

.order-details ul {
    margin: 10px 0;
    padding-left: 20px;
    list-style-type: disc; /* Bullet points for better structure */
    color: #555; /* Softer text color */
    font-size: 14px; /* Standard font size */
}

.order-details ul li {
    margin-bottom: 5px;
    line-height: 1.5; /* Improved line spacing */
}

/* Highlight Important Text */
.order-details .highlight {
    color: #d4af37; /* Gold color for highlights */
    font-weight: bold; /* Bold for emphasis */
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-header h4 {
        font-size: 16px; /* Adjust font size for smaller screens */
    }

    .order-details p, .order-details ul {
        font-size: 13px; /* Adjust font size for smaller screens */
    }
}
/* General Typography */
body {
    font-family: 'Roboto', sans-serif; /* Use a modern, professional font */
    color: #333; /* Default text color for better readability */
}

/* Order Header Styling */
.order-header h4 {
    font-size: 22px; /* Larger font size for the order title */
    font-weight: 700; /* Bold for emphasis */
    color: #2c3e50; /* Darker text color for better readability */
    margin: 0; /* Remove default margin */
    letter-spacing: 0.5px; /* Slight letter spacing for clarity */
}

.order-header .status-badge {
    padding: 8px 15px; /* Slightly larger padding for better visibility */
    font-size: 12px; /* Adjusted font size */
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 20px; /* Fully rounded badges */
    color: white; /* White text for contrast */
    letter-spacing: 0.5px; /* Slight letter spacing for clarity */
}

/* Order Details Styling */
.order-details p {
    margin: 10px 0; /* Increased spacing for better readability */
    font-size: 16px; /* Slightly larger font size for clarity */
    color: #555; /* Softer text color */
    line-height: 1.8; /* Improved line spacing for readability */
}

.order-details p strong {
    color: #2c3e50; /* Darker color for labels */
    font-weight: bold; /* Bold for emphasis */
}

.order-details ul {
    margin: 10px 0;
    padding-left: 20px;
    list-style-type: disc; /* Bullet points for better structure */
    color: #555; /* Softer text color */
    font-size: 16px; /* Standard font size */
    line-height: 1.8; /* Improved line spacing */
}

.order-details ul li {
    margin-bottom: 8px;
    line-height: 1.6; /* Improved line spacing */
}

/* Highlight Important Text */
.order-details .highlight {
    color: #d4af37; /* Gold color for highlights */
    font-weight: bold; /* Bold for emphasis */
    font-size: 18px; /* Slightly larger font size for highlights */
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-header h4 {
        font-size: 20px; /* Adjust font size for smaller screens */
    }

    .order-details p, .order-details ul {
        font-size: 14px; /* Adjust font size for smaller screens */
    }
}

</style>
</body>
</html>
