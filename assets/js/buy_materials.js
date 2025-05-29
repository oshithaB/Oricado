document.addEventListener('DOMContentLoaded', function() {
    const supplierName = document.getElementById('supplierName');
    const supplierContact = document.getElementById('supplierContact');
    const materialsTable = document.getElementById('materialsTable').getElementsByTagName('tbody')[0];
    
    // Material row counter for unique field names
    let rowCount = 0;

    // Supplier name suggestions
    supplierName.addEventListener('input', function() {
        if (this.value.length > 2) {
            fetch('../office/search_contacts.php?term=' + this.value + '&type=supplier')
                .then(response => response.json())
                .then(data => {
                    const suggestions = document.getElementById('supplierSuggestions');
                    suggestions.innerHTML = '';
                    suggestions.style.display = 'block';
                    
                    data.forEach(contact => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.textContent = contact.name;
                        div.onclick = () => {
                            supplierName.value = contact.name;
                            supplierContact.value = contact.mobile;
                            suggestions.style.display = 'none';
                        };
                        suggestions.appendChild(div);
                    });
                });
        }
    });

    // Add existing material
    document.getElementById('addExisting').onclick = () => {
        const row = materialsTable.insertRow();
        const currentRow = rowCount++;
        
        row.innerHTML = `
            <td>
                <div class="material-search">
                    <input type="text" class="material-name" required>
                    <input type="hidden" name="items[${currentRow}][material_id]">
                    <input type="hidden" name="items[${currentRow}][name]">
                    <input type="hidden" name="items[${currentRow}][existing]" value="1">
                </div>
            </td>
            <td><input type="text" name="items[${currentRow}][unit]" readonly></td>
            <td><input type="number" name="items[${currentRow}][price]" step="0.01" required></td>
            <td><input type="number" name="items[${currentRow}][saleprice]" step="0.01" required></td>
            <td><input type="number" name="items[${currentRow}][quantity]" step="0.01" required></td>
            <td><button type="button" class="remove-btn" onclick="this.closest('tr').remove()">Remove</button></td>
        `;

        setupMaterialSearch(row, currentRow);
    };

    // Add new material
    document.getElementById('addNew').onclick = () => {
        const row = materialsTable.insertRow();
        const currentRow = rowCount++;
        
        row.innerHTML = `
            <td>
                <input type="text" name="items[${currentRow}][name]" required>
                <input type="hidden" name="items[${currentRow}][existing]" value="0">
            </td>
            <td>
                <select name="items[${currentRow}][unit]" required>
                    <option value="sqft">Square Feet</option>
                    <option value="pieces">Pieces</option>
                    <option value="meters">Meters</option>
                    <option value="liters">Liters</option>
                </select>
            </td>
            <td><input type="number" name="items[${currentRow}][price]" step="0.01" required></td>
            <td><input type="number" name="items[${currentRow}][saleprice]" step="0.01" required></td>
            <td><input type="number" name="items[${currentRow}][quantity]" step="0.01" required></td>
            <td><button type="button" class="remove-btn" onclick="this.closest('tr').remove()">Remove</button></td>
        `;
    };

    // Setup material search functionality
    function setupMaterialSearch(row, rowIndex) {
        const nameInput = row.querySelector('.material-name');
        const materialIdInput = row.querySelector(`[name="items[${rowIndex}][material_id]"]`);
        const materialNameInput = row.querySelector(`[name="items[${rowIndex}][name]"]`);
        const unitInput = row.querySelector(`[name="items[${rowIndex}][unit]"]`);
        const priceInput = row.querySelector(`[name="items[${rowIndex}][price]"]`);

        nameInput.addEventListener('input', function() {
            if (this.value.length > 2) {
                fetch('../office/search_materials.php?term=' + this.value)
                    .then(response => response.json())
                    .then(data => {
                        const suggestions = document.createElement('div');
                        suggestions.className = 'suggestions-dropdown';
                        suggestions.style.display = 'block';
                        
                        data.forEach(material => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            
                            // Format display name based on material type
                            let displayName = material.name;
                            if (material.type === 'coil') {
                                const color = material.color.replace(/_/g, ' ');
                                displayName = `${material.name} (${material.thickness} - ${color})`;
                            }
                            
                            div.textContent = displayName;
                            div.onclick = () => {
                                nameInput.value = displayName;
                                materialIdInput.value = material.id;
                                materialNameInput.value = material.name;
                                unitInput.value = material.unit;
                                priceInput.value = material.price;
                                suggestions.remove();
                            };
                            suggestions.appendChild(div);
                        });
                        
                        // Remove existing suggestions
                        const existingSuggestions = nameInput.parentNode.querySelector('.suggestions-dropdown');
                        if (existingSuggestions) {
                            existingSuggestions.remove();
                        }
                        
                        nameInput.parentNode.appendChild(suggestions);
                    });
            }
        });
    }

    // Remove button functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-btn')) {
            e.target.closest('tr').remove();
        }
    });
});
