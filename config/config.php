<?php
define('DB_HOST', 'sql303.infinityfree.com');
define('DB_USER', 'if0_38830131');
define('DB_PASS', 'mNUka4Qsrp');
define('DB_NAME', 'if0_38830131_oricado');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

function checkAuth($allowedRoles = []) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['name'])) {
        header('Location: ../login.php');
        exit();
    }
    
    if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: ../unauthorized.php');
        exit();
    }
}
?>
