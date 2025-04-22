<?php
require_once '../config/config.php';
checkAuth(['office_staff', 'admin']);

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/profile/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    chmod($uploadDir, 0777);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $profilePic = 'profilepic.jpg';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $info = getimagesize($_FILES['profile_picture']['tmp_name']);
        if ($info !== false) {
            // Valid image file
            $extension = image_type_to_extension($info[2]);
            $profilePic = uniqid() . $extension;
            
            // Ensure the file is moved with proper permissions
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $profilePic)) {
                chmod($uploadDir . $profilePic, 0644);
            } else {
                $error = "Error uploading file. Check directory permissions. Error: " . error_get_last()['message'];
            }
        } else {
            $error = "Invalid image file. Please upload a valid image.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO contacts (type, name, address, phone, mobile, email, tax_number, 
        website, profile_picture, tags, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
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
        $_SESSION['user_id']
    );

    if ($stmt->execute()) {
        header('Location: contacts.php?success=1');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Contact</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Add New Contact</h2>
                <form method="POST" enctype="multipart/form-data" class="contact-form">
                    <div class="form-group">
                        <label>Type:</label>
                        <div class="radio-group">
                            <label><input type="radio" name="type" value="individual" required> Individual</label>
                            <label><input type="radio" name="type" value="company"> Company</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Address:</label>
                        <textarea name="address" required rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Phone Number:</label>
                        <input type="tel" name="phone">
                    </div>

                    <div class="form-group">
                        <label>Mobile Number:</label>
                        <input type="tel" name="mobile" required>
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email">
                    </div>

                    <div class="form-group">
                        <label>Tax Number:</label>
                        <input type="text" name="tax_number">
                    </div>

                    <div class="form-group">
                        <label>Website:</label>
                        <input type="url" name="website">
                    </div>

                    <div class="form-group">
                        <label>Profile Picture:</label>
                        <input type="file" name="profile_picture" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Tags (comma separated):</label>
                        <input type="text" name="tags" placeholder="customer, supplier, etc">
                    </div>

                    <button type="submit">Add Contact</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
