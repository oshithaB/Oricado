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
        <label class="radio-option">
            <input type="radio" name="type" value="individual" required>
            <span>Individual</span>
        </label>
        <label class="radio-option">
            <input type="radio" name="type" value="company">
            <span>Company</span>
        </label>
        <style>
            .radio-group {
    display: flex;
    gap: 20px; /* Add spacing between options */
    margin-top: 10px;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 8px; /* Add spacing between the radio button and label */
    font-size: 16px; /* Adjust font size */
    cursor: pointer; /* Change cursor to pointer for better UX */
}

.radio-option input[type="radio"] {
    appearance: none; /* Remove default radio button styling */
    width: 18px;
    height: 18px;
    border: 2px solid black; /* Default border color */
    border-radius: 50%; /* Make it circular */
    outline: none;
    cursor: pointer;
    transition: all 0.3s ease; /* Smooth transition */
}

.radio-option input[type="radio"]:checked {
    background-color: #FFC107; /* Change background color to dark yellow */
    border-color: black; /* Set border color to black when selected */
}

.radio-option span {
    color: #333; /* Text color */
    font-weight: 500; /* Slightly bold text */
}
            </style>
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
                        <input type="file" name="profile_picture" accept="image/*" id="profilePictureInput">
                        <div class="profile-picture-preview">
                        <img id="profilePicturePreview" src="#" alt="Profile Preview" style="display: none;">
                <style>
                  .profile-picture-preview {
                               margin-top: 10px;
                        }

                .profile-picture-preview img {
                      max-width: 150px;
                      max-height: 150px;
                      border: 1px solid #ddd;
                      border-radius: 4px;
}
                </style>
           </div>
        </div>
<script>
    document.getElementById('profilePictureInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('profilePicturePreview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.style.display = 'none';
        }
    });
</script>


                    <div class="form-group">
                        <label>Tags (comma separated):</label>
                        <input type="text" name="tags" placeholder="customer, supplier, etc">
                    </div>

                    <button type="submit">Add Contact</button>
                    <style>
                        .contact-form input[type="text"],
                        .contact-form input[type="tel"],
                        .contact-form input[type="email"],
                        .contact-form input[type="url"],
                        .contact-form input[type="file"],
                        .contact-form textarea {
    width: 100%; /* Make all input fields take the full width of their container */
    padding: 10px; /* Add consistent padding */
    border: 1px solid #ddd; /* Add a border */
    border-radius: 4px; /* Add rounded corners */
    font-size: 16px; /* Ensure consistent font size */
    box-sizing: border-box; /* Include padding and border in the element's total width */
}

.contact-form textarea {
    resize: vertical; /* Allow vertical resizing only */
}

.contact-form .form-group {
    margin-bottom: 15px; /* Add spacing between form groups */
}
                        </style>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
