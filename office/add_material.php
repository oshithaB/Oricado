<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO materials (name, type, thickness, color, quantity, unit, price) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // Set default values for non-coil items
        $thickness = $_POST['type'] == 'coil' ? $_POST['thickness'] : null;
        $color = $_POST['type'] == 'coil' ? $_POST['color'] : null;
        $quantity = floatval($_POST['quantity']) ?? 0;
        $price = floatval($_POST['price']) ?? 0;
        
        $stmt->bind_param("ssssdsd", 
            $_POST['name'],
            $_POST['type'],
            $thickness,
            $color,
            $quantity,
            $_POST['unit'],
            $price
        );

        if (!$stmt->execute()) {
            throw new Exception("Error adding material: " . $stmt->error);
        }

        $conn->commit();
        $_SESSION['success_message'] = "Material added successfully!";
        header('Location: manage_stock.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

$coil_colors = ['coffee_brown', 'black_shine', 'blue_color', 'butter_milk', 
                'chocolate_brown', 'black_mate', 'beige'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Material</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Add New Material</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="material-form">
                    <div class="form-group">
                        <label>Material Name:</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Type:</label>
                        <select name="type" id="materialType" required>
                            <option value="coil">Coil</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div id="coilFields">
                        <div class="form-group">
                            <label>Thickness:</label>
                            <select name="thickness">
                                <option value="0.47">0.47</option>
                                <option value="0.60">0.60</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Color:</label>
                            <select name="color">
                                <?php foreach ($coil_colors as $color): ?>
                                    <option value="<?php echo $color; ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($color)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Initial Quantity:</label>
                        <input type="number" name="quantity" value="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Unit:</label>
                        <select name="unit" required>
                            <option value="sqft">Square Feet</option>
                            <option value="pieces">Pieces</option>
                            <option value="meters">Meters</option>
                            <option value="liters">Liters</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Price (Rs.):</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>

                    <div class="form-actions">
                        <a href="manage_stock.php" class="button">Cancel</a>
                        <button type="submit" class="button primary">Add Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('materialType').addEventListener('change', function() {
        const coilFields = document.getElementById('coilFields');
        coilFields.style.display = this.value === 'coil' ? 'block' : 'none';
    });
    </script>
</body>
</html>
