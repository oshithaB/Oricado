<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_done'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'done' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    header('Location: dashboard.php');
    exit();
}

$orders = $conn->query("
    SELECT o.*, u1.name as prepared_by_name, u2.name as checked_by_name 
    FROM orders o 
    LEFT JOIN users u1 ON o.prepared_by = u1.id
    LEFT JOIN users u2 ON o.checked_by = u2.id
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Office Staff Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ . '/includes/navigation.php'; ?>
        <div class="content">
            <h3>Recent Orders</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <style>
/* Table Container */
.table-container {
    margin-top: 20px;
    overflow-x: auto; /* Enable horizontal scrolling for small screens */
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

/* Table Styling */
.table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    background-color: #fff;
}

.table th {
    background-color: rgb(255, 179, 0); /* Yellow background */
    color: black; /* Black text */
    text-align: left;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    border-bottom: 2px solid black;
}

.table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 14px;
    color: #333;
}

.table tr:nth-child(even) {
    background-color: #f9f9f9; /* Light gray for alternating rows */
}

.table tr:hover {
    background-color: rgb(255, 230, 128); /* Light yellow on hover */
    cursor: pointer;
}

/* Buttons Styling */
.button {
    padding: 8px 15px;
    background-color: rgb(255, 238, 0); /* Yellow background */
    color: black; /* Black text */
    border: 2px solid black;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
    display: inline-block;
}

.button:hover {
    background-color: black; /* Black background on hover */
    color: white; /* White text on hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .table th, .table td {
        font-size: 12px;
        padding: 8px;
    }

    .button {
        font-size: 12px;
        padding: 6px 10px;
    }
}
</style>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo ucfirst($order['status']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td>
                            <?php if ($order['status'] == 'reviewed'): ?>
                                <a href="confirm_order.php?id=<?php echo $order['id']; ?>" class="button">Confirm Order</a>
                            <?php elseif ($order['status'] == 'completed'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="mark_done" value="1">
                                    <button type="submit" class="button">Mark as Done</button>
                                </form>
                            <?php else: ?>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="button">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
