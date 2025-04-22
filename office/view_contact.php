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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <div class="contact-header">
                    <img src="../uploads/profile/<?php echo htmlspecialchars($contact['profile_picture']); ?>" 
                         alt="Profile" 
                         class="profile-image"
                         onerror="this.src='../uploads/profile/profilepic.jpg'">
                    <h2><?php echo htmlspecialchars($contact['name']); ?></h2>
                    <span class="contact-type"><?php echo ucfirst($contact['type']); ?></span>
                </div>

                <div class="contact-details">
                    <div class="detail-group">
                        <h3>Contact Information</h3>
                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($contact['mobile']); ?></p>
                        <?php if ($contact['phone']): ?>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($contact['phone']); ?></p>
                        <?php endif; ?>
                        <?php if ($contact['email']): ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($contact['email']); ?></p>
                        <?php endif; ?>
                        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($contact['address'])); ?></p>
                    </div>

                    <?php if ($contact['tax_number'] || $contact['website']): ?>
                    <div class="detail-group">
                        <h3>Additional Information</h3>
                        <?php if ($contact['tax_number']): ?>
                            <p><strong>Tax Number:</strong> <?php echo htmlspecialchars($contact['tax_number']); ?></p>
                        <?php endif; ?>
                        <?php if ($contact['website']): ?>
                            <p><strong>Website:</strong> <a href="<?php echo htmlspecialchars($contact['website']); ?>" target="_blank"><?php echo htmlspecialchars($contact['website']); ?></a></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($contact['tags']): ?>
                    <div class="detail-group">
                        <h3>Tags</h3>
                        <div class="tags">
                            <?php foreach (explode(',', $contact['tags']) as $tag): ?>
                                <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="actions">
                    <a href="contacts.php" class="button">Back to Contacts</a>
                    <a href="edit_contact.php?id=<?php echo $contact['id']; ?>" class="button primary">Edit Contact</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
