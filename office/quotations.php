<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Get search parameters
$search = $_GET['search'] ?? '';

// Debug mode - set to true to see debugging information
$debug_mode = false;

// First, let's get all quotations without supplier quotations
$query = "SELECT q.* FROM quotations q 
          LEFT JOIN supplier_quotations sq ON q.id = sq.quotation_id 
          WHERE sq.id IS NULL";  // Only get quotations that are NOT in supplier_quotations

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (q.customer_name LIKE '%$search%' 
                OR q.customer_contact LIKE '%$search%'
                OR q.id LIKE '%$search%')";
}

$query .= " ORDER BY q.created_at DESC";

if ($debug_mode) {
    echo "<!-- DEBUG: Main Query: " . $query . " -->\n";
}

$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$quotations = $result->fetch_all(MYSQLI_ASSOC);

if ($debug_mode) {
    echo "<!-- DEBUG: Found " . count($quotations) . " quotations -->\n";
    foreach ($quotations as $q) {
        echo "<!-- DEBUG: Quotation ID: " . $q['id'] . ", Customer: " . $q['customer_name'] . " -->\n";
    }
}

// Get user names separately
$users = [];
$users_query = "SELECT id, name FROM users";
$users_result = $conn->query($users_query);
if ($users_result) {
    while ($user = $users_result->fetch_assoc()) {
        $users[$user['id']] = $user['name'];
    }
}

// Get orders separately
$orders = [];
$orders_query = "SELECT quotation_id FROM orders";
$orders_result = $conn->query($orders_query);
if ($orders_result) {
    while ($order = $orders_result->fetch_assoc()) {
        $orders[] = $order['quotation_id'];
    }
}

// Get quotation items separately
$items_query = "
    SELECT qi.*, m.color, m.thickness, m.type
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    ORDER BY qi.quotation_id, qi.id
";

$items_result = $conn->query($items_query);
$all_items = [];
if ($items_result) {
    while ($item = $items_result->fetch_assoc()) {
        $all_items[$item['quotation_id']][] = $item;
    }
}

if ($debug_mode) {
    echo "<!-- DEBUG: Items grouped by quotation_id: -->\n";
    foreach ($all_items as $qid => $items) {
        echo "<!-- DEBUG: Quotation $qid has " . count($items) . " items -->\n";
    }
}

// Now build the final array with all related data
$final_quotations = [];
foreach ($quotations as $quotation) {
    // Add user name
    $quotation['prepared_by_name'] = $users[$quotation['created_by']] ?? '';
    
    // Add order status
    $quotation['has_order'] = in_array($quotation['id'], $orders) ? 1 : 0;
    
    // Add items
    $quotation['items'] = $all_items[$quotation['id']] ?? [];
    
    $final_quotations[] = $quotation;
}

