<?php
require_once '../config/config.php';
require_once '../includes/PDFGenerator.php';
checkAuth(['admin']);

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: reviewed_orders.php');
    exit();
}

// Get complete order details with all measurements
$order = $conn->query("
    SELECT o.*, 
           rdm.*, 
           wdm.*,
           u1.name as prepared_by_name,
           u2.name as checked_by_name,
           u3.name as admin_approved_by_name,
           o.admin_approved_at,
           q.id as quotation_id,
           q.total_amount as quotation_amount
    FROM orders o 
    LEFT JOIN roller_door_measurements rdm ON o.id = rdm.order_id
    LEFT JOIN wicket_door_measurements wdm ON o.id = wdm.order_id
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    LEFT JOIN users u3 ON o.admin_approved_by = u3.id
    LEFT JOIN quotations q ON o.quotation_id = q.id
    WHERE o.id = $order_id
")->fetch_assoc();

// Get materials with costs
$materials = $conn->query("
    SELECT m.*, om.quantity as used_quantity,
           (m.price * om.quantity) as material_cost
    FROM order_materials om
    JOIN materials m ON om.material_id = m.id
    WHERE om.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);

$order['materials'] = $materials;
$order['total_material_cost'] = array_sum(array_column($materials, 'material_cost'));

// Include all roller door measurements
$order['roller_door'] = [
    'section1' => $order['section1'],
    'section2' => $order['section2'],
    'outside_width' => $order['outside_width'],
    'inside_width' => $order['inside_width'],
    'door_width' => $order['door_width'],
    'tower_height' => $order['tower_height'],
    'tower_type' => $order['tower_type'],
    'coil_color' => $order['coil_color'],
    'thickness' => $order['thickness'],
    'covering' => $order['covering'],
    'side_lock' => $order['side_lock'],
    'motor' => $order['motor'],
    'fixing' => $order['fixing'],
    'down_lock' => $order['down_lock']
];

// Include wicket door measurements if they exist
if ($order['point1']) {
    $order['wicket_door'] = [
        'point1' => $order['point1'],
        'point2' => $order['point2'],
        'point3' => $order['point3'],
        'point4' => $order['point4'],
        'point5' => $order['point5'],
        'thickness' => $order['thickness'],
        'door_opening' => $order['door_opening'],
        'handle' => $order['handle'],
        'letter_box' => $order['letter_box'],
        'coil_color' => $order['coil_color']
    ];
}

// Generate PDF
$pdf = new PDFGenerator($order_id);
$pdf->generatePDF($order, 'admin_review');
