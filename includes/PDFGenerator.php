<?php
class PDFGenerator {
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function generatePDF($data, $type = 'order') {
        // Set PDF headers
        header('Content-Type: text/html; charset=utf-8');
        
        $logoPath = __DIR__ . '/../assets/images/oricado logo.jpg';
        $logoHtml = file_exists($logoPath) ? 
        '<div style="
        display: inline-block;
        width: 120px;
        height: 120px;
        border-radius: 50%; /* Makes the logo circular */
        border: 5px solid #FFD700; /* Gold border */
        box-shadow: 0 0 10px 10px #FFD700; /* Shine effect */
        overflow: hidden; /* Ensures the logo stays within the circle */
        margin: 20px auto; /* Adds space above and below the logo */
    ">
        <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
             alt="Oricado Logo" 
             style="width: 100%; height: 100%; object-fit: cover;">
    </div>' : '';

        $title = '';
        switch ($type) {
            case 'supplier_quotation':
                $title = 'Supplier Purchase Quotation';
                break;
            case 'quotation':
                $title = 'Sales Quotation';
                break;
            case 'invoice_advance':
                $title = 'Advance Payment Invoice';
                echo $this->generateAdvanceInvoiceHTML($data);
                break;
            case 'invoice_final':
                $title = 'Final Payment Invoice';
                echo $this->generateFinalInvoiceHTML($data);
                break;
            default:
                $title = ucfirst($type);
        }

        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $title . ' #' . $this->id . '</title>
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
                <div>' . $title . ' #' . $this->id . '</div>
                <div>Date: ' . date('Y-m-d') . '</div>
            </div>';

        switch ($type) {
            case 'supplier_quotation':
                echo $this->getSupplierQuotationHTML($data);
                break;
            case 'quotation':
                echo $this->getQuotationHTML($data);
                break;
            case 'materials':
                echo $this->getMaterialsListHTML($data);
                break;
            case 'new_order':
                echo $this->getNewOrderDetailsHTML($data);
                break;
            default:
                echo $this->getOrderDetailsHTML($data);
        }

        echo '<script>window.onload = function() { window.print(); }</script></body></html>';
        exit;
    }

