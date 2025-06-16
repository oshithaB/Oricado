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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stock-card {
            transition: all 0.3s ease;
            border-left: 4px solid #d4af37;
            margin-bottom: 20px;
        }
        .stock-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .badge-coil {
            background-color: #d4af37;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .badge-other {
            background-color: #2c3e50;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-input {
            border-radius: 20px;
            padding-left: 15px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <nav>
            <h2>Stock Management</h2>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>

        <div class="content p-4">
            <!-- Add search bar -->
            <div class="search-container">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" id="materialSearch" class="form-control search-input border-start-0" 
                           placeholder="Search materials...">
                </div>
            </div>

            <!-- Rest of the content -->
            <div class="row">
                <!-- Modified material list structure -->
                <?php foreach ($materials as $material): ?>
                <div class="col-12 col-md-6 col-lg-4 material-item">
                    <div class="card stock-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($material['name']); ?>
                                <span class="badge badge-<?php echo $material['type']; ?> float-end">
                                    <?php echo ucfirst($material['type']); ?>
                                </span>
                            </h5>
                            <div class="details mt-3">
                                <?php if ($material['type'] == 'coil'): ?>
                                    <p class="text-muted mb-2">
                                        Color: <?php echo htmlspecialchars($material['color']); ?><br>
                                        Thickness: <?php echo $material['thickness']; ?>
                                    </p>
                                <?php endif; ?>
                                <p class="stock-amount mb-2">
                                    <strong>Stock:</strong> <?php echo $material['quantity'] . ' ' . $material['unit']; ?>
                                </p>
                                <a href="edit_material.php?id=<?php echo $material['id']; ?>" 
                                   class="btn btn-primary btn-sm">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('materialSearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            document.querySelectorAll('.material-item').forEach(item => {
                const materialName = item.querySelector('.card-title').textContent.toLowerCase();
                const materialDetails = item.querySelector('.details').textContent.toLowerCase();
                if (materialName.includes(searchValue) || materialDetails.includes(searchValue)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
