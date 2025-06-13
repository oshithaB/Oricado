<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['quotation_id'] ?? 0;

if ($quotation_id) {
    $materials = $conn->query("
        SELECT qi.name, qi.quantity, qi.unit, qi.price, qi.amount
        FROM quotation_items qi
        WHERE qi.quotation_id = $quotation_id
        ORDER BY qi.name
    ")->fetch_all(MYSQLI_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($materials);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Quotation ID required']);
}
