document.addEventListener('DOMContentLoaded', function() {
    const quotationType = document.getElementById('quotationType');
    const coilThicknessSection = document.getElementById('coilThicknessSection');
    const quotationTextSection = document.getElementById('quotationTextSection');
    const coilThickness = document.getElementById('coilThickness');
    const quotationText = document.getElementById('quotationText');
    const addItemBtn = document.getElementById('addItem');
    const itemsTable = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];

    const quotationTexts = {
        '0.60': 'Default text for 0.60 thickness quotation...',
        '0.47': 'Default text for 0.47 thickness quotation...'
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
        <td>
            <div class="material-search">
                <input type="text" class="item-name" onkeyup="searchMaterials(this)" autocomplete="off" required>
                <div class="material-suggestions"></div>
                <input type="hidden" name="items[${rowIndex}][material_id]" class="material-id">
                <input type="hidden" name="items[${rowIndex}][name]" class="item-name-input">
            </div>
        </td>
        <td><input type="number" name="items[${rowIndex}][quantity]" class="quantity" step="0.01" oninput="calculateRowTotal(this)" required></td>
        <td><input type="text" name="items[${rowIndex}][unit]" class="unit"></td>
        <td><input type="number" name="items[${rowIndex}][discount]" class="discount" step="0.01" value="0" oninput="calculateRowTotal(this)"></td>
        <td><input type="number" name="items[${rowIndex}][price]" class="price" step="0.01" oninput="calculateRowTotal(this)"></td>
        <td><input type="number" name="items[${rowIndex}][taxes]" class="taxes" step="0.01" value="0" oninput="calculateRowTotal(this)"></td>
        <td><input type="number" name="items[${rowIndex}][amount]" class="amount" readonly></td>
        <td><button type="button" onclick="removeRow(this)">Remove</button></td>
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
