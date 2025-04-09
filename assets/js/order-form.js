document.addEventListener('DOMContentLoaded', function() {
    // Handle wicket door toggle
    const wicketDoorCheckbox = document.getElementById('has_wicket_door');
    const wicketDoorFields = document.getElementById('wicket-door-fields');
    
    if (wicketDoorCheckbox && wicketDoorFields) {
        wicketDoorCheckbox.addEventListener('change', function() {
            wicketDoorFields.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Handle thickness custom input
    const thicknessSelect = document.getElementById('thickness');
    const customThickness = document.getElementById('custom_thickness');
    
    if (thicknessSelect && customThickness) {
        thicknessSelect.addEventListener('change', function() {
            customThickness.style.display = this.value === 'custom' ? 'block' : 'none';
            if (this.value === 'custom') {
                customThickness.required = true;
            } else {
                customThickness.required = false;
                customThickness.value = '';
            }
        });
    }
});
