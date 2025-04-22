<?php
require_once '../config/config.php';
checkAuth(['admin']);

$material_id = $_GET['id'] ?? null;
if (!$material_id) {
    header('Location: stock.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("UPDATE materials SET name = ?, type = ?, thickness = ?, color = ?, quantity = ?, unit = ? WHERE id = ?");
    $stmt->bind_param("ssdsisi", 
        $_POST['name'],
        $_POST['type'],
        $_POST['thickness'],
        $_POST['color'],
        $_POST['quantity'],
        $_POST['unit'],
        $material_id
    );
    
    if ($stmt->execute()) {
        header('Location: stock.php?success=1');
        exit();
    }
}

// Get material details
$material = $conn->query("SELECT * FROM materials WHERE id = $material_id")->fetch_assoc();
if (!$material) {
    header('Location: stock.php');
    exit();
}

$coil_colors = ['coffee_brown', 'black_shine', 'blue_color', 'butter_milk', 'chocolate_brown', 'black_mate', 'beige'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Material</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Edit Material</h2>
            <a href="stock.php">Back to Stock Management</a>
        </nav>

        <div class="content">
            <div class="section">
                <h3>Edit <?php echo htmlspecialchars($material['name']); ?></h3>
                <form method="POST" class="edit-material-form">
                    <div class="form-group">
                        <label>Material Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($material['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Type:</label>
                        <select name="type" id="material-type" required>
                            <option value="coil" <?php echo $material['type'] == 'coil' ? 'selected' : ''; ?>>Coil</option>
                            <option value="other" <?php echo $material['type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div id="coil-fields" <?php echo $material['type'] != 'coil' ? 'style="display:none"' : ''; ?>>
                        <div class="form-group">
                            <label>Thickness:</label>
                            <input type="number" name="thickness" step="0.01" value="<?php echo $material['thickness']; ?>">
                        </div>

                        <div class="form-group">
                            <label>Color:</label>
                            <select name="color">
                                <?php foreach ($coil_colors as $color): ?>
                                    <option value="<?php echo $color; ?>" <?php echo $material['color'] == $color ? 'selected' : ''; ?>>
                                        <?php echo str_replace('_', ' ', ucfirst($color)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Current Quantity:</label>
                        <input type="number" name="quantity" step="0.01" value="<?php echo $material['quantity']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Unit:</label>
                        <input type="text" name="unit" value="<?php echo htmlspecialchars($material['unit']); ?>" required>
                    </div>

                    <button type="submit">Update Material</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('material-type').addEventListener('change', function() {
        const coilFields = document.getElementById('coil-fields');
        coilFields.style.display = this.value === 'coil' ? 'block' : 'none';
        
        if (this.value !== 'coil') {
            document.querySelector('input[name="thickness"]').value = '';
            document.querySelector('select[name="color"]').value = '';
        }
    });
    </script>
</body>
</html>
