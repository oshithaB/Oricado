<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$term = $_GET['term'] ?? '';
$contacts = $conn->query("
    SELECT id, name, mobile, type 
    FROM contacts 
    WHERE name LIKE '%$term%' 
    ORDER BY name 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($contacts);
