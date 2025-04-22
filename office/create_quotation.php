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
        // Insert quotation
        $stmt = $conn->prepare("INSERT INTO quotations (type, customer_name, customer_contact, 
            total_amount, created_by, coil_thickness, quotation_text) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssidds", 
            $_POST['quotation_type'],
            $_POST['customer_name'],
            $_POST['customer_contact'],
            $_POST['total_amount'],
            $_SESSION['user_id'],
            $_POST['coil_thickness'],
            $_POST['quotation_text']
        );
        $stmt->execute();
        $quotation_id = $conn->insert_id;

        // Insert items
        foreach ($_POST['items'] as $item) {
            $stmt = $conn->prepare("INSERT INTO quotation_items (quotation_id, material_id, 
                name, quantity, unit, discount, price, taxes, amount) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("iisdsdddd",
                $quotation_id,
                $item['material_id'],
                $item['name'],
                $item['quantity'],
                $item['unit'],
                $item['discount'],
                $item['price'],
                $item['taxes'],
                $item['amount']
            );
            $stmt->execute();

            // Deduct from stock if raw materials quotation
            if ($_POST['quotation_type'] == 'raw_materials') {
                $stmt = $conn->prepare("UPDATE materials SET quantity = quantity - ? WHERE id = ?");
                $stmt->bind_param("di", $item['quantity'], $item['material_id']);
                $stmt->execute();
            }
        }

        $conn->commit();

        if ($_POST['action'] == 'download') {
            header("Location: download_quotation.php?id=" . $quotation_id);
            exit();
        } else {
            $_SESSION['success_message'] = "Quotation created successfully!";
            header("Location: quotations.php");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
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
                        <table id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Discount (%)</th>
                                    <th>Price</th>
                                    <th>Taxes</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" id="addItem">Add Product</button>
                    </div>

                    <div class="quotation-text" id="quotationTextSection" style="display: none;">
                        <label>Quotation Text:</label>
                        <textarea name="quotation_text" id="quotationText" rows="5"></textarea>
                    </div>

                    <div class="total-section">
                        <h3>Total Amount: <span id="totalAmount">0.00</span></h3>
                        <input type="hidden" name="total_amount" id="totalAmountInput">
                    </div>

                    <div class="actions">
                        <button type="submit" name="action" value="save">Create Quotation</button>
                       
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/quotation.js"></script>
</body>
</html>
