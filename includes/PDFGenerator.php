<?php
class PDFGenerator {
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function generatePDF($data, $type = 'order') {
        // Set PDF headers
        header('Content-Type: text/html; charset=utf-8');
        
        // Check for local logo file
        $logoPath = __DIR__ . '/../assets/images/oricado logo.jpg';
        $logoHtml = file_exists($logoPath) ? 
            '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                  style="max-width: 200px; display: block; margin: 0 auto;">' : 
            '';

        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . ($type == 'quotation' ? 'Quotation' : 'Order') . ' #' . $this->id . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    margin: 30px; 
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
                    background: #f4f4f4; 
                }
                .section { 
                    margin: 30px 0; 
                }
                h2 { 
                    color: #333; 
                    border-bottom: 1px solid #ddd; 
                    padding-bottom: 5px; 
                }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                ' . $logoHtml . '
                <div class="company-name">Oricado Roller Doors</div>
                <div>' . ($type == 'quotation' ? 'Quotation' : 'Order') . ' #' . $this->id . '</div>
                <div>Date: ' . date('Y-m-d') . '</div>
            </div>';

        if ($type === 'quotation') {
            echo $this->getQuotationHTML($data);
        } elseif ($type === 'materials') {
            echo $this->getMaterialsListHTML($data);
        } elseif ($type === 'new_order') {
            echo $this->getNewOrderDetailsHTML($data);
        } else {
            echo $this->getOrderDetailsHTML($data);
        }

