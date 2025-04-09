<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $material_id = $_POST['material_id'];
    $quantity = $_POST['quantity'];
    
    $stmt = $conn->prepare("UPDATE materials SET quantity = quantity + ? WHERE id = ?");
    $stmt->bind_param("di", $quantity, $material_id);
    $stmt->execute();
    
    header('Location: manage_stock.php?success=1');
    exit();
}

$materials = $conn->query("SELECT * FROM materials ORDER BY type, name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Stock</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        <div class="content">
            <h3>Current Stock Levels</h3>
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Current Stock</th>
                        <th>Add Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($material['name']); ?></td>
                        <td><?php echo ucfirst($material['type']); ?></td>
                        <td>
                            <?php if ($material['type'] == 'coil'): ?>
                                Color: <?php echo $material['color']; ?><br>
                                Thickness: <?php echo $material['thickness']; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $material['quantity'] . ' ' . $material['unit']; ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                <input type="number" name="quantity" step="0.01" min="0" required>
                                <button type="submit">Add</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
