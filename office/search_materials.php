<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$term = $_GET['term'] ?? '';
$materials = $conn->query("
    SELECT id, name, type, unit, saleprice, quantity, color, thickness
    FROM materials 
    WHERE name LIKE '%$term%' 
    ORDER BY name 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Format material names for display
foreach ($materials as &$material) {
    if ($material['type'] == 'coil') {
        $material['display_name'] = "{$material['name']} - {$material['color']} ({$material['thickness']})";
    } else {
        $material['display_name'] = $material['name'];
    }
}

header('Content-Type: application/json');
echo json_encode($materials);
