<?php
require_once '../config/config.php';
checkAuth(['office_staff', 'admin']);

$search = $_GET['search'] ?? '';
$searchSQL = $search ? "WHERE name LIKE '%$search%'" : '';

$contacts = $conn->query("SELECT * FROM contacts $searchSQL ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contacts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="contacts-header">
                <h2>Contacts</h2>
                <div class="search-bar">
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Search contacts..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">Search</button>
                    </form>
                </div>
            </div>
            
            <div class="contacts-grid">
                <?php foreach ($contacts as $contact): ?>
                <div class="contact-card">
                    <?php 
                    $profileImage = '../uploads/profile/' . $contact['profile_picture'];
                    // Add debug information
                    if (!file_exists($profileImage)) {
                        error_log("Profile image not found: " . $profileImage);
                        $profileImage = '../uploads/profile/profilepic.jpg';
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($profileImage); ?>" 
                         alt="Profile" 
                         class="profile-image"
                         onerror="this.src='../uploads/profile/profilepic.jpg'; console.log('Image failed to load:', this.src);">
                    <div class="contact-info">
                        <h3><?php echo htmlspecialchars($contact['name']); ?></h3>
                        <span class="contact-type"><?php echo ucfirst($contact['type']); ?></span>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['mobile']); ?></p>
                        <?php if ($contact['email']): ?>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?></p>
                        <?php endif; ?>
                        <?php if ($contact['tags']): ?>
                            <div class="tags">
                                <?php foreach (explode(',', $contact['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="contact-actions">
                        <a href="view_contact.php?id=<?php echo $contact['id']; ?>" class="button">View Details</a>
                        <a href="edit_contact.php?id=<?php echo $contact['id']; ?>" class="button">Edit</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
