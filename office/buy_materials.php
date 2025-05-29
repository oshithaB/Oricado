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

                // Update material or insert new one
                if (!empty($item['material_id'])) {
                    $material = $conn->query("SELECT quantity, price FROM materials WHERE id = {$item['material_id']}")->fetch_assoc();
                    $total_qty = $material['quantity'] + $quantity;
                    $avg_price = (($material['quantity'] * $material['price']) + ($quantity * $price)) / $total_qty;
                    
                    $stmt = $conn->prepare("UPDATE materials SET quantity = ?, price = ?, saleprice = ? WHERE id = ?");
                    $stmt->bind_param("dddi", $total_qty, $avg_price, $item['saleprice'], $item['material_id']);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO materials (name, type, unit, quantity, price, saleprice)
                        VALUES (?, 'other', ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("ssddd", $item['name'], $item['unit'], $quantity, $price, $item['saleprice']);
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
<style>
/* Suggestions Dropdown Styling */
.suggestions-dropdown {
    position: absolute; /* Position relative to the input field */
    top: 100%; /* Position below the input field */
    left: 0; /* Align with the left edge of the input field */
    width: 100%; /* Match the width of the input field */
    background-color: #fff; /* White background for the dropdown */
    border: 1px solid #ddd; /* Light border for the dropdown */
    border-radius: 4px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for better visibility */
    z-index: 1000; /* Ensure it appears above other elements */
    max-height: 200px; /* Limit the height of the dropdown */
    overflow-y: auto; /* Add scroll if the content exceeds the height */
}

.suggestions-dropdown div {
    padding: 10px; /* Padding for each suggestion */
    cursor: pointer; /* Pointer cursor for interactivity */
    font-size: 14px; /* Font size for readability */
    color: #333; /* Text color */
}

.suggestions-dropdown div:hover {
    background-color: #f0f0f0; /* Highlight on hover */
}
</style>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Create Buy Quotation</h2>
                <form method="POST" id="buyForm">
                    <div class="supplier-section">
                    <div class="form-group" style="position: relative;">
    <label>Supplier Name:</label>
    <input type="text" name="supplier_name" id="supplierName" required autocomplete="off">
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
                                    <th>Buy Price</th>
                                    <th>Sale Price</th>
                                    <th>Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <style>
/* Table Styling */
#materialsTable {
    width: 100%; /* Ensure the table takes the full width */
    border-collapse: collapse; /* Remove gaps between table cells */
    margin-top: 20px;
}

#materialsTable th, #materialsTable td {
    text-align: left; /* Align text to the left */
    padding: 10px; /* Add padding for better spacing */
    border: 1px solid #ddd; /* Add a light border for clarity */
}

#materialsTable th {
    background-color: #f4f4f4; /* Light gray background for headers */
    font-weight: bold; /* Bold text for headers */
    text-align: center; /* Center-align header text */
}

#materialsTable td {
    vertical-align: middle; /* Align content vertically in the middle */
}

/* Input Field Styling */
#materialsTable input[type="text"],
#materialsTable input[type="number"],
#materialsTable select {
    width: 100%; /* Make inputs fit the column width */
    padding: 8px; /* Add padding for better usability */
    box-sizing: border-box; /* Include padding and border in width */
    border: 1px solid #ccc; /* Light border for inputs */
    border-radius: 4px; /* Rounded corners for inputs */
    font-size: 14px; /* Standard font size for readability */
}

/* Button Styling */
#materialsTable button {
    padding: 8px 12px; /* Add padding for buttons */
    font-size: 14px; /* Standard font size */
    background-color: #f44336; /* Red background for "Remove" button */
    color: white; /* White text for contrast */
    border: none; /* Remove default border */
    border-radius: 4px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor for interactivity */
}

#materialsTable button:hover {
    background-color: #d32f2f; /* Darker red on hover */
}
</style>
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
