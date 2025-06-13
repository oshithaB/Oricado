<?php
require_once '../config/config.php';
checkAuth(['office_staff']);


$quotation_texts = [
    '0.60' => "FEATURES OF THE ROLLER DOOR

Panel:
 914mm wide, 0.60mm thick powder-coated roller door panel

1. Components Include:
 Springs
 Pulleys
 GI Center Bar
 Dust Seal
 Nylon Strip
 Aluminum Bottom Bars
 Side Locks

2. AVAILABLE COLORS
 Black
 Buttermilk
 Beige
 Coffee Brown
 Blue
 Green
 Maroon
 Autumn Red
 Maroon (sand finished)

3. WARRANTY
 10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)
 Warranty Card issued upon installation after full payment
 2-Year Warranty for motor
 1-Year Warranty for remotes (Conditions Apply)

4. TERMS & CONDITIONS

Validity:
 Quotation valid for 7 days only.

Payment:
 50% of the grand total is due as an advance payment within 3 days of the quotation date.
 This payment is non-refundable and non-transferable.

Site Access:
 The customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.
 The customer or an authorized representative must be present during site visits.
 The company is not responsible for any delays or additional costs due to restricted access or delays by the customer.
 The customer must prepare the site within 12 working days of the advance payment.
 Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.

Final Payment:
 Full payment is required prior to delivery and installation.
 Ownership remains with ORICADO ROLLER DOORS until full payment is received.
 In case of non-payment, the company reserves the right to claim any damages and costs.
 The advance payment will be forfeited.

Price Adjustments:
 Prices are subject to change based on government budget updates or exchange rate fluctuations.

Tax Exclusion:
 Prices exclude applicable taxes.

BANK DETAILS
Account Name: RIYON INTERNATIONAL (PVT) LTD
Bank: HATTON NATIONAL BANK - MALABE
Account Number: 1560 1000 9853

For Inquiries:
 Ms. Poojani: +94 76 827 4015
 Ms. Chathuri: +94 74 156 8098

We are committed to providing high-quality products using the latest technology and premium materials.

Thank you for considering ORICADO ROLLER DOOR.

Yours Sincerely,
ORICADO ROLLER DOORS

Prepared By: ......................................
Checked By: ...........................................
Authorized By: ...................................................",

    '0.47' => "FEATURES OF THE ROLLER DOOR

Panel:
• 914mm wide, 0.47mm thick Zinc Aluminum Roller Door Panel

Components Include:
• Springs
• Pulleys
• GI Center Bar
• Dust Seal
• Nylon Strip
• Aluminium Bottom Bars
• Side Locks

AVAILABLE COLORS
• Black
• Buttermilk
• Beige
• Coffee Brown
• Blue
• Green
• Maroon
• Autumn Red
• Maroon (sand finished)

TERMS & CONDITIONS

Validity:
• Quotation valid for 7 days from the date issued.

Advance Payment:
• 50% of the grand total is due within 3 days of the quotation date.
• This is a non-refundable, non-transferable advance.

Site Access:
• The customer agrees to allow company representatives to access the site during office hours for installation.
• The customer or an authorized representative must be present during site visits.
• ORICADO ROLLER DOORS is not liable for delays or extra costs if access is restricted.
• The customer should ensure the site is ready for installation within 12 working days of the advance payment.
• Delays in preparation may lead to price adjustments, and the advance payment will not be refunded.

Final Payment:
• Full payment is required prior to delivery and installation.
• Ownership remains with ORICADO ROLLER DOORS until full payment is received.
• In case of non-payment, the company reserves the right to claim any damages and costs.
• The advance payment will be forfeited.

Price Adjustments:
• Prices are based on the current government budget and may be revised in case of any government price changes or budget updates.

Currency Fluctuation:
• Prices are subject to change due to fluctuations in the US Dollar exchange rate.

Exclusion of Taxes:
• Prices are exclusive of all applicable taxes.

BANK DETAILS
Account Name: RIYON INTERNATIONAL (PVT) LTD
Bank: HATTON NATIONAL BANK - MALABE
Account Number: 1560 1000 9853

For Inquiries:
• Ms. Poojani: +94 76 827 4015
• Ms. Chathuri: +94 74 156 8098

We trust this quotation meets your requirements.
ORICADO ROLLER DOORS is committed to delivering high-quality products using advanced technology and premium materials.

