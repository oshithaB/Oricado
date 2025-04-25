<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
$calculated_sqft = $_GET['calculated_sqft'] ?? 0;

if (!$quotation_id) {
    header('Location: quotations.php');
    exit();
}

// Get quotation item details with better error handling
$query = "
    SELECT q.*, qi.*, m.price as base_price, qi.name as material_name
    FROM quotations q
    JOIN quotation_items qi ON q.id = qi.quotation_id
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE q.id = ? AND qi.name LIKE '%Roller Door%'
    LIMIT 1
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $quotation_id);
$stmt->execute();
$result = $stmt->get_result();
$quotation = $result->fetch_assoc();

if (!$quotation) {
    $_SESSION['error_message'] = "Quotation or coil item not found";
    header('Location: quotations.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Update quotation item with new square footage
        $stmt = $conn->prepare("
            UPDATE quotation_items 
            SET quantity = ?, price = ?, discount = ?, taxes = ?, amount = ?
            WHERE quotation_id = ? AND material_id = ?
        ");

        $quantity = floatval($_POST['quantity']);
        $price = floatval($_POST['price']);
        $discount = floatval($_POST['discount']);
        $taxes = floatval($_POST['taxes']);
        
        // Calculate new amount
        $amount = $quantity * $price;
        $amount = $amount * (1 - ($discount/100));
        $amount = $amount * (1 + ($taxes/100));

        $stmt->bind_param("dddddii", 
            $quantity, $price, $discount, $taxes, $amount,
            $quotation_id, $_POST['material_id']
        );
        $stmt->execute();

        // Update quotation total
        $stmt = $conn->prepare("
            UPDATE quotations 
            SET total_amount = (
                SELECT SUM(amount) FROM quotation_items WHERE quotation_id = ?
            ),
            is_updated = 1
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $quotation_id, $quotation_id);
        $stmt->execute();

        $conn->commit();
        
        // Redirect back to pending orders if order was already created
        if (isset($_GET['order_id'])) {
            header('Location: pending_orders.php');
            exit();
        }

        header('Location: quotations.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Quotation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <div class="section">
                <h2>Edit Quotation</h2>
                <div class="alert warning">
                    <p>Square feet mismatch detected:</p>
                    <ul>
                        <li>Calculated from measurements: <strong><?php echo number_format($calculated_sqft, 2); ?> sqft</strong></li>
                        <li>Current in quotation: <strong><?php echo number_format($quotation['quantity'] ?? 0, 2); ?> sqft</strong></li>
                        <li>Material: <strong><?php echo htmlspecialchars($quotation['material_name'] ?? 'Unknown'); ?></strong></li>
                    </ul>
                </div>

                <form method="POST">
                    <input type="hidden" name="material_id" value="<?php echo $quotation['material_id']; ?>">
                    
                    <div class="form-group">
                        <label>Square Feet:</label>
                        <input type="number" name="quantity" step="0.01" 
                               value="<?php echo $calculated_sqft; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Price per Unit:</label>
                        <input type="number" name="price" step="0.01" 
                               value="<?php echo $quotation['price']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Discount (%):</label>
                        <input type="number" name="discount" step="0.01" 
                               value="<?php echo $quotation['discount']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Taxes (%):</label>
                        <input type="number" name="taxes" step="0.01" 
                               value="<?php echo $quotation['taxes']; ?>" required>
                    </div>

                    <button type="submit">Update Quotation</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
