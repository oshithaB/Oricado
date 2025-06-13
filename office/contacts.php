<?php
require_once '../config/config.php';
checkAuth(['office_staff', 'admin']);

$search = $_GET['search'] ?? '';
$searchType = $_GET['searchType'] ?? 'name';

// Update search SQL based on search type
$searchSQL = '';
if ($search) {
    switch ($searchType) {
        case 'phone':
            $searchSQL = "WHERE mobile LIKE '%$search%' OR phone LIKE '%$search%'";
            break;
        case 'address':
            $searchSQL = "WHERE address LIKE '%$search%'";
            break;
        default:
            $searchSQL = "WHERE name LIKE '%$search%'";
    }
}

$contacts = $conn->query("SELECT * FROM contacts $searchSQL ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contacts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Preserve dashboard styles */
        .dashboard {
            display: flex;
        }
        
        .content {
            flex: 1;
            padding: 20px;
        }
        
        /* Updated contact card styles */
        .contacts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .contact-card {
            background: white;
            border-radius: 12px; /* Increased border radius */
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1); /* Subtle border */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1),
                       0 0 0 1px rgba(0, 0, 0, 0.05),
                       inset 0 1px 0 rgba(255, 255, 255, 0.6); /* Inner highlight */
            padding: 1.5rem 1rem; /* Increased padding */
            position: relative; /* For pseudo-element */
            overflow: hidden; /* Contains the shine effect */
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(
                90deg,
                rgba(0, 0, 0, 0.1),
                rgba(0, 0, 0, 0.3),
                rgba(0, 0, 0, 0.1)
            );
        }

        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15),
                       0 0 0 1px rgba(0, 0, 0, 0.1),
                       inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .profile-image {
            width: 120px;  /* Slightly increased for better visibility */
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 1rem auto;
            display: block;
            border: 2px solid #fff; /* Thinner border */
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
            background-color: #fff;
        }

        .profile-image:hover {
            transform: scale(1.02); /* Subtler hover effect */
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        /* Update profile image container for better integration */
        .profile-image-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            overflow: hidden;
            background-color: #fff;
            border: 3px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .contact-info {
            text-align: center;
            padding: 1rem;
            transition: transform 0.2s ease;
        }

        .contact-card:hover .contact-info {
            transform: translateY(-3px);
        }

        .tag {
            font-size: 0.8rem;
            padding: 0.2rem 0.6rem;
            margin: 0.2rem;
            border-radius: 15px;
            display: inline-block;
            background-color: #e9ecef;
        }

        .contact-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }

        /* Add to your existing <style> section */
        .search-container {
            padding: 1rem 0;
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .search-container .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
            border-color: #86b7fe;
        }

        .search-container .card {
            border: none;
            background-color: white;
        }

        .search-container .alert {
            margin-bottom: 0;
            border-left: 4px solid #0dcaf0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <!-- Add this search container -->
            <div class="search-container mb-4">
                <div class="container">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" 
                                               name="search" 
                                               class="form-control" 
                                               placeholder="Search contacts..." 
                                               value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select name="searchType" class="form-select">
                                        <option value="name" <?php echo $searchType === 'name' ? 'selected' : ''; ?>>Search by Name</option>
                                        <option value="phone" <?php echo $searchType === 'phone' ? 'selected' : ''; ?>>Search by Phone</option>
                                        <option value="address" <?php echo $searchType === 'address' ? 'selected' : ''; ?>>Search by Address</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <a href="contacts.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($search): ?>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-search me-2"></i>
                        Search results for: <strong><?php echo htmlspecialchars($search); ?></strong>
                        (<?php echo count($contacts); ?> results found)
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="contacts-grid">
                <?php foreach ($contacts as $contact): ?>
                <div class="contact-card">
                    <div class="card-body text-center p-4">
                        <?php 
                        $profileImage = '../uploads/profile/' . $contact['profile_picture'];
                        if (!file_exists($profileImage)) {
                            $profileImage = '../uploads/profile/profilepic.jpg';
                        }
                        ?>
                        <div class="profile-image-container">
                            <img src="<?php echo htmlspecialchars($profileImage); ?>" 
                                 alt="Profile" 
                                 class="profile-image"
                                 onerror="this.src='../uploads/profile/profilepic.jpg';">
                        </div>
                        
                        <h4 class="mb-1"><?php echo htmlspecialchars($contact['name']); ?></h4>
                        <span class="badge bg-primary mb-3"><?php echo ucfirst($contact['type']); ?></span>
                        
                        <div class="contact-info">
                            <p class="mb-1">
                                <i class="fas fa-phone text-primary"></i> 
                                <?php echo htmlspecialchars($contact['mobile']); ?>
                            </p>
                            <?php if ($contact['email']): ?>
                                <p class="mb-1">
                                    <i class="fas fa-envelope text-primary"></i> 
                                    <?php echo htmlspecialchars($contact['email']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if ($contact['tags']): ?>
                            <div class="tags mb-3">
                                <?php foreach (explode(',', $contact['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="contact-actions">
                            <a href="view_contact.php?id=<?php echo $contact['id']; ?>" 
                               class="btn btn-outline-primary btn-sm">View Details</a>
                            <a href="edit_contact.php?id=<?php echo $contact['id']; ?>" 
                               class="btn btn-outline-secondary btn-sm">Edit</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
