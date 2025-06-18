<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

if (!isset($_GET['id'])) {
    die('No quotation ID provided');
}

$id = $conn->real_escape_string($_GET['id']);

// Get quotation details
$quotation = $conn->query("
    SELECT q.*, u.name as prepared_by_name 
    FROM quotations q
    LEFT JOIN users u ON q.created_by = u.id
    WHERE q.id = $id
")->fetch_assoc();

// Get coil items only
$items = $conn->query("
    SELECT qi.*, m.color, m.thickness, m.type, qi.coil_inches, qi.pieces
    FROM quotation_items qi
    LEFT JOIN materials m ON qi.material_id = m.id
    WHERE qi.quotation_id = $id AND m.type = 'coil'
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Card #<?php echo $id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 10px 0;
            font-size: 20px;
            color: #666;
        }
        .ref-number {
            margin: 10px 0;
            font-size: 16px;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .signature {
            margin-top: 50px;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()">Print Job Card</button>
    </div>

    <div class="header">
        <h1>JOB CARD</h1>
        <h2>ROLLER DOOR SHEET CUTTING ORDER</h2>
        <div class="ref-number">
            <strong>Quotation #:</strong> <?php echo $id; ?>
        </div>
    </div>

    <div class="info">
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($quotation['customer_name']); ?></p>
        <p><strong>Prepared By:</strong> <?php echo htmlspecialchars($quotation['prepared_by_name']); ?></p>
        <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($quotation['created_at'])); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Details</th>
                <th>Inches</th>
                <th>Pieces</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>
                    Color: <?php echo str_replace('_', ' ', ucfirst($item['color'])); ?><br>
                    Thickness: <?php echo $item['thickness']; ?>
                </td>
                <td><?php echo $item['coil_inches'] ? number_format($item['coil_inches'], 2) : '-'; ?></td>
                <td><?php echo $item['pieces'] ? number_format($item['pieces']) : '-'; ?></td>
                <td><?php echo number_format($item['quantity'], 2); ?></td>
                <td><?php echo htmlspecialchars($item['unit']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="signature">
        <div class="signature-line"></div>
        <p>Job Handover By</p>
    </div>
</body>
</html>
