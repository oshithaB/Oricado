<?php
require_once '../config/config.php';
checkAuth(['admin']);

// Get coil statistics
$coilQuery = "SELECT m.*, 
    (SELECT COUNT(*) FROM order_materials om WHERE om.material_id = m.id) as usage_count,
    (SELECT SUM(o.total_price) FROM order_materials om 
     JOIN orders o ON om.order_id = o.id 
     WHERE om.material_id = m.id) as total_revenue
FROM materials m 
WHERE m.type = 'coil'";

$coils = $conn->query($coilQuery)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
        <img src="../assets/images/oricado logo.jpg" alt="Oricado Logo" class="navigation-logo">
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
    </style>
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="users.php">Manage Users</a></li>
                <li><a href="orders.php">View Orders</a></li>
                <li><a href="stock.php">Stock Management</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="content">
            <h3>Coil Overview</h3>
            <div class="coil-grid">
                <?php 
                $colors = [
                    'coffee_brown' => ['hex' => '#4A2C2A', 'text' => 'white'],
                    'black_shine' => ['hex' => '#2C2C2C', 'text' => 'white'],
                    'blue_color' => ['hex' => '#1E4D8C', 'text' => 'white'],
                    'butter_milk' => ['hex' => '#FFF3D9', 'text' => 'black'],
                    'chocolate_brown' => ['hex' => '#3C1F1E', 'text' => 'white'],
                    'black_mate' => ['hex' => '#1C1C1C', 'text' => 'white'],
                    'beige' => ['hex' => '#E8DCC4', 'text' => 'black']
                ];
                
                foreach ($coils as $coil): 
                    $colorInfo = $colors[$coil['color']] ?? ['hex' => '#000000', 'text' => 'white'];
                    $colorName = str_replace('_', ' ', ucfirst($coil['color']));
                ?>
                    <div class="coil-card">
                        <div class="color-header" style="background-color: <?php echo $colorInfo['hex']; ?>; color: <?php echo $colorInfo['text']; ?>">
                            <h4><?php echo $colorName; ?></h4>
                        </div>
                        <div class="coil-details">
                            <p>Thickness: <?php echo $coil['thickness']; ?></p>
                            <p>Available: <?php echo $coil['quantity']; ?> <?php echo $coil['unit']; ?></p>
                            <p>Usage Count: <?php echo $coil['usage_count']; ?></p>
                            <p>Revenue: Rs. <?php echo number_format($coil['total_revenue'], 2); ?></p>
                        </div>
                        <button onclick="window.location.href='coil_details.php?id=<?php echo $coil['id']; ?>'" class="view-details-btn">
                            View Details
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
