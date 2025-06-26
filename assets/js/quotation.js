document.addEventListener('DOMContentLoaded', function() {
    const quotationType = document.getElementById('quotationType');
    const coilThicknessSection = document.getElementById('coilThicknessSection');
    const quotationTextSection = document.getElementById('quotationTextSection');
    const coilThickness = document.getElementById('coilThickness');
    const quotationText = document.getElementById('quotationText');
    const addItemBtn = document.getElementById('addItem');
    const itemsTable = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];

    const quotationTexts = {
        '0.60': `📄 ORICADO ROLLER DOORS — PRODUCT & QUOTATION DETAILS

🔧 Features of the Roller Door
Panel Size: 914mm wide, 0.60mm thick powder-coated roller door panel

Included Components: Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, Side Locks

🎨 Available Colors
Black

Buttermilk

Beige

Coffee Brown

Blue

Green

Maroon

Autumn Red

Maroon (Sand Finished)

🛡️ Warranty
10-Year Warranty on non-corrosive states of the door panel (conditions apply)

Warranty card issued upon installation after full payment

2-Year Warranty for motor & 1-Year Warranty for remotes (conditions apply)

📜 Terms & Conditions
✅ Validity
Quotation is valid for 7 days only

💰 Payment Terms
50% advance payment (non-refundable & non-transferable) within 3 days of quotation date

Full payment required prior to delivery and installation

🏗️ Site Access
Customer agrees to allow ORICADO representatives to visit the site during office hours at a mutually agreed time

Customer or authorized person must be present during visits

Company is not responsible for delays or extra costs due to site inaccessibility or delays caused by the customer

Site must be ready within 12 working days of advance payment. Delay may lead to price adjustments. Advance is non-refundable

🔒 Ownership & Final Payment
Ownership remains with ORICADO ROLLER DOORS until full payment is received

In case of non-payment, the company reserves the right to claim damages and costs, and the advance will be forfeited

💸 Price Adjustments
Subject to change based on government budget updates or exchange rate fluctuations

🧾 Tax Exclusion
All prices are exclusive of applicable taxes

🏦 Bank Details
Account Name: RIYON INTERNATIONAL (PVT) LTD

Bank: Hatton National Bank – Malabe

Account Number: 1560 1000 9853

📞 For Inquiries
Ms. Poojani: +94 76 827 4015

Ms. Chathuri: +94 74 156 8098

We are committed to providing high-quality roller doors using the latest technology and premium materials.

Thank you for choosing ORICADO ROLLER DOORS.

Yours sincerely,
ORICADO ROLLER DOORS

Prepared By: ..........................................
Checked By: .............................................
Authorized By: ..........................................

`,
        '0.47': `📄 ORICADO ROLLER DOORS — PRODUCT & QUOTATION DETAILS

🔧 Features of the Roller Door
Panel: 914mm wide, 0.47mm thick Zinc Aluminum Roller Door Panel

Included Components: Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminium Bottom Bars, Side Locks

🎨 Available Colors
Black

Buttermilk

Beige

Coffee Brown

Blue

Green

Maroon

Autumn Red

Maroon (Sand Finished)

📜 Terms & Conditions
✅ Validity
Quotation is valid for 7 days from the date issued.

💰 Advance Payment
50% of the grand total must be paid within 3 days of the quotation date.

This amount is non-refundable and non-transferable.

🏗️ Site Access
Customer agrees to allow ORICADO representatives to access the site during office hours for installation.

The customer or an authorized representative must be present during site visits.

ORICADO ROLLER DOORS is not liable for delays or extra costs if access is restricted.

Site must be ready within 12 working days of advance payment.

Delays in site preparation may result in price adjustments, and the advance payment will not be refunded.

🔒 Final Payment & Ownership
Full payment is required prior to delivery and installation.

Ownership remains with ORICADO ROLLER DOORS until full payment is received.

In the event of non-payment, the company reserves the right to claim damages and costs, and the advance will be forfeited.

💸 Price Adjustments
Prices are based on the current government budget. Any changes may affect final pricing.

Prices may also change due to currency fluctuations, particularly the US Dollar exchange rate.

🧾 Exclusion of Taxes
Prices listed are exclusive of all applicable taxes.

🏦 Bank Details
Account Name: RIYON INTERNATIONAL (PVT) LTD

Bank: Hatton National Bank – Malabe

Account Number: 1560 1000 9853

📞 For Inquiries
Ms. Poojani: +94 76 827 4015

Ms. Chathuri: +94 74 156 8098

We trust this quotation meets your expectations.
ORICADO ROLLER DOORS is committed to delivering high-quality products using advanced technology and premium materials.

Yours sincerely,
ORICADO ROLLER DOORS

Prepared By: ..........................................
Checked By: .............................................
Authorized By: ..........................................`
    };

    quotationType.addEventListener('change', function() {
        if (this.value === 'order') {
            coilThicknessSection.style.display = 'block';
            quotationTextSection.style.display = 'block';
            quotationText.value = quotationTexts[coilThickness.value];
        } else {
            coilThicknessSection.style.display = 'none';
            quotationTextSection.style.display = 'none';
        }
    });

    coilThickness.addEventListener('change', function() {
        quotationText.value = quotationTexts[this.value];
    });

    addItemBtn.addEventListener('click', addNewRow);

    setupCustomerSearch();

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.material-search') && !e.target.closest('#customerSuggestions')) {
            document.querySelectorAll('.material-suggestions, #customerSuggestions').forEach(el => {
                el.style.display = 'none';
            });
        }
    });

    document.getElementById('vatType').addEventListener('change', updateTotal);
});

