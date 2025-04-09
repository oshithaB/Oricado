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
                <?php foreach ($coils as $coil): ?>
                    <div class="coil-card">
                        <h4><?php echo htmlspecialchars($coil['name']); ?></h4>
                        <p>Color: <?php echo htmlspecialchars($coil['color']); ?></p>
                        <p>Thickness: <?php echo $coil['thickness']; ?></p>
                        <p>Available: <?php echo $coil['quantity']; ?> <?php echo $coil['unit']; ?></p>
                        <p>Usage Count: <?php echo $coil['usage_count']; ?></p>
                        <p>Revenue: Rs. <?php echo number_format($coil['total_revenue'], 2); ?></p>
                        <a href="coil_details.php?id=<?php echo $coil['id']; ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
