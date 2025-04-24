<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Create quotation record
        $stmt = $conn->prepare("
            INSERT INTO quotations (
                customer_name, customer_contact, type, created_by
            ) VALUES (?, ?, 'sell', ?)
        ");
        
        $stmt->bind_param("ssi", 
            $_POST['supplier_name'],
            $_POST['supplier_contact'],
            $_SESSION['user_id']
        );
        $stmt->execute();
        $quotation_id = $conn->insert_id;

        $total_amount = 0;

        // Process items
        if (!empty($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                $quantity = floatval($item['quantity']);
                $price = floatval($item['price']);
                $amount = $quantity * $price;
                $total_amount += $amount;

                // Add quotation item
                $stmt = $conn->prepare("
                    INSERT INTO quotation_items (
                        quotation_id, material_id, name, quantity, unit, price, amount
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $material_id = isset($item['material_id']) ? $item['material_id'] : null;
                
                $stmt->bind_param("iisdsdd",
                    $quotation_id,
                    $material_id,
                    $item['name'],
                    $quantity,
                    $item['unit'],
                    $price,
                    $amount
                );
                $stmt->execute();

                // Update material
                if (!empty($item['material_id'])) {
                    $material = $conn->query("SELECT quantity, price FROM materials WHERE id = {$item['material_id']}")->fetch_assoc();
                    $total_qty = $material['quantity'] + $quantity;
                    $avg_price = (($material['quantity'] * $material['price']) + ($quantity * $price)) / $total_qty;
                    
                    $stmt = $conn->prepare("UPDATE materials SET quantity = ?, price = ? WHERE id = ?");
                    $stmt->bind_param("ddi", $total_qty, $avg_price, $item['material_id']);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO materials (name, type, unit, quantity, price)
                        VALUES (?, 'other', ?, ?, ?)
                    ");
                    $stmt->bind_param("ssdd", $item['name'], $item['unit'], $quantity, $price);
                    $stmt->execute();
                }
            }
        }

        // Update quotation total
        $stmt = $conn->prepare("UPDATE quotations SET total_amount = ? WHERE id = ?");
        $stmt->bind_param("di", $total_amount, $quotation_id);
        $stmt->execute();

        // Create supplier quotation record
        $stmt = $conn->prepare("
            INSERT INTO supplier_quotations (
                quotation_id, supplier_name, supplier_contact, total_amount, created_by
            ) VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issdi",
            $quotation_id,
            $_POST['supplier_name'],
            $_POST['supplier_contact'],
            $total_amount,
            $_SESSION['user_id']
        );
        $stmt->execute();

        $conn->commit();
        $_SESSION['success_message'] = "Buy quotation created successfully!";
        header('Location: supplier_quotations.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buy Materials</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Create Buy Quotation</h2>
                <form method="POST" id="buyForm">
                    <div class="supplier-section">
                        <div class="form-group">
                            <label>Supplier Name:</label>
                            <input type="text" name="supplier_name" id="supplierName" required>
                            <div id="supplierSuggestions" class="suggestions-dropdown"></div>
                        </div>
                        <div class="form-group">
                            <label>Supplier Contact:</label>
                            <input type="text" name="supplier_contact" id="supplierContact" required>
                        </div>
                        <div class="form-group">
                            <label>Created By:</label>
                            <input type="text" value="<?php echo $_SESSION['name']; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Contact:</label>
                            <input type="text" value="<?php echo $_SESSION['contact']; ?>" readonly>
                        </div>
                    </div>

                    <div class="items-section">
                        <h3>Materials</h3>
                        <table id="materialsTable">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div class="button-group">
                            <button type="button" id="addExisting">Add Existing Product</button>
                            <button type="button" id="addNew">Add New Product</button>
                        </div>
                    </div>

                    <button type="submit">Create Buy Quotation</button>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/buy_materials.js"></script>
</body>
</html>
