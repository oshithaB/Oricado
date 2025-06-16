<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $conn->begin_transaction();
    
    try {
        $id = $conn->real_escape_string($_POST['id']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Update status
        $result = $conn->query("
            UPDATE supplier_quotations 
            SET status = '$status' 
            WHERE quotation_id = $id
        ");

        // If status is changed to 'receved', update material quantities
        if ($status === 'receved') {
            // Get all items from this quotation with all needed fields
            $items = $conn->query("
                SELECT 
                    qi.*, 
                    m.quantity as current_quantity,
                    qi.price as buy_price,
                    qi.newsaleprice as sale_price
                FROM quotation_items qi
                LEFT JOIN materials m ON qi.material_id = m.id
                WHERE qi.quotation_id = $id
            ")->fetch_all(MYSQLI_ASSOC);

            foreach ($items as $item) {
                if (!empty($item['material_id'])) {
                    // Update existing material
                    $new_quantity = $item['current_quantity'] + $item['quantity'];
                    
                    $stmt = $conn->prepare("
                        UPDATE materials 
                        SET quantity = ?,
                            price = ?,
                            saleprice = ?
                        WHERE id = ?
                    ");
                    $stmt->bind_param("dddi", 
                        $new_quantity,
                        $item['buy_price'],
                        $item['sale_price'],
                        $item['material_id']
                    );
                    $stmt->execute();
                } else {
                    // Insert new material
                    $stmt = $conn->prepare("
                        INSERT INTO materials (
                            name, type, unit, quantity, price, saleprice
                        ) VALUES (?, 'other', ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("ssddd",
                        $item['name'],
                        $item['unit'],
                        $item['quantity'],
                        $item['buy_price'],
                        $item['sale_price']
                    );
                    $stmt->execute();
                }
            }
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false]);
}
