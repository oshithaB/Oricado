<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, password, role, name, contact FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['contact'] = $user['contact'];
            
            $redirect = '';
            switch($user['role']) {
                case 'admin':
                    $redirect = 'admin/dashboard.php';
                    break;
                case 'supervisor':
                    $redirect = 'supervisor/dashboard.php';
                    break;
                case 'office_staff':
                    $redirect = 'office/dashboard.php';
                    break;
            }
            echo $redirect;
            exit();
        }
    }
    echo "error";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Oricado Login</title>
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
            transition: all 0.3s ease;
        }

        .login-container:hover {
            box-shadow: 0 0 30px rgba(255, 193, 7, 0.4);
        }

        .login-logo {
            max-width: 200px;
            height: auto;
            border-radius: 30px;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .login-logo:hover {
            transform: scale(1.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
            border-color: #ffc107;
        }

        .btn-login {
            background: #ffc107;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #ffb300;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        h2 {
            color: #333;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-content {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
        }

        .spinner-border {
            color: #ffc107;
            width: 3rem;
            height: 3rem;
        }

        .loading-text {
            margin-top: 1rem;
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center">
            <img src="assets/images/oricadologo.jpeg" alt="Oricado Logo" class="login-logo">
            <h2>Oricado Login</h2>
        </div>
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter username"
                           required>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter password"
                           required>
                </div>
                <div class="text-end mt-2">
                    <a href="forgot_password.php" class="text-decoration-none text-muted small">
                        <i class="fas fa-key me-1"></i>Forgot Password?
                    </a>
                </div>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>
    </div>

    <div class="loading-overlay">
        <div class="loading-content">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-text">
                Logging in...
                <div class="small text-muted">Please wait</div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Toast configuration
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000",
                "extendedTimeOut": "2000"
            };

            // Handle form submission
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading overlay
                $('.loading-overlay').css('display', 'flex').hide().fadeIn(300);
                
                // Disable submit button
                $('.btn-login').prop('disabled', true);
                
                // Submit the form
                $.ajax({
                    type: 'POST',
                    url: 'index.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.includes('admin/dashboard.php') || 
                            response.includes('supervisor/dashboard.php') || 
                            response.includes('office/dashboard.php')) {
                            
                            // Add 2 second delay before redirect
                            setTimeout(function() {
                                window.location.href = response;
                            }, 2000); // 2000 milliseconds = 2 seconds
                            
                        } else {
                            // Hide loading overlay
                            $('.loading-overlay').fadeOut(300);
                            // Enable submit button
                            $('.btn-login').prop('disabled', false);
                            // Show error message
                            toastr.error('Invalid username or password', 'Login Failed');
                        }
                    },
                    error: function() {
                        // Hide loading overlay
                        $('.loading-overlay').fadeOut(300);
                        // Enable submit button
                        $('.btn-login').prop('disabled', false);
                        // Show error message
                        toastr.error('Something went wrong. Please try again.', 'Error');
                    }
                });
            });
        });

        <?php if (isset($error)): ?>
            toastr.error('<?php echo $error; ?>', 'Login Failed');
        <?php endif; ?>
    </script>
</body>
</html>