if ($debug_mode) {
    echo "<!-- DEBUG: Final quotations count: " . count($final_quotations) . " -->\n";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quotations</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Search section styles */
        .search-section {
            margin-bottom: 20px;
            padding: 20px;
            background-color: #fff;
            border: 2px solid black;
            border-radius: 8px;
        }
        
        .search-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-form input[type="text"] {
            padding: 8px 12px;
            border: 2px solid black;
            border-radius: 4px;
            font-size: 14px;
            width: 300px;
        }

        /* Table Styling */
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            font-family: Arial, sans-serif;
            background-color: #fff;
            border: 2px solid black;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .items-table th {
            background-color: rgb(255, 179, 0); /* Yellow background */
            color: black; /* Black text */
            text-align: left;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid black;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
            color: #333;
        }

        .items-table tr:nth-child(even) {
            background-color: #f9f9f9; /* Light gray for alternating rows */
        }

        .items-table tr:hover {
            background-color: rgb(255, 230, 128); /* Light yellow on hover */
            cursor: pointer;
        }

        /* Total Row Styling */
        .total-row {
            background-color: rgb(255, 179, 0); /* Yellow background */
            color: black; /* Black text */
            font-weight: bold;
            font-size: 16px;
        }

        /* Buttons Styling */
        .button {
            padding: 10px 20px;
            background-color: rgb(255, 179, 0); /* Yellow background */
            color: black; /* Black text */
            border: 2px solid black;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
            display: inline-block;
            margin-right: 10px;
        }

        .button:hover {
            background-color: black; /* Black background on hover */
            color: white; /* White text on hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn {
            padding: 8px 16px;
            background-color: rgb(255, 179, 0);
            color: black;
            border: 2px solid black;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            display: inline-block;
            margin-right: 8px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: black;
            color: white;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-warning {
            background-color: #ffc107;
            color: black;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #e0a800;
        }

        /* Card Styling */
        .quotation-card {
            background-color: #fff;
            border: 2px solid black;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .quotation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .quotation-header h3 {
            margin: 0;
            font-size: 20px;
            color: black;
        }

        .quotation-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .quotation-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        /* Alert messages */
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Section styling */
        .section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
        }

        .section h2 {
            color: black;
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* Dashboard layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 20px;
            background-color: #f8f9fa;
        }

        /* Debug info styling */
        .debug-info {
            background-color: #e9ecef;
            border: 1px solid #adb5bd;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 12px;
            color: #495057;
            font-family: monospace;
        }

        .raw-data {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 11px;
            color: #6c757d;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }

        .quotation-note {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 3px solid #ffc107;
            margin-top: 10px;
            border-radius: 4px;
        }

        .quotation-note span {
            display: block;
            margin-top: 5px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/navigation.php'; ?>
        <div class="content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert error">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($debug_mode): ?>
                <div class="debug-info">
                    <strong>DEBUG MODE ACTIVE</strong><br>
                    Total Quotations Found: <?php echo count($final_quotations); ?><br>
                    Search Term: "<?php echo htmlspecialchars($search); ?>"<br>
                    Users Count: <?php echo count($users); ?><br>
                    Orders Count: <?php echo count($orders); ?><br>
                    Raw Quotations from DB: <?php echo count($quotations); ?>
                </div>
            <?php endif; ?>

            <div class="search-section">
                <form method="GET" class="search-form">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by quotation ID, customer name or contact"
                           style="width: 350px;">
                    <button type="submit" class="button">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="quotations.php" class="button">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="section">
                <h2>Quotations (Displaying: <?php echo count($final_quotations); ?>)</h2>
                
                <?php if (empty($final_quotations)): ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <?php if (!empty($search)): ?>
                            No quotations found matching your search criteria.
                        <?php else: ?>
                            No quotations found in database.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($final_quotations as $index => $quotation): ?>
                    <div class="quotation-card">
                        <div class="quotation-header">
                            <h3>Quotation #<?php echo $quotation['id']; ?></h3>
                            <div class="quotation-info">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($quotation['customer_name']); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($quotation['customer_contact']); ?></p>
                                <p><strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $quotation['type'])); ?> Quotation</p>
                                <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($quotation['created_at'])); ?></p>
                                <?php if (!empty($quotation['prepared_by_name'])): ?>
                                    <p><strong>Prepared by:</strong> <?php echo htmlspecialchars($quotation['prepared_by_name']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($quotation['note'])): ?>
                                    <p class="quotation-note">
                                        <strong>Note:</strong> 
                                        <span><?php echo nl2br(htmlspecialchars($quotation['note'])); ?></span>
                                    </p>
                                <?php endif; ?>
                                <?php if ($quotation['has_order']): ?>
                                    <p><strong>Status:</strong> <span style="color: green;">Has Order</span></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($debug_mode): ?>
                        <div class="debug-info">
                            <strong>DEBUG INFO:</strong><br>
                            Array Index: <?php echo $index; ?><br>
                            Quotation ID: <?php echo $quotation['id']; ?><br>
                            Items Count: <?php echo count($quotation['items']); ?><br>
                            Has Order: <?php echo $quotation['has_order'] ? 'Yes' : 'No'; ?><br>
                            Created By: <?php echo $quotation['created_by']; ?><br>
                            Is Updated: <?php echo $quotation['is_updated'] ? 'Yes' : 'No'; ?>
                        </div>

                        <div class="raw-data">
                            <strong>RAW DATA:</strong>
                            <?php echo print_r($quotation, true); ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($quotation['items'])): ?>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Details</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Taxes</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quotation['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <?php if ($item['type'] == 'coil'): ?>
                                            Color: <?php echo str_replace('_', ' ', ucfirst($item['color'] ?? 'N/A')); ?><br>
                                            Thickness: <?php echo $item['thickness'] ?? 'N/A'; ?>
                                        <?php else: ?>
                                            <?php echo !empty($item['type']) ? ucfirst($item['type']) : 'N/A'; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($item['quantity'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo number_format($item['discount'], 2); ?>%</td>
                                    <td><?php echo number_format($item['taxes'], 2); ?>%</td>
                                    <td>Rs. <?php echo number_format($item['amount'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="7" align="right"><strong>Subtotal:</strong></td>
                                    <td><strong>Rs. <?php echo number_format($quotation['subtotal'] ?? 0, 2); ?></strong></td>
                                </tr>
                                <?php if ($quotation['is_vat_quotation']): ?>
                                <tr class="total-row">
                                    <td colspan="7" align="right"><strong>VAT (18%):</strong></td>
                                    <td><strong>Rs. <?php echo number_format($quotation['vat'] ?? 0, 2); ?></strong></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="total-row">
                                    <td colspan="7" align="right"><strong>Total Amount:</strong></td>
                                    <td><strong>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p style="text-align: center; color: #666; padding: 20px;">No items found for this quotation.</p>
                        <?php endif; ?>

                        <div class="quotation-actions">
                            <a href="download_quotation.php?id=<?php echo $quotation['id']; ?>" 
                               class="button download-btn">Download Quotation</a>
                            
                            <?php if ($quotation['type'] == 'raw_materials' && !$quotation['has_order']): ?>
                                <form method="POST" action="confirm_material_quotation.php" style="display: inline;">
                                    <input type="hidden" name="quotation_id" value="<?php echo $quotation['id']; ?>">
                                    <button type="submit" class="button confirm-btn">Confirm Material Order</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($quotation['type'] == 'order' && !$quotation['has_order'] && !$quotation['is_updated']): ?>
                                <a href="create_order.php?quotation_id=<?php echo $quotation['id']; ?>" 
                                   class="button add-measurements-btn">Add Measurements</a>
                            <?php endif; ?>

                            <a href="view_quotation.php?id=<?php echo $quotation['id']; ?>" class="btn btn-primary">View</a>
                            <a href="download_vat_invoice.php?id=<?php echo $quotation['id']; ?>" class="btn btn-warning">Download VAT Invoice</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Debug: Log quotation data to console
        <?php if ($debug_mode): ?>
        console.log('Quotations Debug Data:');
        console.log(<?php echo json_encode($final_quotations); ?>);
        <?php endif; ?>
    </script>
</body>
</html>