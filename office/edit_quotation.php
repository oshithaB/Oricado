<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

$quotation_id = $_GET['id'] ?? null;
$calculated_sqft = $_GET['calculated_sqft'] ?? 0;

if (!$quotation_id) {
    header('Location: quotations.php');
    exit();
}

// Get quotation details
$quotation = $conn->query("
    SELECT q.*, qi.* 
    FROM quotations q
    JOIN quotation_items qi ON q.id = qi.quotation_id
    JOIN materials m ON qi.material_id = m.id
    WHERE q.id = $quotation_id AND m.type = 'coil'
")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Update quotation item quantity
        $stmt = $conn->prepare("
            UPDATE quotation_items 
            SET quantity = ?, amount = quantity * price * (1 - discount/100) * (1 + taxes/100)
            WHERE quotation_id = ? AND material_id = ?
        ");
        
        if ($stmt === false) {
            throw new Exception("Error preparing update statement: " . $conn->error);
        }
        
        $stmt->bind_param("dii", $_POST['quantity'], $quotation_id, $_POST['material_id']);
        $stmt->execute();

        // Update quotation total amount
        $stmt = $conn->prepare("
            UPDATE quotations 
            SET total_amount = (
                SELECT SUM(amount) 
                FROM quotation_items 
                WHERE quotation_id = ?
            )
            WHERE id = ?
        ");
        
        if ($stmt === false) {
            throw new Exception("Error preparing total update statement: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $quotation_id, $quotation_id);
        $stmt->execute();

        $conn->commit();

        // Instead of requiring create_order.php, redirect to it with stored data
        if (isset($_SESSION['order_data'])) {
            $_SESSION['order_from_quotation'] = true;
            header("Location: create_order.php?quotation_id=" . $quotation_id);
            exit();
        }

        header('Location: quotations.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating quotation: " . $e->getMessage();
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
                    The calculated square feet (<?php echo number_format($calculated_sqft, 2); ?> sqft) 
                    doesn't match the quotation (<?php echo number_format($quotation['quantity'], 2); ?> sqft).
                    Please update the quotation to proceed.
                </div>

                <form method="POST">
                    <input type="hidden" name="material_id" value="<?php echo $quotation['material_id']; ?>">
                    <div class="form-group">
                        <label>Square Feet:</label>
                        <input type="number" name="quantity" step="0.01" 
                               value="<?php echo $calculated_sqft; ?>" required>
                    </div>
                    <button type="submit">Update Quotation</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
