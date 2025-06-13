<?php
require_once 'config/config.php';

// Check if PHPMailer is installed
if (!file_exists('vendor/autoload.php')) {
    die('Please install PHPMailer first. Run: composer require phpmailer/phpmailer');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure you have PHPMailer installed via composer

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Add email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if email exists in database with better error handling
        $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE email = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $error = "An error occurred. Please try again.";
        } else {
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $error = "An error occurred. Please try again.";
            } else {
                $result = $stmt->get_result();
                if ($user = $result->fetch_assoc()) {
                    // Generate 4-digit code
                    $reset_code = sprintf("%04d", rand(0, 9999));
                    $reset_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    
                    // Store reset code in database
                    $stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_expires = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $reset_code, $reset_expires, $user['id']);
                    $stmt->execute();
                    
                    // Send email with reset code
                    // Update email configuration with proper error handling
                    // Load email configuration
                    $email_config = require_once 'config/email_config.php';

                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->SMTPDebug = 2; // Enable debug output
                        $mail->Host = $email_config['smtp_host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $email_config['smtp_username'];
                        $mail->Password = $email_config['smtp_password'];
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = $email_config['smtp_port'];
                                
                        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
                        $mail->addAddress($email);
                                
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Code - Oricado';
                        $mail->Body = "Your password reset code is: <b>{$reset_code}</b><br>This code will expire in 15 minutes.";
                                
                        $mail->send();
                        header('Location: verify_reset_code.php?email=' . urlencode($email));
                        exit();
                    } catch (Exception $e) {
                        error_log("Mailer Error: " . $mail->ErrorInfo);
                        $error = "Email could not be sent. Error: " . $mail->ErrorInfo;
                    }
                } else {
                    // Add debugging information
                    error_log("Email not found: " . $email);
                    $error = "Email not found in our records.";
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Oricado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            max-width: 400px;
            width: 90%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
        }

        .login-logo {
            width: 150px; /* Fixed width */
            height: auto; /* Maintain aspect ratio */
            margin-bottom: 1.5rem;
            border-radius: 10px; /* Slightly rounded corners */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Subtle shadow */
            transition: transform 0.3s ease; /* Smooth hover effect */
        }

        .login-logo:hover {
            transform: scale(1.05); /* Slight zoom on hover */
        }

        .btn-login {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .btn-login:hover {
            background-color: #ffb300;
            border-color: #ffb300;
            color: #000;
        }

        .input-group-text {
            background-color: #fff;
            border-right: none;
        }

        .form-control {
            border-left: none;
        }

        .form-control:focus {
            border-color: #ffc107;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <img src="assets/images/oricadologo.jpeg" 
                 alt="Oricado Logo" 
                 class="login-logo"
                 onerror="this.onerror=null; this.src='assets/images/default-logo.png';">
            <h2 class="mt-3">Forgot Password</h2>
            <p class="text-muted">Enter your email to receive a reset code</p>
        </div>
        
        <form method="POST">
            <div class="form-group mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="Enter your email"
                           required>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100 mb-3">
                <i class="fas fa-paper-plane me-2"></i>Send Reset Code
            </button>
            <a href="index.php" class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    
    <?php if (isset($error)): ?>
    <script>
        toastr.error('<?php echo $error; ?>', 'Error');
    </script>
    <?php endif; ?>
</body>
</html>