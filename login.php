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
            
            switch($user['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'supervisor':
                    header('Location: supervisor/dashboard.php');
                    break;
                case 'office_staff':
                    header('Location: office/dashboard.php');
                    break;
            }
            exit();
        }
    }
    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Oricado Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="assets/images/oricado logo.jpg" alt="Oricado Logo" class="login-logo"> 
            <style>
         .logo-container {
          text-align: center;
          margin-bottom: 20px;
        }
        .login-logo {
    max-width: 300px;
    height: auto;
    border-radius: 60px;
}
</style>
             
        </div>
        <h2>Oricado Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            
        </form>
        <style>
.logo-container {
    text-align: center;
    margin-bottom: 20px;
}

.login-logo {
    max-width: 300px;
    height: auto;
    border-radius: 60px;
}

.login-container {
    max-width: 400px;
    margin: 100px auto;
    padding: 20px;
    background: white;
    border-radius: 4px;
    box-shadow: 0 0 10px 2px #ffc107; /* Highlight color changed to yellow */
}
</style>
    </div>
</body>
</html>
