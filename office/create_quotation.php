<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_texts = [
    '0.60' => "Features of the Roller Door
914mm wide, 0.60mm thick powder-coated roller door panel
Includes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks
Available Colors
Black, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)
Warranty
10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)
Warranty Card issued upon installation after full payment
2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)
Terms & Conditions
Validity: Quotation valid for 7 days only.
Payment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.
Site Access:

The customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.
The customer or an authorized representative must be present during site visits.
The company is not responsible for any delays or additional costs due to restricted access or delays by the customer.
The customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.
Final Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.
Price Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.
Tax Exclusion: Prices exclude applicable taxes.
Bank Details
Account Name: RIYON INTERNATIONAL (PVT) LTD
Bank: HATTON NATIONAL BANK - MALABE
Account Number: 1560 1000 9853
For inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   

We are committed to providing high-quality products using the latest technology and premium materials.

Thank you for considering ORICADO ROLLER DOOR.

Yours Sincerely,

ORICADO ROLLER DOORS



Prepared By: ......................................	Checked By:...........................................	Authorized By:...................................................",

    '0.47' => "Features of the Roller Door
Panel: 914mm wide, 0.47mm thick Zinc Aluminum Roller Door Panel
Components: Includes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminium Bottom Bars, and Side Locks
Available Colors
Black, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)
Terms & Conditions
Validity: Quotation valid for 7 days from the date issued.
Advance Payment: 50% of the grand total is due within 3 days of the quotation date as a non-refundable, non-transferable advance.
Site Access:

The customer agrees to allow company representatives to access the site during office hours for installation.
The customer or an authorized representative must be present during site visits.
ORICADO ROLLER DOORS is not liable for delays or extra costs if access is restricted.
The customer should ensure the site is ready for installation within 12 working days of the advance payment. Delays in preparation may lead to price adjustments, and the advance payment will not be refunded.
Final Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.
Price Adjustments: Prices are based on the current government budget and may be revised in case of any government price changes or budget updates.
Currency Fluctuation: Prices are subject to change due to fluctuations in the US Dollar exchange rate.
Exclusion of Taxes: Prices are exclusive of all applicable taxes.
Bank Details
Account Name: RIYON INTERNATIONAL (PVT) LTD
Bank: HATTON NATIONAL BANK - MALABE
Account Number: 1560 1000 9853
For inquiries, please contact  Ms. Poojani at +94 76 827 4015. /  Ms. Chathuri at +94 74 156 8098.

We trust this quotation meets your requirements. ORICADO ROLLER DOORS is committed to delivering high-quality products using advanced technology and premium materials.

Yours Sincerely,

ORICADO ROLLER DOORS



Prepared By: ......................................	Checked By:.........................................	Authorized By:..........................................."
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Handle raw materials quotation
        if ($_POST['type'] == 'raw') {
            // Create the main quotation first
            $stmt = $conn->prepare("INSERT INTO quotations (
                customer_name, customer_contact, type, created_by, total_amount
            ) VALUES (?, ?, 'raw', ?, 0)");
            
            $stmt->bind_param("ssi", 
                $_POST['customer_name'],
                $_POST['customer_contact'],
                $_SESSION['user_id']
            );
            $stmt->execute();
            $quotation_id = $conn->insert_id;

            $total_amount = 0;
            
            foreach ($_POST['materials'] as $material_id => $data) {
                if ($data['quantity'] > 0) {
                    // Get material details
                    $material = $conn->query("SELECT * FROM materials WHERE id = $material_id")->fetch_assoc();
                    if (!$material) {
                        throw new Exception("Material not found: ID " . $material_id);
                    }

                    if ($material['quantity'] < $data['quantity']) {
                        throw new Exception("Insufficient stock for material: " . $material['name']);
                    }

                    // Calculate amount
                    $amount = $data['quantity'] * $data['price'];
                    $amount = $amount * (1 - ($data['discount'] / 100));
                    $amount = $amount * (1 + ($data['taxes'] / 100));
                    $total_amount += $amount;

                    // Insert quotation item
                    $stmt = $conn->prepare("
                        INSERT INTO quotation_items (
                            quotation_id, material_id, name, quantity, unit, 
                            price, discount, taxes, amount
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->bind_param("iisdsdddd", 
                        $quotation_id,
                        $material_id,
                        $material['name'],
                        $data['quantity'],
                        $material['unit'],
                        $data['price'],
                        $data['discount'],
                        $data['taxes'],
                        $amount
                    );
                    $stmt->execute();

                    // Update stock
                    $new_quantity = $material['quantity'] - $data['quantity'];
                    $stmt = $conn->prepare("UPDATE materials SET quantity = ? WHERE id = ?");
                    $stmt->bind_param("di", $new_quantity, $material_id);
                    $stmt->execute();
                }
            }

            // Update quotation with total amount
            $stmt = $conn->prepare("UPDATE quotations SET total_amount = ? WHERE id = ?");
            $stmt->bind_param("di", $total_amount, $quotation_id);
            $stmt->execute();

            // Create done order
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    customer_name, customer_contact, status, 
                    prepared_by, quotation_id, total_price
                ) VALUES (?, ?, 'done', ?, ?, ?)
            ");
            $stmt->bind_param("ssidd", 
                $_POST['customer_name'],
                $_POST['customer_contact'],
                $_SESSION['user_id'],
                $quotation_id,
                $total_amount
            );
            $stmt->execute();
        } else {
            // Handle order quotation (existing code)
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
        }

        $conn->commit();
        $_SESSION['success_message'] = "Quotation created successfully!";
        header('Location: quotations.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: create_quotation.php');
        exit();
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
<div class="form-group" style="position: relative;">
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
