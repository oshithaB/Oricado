<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO materials (name, type, thickness, color, quantity, unit, price, saleprice) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Set default values for non-coil items
        $thickness = $_POST['type'] == 'coil' ? $_POST['thickness'] : null;
        $color = $_POST['type'] == 'coil' ? $_POST['color'] : null;
        $quantity = floatval($_POST['quantity']) ?? 0;
        $price = floatval($_POST['price']) ?? 0;
        $saleprice = floatval($_POST['saleprice']) ?? 0;
        
        $stmt->bind_param("ssssdsdd", 
            $_POST['name'],
            $_POST['type'],
            $thickness,
            $color,
            $quantity,
            $_POST['unit'],
            $price,
            $saleprice
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
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .content {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }
        .form-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 20px auto;
            max-width: 800px;
        }
        .section-title {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.625rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .btn-action {
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="form-section">
                <h2 class="section-title">Add New Material</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Material Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type" id="materialType" required>
                                    <option value="coil">Coil</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div id="coilFields" class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thickness</label>
                                    <select class="form-select" name="thickness">
                                        <option value="0.47">0.47</option>
                                        <option value="0.60">0.60</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <select class="form-select" name="color">
                                        <?php foreach ($coil_colors as $color): ?>
                                            <option value="<?php echo $color; ?>">
                                                <?php echo str_replace('_', ' ', ucfirst($color)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Initial Quantity</label>
                                <input type="number" class="form-control" name="quantity" value="0" step="0.01" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <select class="form-select" name="unit" required>
                                    <option value="sqft">Square Feet</option>
                                    <option value="pieces">Pieces</option>
                                    <option value="meters">Meters</option>
                                    <option value="liters">Liters</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Buy Price</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sale Price</label>
                                <input type="number" class="form-control" name="saleprice" step="0.01" required>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="manage_stock.php" class="btn btn-secondary btn-action">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-action">
                                    <i class="bi bi-plus-circle me-2"></i>Add Material
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('materialType').addEventListener('change', function() {
        const coilFields = document.getElementById('coilFields');
        coilFields.style.display = this.value === 'coil' ? 'block' : 'none';
    });

    // Bootstrap form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
