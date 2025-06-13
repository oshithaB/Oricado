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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stock-card {
            transition: all 0.3s ease;
            border-left: 4px solid #d4af37;
        }
        .stock-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .input-group-sm {
            max-width: 200px;
        }
        .badge-coil {
            background-color: #d4af37;
            color: white;
        }
        .badge-accessory {
            background-color: #2c3e50;
            color: white;
        }
        .stock-level {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .low-stock {
            color: #dc3545;
        }
        /* Update the search container styles in your existing <style> tag */
        .search-container {
            position: relative;
            min-width: 300px;
        }
        
        .search-container .input-group {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 50px;
            overflow: hidden;
        }
        
        .search-container .input-group-text {
            border: none;
            padding-left: 1.2rem;
        }
        
        .search-container .form-control {
            border: none;
            padding-left: 0.5rem;
        }
        
        .search-container .form-control:focus {
            box-shadow: none;
        }
        
        .alert {
            margin-bottom: 0;
            padding: 0.5rem 1rem;
        }
        
        .input-group.gap-2 {
            display: flex;
            flex-wrap: nowrap;
        }
        
        .input-group.gap-2 .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .input-group.gap-2 .input-group-text {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .btn-primary {
            background-color:rgb(2, 90, 241);
            border-color:rgb(255, 255, 255);
        }
        
        .btn-primary:hover {
            background-color: #c4a130;
            border-color: #c4a130;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        <div class="content p-4">
            <!-- Replace the existing search and header section with this -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-boxes me-2"></i>Current Stock Levels</h3>
                <div class="d-flex align-items-center gap-3">
                    <div class="search-container">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                id="materialSearch" 
                                class="form-control" 
                                placeholder="Search materials..."
                            >
                        </div>
                    </div>
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                            Stock updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($materials as $material): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card stock-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($material['name']); ?>
                                        <span class="badge <?php echo 'badge-' . $material['type']; ?> ms-2">
                                            <?php echo ucfirst($material['type']); ?>
                                        </span>
                                    </h5>
                                </div>
                                
                                <?php if ($material['type'] == 'coil'): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-palette me-1"></i> Color: <?php echo $material['color']; ?><br>
                                            <i class="fas fa-layer-group me-1"></i> Thickness: <?php echo $material['thickness']; ?>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <p class="stock-level <?php echo $material['quantity'] < 10 ? 'low-stock' : ''; ?>">
                                    <i class="fas fa-cubes me-2"></i>
                                    <?php echo $material['quantity'] . ' ' . $material['unit']; ?>
                                </p>

                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                    <div class="input-group input-group-sm gap-2">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="quantity" 
                                                   step="0.01" 
                                                   min="0" 
                                                   placeholder="Amount" 
                                                   required>
                                            <span class="input-group-text"><?php echo $material['unit']; ?></span>
                                        </div>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-plus-circle me-1"></i> Add
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Material search functionality
        document.getElementById('materialSearch').addEventListener('keyup', function(e) {
            const searchText = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.stock-card');
            
            cards.forEach(card => {
                const materialName = card.querySelector('.card-title').textContent.toLowerCase();
                const materialType = card.querySelector('.badge').textContent.toLowerCase();
                const colorElement = card.querySelector('.text-muted');
                const color = colorElement ? colorElement.textContent.toLowerCase() : '';
                
                if (materialName.includes(searchText) || 
                    materialType.includes(searchText) || 
                    color.includes(searchText)) {
                    card.parentElement.style.display = '';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });

        // Auto-hide alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }, 3000);
            });
        });
    </script>
</body>
</html>
