<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_texts = [
    '0.60' => 'Default text for 0.60 thickness quotation...',
    '0.47' => 'Default text for 0.47 thickness quotation...'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Prepare variables for binding
        $quotationType = $_POST['quotation_type'];
        $customerName = $_POST['customer_name'];
        $customerContact = $_POST['customer_contact'];
        $totalAmount = $_POST['total_amount'];
        $userId = $_SESSION['user_id'];
        $coilThickness = $_POST['coil_thickness'] ?? '';
        $quotationText = $_POST['quotation_text'] ?? '';

        // Insert quotation
        $stmt = $conn->prepare("INSERT INTO quotations (
            type, customer_name, customer_contact, total_amount, 
            created_by, coil_thickness, quotation_text
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssidss", 
            $quotationType,
            $customerName,
            $customerContact,
            $totalAmount,
            $userId,
            $coilThickness,
            $quotationText
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating quotation: " . $stmt->error);
        }
        
        $quotation_id = $conn->insert_id;

        // Process items
        if (!empty($_POST['items']) && is_array($_POST['items'])) {
            $itemStmt = $conn->prepare("INSERT INTO quotation_items (
                quotation_id, material_id, name, quantity, unit, 
                discount, price, taxes, amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($_POST['items'] as $item) {
                // Prepare variables for item binding
                $materialId = $item['material_id'];
                $name = $item['name'];
                $quantity = floatval($item['quantity']);
                $unit = $item['unit'];
                $discount = floatval($item['discount']);
                $price = floatval($item['price']);
                $taxes = floatval($item['taxes']);
                $amount = floatval($item['amount']);

                $itemStmt->bind_param("iisdsdddd",
                    $quotation_id,
                    $materialId,
                    $name,
                    $quantity,
                    $unit,
                    $discount,
                    $price,
                    $taxes,
                    $amount
                );
                
                if (!$itemStmt->execute()) {
                    throw new Exception("Error inserting item: " . $itemStmt->error);
                }

                // Deduct stock for raw materials quotation
                if ($_POST['quotation_type'] == 'raw_materials') {
                    $updateStmt = $conn->prepare("UPDATE materials SET quantity = quantity - ? WHERE id = ?");
                    $updateStmt->bind_param("di", $item['quantity'], $item['material_id']);
                    if (!$updateStmt->execute()) {
                        throw new Exception("Error updating stock: " . $updateStmt->error);
                    }
                }
            }
        } else {
            throw new Exception("No items provided");
        }

        $conn->commit();
        $_SESSION['success_message'] = "Quotation created successfully!";
        header("Location: quotations.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in quotation creation: " . $e->getMessage());
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Quotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Create New Quotation</h2>
                <form method="POST" id="quotationForm">
                    <div class="form-group">
                        <label>Quotation Type:</label>
                        <select name="quotation_type" id="quotationType" required>
                            <option value="">Select Type</option>
                            <option value="raw_materials">Raw Materials Quotation</option>
                            <option value="order">Order Quotation</option>
                        </select>
                    </div>

                    <div id="coilThicknessSection" style="display: none;">
                        <div class="form-group">
                            <label>Coil Thickness:</label>
                            <select name="coil_thickness" id="coilThickness">
                                <option value="0.60">0.60</option>
                                <option value="0.47">0.47</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Customer Name:</label>
                        <input type="text" name="customer_name" id="customerName" required autocomplete="off">
                        <div id="customerSuggestions" class="suggestions-dropdown"></div>
                    </div>

                    <div class="form-group">
                        <label>Customer Contact:</label>
                        <input type="text" name="customer_contact" id="customerContact" required>
                    </div>

                    <div class="items-section">
    <h3>Items</h3>
    <table id="itemsTable" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Name</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Quantity</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Unit</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Discount (%)</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Price</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Taxes</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Amount</th>
                <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <button type="button" id="addItem" 
            style="padding: 10px 20px; background-color:rgb(255, 179, 0); color: white; border:2px solid black; border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease; margin-top: 10px;">Add Product</button>
</div>

<div class="quotation-text" id="quotationTextSection" style="display: none;">
    <label>Quotation Text:</label>
    <textarea name="quotation_text" id="quotationText" rows="5" 
              style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;"></textarea>
</div>

<div class="total-section">
    <h3>Total Amount: <span id="totalAmount">0.00</span></h3>
    <input type="hidden" name="total_amount" id="totalAmountInput">
</div>

<div class="actions">
    <button type="submit" name="action" value="save" 
            style="padding: 10px 20px; background-color:rgb(255, 179, 0); color: white; border: 2px solid black; border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease;">Create Quotation</button>
</div>
                </form>
            </div>
        </div>
    </div>
 
    <script src="../assets/js/quotation.js"></script>
</body>
</html>
