document.addEventListener('DOMContentLoaded', function() {
    const quotationType = document.getElementById('quotationType');
    const coilThicknessSection = document.getElementById('coilThicknessSection');
    const quotationTextSection = document.getElementById('quotationTextSection');
    const coilThickness = document.getElementById('coilThickness');
    const quotationText = document.getElementById('quotationText');
    const addItemBtn = document.getElementById('addItem');
    const itemsTable = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];

    const quotationTexts = {
        '0.60': `Features of the Roller Door
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
Final Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.
Price Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.
Tax Exclusion: Prices exclude applicable taxes.
Bank Details
Account Name: RIYON INTERNATIONAL (PVT) LTD
Bank: HATTON NATIONAL BANK - MALABE
Account Number: 1560 1000 9853
For inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   

We are committed to providing high-quality products using the latest technology and premium materials.

Thank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​

Yours Sincerely,

ORICADO ROLLER DOORS



Prepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................`,
        '0.47': `Features of the Roller Door
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
For inquiries, please contact  Ms. Poojani at +94 76 827 4015. /  Ms. Chathuri at +94 74 156 8098.

​

We trust this quotation meets your requirements. ORICADO ROLLER DOORS is committed to delivering high-quality products using advanced technology and premium materials.

Yours Sincerely,

ORICADO ROLLER DOORS





Prepared By: ......................................	​​	​​Checked By:.........................................	​	​	​Authorized By:.........................................`
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
});

function selectCustomer(name, contact) {
    document.getElementById('customerName').value = name;
    document.getElementById('customerContact').value = contact;
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
    const suggestionDiv = row.querySelector('.material-suggestions');

    nameInput.value = material.display_name || material.name;
    nameInputHidden.value = material.display_name || material.name;
    materialIdInput.value = material.id;
    unitInput.value = material.unit;
    priceInput.value = material.price || 0;
    // Remove readonly attribute from price input
    priceInput.removeAttribute('readonly');
    suggestionDiv.style.display = 'none';

    calculateRowTotal(row.querySelector('.quantity'));
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
                        <div class="suggestion-item" onclick="selectCustomer('${contact.name}', '${contact.mobile}')">
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
    document.getElementById('totalAmount').textContent = total.toFixed(2);
    document.getElementById('totalAmountInput').value = total.toFixed(2);
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