        echo '<script>
            window.onload = function() { window.print(); }
        </script>
        </body></html>';
        exit;
    }

    private function getQuotationHTML($quotation) {
        $html = '<div class="section">
            <h2>Quotation Details</h2>
            <table>
                <tr>
                    <th>Customer Name</th>
                    <td>' . htmlspecialchars($quotation['customer_name']) . '</td>
                </tr>
                <tr>
                    <th>Customer Contact</th>
                    <td>' . htmlspecialchars($quotation['customer_contact']) . '</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>' . ucfirst($quotation['type']) . ' Quotation</td>
                </tr>
                <tr>
                    <th>Created Date</th>
                    <td>' . date('Y-m-d', strtotime($quotation['created_at'])) . '</td>
                </tr>
            </table>';

        // Add quotation text for order type
        if ($quotation['type'] === 'order') {
            $html .= '<div class="additional-info">
                <div>' . nl2br(htmlspecialchars($quotation['quotation_text'])) . '</div>
            </div>';
        }

        // Items table with all details
        $html .= '<table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Details</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Price (Rs.)</th>
                    <th>Discount (%)</th>
                    <th>Taxes (%)</th>
                    <th>Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($quotation['items'] as $item) {
            $html .= '<tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . ($item['type'] == 'coil' ? 
                    'Color: ' . str_replace('_', ' ', ucfirst($item['color'])) . '<br>Thickness: ' . $item['thickness'] 
                    : '') . '</td>
                <td>' . number_format($item['quantity'], 2) . '</td>
                <td>' . htmlspecialchars($item['unit']) . '</td>
                <td>' . number_format($item['price'], 2) . '</td>
                <td>' . number_format($item['discount'], 2) . '</td>
                <td>' . number_format($item['taxes'], 2) . '</td>
                <td>' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }

        $html .= '</tbody>
            <tfoot>
                <tr>
                    <th colspan="7" style="text-align: right">Total Amount:</th>
                    <td>Rs. ' . number_format($quotation['total_amount'], 2) . '</td>
                </tr>
            </tfoot>
        </table>';

        return $html . '</div>';
    }

    private function getMaterialsListHTML($materials) {
        $html = '<div class="section">
            <h2>Materials List</h2>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Details</th>
                    <th>Quantity</th>
                </tr>';

        foreach ($materials as $material) {
            $html .= '<tr>
                <td>' . htmlspecialchars($material['name']) . '</td>
                <td>' . ($material['type'] == 'coil' ? 
                    'Color: ' . htmlspecialchars($material['color']) . '<br>Thickness: ' . $material['thickness'] 
                    : '') . '</td>
                <td>' . $material['used_quantity'] . ' ' . $material['unit'] . '</td>
            </tr>';
        }

        $html .= '</table></div>';
        return $html;
    }

    private function getOrderDetailsHTML($order) {
        $html = '<div class="section">
            <h2>Customer Information</h2>
            <table>
                <tr>
                    <th>Customer Name</th>
                    <td>' . htmlspecialchars($order['customer_name']) . '</td>
                </tr>
                <tr>
                    <th>Customer Contact</th>
                    <td>' . htmlspecialchars($order['customer_contact']) . '</td>
                </tr>
                <tr>
                    <th>Customer Address</th>
                    <td>' . nl2br(htmlspecialchars($order['customer_address'])) . '</td>
                </tr>
                <tr>
                    <th>Order Status</th>
                    <td>' . ucfirst($order['status']) . '</td>
                </tr>
                <tr>
                    <th>Created Date</th>
                    <td>' . date('Y-m-d H:i', strtotime($order['created_at'])) . '</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>' . htmlspecialchars($order['prepared_by_name']) . '</td>
                </tr>
                <tr>
                    <th>Contact Number</th>
                    <td>' . htmlspecialchars($order['prepared_by_contact']) . '</td>
                </tr>';
        
        // Add price for done orders
        if ($order['status'] === 'done' && isset($order['total_price'])) {
            $html .= '<tr>
                <th>Total Price</th>
                <td>Rs. ' . number_format($order['total_price'], 2) . '</td>
            </tr>';
        }
        
        $html .= '</table>';

        $html .= $this->getMeasurementsHTML($order);

        if (isset($order['materials'])) {
            $html .= $this->getMaterialsListHTML($order['materials']);
        }

        return $html . '</div>';
    }

    private function getNewOrderDetailsHTML($order) {
        $html = '<div class="section">
            <h2>Customer Information</h2>
            <table>
                <tr>
                    <th>Customer Name</th>
                    <td>' . htmlspecialchars($order['customer_name']) . '</td>
                </tr>
                <tr>
                    <th>Customer Contact</th>
                    <td>' . htmlspecialchars($order['customer_contact']) . '</td>
                </tr>
                <tr>
                    <th>Customer Address</th>
                    <td>' . nl2br(htmlspecialchars($order['customer_address'])) . '</td>
                </tr>
                <tr>
                    <th>Created Date</th>
                    <td>' . date('Y-m-d H:i', strtotime($order['created_at'])) . '</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>' . htmlspecialchars($order['prepared_by_name']) . '</td>
                </tr>
                <tr>
                    <th>Contact Number</th>
                    <td>' . htmlspecialchars($order['prepared_by_contact']) . '</td>
                </tr>
            </table>';

        $html .= $this->getMeasurementsHTML($order);
        
        return $html . '</div>';
    }

    private function getMeasurementsHTML($order) {
        $rollerDoorImg = __DIR__ . '/../rollerdoor.jpg';
        $wicketDoorImg = __DIR__ . '/../wicketdoor.jpg';

        $html = '<div class="section">
            <h2>Measurements</h2>
            <div class="measurement-guide">
                <h3>Roller Door Guide</h3>
                ' . (file_exists($rollerDoorImg) ? 
                    '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($rollerDoorImg)) . '" style="max-width: 100%; margin: 10px 0;">' 
                    : '') . '
            </div>
            <h2>Roller Door</h2>
            <table>
                <tr>
                    <th>Outside Width</th>
                    <td>' . $order['outside_width'] . '</td>
                    <th>Inside Width</th>
                    <td>' . $order['inside_width'] . '</td>
                </tr>
                <tr>
                    <th>Door Width</th>
                    <td>' . $order['door_width'] . '</td>
                    <th>Tower Height</th>
                    <td>' . $order['tower_height'] . '</td>
                </tr>
                <tr>
                    <th>Tower Type</th>
                    <td>' . ucfirst($order['tower_type']) . '</td>
                    <th>Coil Color</th>
                    <td>' . str_replace('_', ' ', ucfirst($order['coil_color'])) . '</td>
                </tr>
            </table>';

        if ($order['point1']) {
            $html .= '<div class="measurement-guide">
                <h3>Wicket Door Guide</h3>
                ' . (file_exists($wicketDoorImg) ? 
                    '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($wicketDoorImg)) . '" style="max-width: 100%; margin: 10px 0;">' 
                    : '') . '
            </div>
            <h2>Wicket Door</h2>
            <table>
                <tr>
                    <th>Point 1</th>
                    <td>' . $order['point1'] . '</td>
                    <th>Point 2</th>
                    <td>' . $order['point2'] . '</td>
                </tr>
                <tr>
                    <th>Point 3</th>
                    <td>' . $order['point3'] . '</td>
                    <th>Point 4</th>
                    <td>' . $order['point4'] . '</td>
                </tr>
                <tr>
                    <th>Point 5</th>
                    <td>' . $order['point5'] . '</td>
                    <th>Door Opening</th>
                    <td>' . str_replace('_', ' ', ucfirst($order['door_opening'])) . '</td>
                </tr>
            </table>';
        }

        return $html . '</div>';
    }

    private function getWicketDoorHTML($order) {
        return '<h2>Wicket Door</h2>
        <table>
            <tr>
                <th>Point 1</th>
                <td>' . $order['point1'] . '</td>
                <th>Point 2</th>
                <td>' . $order['point2'] . '</td>
            </tr>
            <tr>
                <th>Point 3</th>
                <td>' . $order['point3'] . '</td>
                <th>Point 4</th>
                <td>' . $order['point4'] . '</td>
            </tr>
            <tr>
                <th>Point 5</th>
                <td>' . $order['point5'] . '</td>
                <th>Door Opening</th>
                <td>' . str_replace('_', ' ', ucfirst($order['door_opening'])) . '</td>
            </tr>
        </table>';
    }
}