Yours Sincerely,
ORICADO ROLLER DOORS

Prepared By:   ......................................
Checked By:    ......................................
Authorized By: ......................................

"
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

                    // Calculate amount using the saleprice field with proper decimal handling
                    $amount = round(floatval($data['quantity']) * floatval($data['saleprice']), 2);
                    $amount = round($amount * (1 - (floatval($data['discount']) / 100)), 2);
                    $amount = round($amount * (1 + (floatval($data['taxes']) / 100)), 2);
                    $total_amount += $amount;

                    // Insert quotation item with DECIMAL precision
                    $stmt = $conn->prepare("
                        INSERT INTO quotation_items (
                            quotation_id, material_id, name, quantity, unit, 
                            price, discount, taxes, amount
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $price = round(floatval($data['price']), 2);
                    $discount = round(floatval($data['discount']), 2);
                    $taxes = round(floatval($data['taxes']), 2);

                    $stmt->bind_param("iisdsdddd", 
                        $quotation_id,
                        $material_id,
                        $material['name'],
                        $data['quantity'],
                        $material['unit'],
                        $price,
                        $discount,
                        $taxes,
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
            // Handle order quotation
            $quotationType = $_POST['quotation_type'];
            $customerName = $_POST['customer_name'];
            $customerContact = $_POST['customer_contact'];
            $subtotal = round(floatval($_POST['total_amount']), 2);
            $isVatQuotation = $_POST['vat_type'] === 'vat' ? 1 : 0; // Convert to explicit 1 or 0 for boolean
            $vat = $isVatQuotation ? round($subtotal * 0.18, 2) : 0;
            $totalAmount = round($subtotal + $vat, 2);
            $userId = $_SESSION['user_id'];
            $coilThickness = $_POST['coil_thickness'] ?? '';
            $quotationText = $_POST['quotation_text'] ?? '';

            // For order quotations, preserve text formatting
            if ($_POST['type'] != 'raw') {
                // Replace standard line breaks with DB-safe line breaks
                $quotationText = str_replace(["\r\n", "\r", "\n"], PHP_EOL, $_POST['quotation_text']);
                
                $stmt = $conn->prepare("INSERT INTO quotations (
                    type, customer_name, customer_contact, subtotal, vat, 
                    total_amount, created_by, coil_thickness, quotation_text, 
                    is_vat_quotation, note
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("sssdddsssis",  // Changed 'b' to 'i' for boolean field
                    $quotationType,
                    $customerName,
                    $customerContact,
                    $subtotal,
                    $vat,
                    $totalAmount,
                    $userId,
                    $coilThickness,
                    $quotationText,
                    $isVatQuotation,
                    $_POST['note']  // Add note to binding
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
                        // Get item variables
                        $materialId = intval($item['material_id']);
                        $name = $item['name']; // Make sure name is set
                        $quantity = number_format(round(floatval($item['quantity']), 2), 2, '.', '');
                        $unit = $item['unit'];
                        $discount = number_format(round(floatval($item['discount']), 2), 2, '.', '');
                        $price = number_format(round(floatval($item['price']), 2), 2, '.', '');
                        $taxes = number_format(round(floatval($item['taxes']), 2), 2, '.', '');
                        $amount = number_format(round(floatval($item['amount']), 2), 2, '.', '');

                        // Verify name is not empty
                        if (empty($name)) {
                            throw new Exception("Item name cannot be empty");
                        }

                        $itemStmt->bind_param("iisssssss",  // Changed numeric types to s
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Navigation styles */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #2c3e50;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 0;
            color: white;
            z-index: 1000;
        }

        .sidebar .logo-container {
            padding: 0 20px;
            margin-bottom: 30px;
        }

        .sidebar .logo-container img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-menu li {
            margin: 5px 0;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffd700;
        }

        .nav-menu a.active {
            background: #34495e;
            color: #ffd700;
            border-left: 4px solid #ffd700;
        }

        .nav-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .content {
           
            padding: 20px;
            flex: 1;          /* Make content grow to fill available space */
            width: calc(100% - 250px); /* Set explicit width accounting for sidebar */
            display: flex;
            flex-direction: column;
        }

        .dashboard {
            min-height: 100vh;
            background: #f8f9fa;
            display: flex;    /* Enable flex layout */
            width: 100%;      /* Ensure dashboard takes full width */
        }
        
        .section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            flex: 1;          /* Allow section to grow */
            width: 100%;      /* Make section take full width of parent */
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .custom-btn {
            background-color: rgb(255, 179, 0);
            border: 2px solid black;
            color: black;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .custom-btn:hover {
            background-color: black;
            color: rgb(255, 179, 0);
            transform: translateY(-2px);
        }

        .total-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }

        .suggestions-dropdown {
            position: absolute;
            width: 100%;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        .suggestions-dropdown div {
            padding: 0.5rem 1rem;
            cursor: pointer;
        }

        .suggestions-dropdown div:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2 class="mb-4">Create New Quotation</h2>
                <form method="POST" id="quotationForm" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="quotation_type" id="quotationType" required>
                                    <option value="">Select Type</option>
                                    <option value="raw_materials">Raw Materials Quotation</option>
                                    <option value="order">Order Quotation</option>
                                </select>
                                <label>Quotation Type</label>
                            </div>
                        </div>

                        <div class="col-md-6" id="coilThicknessSection" style="display: none;">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="coil_thickness" id="coilThickness">
                                    <option value="0.60">0.60</option>
                                    <option value="0.47">0.47</option>
                                </select>
                                <label>Coil Thickness</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="customer_name" id="customerName" required>
                                <label>Customer Name</label>
                                <div id="customerSuggestions" class="suggestions-dropdown"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="customer_contact" id="customerContact" required>
                                <label>Customer Contact</label>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="itemsTable" class="table table-hover align-middle">
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
                            </div>
                            <button type="button" id="addItem" class="btn custom-btn mt-3">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </button>
                        </div>
                    </div>

                    <div class="quotation-text" id="quotationTextSection" style="display: none;">
                        <label>Quotation Text:</label>
                        <textarea name="quotation_text" id="quotationText" rows="15" 
                                  class="form-control" style="white-space: pre-wrap; font-family: 'Courier New', monospace;"><?php 
                                if (isset($_POST['coil_thickness'])) {
                                    echo htmlspecialchars($quotation_texts[$_POST['coil_thickness']]);
                                }
                            ?></textarea>
                    </div>

                    <!-- Add Note Field -->
                    <div class="form-group">
                        <label>Additional Note:</label>
                        <textarea name="note" rows="3" class="form-control"
                        placeholder="Add any additional notes or remarks for this quotation"></textarea>
                    </div>

                    <div class="form-group">
                        <label>VAT Type:</label>
                        <select name="vat_type" id="vatType" class="form-select" required>
                            <option value="vat">VAT Quotation (18%)</option>
                            <option value="non_vat">Non-VAT Quotation</option>
                        </select>
                    </div>

                    <div class="total-section">
                        <h3>Total Amount: <span id="totalAmount">0.00</span></h3>
                        <h3 id="vatSection">VAT Amount: <span id="vatAmount">0.00</span></h3>
                        <h3>Grand Total: <span id="grandTotal">0.00</span></h3>
                        <input type="hidden" name="total_amount" id="totalAmountInput">
                        <input type="hidden" name="vat_amount" id="vatAmountInput">
                        <input type="hidden" name="grand_total" id="grandTotalInput">
                    </div>

                    <div class="actions">
                        <button type="submit" name="action" value="save" class="btn custom-btn">
                            <i class="fas fa-save me-2"></i>Create Quotation
                        </button>
                        <button type="button" id="createVatInvoice" class="btn custom-btn">
                            <i class="fas fa-file-invoice me-2"></i>Create VAT Invoice
                        </button>
                    </div>

                    <!-- VAT Modal -->
                    <div id="vatModal" class="modal" style="display: none;">
                        <div class="modal-content">
                            <h2>VAT Invoice Details</h2>
                            <div class="form-group">
                                <label>VAT Percentage:</label>
                                <input type="number" id="vatPercentage" value="18" min="0" max="100">
                            </div>
                            <div class="form-group">
                                <label>Customer Tax ID:</label>
                                <input type="text" id="customerTaxId">
                            </div>
                            <div class="form-group">
                                <label>Customer Address:</label>
                                <textarea id="customerAddress"></textarea>
                            </div>
                            <div class="actions">
                                <button onclick="generateVatInvoice()">Create VAT Invoice</button>
                                <button onclick="document.getElementById('vatModal').style.display='none'">Cancel</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
 
    <script src="../assets/js/quotation.js"></script>
</body>
</html>
