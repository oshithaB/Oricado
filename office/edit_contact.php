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

// Add delete contact handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    // Delete the contact's profile picture if it's not the default one
    if ($contact['profile_picture'] != 'profilepic.jpg') {
        $profilePath = '../uploads/profile/' . $contact['profile_picture'];
        if (file_exists($profilePath)) {
            unlink($profilePath);
        }
    }
    
    // Delete the contact from database
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $contact_id);
    if ($stmt->execute()) {
        header('Location: contacts.php?deleted=1');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = '../uploads/profile/';
    $profilePic = $contact['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $info = getimagesize($_FILES['profile_picture']['tmp_name']);
        if ($info !== false) {
            $extension = image_type_to_extension($info[2]);
            $profilePic = uniqid() . $extension;
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $profilePic);
        }
    }

    $stmt = $conn->prepare("UPDATE contacts SET 
        type = ?, name = ?, address = ?, phone = ?, mobile = ?, 
        email = ?, tax_number = ?, website = ?, profile_picture = ?, tags = ? 
        WHERE id = ?");
    
    $stmt->bind_param("ssssssssssi",
        $_POST['type'],
        $_POST['name'],
        $_POST['address'],
        $_POST['phone'],
        $_POST['mobile'],
        $_POST['email'],
        $_POST['tax_number'],
        $_POST['website'],
        $profilePic,
        $_POST['tags'],
        $contact_id
    );

    if ($stmt->execute()) {
        header('Location: view_contact.php?id=' . $contact_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Contact</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Edit Contact</h2>
                
                <!-- Add delete button at the top -->
                <div class="actions" style="margin-bottom: 20px;">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this contact? This action cannot be undone.');" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="button delete-btn">Delete Contact</button>
                    </form>
                </div>

                <form method="POST" enctype="multipart/form-data" class="contact-form">
                    <div class="form-group">
                        <label>Type:</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="type" value="individual" <?php echo $contact['type'] == 'individual' ? 'checked' : ''; ?> required>
                                Individual
                            </label>
                            <label>
                                <input type="radio" name="type" value="company" <?php echo $contact['type'] == 'company' ? 'checked' : ''; ?>>
                                Company
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($contact['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Address:</label>
                        <textarea name="address" required rows="3"><?php echo htmlspecialchars($contact['address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Phone Number:</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($contact['phone']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Mobile Number:</label>
                        <input type="tel" name="mobile" value="<?php echo htmlspecialchars($contact['mobile']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($contact['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Tax Number:</label>
                        <input type="text" name="tax_number" value="<?php echo htmlspecialchars($contact['tax_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Website:</label>
                        <input type="url" name="website" value="<?php echo htmlspecialchars($contact['website']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Current Profile Picture:</label>
                        <img src="../uploads/profile/<?php echo htmlspecialchars($contact['profile_picture']); ?>" 
                             alt="Current Profile" 
                             style="max-width: 100px;"
                             onerror="this.src='../uploads/profile/profilepic.jpg'">
                        <input type="file" name="profile_picture" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Tags (comma separated):</label>
                        <input type="text" name="tags" value="<?php echo htmlspecialchars($contact['tags']); ?>" placeholder="customer, supplier, etc">
                    </div>

                    <div class="actions">
                        <a href="contacts.php" class="button">Cancel</a>
                        <button type="submit" class="button primary">Update Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
