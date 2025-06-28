<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Fetch logged-in user info
//session_start();
$user_id = $_SESSION['user_id'] ?? null;
$user_info = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get recent orders with creator info
$recent_orders = $conn->query("
    SELECT o.*, 
           u.name as prepared_by_name,
           u.contact as prepared_by_contact,
           rdm.door_width, rdm.tower_height, rdm.tower_type, rdm.coil_color
    FROM orders o
    LEFT JOIN users u ON o.prepared_by = u.id
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get low stock materials
$low_stock_materials = $conn->query("
    SELECT name, quantity, unit, type, color
    FROM materials 
    WHERE quantity < 100
    ORDER BY quantity ASC
")->fetch_all(MYSQLI_ASSOC);

// Prepare gauge carousel data (show 4 at a time)
$low_stock_chunks = array_chunk($low_stock_materials, 4);

// Get the 15 most recent materials (adjust as needed)
$recent_materials = $conn->query("
    SELECT name, color, quantity, unit
    FROM materials
    ORDER BY id DESC
    LIMIT 15
")->fetch_all(MYSQLI_ASSOC);

// Split into chunks of 5 for carousel
$material_chunks = array_chunk($recent_materials, 5);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Office Staff Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Add Bootstrap and FontAwesome for carousel and icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Orders Grid Styling */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

/* Order Card Styling */
.order-card {
    background: white;
    border-radius: 12px; /* Slightly more rounded corners */
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transition for hover effects */
    cursor: pointer; /* Pointer cursor for interactivity */
}

.order-card:hover {
    transform: translateY(-10px); /* Slight jump effect */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
}

/* Order Header Styling */
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.status-badge {
    padding: 6px 12px; /* Slightly larger padding for better visibility */
    border-radius: 20px; /* Fully rounded badges */
    font-size: 0.9em; /* Slightly larger font size */
    text-transform: uppercase;
    font-weight: bold;
    color: white; /* White text for better contrast */
}

.status-badge.pending { background: #ffd700; }
.status-badge.reviewed { background: #87ceeb; }
.status-badge.confirmed { background: #98fb98; }
.status-badge.completed { background: #dda0dd; }
.status-badge.done { background: #90ee90; }

/* Order Details Styling */
.order-details p {
    margin: 8px 0; /* Increased spacing for better readability */
    font-size: 14px; /* Slightly larger font size */
    color: #555; /* Softer text color */
}

.order-details ul {
    margin: 8px 0;
    padding-left: 20px;
    list-style-type: disc; /* Add bullet points for better structure */
    color: #555; /* Softer text color */
}

.order-details ul li {
    margin-bottom: 5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .orders-grid {
        grid-template-columns: 1fr; /* Single column layout for smaller screens */
    }

    .order-card {
        padding: 15px;
    }
}
/* Order Card Styling */
.order-card {
    background: white;
    border-radius: 12px; /* Slightly more rounded corners */
    padding: 20px;
    border: 2px solid transparent; /* Default transparent border */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease; /* Smooth transition for hover effects */
    cursor: pointer; /* Pointer cursor for interactivity */
}

.order-card:hover {
    transform: translateY(-10px); /* Slight jump effect */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
    border-color: #d4af37; /* Gold border on hover */
}

/* Order Header Styling */
.order-header h4 {
    font-size: 18px; /* Larger font size for the order title */
    font-weight: bold; /* Bold for emphasis */
    color: #333; /* Darker text color for better readability */
    margin: 0; /* Remove default margin */
}

.order-header .status-badge {
    padding: 8px 15px; /* Slightly larger padding for better visibility */
    font-size: 12px; /* Adjusted font size */
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 20px; /* Fully rounded badges */
    color: white; /* White text for contrast */
}

/* Order Details Styling */
.order-details p {
    margin: 6px 0; /* Reduced spacing for compactness */
    font-size: 14px; /* Standard font size for readability */
    color: #555; /* Softer text color */
    line-height: 1.6; /* Improved line spacing for readability */
}

.order-details p strong {
    color: #333; /* Darker color for labels */
    font-weight: bold; /* Bold for emphasis */
}

.order-details ul {
    margin: 10px 0;
    padding-left: 20px;
    list-style-type: disc; /* Bullet points for better structure */
    color: #555; /* Softer text color */
    font-size: 14px; /* Standard font size */
}

.order-details ul li {
    margin-bottom: 5px;
    line-height: 1.5; /* Improved line spacing */
}

/* Highlight Important Text */
.order-details .highlight {
    color: #d4af37; /* Gold color for highlights */
    font-weight: bold; /* Bold for emphasis */
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-header h4 {
        font-size: 16px; /* Adjust font size for smaller screens */
    }

    .order-details p, .order-details ul {
        font-size: 13px; /* Adjust font size for smaller screens */
    }
}
/* General Typography */
body {
    font-family: 'Roboto', sans-serif; /* Use a modern, professional font */
    color: #333; /* Default text color for better readability */
}

/* Order Header Styling */
.order-header h4 {
    font-size: 22px; /* Larger font size for the order title */
    font-weight: 700; /* Bold for emphasis */
    color: #2c3e50; /* Darker text color for better readability */
    margin: 0; /* Remove default margin */
    letter-spacing: 0.5px; /* Slight letter spacing for clarity */
}

.order-header .status-badge {
    padding: 8px 15px; /* Slightly larger padding for better visibility */
    font-size: 12px; /* Adjusted font size */
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 20px; /* Fully rounded badges */
    color: white; /* White text for contrast */
    letter-spacing: 0.5px; /* Slight letter spacing for clarity */
}

/* Order Details Styling */
.order-details p {
    margin: 10px 0; /* Increased spacing for better readability */
    font-size: 16px; /* Slightly larger font size for clarity */
    color: #555; /* Softer text color */
    line-height: 1.8; /* Improved line spacing for readability */
}

.order-details p strong {
    color: #2c3e50; /* Darker color for labels */
    font-weight: bold; /* Bold for emphasis */
}

.order-details ul {
    margin: 10px 0;
    padding-left: 20px;
    list-style-type: disc; /* Bullet points for better structure */
    color: #555; /* Softer text color */
    font-size: 16px; /* Standard font size */
    line-height: 1.8; /* Improved line spacing */
}

.order-details ul li {
    margin-bottom: 8px;
    line-height: 1.6; /* Improved line spacing */
}

/* Highlight Important Text */
.order-details .highlight {
    color: #d4af37; /* Gold color for highlights */
    font-weight: bold; /* Bold for emphasis */
    font-size: 18px; /* Slightly larger font size for highlights */
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-header h4 {
        font-size: 20px; /* Adjust font size for smaller screens */
    }

    .order-details p, .order-details ul {
        font-size: 14px; /* Adjust font size for smaller screens */
    }
}

/* Modal Styles */
.modal-header.bg-warning {
    background-color: #ffc107;
    color: #000;
}

.list-group-item {
    border-left: 3px solid #dc3545;
}

.badge.bg-danger {
    font-size: 0.9em;
}

.modal-content {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem;
}

.modal-header .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.list-group-item {
    transition: all 0.3s ease;
    padding: 1rem;
}

.list-group-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}

.badge.bg-danger {
    font-size: 0.875rem;
}

.btn {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    border-radius: 6px;
}

.btn-primary {
    background-color: #0d6efd;
    border: none;
}

.btn-primary:hover {
    background-color: #0b5ed7;
}

.alert {
    border-radius: 8px;
}

@media (max-width: 768px) {
    .list-group-item .row > div {
        margin-bottom: 0.5rem;
        text-align: left !important;
    }
    
    .list-group-item .row > div:last-child {
        margin-bottom: 0;
    }
}

.modal-content {
    border-radius: 15px;
}

.list-group-item {
    border-radius: 8px !important;
    transition: transform 0.2s ease;
}

.list-group-item:hover {
    transform: translateX(5px);
}

.btn-primary {
    background-color: #0d6efd;
    border: none;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    border-radius: 8px;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.badge {
    font-weight: 500;
    font-size: 0.9rem;
}

.alert {
    border-radius: 8px;
    border: none;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
    }
    
    .list-group-item {
        padding: 0.75rem;
    }
}

/* Enhanced attention mark and animations */
.attention-mark {
    position: absolute;
    top: -20px;
    right: -20px;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    transform: rotate(45deg);
    pointer-events: none;
}

.attention-icon i {
    color: #da4453;
    animation: bounce 2s infinite;
}

.pulse-icon {
    position: relative;
    color: #da4453;
}

.pulse-icon::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    background: rgba(218, 68, 83, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    animation: pulse 2s infinite;
}

/* Material card styling */
.list-group-item {
    border-radius: 10px !important;
    transition: all 0.3s ease;
}

.shadow-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
}

.stock-badge .badge {
    font-size: 0.9rem;
    font-weight: 500;
}

/* Button styling */
.btn-primary {
    background: linear-gradient(45deg, #2980b9, #3498db);
    border: none;
    padding: 1rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(41,128,185,0.4);
}

/* Close button styling */
.btn-close {
    opacity: 0.8;
    transition: all 0.3s ease;
}

.btn-close:hover {
    opacity: 1;
    transform: rotate(90deg);
}

/* Animations */
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
}

/* Modal enhancements */
.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
    }
    
    .btn-primary {
        padding: 0.75rem 1.5rem;
    }
}

/* Gauge Styles */
.gauge-container {
    position: relative;
    width: 110px;
    margin: 0 auto 10px auto;
}
.gauge-label {
    position: absolute;
    top: 32px;
    left: 0;
    width: 100%;
    text-align: center;
    font-size: 1.1rem;
    color: #222;
    font-weight: bold;
    pointer-events: none;
}
.gauge-card {
    border-radius: 1rem;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    background: #fff;
    transition: box-shadow 0.3s, transform 0.3s;
}
.gauge-card:hover {
    box-shadow: 0 8px 24px rgba(212,175,55,0.15);
    transform: translateY(-4px) scale(1.03);
}
.gauge-material-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}
.gauge-badge {
    background: linear-gradient(45deg, #d4af37, #f7e08e);
    color: #222;
    font-weight: 500;
    border-radius: 12px;
    padding: 0.3em 0.8em;
    font-size: 0.9em;
}
.gauge-quantity {
    font-size: 1.2rem;
    font-weight: bold;
    color: #dc3545;
}
.carousel-inner {
    min-height: 260px;
}
.dashboard-section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #d4af37;
    margin-bottom: 0.7em;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 0.5em;
}
.carousel-indicators [data-bs-target] {
    background-color: #d4af37;
}
@media (max-width: 768px) {
    .gauge-container { width: 90px; }
}
        /* --- Add carousel styles --- */
        .material-carousel-top .card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            background: #fff;
            transition: box-shadow 0.3s, transform 0.3s;
            min-height: 160px;
        }
        .material-carousel-top .card:hover {
            box-shadow: 0 8px 24px rgba(52,152,219,0.15);
            transform: translateY(-4px) scale(1.03);
        }
        .material-carousel-top .material-qty {
            font-size: 1.3rem;
            font-weight: bold;
            color: #198754;
        }
        .material-carousel-top .material-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        .material-carousel-top .material-color {
            font-size: 0.95rem;
            color: #888;
        }
        .material-carousel-top .carousel-inner {
            min-height: 200px;
        }
        .material-carousel-top .carousel-indicators [data-bs-target] {
            background-color: #3498db;
        }
        .material-carousel-top .carousel-control-prev-icon,
        .material-carousel-top .carousel-control-next-icon {
            background-color: #3498db;
            border-radius: 50%;
        }
        /* --- End carousel styles --- */
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <h2>Dashboard</h2>

            <!-- --- Material Carousel under Dashboard topic --- -->
            <div class="container-fluid px-0 mb-4">
              <div class="card border-0 shadow-lg rounded-4 bg-gradient" style="background: linear-gradient(90deg, #e3f2fd 0%, #f8fafc 100%);">
                <div class="card-header bg-primary bg-gradient text-white rounded-top-4 d-flex align-items-center" style="min-height: 60px;">
                  <i class="fas fa-cubes fa-lg me-2"></i>
                  <span class="fs-5 fw-semibold">Current Material Quantities</span>
                </div>
                <div class="card-body p-0">
                  <div id="recentMaterialsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                    <div class="carousel-inner">
                      <?php foreach ($material_chunks as $i => $chunk): ?>
                        <div class="carousel-item <?php if ($i === 0) echo 'active'; ?>">
                          <div class="row g-4 justify-content-center py-4">
                            <?php foreach ($chunk as $mat): ?>
                              <div class="col-12 col-sm-6 col-lg-2">
                                <div class="card h-100 border-0 shadow-sm text-center rounded-4 bg-white position-relative overflow-hidden material-card-hover">
                                  <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                                    <div class="mb-2">
                                      <i class="fas fa-cube fa-2x text-primary"></i>
                                    </div>
                                    <div class="fw-bold fs-6 mb-1 material-name text-truncate w-100">
                                      <?php echo htmlspecialchars($mat['name'] ?? ''); ?>
                                    </div>
                                    <div class="mb-1 text-secondary material-color text-truncate w-100">
                                      <i class="fas fa-palette me-1"></i>
                                      <?php echo htmlspecialchars($mat['color'] ?? ''); ?>
                                    </div>
                                    <div class="fs-4 fw-bold text-success mb-2 material-qty" data-qty="<?php echo $mat['quantity']; ?>">
                                      0 <?php echo strtoupper($mat['unit'] ?? ''); ?>
                                    </div>
                                    <span class="badge rounded-pill bg-info-subtle text-primary-emphasis px-3 py-2 shadow-sm" style="font-size:0.95rem;">
                                      In Stock
                                    </span>
                                  </div>
                                  <div class="material-card-glow"></div>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#recentMaterialsCarousel" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon bg-primary rounded-circle shadow"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#recentMaterialsCarousel" data-bs-slide="next">
                      <span class="carousel-control-next-icon bg-primary rounded-circle shadow"></span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <!-- --- End Material Carousel --- -->

            <div class="section">
                <h3>Recent Orders</h3>
                <div class="orders-grid">
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h4>Order #<?php echo $order['id']; ?></h4>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['customer_contact']); ?></p>
                                <p><strong>Created By:</strong> <?php echo htmlspecialchars($order['prepared_by_name']); ?></p>
                                <p><strong>Staff Contact:</strong> <?php echo htmlspecialchars($order['prepared_by_contact']); ?></p>
                                <p><strong>Created:</strong> <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                                <?php if ($order['door_width']): ?>
                                    <p><strong>Specifications:</strong></p>
                                    <ul>
                                        <li>Width: <?php echo $order['door_width']; ?></li>
                                        <li>Height: <?php echo $order['tower_height']; ?></li>
                                        <li>Type: <?php echo ucfirst($order['tower_type']); ?></li>
                                        <li>Color: <?php echo str_replace('_', ' ', ucfirst($order['coil_color'])); ?></li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- User Info Badge (top right) -->
<div class="position-absolute top-0 end-0 p-3" style="z-index: 1050;">
  <?php if ($user_info): ?>
    <span class="badge bg-primary fs-6 me-2">
      <i class="fas fa-user-circle me-1"></i>
      <?php echo htmlspecialchars($user_info['name']); ?>
    </span>
    <span class="badge bg-secondary fs-6 text-uppercase">
      <i class="fas fa-user-tag me-1"></i>
      <?php echo htmlspecialchars($user_info['role']); ?>
    </span>
  <?php endif; ?>
</div>

    <?php
    // Check if there is any low stock (quantity < 100)
    $has_low_stock = false;
    foreach ($low_stock_materials as $m) {
        if ($m['quantity'] < 100) {
            $has_low_stock = true;
            break;
        }
    }
    ?>

    <!-- Low Stock Modal -->
    <div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-warning bg-gradient text-dark">
            <h5 class="modal-title d-flex align-items-center gap-2" id="lowStockModalLabel">
              <i class="fas fa-exclamation-triangle text-danger fs-3"></i>
              Low Stock Alert
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <p class="fs-5 mb-3">
              <span class="fw-bold text-danger">Attention!</span> Some materials are running low (less than <span class="fw-bold">100</span>).
            </p>
            <ul class="list-group mb-4">
              <?php foreach ($low_stock_materials as $m): ?>
                <?php if ($m['quantity'] < 100): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center border-0 bg-light rounded mb-2">
                    <span>
                      <i class="fas fa-cube text-warning me-2"></i>
                      <strong><?php echo htmlspecialchars($m['name']); ?></strong>
                      <?php if ($m['color']) echo ' - ' . htmlspecialchars($m['color']); ?>
                    </span>
                    <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                      <?php echo $m['quantity'] . ' ' . strtoupper($m['unit']); ?>
                    </span>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
            <a href="manage_stock.php" class="btn btn-lg btn-primary w-100 shadow-sm">
              <i class="fas fa-boxes-stacked me-2"></i>
              Manage Stock
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Roller Door Animation Overlay -->
<div id="rollerDoorOverlay" class="roller-door-overlay d-flex flex-column justify-content-end align-items-center">
  <div class="roller-door-inner d-flex flex-column align-items-center justify-content-center">
    <i class="fas fa-warehouse fa-4x text-secondary mb-3"></i>
    <span class="roller-door-text fw-bold text-secondary">Welcome, <?php echo htmlspecialchars($user_info['name']); ?>!</span>
    <span class="roller-door-subtext text-muted">Loading your dashboard...</span>
  </div>
</div>

    <!-- Bootstrap JS (place before </body>) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($has_low_stock): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var lowStockModal = new bootstrap.Modal(document.getElementById('lowStockModal'));
        lowStockModal.show();

        // Remove lingering backdrop on modal close
        document.getElementById('lowStockModal').addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(function(el) {
                el.parentNode.removeChild(el);
            });
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate material quantities
        document.querySelectorAll('.material-qty').forEach(function(el) {
            let target = parseFloat(el.getAttribute('data-qty'));
            let decimals = (target % 1 !== 0) ? 2 : 0;
            let duration = 1000;
            let start = 0;
            let startTime = null;
            let unit = el.textContent.replace(/[0-9., ]+/g, '').trim();

            function animateQty(ts) {
                if (!startTime) startTime = ts;
                let progress = Math.min((ts - startTime) / duration, 1);
                let current = start + (target - start) * progress;
                el.textContent = current.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: decimals}) + ' ' + unit;
                if (progress < 1) {
                    requestAnimationFrame(animateQty);
                } else {
                    el.textContent = target.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: decimals}) + ' ' + unit;
                }
            }
            requestAnimationFrame(animateQty);
        });
    });
    </script>
    <style>
.carousel-control-prev,
.carousel-control-next {
    background: transparent !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}
.carousel-control-prev:focus,
.carousel-control-next:focus,
.carousel-control-prev:hover,
.carousel-control-next:hover {
    background: transparent !important;
    box-shadow: none !important;
}
</style>
</body>
</html>
