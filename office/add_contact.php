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
    // Set default profile picture path relative to root
    $profilePic = '../../profilepic.jpg'; // Changed from just 'profilepic.jpg'

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $info = getimagesize($_FILES['profile_picture']['tmp_name']);
        if ($info !== false) {
            // Valid image file
            $extension = image_type_to_extension($info[2]);
            $profilePic = uniqid() . $extension;
            
            // Move uploaded file to uploads directory
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $profilePic)) {
                $error = "Error uploading file. Check directory permissions.";
                $profilePic = '../profilepic.jpg'; // Changed default path
            } else {
                $profilePic = 'uploads/profile/' . $profilePic; // Set path relative to root
            }
        } else {
            $error = "Invalid image file. Using default profile picture.";
            $profilePic = '../profilepic.jpg'; // Changed default path
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
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .form-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 20px;
        }
        .section-title {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }
        .radio-group {
            gap: 15px;
            padding: 10px 0;
        }
        .profile-preview-container {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
            border: 2px dashed #ddd;
        }
        #profilePicturePreview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .submit-btn {
            padding: 12px 30px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="container py-4">
                <div class="form-section">
                    <h2 class="section-title">Add New Contact</h2>
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type</label>
                                <div class="radio-group d-flex">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" value="individual" required>
                                        <label class="form-check-label">Individual</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" value="company">
                                        <label class="form-check-label">Company</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3" required></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control" name="mobile" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax Number</label>
                                <input type="text" class="form-control" name="tax_number">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" class="form-control" name="website">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tags</label>
                                <input type="text" class="form-control" name="tags" placeholder="customer, supplier, etc">
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" name="profile_picture" accept="image/*" id="profilePictureInput">
                                <div class="profile-preview-container mt-2">
                                    <img id="profilePicturePreview" src="#" alt="Profile Preview" style="display: none;">
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary submit-btn">
                                    <i class="bi bi-person-plus-fill me-2"></i>Add Contact
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS and its dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Profile picture preview
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
</body>
</html>
