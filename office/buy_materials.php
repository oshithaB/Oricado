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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
/* Suggestions Dropdown Styling */
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
}

.suggestions-dropdown div {
    padding: 10px;
    cursor: pointer;
    font-size: 14px;
    color: #333;
}

.suggestions-dropdown div:hover {
    background-color: #f0f0f0;
}

/* Custom Modal Styling (for coil calculator) */
.custom-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
}
.custom-modal-content {
    background: white;
    width: 400px;
    margin: 50px auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.result-section {
    margin: 15px 0;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

/* Table Styling */
#materialsTable {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

#materialsTable th, #materialsTable td {
    text-align: left;
    padding: 10px;
    border: 1px solid #ddd;
}

#materialsTable th {
    background-color: #f4f4f4;
    font-weight: bold;
    text-align: center;
}

#materialsTable td {
    vertical-align: middle;
}

#materialsTable input[type="text"],
#materialsTable input[type="number"],
#materialsTable select {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

#materialsTable button {
    padding: 8px 12px;
    font-size: 14px;
    background-color: #f44336;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

#materialsTable button:hover {
    background-color: #d32f2f;
}

.btn-grn-calculator {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    margin-top: 10px;
}

.btn-grn-calculator:hover {
    background-color: #218838;
}
</style>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>GRN</h2>
                <!-- GRN Calculator Button -->
                <div class="mb-3">
                    <button type="button" class="btn-grn-calculator" onclick="openGrnCalculatorModal()">
                        <i class="fas fa-calculator"></i> GRN Calculator
                    </button>
                </div>
                
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
                        <!-- Original Coil Calculator Modal -->
                        <div id="coilCalculatorModal" class="custom-modal" style="display: none;">
                            <div class="custom-modal-content">
                                <h3>Calculate Coil Length</h3>
                                <div class="form-group">
                                    <label>Coil Roll Weight (kg):</label>
                                    <input type="number" id="coilRollWeight" step="0.01">
                                </div>
                                <div class="form-group">
                                    <label>Wooden Roll Weight (kg):</label>
                                    <input type="number" id="woodenRollWeight" step="0.01">
                                </div>
                                <div class="form-group">
                                    <label>Weight per LFt (kg):</label>
                                    <input type="number" id="weightPerLft" step="0.001">
                                </div>
                                <div class="result-section">
                                    <p>Net Weight: <span id="netWeight">0</span> kg</p>
                                    <p>Total LFt: <span id="totalLft">0</span></p>
                                </div>
                                <div class="modal-buttons">
                                    <button type="button" onclick="applyCoilCalculation()">Apply</button>
                                    <button type="button" onclick="closeCoilModal()">Cancel</button>
                                </div>
                            </div>
                        </div>

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

    <!-- GRN Calculator Modal -->
    <div id="grnCalculatorModal" class="custom-modal" style="display: none;">
        <div class="custom-modal-content" style="width: 500px; max-width: 90%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                <h3 style="margin: 0;">GRN Calculator</h3>
                <button type="button" onclick="closeGrnCalculatorModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            
            <!-- Input fields section -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Coil Weight</label>
                <div style="display: flex; gap: 5px;">
                    <input type="number" id="coilWeight" step="0.001" placeholder="Enter coil weight" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <select id="coilWeightUnit" style="width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="kg">KG</option>
                        <option value="ton">Ton</option>
                        <option value="gram">Gram</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Wooden Roll Weight</label>
                <div style="display: flex; gap: 5px;">
                    <input type="number" id="woodenWeight" step="0.001" placeholder="Enter wooden roll weight" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <select id="woodenWeightUnit" style="width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="kg">KG</option>
                        <option value="ton">Ton</option>
                        <option value="gram">Gram</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Weight per Linear Feet</label>
                <div style="display: flex; gap: 5px;">
                    <input type="number" id="linearFeetWeight" step="0.001" placeholder="Enter weight per linear feet" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <select id="linearFeetUnit" style="width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="kg">KG</option>
                        <option value="ton">Ton</option>
                        <option value="gram">Gram</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: space-between; margin: 20px 0;">
                <button type="button" onclick="resetCalculator()" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Reset</button>
                <button type="button" onclick="doCalculate()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Calculate</button>
            </div>

            <!-- Results -->
            <div style="background-color: #d1ecf1; padding: 15px; border-radius: 4px; border: 1px solid #bee5eb;">
                <p style="margin-bottom: 10px;"><strong>Net Weight:</strong> <span id="grnNetWeight">0</span> KG</p>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <p style="margin: 0;"><strong>Total Linear Feet:</strong> <span id="grnTotalLft">0</span></p>
                    <button type="button" onclick="copyValue('grnTotalLft')" title="Copy value" style="padding: 5px 10px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/buy_materials.js"></script>
    <script>
    // GRN Calculator Modal Functions
    function openGrnCalculatorModal() {
        document.getElementById('grnCalculatorModal').style.display = 'block';
        console.log('GRN Calculator modal opened');
    }

    function closeGrnCalculatorModal() {
        document.getElementById('grnCalculatorModal').style.display = 'none';
        resetCalculator();
        console.log('GRN Calculator modal closed');
    }

    // Global variables
    let currentQuantityInput = null;

    // Convert weight to KG
    function convertToKG(value, unit) {
        value = parseFloat(value) || 0;
        switch(unit) {
            case 'ton':
                return value * 1000;
            case 'gram':
                return value / 1000;
            default:
                return value;
        }
    }

    // Main GRN calculation function
    function doCalculate() {
        console.log('Calculate function called');
        
        // Get input values
        const coilWeight = parseFloat(document.getElementById('coilWeight').value) || 0;
        const woodenWeight = parseFloat(document.getElementById('woodenWeight').value) || 0;
        const linearFeetWeight = parseFloat(document.getElementById('linearFeetWeight').value) || 0;

        console.log('Input values:', { coilWeight, woodenWeight, linearFeetWeight });

        // Validate inputs
        if (coilWeight === 0 || linearFeetWeight === 0) {
            alert('Please enter valid values for Coil Weight and Weight per Linear Feet');
            return;
        }

        // Get unit selections
        const coilUnit = document.getElementById('coilWeightUnit').value;
        const woodenUnit = document.getElementById('woodenWeightUnit').value;
        const linearUnit = document.getElementById('linearFeetUnit').value;

        // Convert all weights to KG
        const coilKg = convertToKG(coilWeight, coilUnit);
        const woodenKg = convertToKG(woodenWeight, woodenUnit);
        const linearKg = convertToKG(linearFeetWeight, linearUnit);

        console.log('Converted to KG:', { coilKg, woodenKg, linearKg });

        // Calculate net weight
        const netWeight = coilKg - woodenKg;

        // Calculate total linear feet
        const totalLft = linearKg > 0 ? netWeight / linearKg : 0;

        console.log('Final calculations:', { netWeight, totalLft });

        // Update display
        const netWeightElement = document.getElementById('grnNetWeight');
        const totalLftElement = document.getElementById('grnTotalLft');
        
        if (netWeightElement && totalLftElement) {
            netWeightElement.textContent = netWeight.toFixed(3);
            totalLftElement.textContent = totalLft.toFixed(2);
            console.log('Display updated successfully');
        } else {
            console.error('Display elements not found');
        }
    }

    // Reset calculator function
    function resetCalculator() {
        console.log('Reset function called');
        
        // Clear all input fields
        document.getElementById('coilWeight').value = '';
        document.getElementById('woodenWeight').value = '';
        document.getElementById('linearFeetWeight').value = '';
        
        // Reset unit selections to default (KG)
        document.getElementById('coilWeightUnit').value = 'kg';
        document.getElementById('woodenWeightUnit').value = 'kg';
        document.getElementById('linearFeetUnit').value = 'kg';
        
        // Reset display values
        document.getElementById('grnNetWeight').textContent = '0';
        document.getElementById('grnTotalLft').textContent = '0';
        
        console.log('Calculator reset successfully');
    }

    // Copy value function
    function copyValue(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const value = element.textContent;
            navigator.clipboard.writeText(value).then(function() {
                console.log('Value copied to clipboard:', value);
                // Show success feedback
                const button = event.target;
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 1000);
            }).catch(function(err) {
                console.error('Failed to copy value:', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = value;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    console.log('Value copied using fallback method');
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                }
                document.body.removeChild(textArea);
            });
        }
    }

    // Original Coil Calculator Functions (for the table-specific calculator)
    function showCoilCalculator(quantityInput) {
        currentQuantityInput = quantityInput;
        document.getElementById('coilCalculatorModal').style.display = 'block';
    }

    function calculateLft() {
        const rollWeight = parseFloat(document.getElementById('coilRollWeight').value) || 0;
        const woodenWeight = parseFloat(document.getElementById('woodenRollWeight').value) || 0;
        const weightPerLft = parseFloat(document.getElementById('weightPerLft').value) || 0;

        const netWeight = rollWeight - woodenWeight;
        const totalLft = weightPerLft > 0 ? netWeight / weightPerLft : 0;

        document.getElementById('netWeight').textContent = netWeight.toFixed(2);
        document.getElementById('totalLft').textContent = totalLft.toFixed(2);
        return totalLft;
    }

    function applyCoilCalculation() {
        const totalLft = calculateLft();
        if (currentQuantityInput && totalLft > 0) {
            currentQuantityInput.value = totalLft.toFixed(2);
            closeCoilModal();
        }
    }

    function closeCoilModal() {
        document.getElementById('coilCalculatorModal').style.display = 'none';
        currentQuantityInput = null;
    }

    // Modify existing addProduct function to add calculator button for coil products
    if (typeof window.addRow === 'function') {
        const originalAddRow = window.addRow;
        window.addRow = function(product) {
            const row = originalAddRow(product);
            if (product.type === 'coil') {
                const quantityCell = row.querySelector('td:nth-child(5)');
                const quantityInput = quantityCell.querySelector('input');
                const calculateBtn = document.createElement('button');
                calculateBtn.type = 'button';
                calculateBtn.textContent = 'Calculate LFt';
                calculateBtn.style.marginLeft = '10px';
                calculateBtn.onclick = () => showCoilCalculator(quantityInput);
                quantityCell.appendChild(calculateBtn);
            }
            return row;
        };
    }

    // Add validation for negative results
    function validateCalculation() {
        const coilWeight = parseFloat(document.getElementById('coilWeight').value) || 0;
        const woodenWeight = parseFloat(document.getElementById('woodenWeight').value) || 0;
        const coilUnit = document.getElementById('coilWeightUnit').value;
        const woodenUnit = document.getElementById('woodenWeightUnit').value;
        
        const coilKg = convertToKG(coilWeight, coilUnit);
        const woodenKg = convertToKG(woodenWeight, woodenUnit);
        
        if (woodenKg > coilKg) {
            alert('Warning: Wooden roll weight cannot be greater than coil weight!');
            return false;
        }
        return true;
    }

    // Auto-calculate when values change in original coil calculator
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up calculators');
        
        // Add event listeners for real-time calculation in original coil calculator
        const coilInputs = ['coilRollWeight', 'woodenRollWeight', 'weightPerLft'];
        coilInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', calculateLft);
            }
        });

        // Add keyboard shortcut for calculate (Enter key) in GRN calculator
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const modal = document.getElementById('grnCalculatorModal');
                if (modal && modal.style.display === 'block') {
                    e.preventDefault();
                    if (validateCalculation()) {
                        doCalculate();
                    }
                }
            }
            
            // Close modal with Escape key
            if (e.key === 'Escape') {
                const modal = document.getElementById('grnCalculatorModal');
                if (modal && modal.style.display === 'block') {
                    closeGrnCalculatorModal();
                }
            }
        });

        // Close modal when clicking outside
        document.getElementById('grnCalculatorModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeGrnCalculatorModal();
            }
        });
    });
    </script>
</body>
</html>