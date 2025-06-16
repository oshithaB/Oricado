<?php
require_once '../config/config.php';
checkAuth(['admin']);

$colors = [
    'beige' => ['hex' => '#F5F5DC', 'text' => 'black'],
    'blue' => ['hex' => '#0066CC', 'text' => 'white'],
    'buttermilk' => ['hex' => '#FFF6D4', 'text' => 'black'],  // Changed from butter_milk
    'coffee brown' => ['hex' => '#6F4E37', 'text' => 'white'], // Changed to match space instead of underscore
    'green' => ['hex' => '#2E8B57', 'text' => 'white'],
    'maroon' => ['hex' => '#800000', 'text' => 'white']
];

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-content {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 30px;
        }
        
        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .row {
            margin: 0 -15px;
        }

        .col-12.col-md-6.col-lg-4 {
            padding: 15px;
        }

        .coil-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            height: 100%;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }
        
        .coil-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .color-header {
            padding: 20px;
            border-radius: 12px 12px 0 0;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        
        .color-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            text-transform: capitalize;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .color-header .color-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .coil-details {
            padding: 20px;
            flex-grow: 1;
        }
        
        .coil-details p {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #495057;
        }
        
        .stat-label {
            font-weight: 500;
            color: #6c757d;
        }
        
        .stat-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .view-details-btn {
            margin-top: auto;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 0 0 12px 12px;
            transition: all 0.3s ease;
        }
        
        .view-details-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        
        .navigation-logo {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto;
            border-radius: 50%;
            border: 2px solid #FFD700;
            box-shadow: 0 0 10px 2px #FFD700;
        }
    </style>
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
                <li><a href="reviewed_orders.php">confirm oders</a></li>
                <li><a href="../index.php">Logout</a></li>
            </ul>
        </nav>

        <div class="dashboard-content">
            <h2 class="page-title">
                <i class="fas fa-chart-line me-2"></i>Coil Overview
            </h2>
            
            <div class="row">
                <?php foreach ($coils as $coil): 
                    $colorInfo = $colors[$coil['color']] ?? ['hex' => '#000000', 'text' => 'white'];
                    $colorName = str_replace('_', ' ', ucfirst($coil['color']));
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="coil-card">
                        <div class="color-header" style="background-color: <?php echo $colors[$coil['color']]['hex'] ?? '#000000'; ?>;">
                            <h4 style="color: <?php echo $colors[$coil['color']]['text'] ?? 'white'; ?>;">
                                <i class="fas fa-circle me-2"></i>
                                <?php echo ucwords($coil['color']); ?>
                            </h4>
                        </div>
                        <div class="coil-details">
                            <p>
                                <span class="stat-label"><i class="fas fa-layer-group me-2"></i>Thickness:</span>
                                <span class="stat-value"><?php echo $coil['thickness']; ?></span>
                            </p>
                            <p>
                                <span class="stat-label"><i class="fas fa-box me-2"></i>Available:</span>
                                <span class="stat-value"><?php echo $coil['quantity'] . ' ' . $coil['unit']; ?></span>
                            </p>
                            <p>
                                <span class="stat-label"><i class="fas fa-chart-bar me-2"></i>Usage Count:</span>
                                <span class="stat-value"><?php echo $coil['usage_count']; ?></span>
                            </p>
                            <p>
                                <span class="stat-label"><i class="fas fa-rupee-sign me-2"></i>Revenue:</span>
                                <span class="stat-value">Rs. <?php echo number_format($coil['total_revenue'] ?? 0, 2); ?></span>
                            </p>
                        </div>
                        <button onclick="window.location.href='coil_details.php?id=<?php echo $coil['id']; ?>'" 
                                class="view-details-btn btn btn-primary">
                            <i class="fas fa-eye me-2"></i>View Details
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