    public function generateSupplierQuotationPDF($quotation) {
        // Set PDF headers
        header('Content-Type: text/html; charset=utf-8');
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Supplier Purchase Quotation #' . $quotation['id'] . '</title>
            <style>
                body { font-family: Arial; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .total { font-weight: bold; text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Supplier Purchase Quotation</h1>
                <h2>Quotation #' . $quotation['id'] . '</h2>
                <p>Date: ' . date('Y-m-d', strtotime($quotation['created_at'])) . '</p>
            </div>

            <div class="supplier-info">
                <p><strong>Supplier:</strong> ' . htmlspecialchars($quotation['supplier_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($quotation['supplier_contact']) . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Specifications</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>';
                
        foreach ($quotation['items'] as $item) {
            echo '<tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . ($item['type'] == 'coil' ? 
                    'Color: ' . str_replace('_', ' ', ucfirst($item['color'])) . '<br>Thickness: ' . $item['thickness'] 
                    : '') . '</td>
                <td>' . $item['quantity'] . '</td>
                <td>' . htmlspecialchars($item['unit']) . '</td>
                <td>Rs. ' . number_format($item['price'], 2) . '</td>
                <td>Rs. ' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }

        echo '</tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="total">Total Amount:</td>
                        <td>Rs. ' . number_format($quotation['total_amount'], 2) . '</td>
                    </tr>
                </tfoot>
            </table>
            <script>window.print();</script>
        </body>
        </html>';
        exit;
    }

    private function getQuotationHTML($quotation) {
        $html = '<div class="section">
            <h2 style="color: #d4af37; border-bottom: 2px solid #d4af37; padding-bottom: 5px;">Quotation Details</h2>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: left;">Customer Name</th>
                    <td style="border: 1px solid #ddd; padding: 10px;">' . htmlspecialchars($quotation['customer_name']) . '</td>
                </tr>
                <tr>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: left;">Customer Contact</th>
                    <td style="border: 1px solid #ddd; padding: 10px;">' . htmlspecialchars($quotation['customer_contact']) . '</td>
                </tr>
                <tr>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: left;">Type</th>
                    <td style="border: 1px solid #ddd; padding: 10px;">' . ucfirst($quotation['type']) . ' Quotation</td>
                </tr>
                <tr>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: left;">Created Date</th>
                    <td style="border: 1px solid #ddd; padding: 10px;">' . date('Y-m-d', strtotime($quotation['created_at'])) . '</td>
                </tr>
            </table>';
    
        // Add quotation text for order type
        if ($quotation['type'] === 'order') {
            $html .= '<div class="additional-info" style="margin: 20px 0; font-style: italic; color: #555;">
                <div>' . nl2br(htmlspecialchars($quotation['quotation_text'])) . '</div>
            </div>';
        }
    
        // Items table with all details
        $html .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: left;">Item</th>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: left;">Details</th>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: center;">Quantity</th>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: center;">Unit</th>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: right;">Price (Rs.)</th>
                    <th style="background: #d4af37; color: white; padding: 10px; text-align: right;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>';
    
        foreach ($quotation['items'] as $item) {
            $html .= '<tr>
                <td style="border: 1px solid #ddd; padding: 10px;">' . htmlspecialchars($item['name']) . '</td>
                <td style="border: 1px solid #ddd; padding: 10px;">' . ($item['type'] == 'coil' ? 
                    'Color: ' . str_replace('_', ' ', ucfirst($item['color'])) . '<br>Thickness: ' . $item['thickness'] 
                    : '') . '</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">' . number_format($item['quantity'], 2) . '</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">' . htmlspecialchars($item['unit']) . '</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">' . number_format($item['price'], 2) . '</td>
                <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }
    
        $html .= '</tbody>
            <tfoot>
                <tr>
                    <th colspan="5" style="text-align: right; background: #d4af37; color: white; padding: 10px;">Total Amount:</th>
                    <td style="border: 1px solid #ddd; padding: 10px; text-align: right;">Rs. ' . number_format($quotation['total_amount'], 2) . '</td>
                </tr>
            </tfoot>
        </table>';
    
        return $html . '</div>';
    }

    private function getSupplierQuotationHTML($quotation) {
        return '<div class="section">
            <h2>Supplier Details</h2>
            <table>
                <tr>
                    <th>Supplier Name</th>
                    <td>' . htmlspecialchars($quotation['supplier_name']) . '</td>
                </tr>
                <tr>
                    <th>Supplier Contact</th>
                    <td>' . htmlspecialchars($quotation['supplier_contact']) . '</td>
                </tr>
                <tr>
                    <th>Created Date</th>
                    <td>' . date('Y-m-d', strtotime($quotation['created_at'])) . '</td>
                </tr>
            </table>

            <h3>Materials</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Specifications</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Price (Rs.)</th>
                        <th>Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>' .
                implode('', array_map(function($item) {
                    return '<tr>
                        <td>' . htmlspecialchars($item['name']) . '</td>
                        <td>' . ($item['type'] == 'coil' ? 
                            'Color: ' . str_replace('_', ' ', ucfirst($item['color'])) . 
                            '<br>Thickness: ' . $item['thickness'] : '') . '</td>
                        <td>' . number_format($item['quantity'], 2) . '</td>
                        <td>' . htmlspecialchars($item['unit']) . '</td>
                        <td>' . number_format($item['price'], 2) . '</td>
                        <td>' . number_format($item['amount'], 2) . '</td>
                    </tr>';
                }, $quotation['items'])) . '
                <tr class="total-row">
                    <td colspan="5" align="right"><strong>Total Amount:</strong></td>
                    <td><strong>Rs. ' . number_format($quotation['total_amount'], 2) . '</strong></td>
                </tr>
                </tbody>
            </table>
        </div>';
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

    private function generateAdvanceInvoiceHTML($invoice) {
        return '
        <div class="invoice-section">
            <h2>Advance Payment Invoice</h2>
            <div class="customer-details">
                <p><strong>Customer:</strong> ' . htmlspecialchars($invoice['customer_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($invoice['customer_contact']) . '</p>
                <p><strong>Address:</strong> ' . htmlspecialchars($invoice['customer_address']) . '</p>
                <p><strong>Order #:</strong> ' . $invoice['order_id'] . '</p>
            </div>
            
            <div class="payment-details">
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Total Amount:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Rs. ' . number_format($invoice['total_price'], 2) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Amount Paid:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd; color: #ff0000;">Rs. ' . number_format($invoice['amount'], 2) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Balance Amount:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Rs. ' . number_format($invoice['balance_amount'], 2) . '</td>
                    </tr>
                </table>
            </div>
            
            <div style="margin-top: 30px;">
                <p><strong>Created By:</strong> ' . htmlspecialchars($invoice['created_by_name']) . '</p>
            </div>
            
            <div style="margin-top: 50px; display: flex; justify-content: space-between;">
                <div style="text-align: center; width: 200px;">
                    <div>____________________</div>
                    <div>Customer Signature</div>
                </div>
                <div style="text-align: center; width: 200px;">
                    <div>____________________</div>
                    <div>Authorized Signature</div>
                </div>
            </div>
        </div>';
    }

    private function generateFinalInvoiceHTML($invoice) {
        return '
        <div class="invoice-section">
            <h2>Final Payment Invoice</h2>
            <div class="customer-details">
                <p><strong>Customer:</strong> ' . htmlspecialchars($invoice['customer_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($invoice['customer_contact']) . '</p>
                <p><strong>Address:</strong> ' . htmlspecialchars($invoice['customer_address']) . '</p>
                <p><strong>Order #:</strong> ' . $invoice['order_id'] . '</p>
            </div>
            
            <div class="payment-details">
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Advance Paid:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Rs. ' . number_format($invoice['advance_amount'], 2) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Balance Amount Paid:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd; color: #ff0000;">Rs. ' . number_format($invoice['amount'], 2) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Total Amount Paid:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd; color: #ff0000;">Rs. ' . number_format($invoice['total_price'], 2) . '</td>
                    </tr>
                </table>
            </div>
            
            <div style="margin-top: 30px;">
                <p><strong>Created By:</strong> ' . htmlspecialchars($invoice['created_by_name']) . '</p>
            </div>
            
            <div style="margin-top: 50px; display: flex; justify-content: space-between;">
                <div style="text-align: center; width: 200px;">
                    <div>____________________</div>
                    <div>Customer Signature</div>
                </div>
                <div style="text-align: center; width: 200px;">
                    <div>____________________</div>
                    <div>Authorized Signature</div>
                </div>
            </div>
        </div>';
    }
}
