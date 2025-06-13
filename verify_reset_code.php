<?php
require_once 'config/config.php';

// Get email from URL parameter first
$email = $_GET['email'] ?? '';
if (empty($email)) {
    header('Location: index.php');
    exit();
}

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'] ?? '';
    
    // Validate input
    if (empty($code) || !preg_match('/^\d{4}$/', $code)) {
        $error = "Please enter a valid 4-digit code";
    } else {
        // First, check if the code exists and hasn't expired
        $stmt = $conn->prepare("SELECT id, reset_code, reset_expires FROM users WHERE email = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $error = "Database error occurred";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            // Debug output
            error_log("Verification attempt:");
            error_log("Email: " . $email);
            error_log("Submitted code: " . $code);
            error_log("DB reset_code: " . ($user['reset_code'] ?? 'null'));
            error_log("DB reset_expires: " . ($user['reset_expires'] ?? 'null'));
            error_log("Current time: " . date('Y-m-d H:i:s'));
            
            if (!$user) {
                $error = "Email not found";
            } elseif ($user['reset_code'] !== $code) {
                $error = "Invalid reset code";
            } elseif (strtotime($user['reset_expires']) < time()) {
                $error = "Reset code has expired";
            } else {
                // Valid code - store user id in session and redirect
                $_SESSION['reset_user_id'] = $user['id'];
                header('Location: reset_password.php');
                exit();
            }
            $stmt->close();
        }
    }
}

// Check current reset code status
$debug_stmt = $conn->prepare("SELECT reset_code, reset_expires FROM users WHERE email = ?");
$debug_stmt->bind_param("s", $email);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();
$debug_user = $debug_result->fetch_assoc();
error_log("Current DB state for email " . $email . ":");
error_log("Reset code in DB: " . ($debug_user['reset_code'] ?? 'null'));
error_log("Reset expires: " . ($debug_user['reset_expires'] ?? 'null'));
$debug_stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Reset Code - Oricado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-container {
            max-width: 400px;
            width: 90%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
        }
        .code-input {
            letter-spacing: 0.5em;
            text-align: center;
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="text-center mb-4">
            <h2>Verify Reset Code</h2>
            <p class="text-muted">Enter the 4-digit code sent to<br><?php echo htmlspecialchars($email); ?></p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group mb-4">
                <input type="text" 
                       name="code" 
                       class="form-control code-input" 
                       placeholder="Enter code"
                       maxlength="4"
                       pattern="\d{4}"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       required
                       autofocus>
            </div>
            <button type="submit" class="btn btn-warning w-100 mb-3">
                <i class="fas fa-check-circle me-2"></i>Verify Code
            </button>
            <div class="text-center">
                <a href="forgot_password.php" class="text-decoration-none">
                    <i class="fas fa-redo me-1"></i>Request new code
                </a>
            </div>
        </form>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Auto-submit when 4 digits are entered
    document.querySelector('input[name="code"]').addEventListener('input', function(e) {
        if (this.value.length === 4 && /^\d{4}$/.test(this.value)) {
            this.form.submit();
        }
    });
    </script>
</body>
</html>