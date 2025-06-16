<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No order ID provided']);
    exit;
}

$order_id = $conn->real_escape_string($_GET['id']);

// Get order details with quotation type
$sql = "SELECT o.*, q.type as quotation_type, q.id as quotation_id 
        FROM orders o
        LEFT JOIN quotations q ON o.quotation_id = q.id
        WHERE o.id = $order_id";

$order = $conn->query($sql)->fetch_assoc();

$materials = [];

if ($order) {
    if ($order['quotation_type'] === 'raw_materials') {
        // For raw material quotations, get items from quotation_items
        $sql = "SELECT 
                    qi.name,
                    qi.quantity,
                    qi.unit,
                    qi.price as cost,
                    qi.newsaleprice as selling_price,
                    m.type,
                    m.color,
                    m.thickness
                FROM quotation_items qi
                LEFT JOIN materials m ON qi.material_id = m.id
                WHERE qi.quotation_id = {$order['quotation_id']}";
    } else {
        // For regular orders, get items from order_materials
        $sql = "SELECT 
                    m.name,
                    om.quantity,
                    m.unit,
                    m.price as cost,
                    m.saleprice as selling_price,
                    m.type,
                    m.color,
                    m.thickness
                FROM order_materials om
                JOIN materials m ON om.material_id = m.id
                WHERE om.order_id = $order_id";
    }

    $result = $conn->query($sql);
    if ($result) {
        $materials = $result->fetch_all(MYSQLI_ASSOC);
    }
}

echo json_encode($materials);
