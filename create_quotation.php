<?php
// ...existing code...

if(isset($_POST['submit'])) {
    // ...existing code...
    foreach($product_ids as $key => $product_id) {
        $quantity = $quantities[$key];
        $price = $prices[$key];
        
        // Get product type to check if it's a coil
        $product_query = mysqli_query($conn, "SELECT product_type FROM products WHERE id = '$product_id'");
        $product_data = mysqli_fetch_assoc($product_query);
        
        $coil_inches = ($product_data['product_type'] == 'coil') ? $coil_inches[$key] : NULL;
        $pieces = ($product_data['product_type'] == 'coil') ? $pieces[$key] : NULL;
        
        $insert_item = mysqli_query($conn, "INSERT INTO quotation_items 
            (quotation_id, product_id, quantity, price, coil_inches, pieces) 
            VALUES ('$quotation_id', '$product_id', '$quantity', '$price', '$coil_inches', '$pieces')");
    }
    // ...existing code...
}
?>

<div class="form-group coil-fields" style="display: none;">
    <label for="coil_inches">Inches:</label>
    <input type="number" step="0.01" class="form-control" name="coil_inches[]" id="coil_inches">
    
    <label for="pieces">Pieces:</label>
    <input type="number" class="form-control" name="pieces[]" id="pieces">
</div>

<script>
$(document).ready(function() {
    $(document).on('change', '.product-select', function() {
        var productId = $(this).val();
        var row = $(this).closest('.product-row');
        
        $.ajax({
            url: 'get_product_type.php',
            type: 'POST',
            data: {product_id: productId},
            success: function(response) {
                if(response == 'coil') {
                    row.find('.coil-fields').show();
                } else {
                    row.find('.coil-fields').hide();
                }
            }
        });
    });
});
</script>