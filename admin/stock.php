<?php
require_once '../config/config.php';
checkAuth(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'add_material') {
        $stmt = $conn->prepare("INSERT INTO materials (name, type, thickness, color, quantity, unit) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsds", 
            $_POST['name'],
            $_POST['type'],
            $_POST['thickness'],
            $_POST['color'],
            $_POST['quantity'],
            $_POST['unit']
        );
        $stmt->execute();
        header('Location: stock.php?success=1');
        exit();
    }
}

$materials = $conn->query("SELECT * FROM materials ORDER BY type, name")->fetch_all(MYSQLI_ASSOC);
$coil_colors = ['Coffee brown', 'Black shine', 'Blue color', 'Butter milk', 'Chocolate brown', 'Black mate', 'Beige'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Stock Management</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content">
            <div class="add-material-form">
                <h3>Add New Material</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_material">
                    <!-- Add material form fields -->
                </form>
            </div>

            <h3>Current Stock</h3>
            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Current Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($material['name']); ?></td>
                        <td><?php echo ucfirst($material['type']); ?></td>
                        <td>
                            <?php if ($material['type'] == 'coil'): ?>
                                Color: <?php echo htmlspecialchars($material['color']); ?><br>
                                Thickness: <?php echo $material['thickness']; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $material['quantity'] . ' ' . $material['unit']; ?></td>
                        <td>
                            <a href="edit_material.php?id=<?php echo $material['id']; ?>" class="button">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