function selectCustomer(name, contact, address, taxNumber) {
    document.getElementById('customerName').value = name;
    document.getElementById('customerContact').value = contact;
    document.getElementById('customerAddress').value = address || '';
    document.getElementById('customerTaxId').value = taxNumber || '';
    document.getElementById('customerSuggestions').style.display = 'none';
}

function searchMaterials(input) {
    const suggestionDiv = input.closest('.material-search').querySelector('.material-suggestions');
    const searchValue = input.value;
    
    if (searchValue.length < 2) {
        suggestionDiv.style.display = 'none';
        return;
    }

    fetch('search_materials.php?term=' + encodeURIComponent(searchValue))
        .then(response => response.json())
        .then(materials => {
            if (materials.length > 0) {
                suggestionDiv.innerHTML = materials.map(material => {
                    const materialJson = JSON.stringify(material).replace(/"/g, '&quot;');
                    return `
                        <div class="suggestion-item" 
                             onclick="selectMaterial(this.closest('tr'), ${materialJson})">
                            ${material.display_name || material.name}
                        </div>
                    `;
                }).join('');
                suggestionDiv.style.display = 'block';
            } else {
                suggestionDiv.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching materials:', error);
            suggestionDiv.style.display = 'none';
        });
}

function selectMaterial(row, material) {
    const nameInput = row.querySelector('.item-name');
    const nameInputHidden = row.querySelector('.item-name-input');
    const materialIdInput = row.querySelector('.material-id');
    const unitInput = row.querySelector('.unit');
    const priceInput = row.querySelector('.price');
    const quantityInput = row.querySelector('.quantity');
    const suggestionDiv = row.querySelector('.material-suggestions');

    nameInput.value = material.display_name || material.name;
    nameInputHidden.value = material.display_name || material.name;
    materialIdInput.value = material.id;
    unitInput.value = material.unit;
    priceInput.value = material.saleprice || 0;
    priceInput.removeAttribute('readonly');
    suggestionDiv.style.display = 'none';

    // Check if material is coil type
    if (material.type === 'coil') {
        // Create inches and pieces fields container if not exists
        let coilFields = row.querySelector('.coil-fields');
        if (!coilFields) {
            coilFields = document.createElement('div');
            coilFields.className = 'coil-fields';
            coilFields.style.marginTop = '5px';
            coilFields.innerHTML = `
                <div style="display: flex; gap: 5px; margin-top: 5px;">
                    <input type="number" class="coil-inches form-control form-control-sm" placeholder="Inches" step="0.01" style="width: 45%;">
                    <input type="number" class="coil-pieces form-control form-control-sm" placeholder="Pieces" step="1" style="width: 45%;">
                </div>
            `;
            row.querySelector('.material-search').appendChild(coilFields);

            // Add event listeners for auto calculation
            const inchesInput = coilFields.querySelector('.coil-inches');
            const piecesInput = coilFields.querySelector('.coil-pieces');

            function calculateQuantity() {
                const inches = parseFloat(inchesInput.value) || 0;
                const pieces = parseInt(piecesInput.value) || 0;
                if (inches && pieces) {
                    const quantity = (inches * pieces) / 12;
                    quantityInput.value = quantity.toFixed(2);
                    // Save inches and pieces to hidden inputs
                    row.querySelector('.coil-inches-input').value = inches;
                    row.querySelector('.pieces-input').value = pieces;
                    calculateRowTotal(quantityInput);
                }
            }

            inchesInput.addEventListener('input', calculateQuantity);
            piecesInput.addEventListener('input', calculateQuantity);
        }
        quantityInput.readOnly = true;
    } else {
        // Remove coil fields if they exist
        const coilFields = row.querySelector('.coil-fields');
        if (coilFields) {
            coilFields.remove();
        }
        quantityInput.readOnly = false;
    }

    calculateRowTotal(quantityInput);
}

function setupCustomerSearch() {
    const customerName = document.getElementById('customerName');
    const customerContact = document.getElementById('customerContact');
    const customerSuggestions = document.getElementById('customerSuggestions');

    customerName.addEventListener('input', function() {
        const searchValue = this.value;
        if (searchValue.length < 2) {
            customerSuggestions.style.display = 'none';
            return;
        }

        fetch('search_contacts.php?term=' + encodeURIComponent(searchValue))
            .then(response => response.json())
            .then(contacts => {
                if (contacts.length > 0) {
                    customerSuggestions.innerHTML = contacts.map(contact => `
                        <div class="suggestion-item" onclick="selectCustomer('${contact.name}', '${contact.mobile}', '${contact.address || ''}', '${contact.tax_number || ''}')">
                            ${contact.name} - ${contact.type} (${contact.mobile})
                        </div>
                    `).join('');
                    customerSuggestions.style.display = 'block';
                } else {
                    customerSuggestions.style.display = 'none';
                }
            });
    });
}

function addNewRow() {
    const rowIndex = itemsTable.rows.length;
    const row = itemsTable.insertRow();
    row.innerHTML = `
        <td style="text-align: center; vertical-align: middle;">
            <div class="material-search" style="display: flex; justify-content: center; align-items: center;">
                <input type="text" class="item-name" onkeyup="searchMaterials(this)" autocomplete="off" required
                       style="width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                <div class="material-suggestions"></div>
                <input type="hidden" name="items[${rowIndex}][material_id]" class="material-id">
                <input type="hidden" name="items[${rowIndex}][name]" class="item-name-input">
                <input type="hidden" name="items[${rowIndex}][coil_inches]" class="coil-inches-input">
                <input type="hidden" name="items[${rowIndex}][pieces]" class="pieces-input">
            </div>
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <input type="number" name="items[${rowIndex}][quantity]" class="quantity" step="0.01" oninput="calculateRowTotal(this)" required
                   style="width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <input type="text" name="items[${rowIndex}][unit]" class="unit"
                   style="width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <input type="number" name="items[${rowIndex}][discount]" class="discount" step="0.01" value="0" oninput="calculateRowTotal(this)"
                   style="width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <input type="number" name="items[${rowIndex}][price]" class="price" step="0.01" oninput="calculateRowTotal(this)"
                   style="width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <input type="number" name="items[${rowIndex}][taxes]" class="taxes" step="0.01" value="0" oninput="calculateRowTotal(this)"
                   style="width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <input type="number" name="items[${rowIndex}][amount]" class="amount" readonly
                   style="width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <button type="button" onclick="removeRow(this)"
                    style="padding: 5px 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease;">Remove</button>
        </td>
    `;
}
function calculateRowTotal(element) {
    const row = element.closest('tr');
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const discount = parseFloat(row.querySelector('.discount').value) || 0;
    const taxes = parseFloat(row.querySelector('.taxes').value) || 0;

    const subtotal = quantity * price;
    const discountAmount = subtotal * (discount / 100);
    const taxAmount = (subtotal - discountAmount) * (taxes / 100);
    const total = subtotal - discountAmount + taxAmount;

    row.querySelector('.amount').value = total.toFixed(2);
    updateTotal();
}

function calculateRowAmount(row) {
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const discount = parseFloat(row.querySelector('.discount').value) || 0;
    const taxes = parseFloat(row.querySelector('.taxes').value) || 0;

    const subtotal = quantity * price;
    const discountAmount = subtotal * (discount / 100);
    const taxAmount = subtotal * (taxes / 100);
    const total = subtotal - discountAmount + taxAmount;

    row.querySelector('.amount').value = total.toFixed(2);
    updateTotal();
}

function updateTotal() {
    const amounts = Array.from(document.getElementsByClassName('amount'))
        .map(input => parseFloat(input.value) || 0);
    const total = amounts.reduce((sum, amount) => sum + amount, 0);
    const vatType = document.getElementById('vatType').value;
    const vatSection = document.getElementById('vatSection');
    
    let vatAmount = 0;
    let grandTotal = total;
    
    if (vatType === 'vat') {
        vatSection.style.display = 'block';
        const vatPercentage = parseFloat(document.getElementById('vatPercentage')?.value || 18);
        vatAmount = total * (vatPercentage / 100);
        grandTotal = total + vatAmount;
    } else {
        vatSection.style.display = 'none';
        vatAmount = 0;
        grandTotal = total;
    }
    
    document.getElementById('totalAmount').textContent = total.toFixed(2);
    document.getElementById('vatAmount').textContent = vatAmount.toFixed(2);
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    
    // Update hidden inputs
    document.getElementById('totalAmountInput').value = total.toFixed(2);
    document.getElementById('vatAmountInput').value = vatAmount.toFixed(2);
    document.getElementById('grandTotalInput').value = grandTotal.toFixed(2);
}

function removeRow(button) {
    const row = button.closest('tr');
    row.remove();
    updateTotal();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

document.getElementById('createVatInvoice').addEventListener('click', function() {
    document.getElementById('vatModal').style.display = 'block';
});

function calculateVat(percentage) {
    const totalAmount = parseFloat(document.getElementById('totalAmount').textContent);
    const vatAmount = totalAmount * (percentage / 100);
    const grandTotal = totalAmount + vatAmount;
    
    document.getElementById('vatAmount').textContent = vatAmount.toFixed(2);
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    document.getElementById('vatAmountInput').value = vatAmount.toFixed(2);
    document.getElementById('grandTotalInput').value = grandTotal.toFixed(2);
    
    // Add VAT amount as a hidden input for form submission
    let vatInput = document.querySelector('input[name="vat_amount"]');
    if (!vatInput) {
        vatInput = document.createElement('input');
        vatInput.type = 'hidden';
        vatInput.name = 'vat_amount';
        document.getElementById('quotationForm').appendChild(vatInput);
    }
    vatInput.value = vatAmount.toFixed(2);
    
    return { totalAmount, vatAmount, grandTotal };
}

function generateVatInvoice() {
    const vatPercentage = parseFloat(document.getElementById('vatPercentage').value);
    const customerTaxId = document.getElementById('customerTaxId').value;
    const customerAddress = document.getElementById('customerAddress').value;
    const totalAmount = parseFloat(document.getElementById('totalAmount').textContent);
    const vatAmount = parseFloat(document.getElementById('vatAmount').textContent);
    const grandTotal = parseFloat(document.getElementById('grandTotal').textContent);
    
    // Create PDF using jsPDF
    const doc = new jsPDF();
    
    // Add header
    doc.setFontSize(18);
    doc.text('Riyon International Pvt(Ltd)', 105, 20, { align: 'center' });
    doc.text('ORICADO', 105, 30, { align: 'center' });
    
    // Add company details
    doc.setFontSize(12);
    doc.text('VAT Number: 174924198-7000', 15, 45);
    doc.text('Address: 456/A, MDH Jayawardhane Mawatha, kaduwela', 15, 55);
    
    // Add customer details
    doc.text('Customer Details:', 15, 70);
    doc.text(`Name: ${document.getElementById('customerName').value}`, 15, 80);
    doc.text(`Address: ${customerAddress}`, 15, 90);
    doc.text(`Tax ID: ${customerTaxId}`, 15, 100);
    
    // Add items table
    let yPos = 120;
    const items = Array.from(document.querySelectorAll('#itemsTable tbody tr')).map(row => ({
        name: row.querySelector('.item-name').value,
        quantity: row.querySelector('.quantity').value,
        price: row.querySelector('.price').value,
        amount: row.querySelector('.amount').value
    }));
    
    // Table headers
    doc.text('Item', 15, yPos);
    doc.text('Qty', 90, yPos);
    doc.text('Price', 130, yPos);
    doc.text('Amount', 170, yPos);
    yPos += 10;
    
    // Table items
    items.forEach(item => {
        doc.text(item.name, 15, yPos);
        doc.text(item.quantity, 90, yPos);
        doc.text(item.price, 130, yPos);
        doc.text(item.amount, 170, yPos);
        yPos += 10;
    });
    
    // Add totals with VAT
    yPos += 10;
    doc.text(`Sub Total: ${totalAmount.toFixed(2)}`, 130, yPos);
    yPos += 10;
    doc.text(`VAT (${vatPercentage}%): ${vatAmount.toFixed(2)}`, 130, yPos);
    yPos += 10;
    doc.text(`Grand Total: ${grandTotal.toFixed(2)}`, 130, yPos);
    
    // Add signature lines
    yPos += 30;
    doc.line(20, yPos, 70, yPos);
    doc.line(130, yPos, 180, yPos);
    doc.text('Authorized Signature', 25, yPos + 10);
    doc.text('Customer Signature', 135, yPos + 10);
    
    // Save the PDF
    doc.save('vat-invoice.pdf');
    document.getElementById('vatModal').style.display = 'none';
}
