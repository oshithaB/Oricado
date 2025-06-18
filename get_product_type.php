<?php
include 'config.php';

if(isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $query = mysqli_query($conn, "SELECT product_type FROM products WHERE id = '$product_id'");
    $product = mysqli_fetch_assoc($query);
    
    echo $product['product_type'];
}
?>
