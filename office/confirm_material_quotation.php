<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quotation_id'])) {
    $quotation_id = intval($_POST['quotation_id']);
    
    $conn->begin_transaction();
    try {
        // Get quotation details
        $quotation = $conn->query("
            SELECT q.*, u.name as prepared_by_name 
            FROM quotations q
            LEFT JOIN users u ON q.created_by = u.id
            WHERE q.id = $quotation_id AND q.type = 'raw_materials'
        ")->fetch_assoc();

        if (!$quotation) {
            throw new Exception("Quotation not found");
        }

        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                customer_name, customer_contact, prepared_by,
                status, total_price, quotation_id, balance_amount,
                created_at
            ) VALUES (?, ?, ?, 'pending', ?, ?, ?, NOW())
        ");

        $stmt->bind_param("ssiddd",
            $quotation['customer_name'],
            $quotation['customer_contact'],
            $quotation['created_by'],
            $quotation['total_amount'],
            $quotation_id,
            $quotation['total_amount']  // Initial balance = total amount
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to create order");
        }

        $conn->commit();
        $_SESSION['success_message'] = "Material order created successfully!";
        header('Location: pending_orders.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: quotations.php');
        exit();
    }
}
