<?php
require_once '../config/config.php';
checkAuth(['office_staff', 'admin']);

$contact_id = $_GET['id'] ?? null;
if (!$contact_id) {
    header('Location: contacts.php');
    exit();
}

$contact = $conn->query("SELECT * FROM contacts WHERE id = $contact_id")->fetch_assoc();
if (!$contact) {
    header('Location: contacts.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .contact-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 30px;
            max-width: 800px;
            margin: 30px auto;
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 60px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .contact-info {
            flex-grow: 1;
        }

        .contact-name {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .contact-type {
            display: inline-block;
            padding: 4px 12px;
            background-color: #e9ecef;
            border-radius: 20px;
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .contact-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .detail-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .detail-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="contact-card">
            <!-- Profile Section -->
            <div class="profile-section">
                <img src="../uploads/profile/<?php echo htmlspecialchars($contact['profile_picture']); ?>" 
                     alt="Profile" 
                     class="profile-image"
                     onerror="this.src='../uploads/profile/profilepic.jpg'">
                <div class="contact-info">
                    <h1 class="contact-name"><?php echo htmlspecialchars($contact['name']); ?></h1>
                    <span class="contact-type"><?php echo ucfirst($contact['type']); ?></span>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="contact-details">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="bi bi-phone me-2"></i>Mobile
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($contact['mobile']); ?></div>
                </div>

                <?php if ($contact['phone']): ?>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="bi bi-telephone me-2"></i>Phone
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($contact['phone']); ?></div>
                </div>
                <?php endif; ?>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="bi bi-geo-alt me-2"></i>Address
                    </div>
                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($contact['address'])); ?></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="contacts.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Contacts
                </a>
                <a href="edit_contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>Edit Contact
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
