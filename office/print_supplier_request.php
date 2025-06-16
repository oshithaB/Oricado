<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if (!isset($_GET['id'])) {
    die('No ID provided');
}

$id = $conn->real_escape_string($_GET['id']);

// Get quotation details
$quotation = $conn->query("
    SELECT sq.*, q.*, u.name as created_by_name
    FROM supplier_quotations sq
    JOIN quotations q ON sq.quotation_id = q.id
    LEFT JOIN users u ON sq.created_by = u.id
    WHERE q.id = $id
")->fetch_assoc();

// Get items
$items = $conn->query("
    SELECT qi.*, m.color, m.thickness, m.type
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = $id
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Material Request #<?php echo $id; ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px;
            padding: 20px;
            background: #f9f9f9;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 20px;
        }
        .signature-block {
            text-align: center;
            flex: 1;
            margin: 0 20px;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #333;
            margin: 50px auto 10px;
        }
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 20px;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()">Print Request</button>
    </div>

    <div class="print-container">
        <div class="header">
            <div class="company-name">ORICADO COMPANY</div>
            <h2>Material Request</h2>
        </div>

        <div class="info">
            <p><strong>Request #:</strong> <?php echo $quotation['id']; ?></p>
            <p><strong>Supplier:</strong> <?php echo htmlspecialchars($quotation['supplier_name']); ?></p>
            <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Specifications</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>
                        <?php if ($item['type'] == 'coil'): ?>
                            Color: <?php echo str_replace('_', ' ', ucfirst($item['color'])); ?><br>
                            Thickness: <?php echo $item['thickness']; ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                    <td>Rs. <?php echo number_format($item['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="5" align="right"><strong>Total Amount:</strong></td>
                    <td><strong>Rs. <?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line"></div>
                <p>Prepared By</p>
                <p><?php echo htmlspecialchars($quotation['created_by_name']); ?></p>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <p>Approved By</p>
            </div>
        </div>
    </div>
</body>
</html>
