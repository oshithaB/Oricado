<?php
define('DB_HOST', 'mysql-12b8ac00-oxxakala-e9f8.j.aivencloud.com');
define('DB_USER', 'avnadmin');
define('DB_PASS', 'AVNS_cTlR-1QPHWr_QjUBCgn');
define('DB_NAME', 'defaultdb');
define('DB_PORT', 18952);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

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
