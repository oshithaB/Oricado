<?php
require_once 'config/config.php';

// Only allow this in development
if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'development') {
    die('Access denied');
}

$result = $conn->query("SELECT id, username, email FROM users");
echo "<h2>Users in Database:</h2>";
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";