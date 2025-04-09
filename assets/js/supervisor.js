function showMaterialForm(orderId) {
    // Create modal for quick material addition
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3>Quick Add Materials</h3>
            <form method="POST" action="add_materials.php">
                <input type="hidden" name="order_id" value="${orderId}">
                <div class="material-grid">
                    <!-- Common materials with quantity inputs -->
                    <div class="form-group">
                        <label>Coil (sqft):</label>
                        <input type="number" name="materials[coil]" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Motor (pieces):</label>
                        <input type="number" name="materials[motor]" min="0">
                    </div>
                    <!-- Add more common materials -->
                </div>
                <button type="submit">Save Materials</button>
                <button type="button" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal() {
    const modal = document.querySelector('.modal');
    if (modal) {
        modal.remove();
    }
}
