<?php
class InvoiceGenerator {
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function generateInvoicePDF($invoice, $type = 'standard') {
        header('Content-Type: text/html; charset=utf-8');
        
        $logoPath = __DIR__ . '/../assets/images/oricado logo.png';
        $logoHtml = file_exists($logoPath) ? 
            '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                  style="width: 120px; height: auto; margin: 20px 0;">' : '';

        if ($type == 'material') {
            $html = $this->generateMaterialInvoiceHTML($invoice, $logoHtml);
        } else if ($invoice['invoice_type'] == 'advance') {
            $html = $this->generateAdvanceInvoiceHTML($invoice, $logoHtml);
        } else {
            $html = $this->generateFinalInvoiceHTML($invoice, $logoHtml);
        }

        echo '<!DOCTYPE html><html><head>';
        echo '<meta charset="UTF-8">
              <title>Invoice #' . $this->id . '</title>
              <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
              <style>
            body { 
                font-family: "Helvetica Neue", Arial, sans-serif;
                background-color: #f8f9fa;
            }
            .invoice-container { 
                max-width: 800px; 
                margin: 40px auto;
                padding: 30px;
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            /* Header styles */
            .header {
                position: relative;
                padding: 15px 30px;
                border-bottom: 2px solid #d4af37;
                margin-bottom: 20px;
                background: white;
            }

            .logo-and-info {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                width: 100%;
                margin-bottom: 15px;
            }

            .company-logo {
                width: 50%;
                height: 60%;
                object-fit: contain;
                border-radius: 25px; /* Increased border radius */
                transition: transform 0.3s ease;
            }

            .logo-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .company-details {
                text-align: right;
                max-width: 60%;
                margin-left: auto;
            }

            .company-name {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
                color: #2c3e50;
            }

            .company-address {
                font-size: 14px;
                color: #666;
                line-height: 1.4;
            }

            .invoice-title {
                color: #2c3e50;
                font-size: 28px;
                font-weight: 600;
                margin: 20px 0 10px;
            }
            .invoice-number {
                color: #6c757d;
                font-size: 16px;
            }
            .customer-info {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
            }
            table { 
                width: 100%;
                margin: 25px 0;
            }
            .table {
                border: 1px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
            }
            .table th { 
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
                font-weight: 600;
                color: #495057;
            }
            .table td, .table th {
                padding: 12px 15px;
                vertical-align: middle;
            }
            .amount { 
                color: #dc3545;
                font-weight: 600;
            }
            .total-row {
                background-color: #f8f9fa;
                font-weight: 600;
            }
            .footer { 
                margin-top: 50px;
                padding-top: 20px;
                border-top: 2px solid #eee;
            }
            .signatures { 
                display: flex;
                justify-content: space-between;
                margin-top: 60px;
                padding: 20px 0;
            }
            .signature-line { 
                width: 200px;
                text-align: center;
            }
            .signature-line div:first-child {
                border-top: 2px solid #dee2e6;
                padding-top: 10px;
                margin-bottom: 5px;
            }
            .signature-line div:last-child {
                color: #6c757d;
                font-size: 14px;
            }

                /* Print-specific fixes */
                @media print {
                    body { margin: 0; padding: 0; }
                    .invoice-container { box-shadow: none; margin: 0; padding: 20px; }
                    
                    /* Hide URLs when printing */
                    @page {
                        size: auto;
                        margin: 0mm;
                    }
                    
                    a:link:after, 
                    a:visited:after {
                        content: "" !important;
                        display: none !important;
                    }
                    
                    a[href]:after {
                        content: none !important;
                    }
                }

                /* Header and footer styles */
                .page-footer {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: #fff;
                    border-top: 2px solid #d4af37;
                    padding: 8px 20px;
                    font-size: 14px;
                    box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
                    z-index: 100;
                    height: 40px;
                    display: flex;
                    align-items: center;
                }

                .page-footer > div {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    width: 100%;
                    max-width: 1000px;
                }

                .page-footer a {
                    color: #4169E1;
                    text-decoration: none;
                    margin: 0 15px;
                    font-weight: 500;
                }

                .tagline {
                    color: #b8860b;
                    font-size: 16px;
                    font-weight: 600;
                    margin-top: 10px;
                    font-style: italic;
                    text-transform: capitalize;
                    letter-spacing: 1.5px; /* Increased letter spacing */
                    line-height: 1.4;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.15);
                    font-family: "Georgia", serif;
                    word-spacing: 3px; /* Add word spacing */
                }
              </style>
        </head>';

        // Add footer HTML
        $html .= '<div class="page-footer">
            <div>
                <div style="display: flex; gap: 20px;">
                    <a href="mailto:info@oricado.lk">📧 info@oricado.lk</a>
                    <a href="http://www.oricado.lk">🌐 www.oricado.lk</a>
                    <a href="tel:+94112270588">📞 +94 112 270 588</a>
                </div>
                <div style="color: #999; font-size: 12px;">
                    Generated on ' . date('d/m/Y H:i') . '
                </div>
            </div>
        </div>';

        echo $html . '<script>window.onload = function() { window.print(); }</script></body></html>';
        exit;
    }

    public function generateSupplierInvoicePDF($invoice) {
        $logoHtml = $this->getLogoHtml();
        $html = '
        <div class="invoice-container">
            <div class="header">
                ' . $logoHtml . '
                <h2>Purchase Invoice</h2>
                <p>Invoice #' . $this->id . '</p>
                <p>Date: ' . date('Y-m-d') . '</p>
            </div>

            <div class="supplier-info">
                <p><strong>Supplier:</strong> ' . htmlspecialchars($invoice['supplier_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($invoice['supplier_contact']) . '</p>
                <p><strong>Created By:</strong> ' . htmlspecialchars($invoice['created_by_name']) . '</p>
            </div>

            <table>
                <tr>
                    <th>Material</th>
                    <th>Specifications</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>
                ' . $this->generateMaterialItemsRows($invoice['items']) . '
                <tr class="total-row">
                    <td colspan="5" align="right"><strong>Total Amount:</strong></td>
                    <td class="amount">Rs. ' . number_format($invoice['total_amount'], 2) . '</td>
                </tr>
            </table>

            <div class="footer">
                <div class="signatures">
                    <div class="signature-line">
                        
                        <div>Supplier Signature</div>
                    </div>
                    <div class="signature-line">
                        
                        <div>Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>';

        $this->outputPDF($html);
    }

    private function generateAdvanceInvoiceHTML($invoice, $logoHtml) {
        return '
        <div class="invoice-container">
            <div class="header">
                <div class="logo-and-info">
                    ' . $logoHtml . '
                    <div class="company-details">
                        <div class="company-name">Oricado Roller Doors</div>
                        <div class="company-address">
                            456/A/1 MDH Jayawardhana Mawatha,<br>
                            Kaduwela 10640<br>
                            Phone: 0112 270 588
                        </div>
                    </div>
                </div>
            </div>

            <div class="customer-info">
                <p><strong>Customer:</strong> ' . htmlspecialchars($invoice['customer_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($invoice['customer_contact']) . '</p>
                <p><strong>Address:</strong> ' . htmlspecialchars($invoice['customer_address']) . '</p>
                <p><strong>Order #:</strong> ' . $invoice['order_id'] . '</p>
            </div>

            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Total Amount</td>
                    <td>Rs. ' . number_format($invoice['total_price'], 2) . '</td>
                </tr>
                <tr>
                    <td>Advance Payment</td>
                    <td class="amount">Rs. ' . number_format($invoice['amount'], 2) . '</td>
                </tr>
                <tr>
                    <td>Balance Amount</td>
                    <td>Rs. ' . number_format($invoice['balance_amount'], 2) . '</td>
                </tr>
            </table>

            <div class="footer">
                <p>Created by: ' . htmlspecialchars($invoice['created_by_name']) . '</p>
                <div class="signatures">
                    <div class="signature-line">
                        
                        <div>Customer Signature</div>
                    </div>
                    <div class="signature-line">
                        
                        <div>Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function generateFinalInvoiceHTML($invoice, $logoHtml) {
        return '
        <div class="invoice-container">
            <div class="header">
                <div class="logo-and-info">
                    ' . $logoHtml . '
                    <div class="company-details">
                        <div class="company-name">Oricado Roller Doors</div>
                        <div class="company-address">
                            456/A/1 MDH Jayawardhana Mawatha,<br>
                            Kaduwela 10640<br>
                            Phone: 0112 270 588
                        </div>
                    </div>
                </div>
            </div>

            <div class="customer-info">
                <p><strong>Customer:</strong> ' . htmlspecialchars($invoice['customer_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($invoice['customer_contact']) . '</p>
                <p><strong>Address:</strong> ' . htmlspecialchars($invoice['customer_address']) . '</p>
                <p><strong>Order #:</strong> ' . $invoice['order_id'] . '</p>
            </div>

            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Total Order Amount</td>
                    <td>Rs. ' . number_format($invoice['total_price'], 2) . '</td>
                </tr>
                <tr>
                    <td>Advance Paid</td>
                    <td>Rs. ' . number_format($invoice['advance_amount'], 2) . '</td>
                </tr>
                <tr>
                    <td>Balance Payment</td>
                    <td class="amount">Rs. ' . number_format($invoice['amount'], 2) . '</td>
                </tr>
                <tr>
                    <td><strong>Total Amount Paid</strong></td>
                    <td class="amount">Rs. ' . number_format($invoice['total_price'], 2) . '</td>
                </tr>
            </table>

            <div class="footer">
                <p>Created by: ' . htmlspecialchars($invoice['created_by_name']) . '</p>
                <div class="signatures">
                    <div class="signature-line">
                       
                        <div>Customer Signature</div>
                    </div>
                    <div class="signature-line">
                        
                        <div>Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function generateMaterialInvoiceHTML($invoice, $logoHtml) {
        return '
        <div class="invoice-container">
            <div class="header">
                <div class="logo-and-info">
                    ' . $logoHtml . '
                    <div class="company-details">
                        <div class="company-name">Oricado Roller Doors</div>
                        <div class="company-address">
                            456/A/1 MDH Jayawardhana Mawatha,<br>
                            Kaduwela 10640<br>
                            Phone: 0112 270 588
                        </div>
                    </div>
                </div>
            </div>

            <div class="customer-info">
                <p><strong>Customer:</strong> ' . htmlspecialchars($invoice['customer_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($invoice['customer_contact']) . '</p>
            </div>

            <table>
                <tr>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>
                ' . $this->generateSimpleMaterialRows($invoice['items']) . '
                <tr>
                    <td colspan="4" align="right"><strong>Total Amount:</strong></td>
                    <td class="amount">Rs. ' . number_format($invoice['total_amount'], 2) . '</td>
                </tr>
            </table>

            <div class="footer">
                <p>Created by: ' . htmlspecialchars($invoice['created_by_name']) . '</p>
                <div class="signatures">
                    <div class="signature-line">
                       
                        <div>Customer Signature</div>
                    </div>
                    <div class="signature-line">
                        
                        <div>Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function generateSimpleMaterialRows($items) {
        $rows = '';
        foreach ($items as $item) {
            $rows .= '<tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . number_format($item['quantity'], 2) . '</td>
                <td>' . htmlspecialchars($item['unit']) . '</td>
                <td>Rs. ' . number_format($item['price'], 2) . '</td>
                <td>Rs. ' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }
        return $rows;
    }

    private function generateMaterialItemsRows($items) {
        $rows = '';
        foreach ($items as $item) {
            $specs = '';
            if ($item['type'] == 'coil') {
                $specs = 'Color: ' . str_replace('_', ' ', ucfirst($item['color'])) . 
                        '<br>Thickness: ' . $item['thickness'];
            }
            
            $rows .= '<tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . $specs . '</td>
                <td>' . number_format($item['quantity'], 2) . '</td>
                <td>' . htmlspecialchars($item['unit']) . '</td>
                <td>Rs. ' . number_format($item['price'], 2) . '</td>
                <td>Rs. ' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }
        return $rows;
    }

    private function getLogoHtml() {
        $logoPath = __DIR__ . '/../assets/images/oricado logo.png';
        return file_exists($logoPath) ? 
            '<div class="logo-container">
                <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                     alt="Oricado Logo" 
                     class="company-logo">
                <div class="tagline">SSttrreennggtthh,, SSttyyllee,, AAnndd SSeeccuurriittyy IInn EEvveerryy RRoolll</div>
            </div>' : '';
    }

    private function outputPDF($html) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head>';
        echo '<meta charset="UTF-8">
              <title>Invoice #' . $this->id . '</title>
              <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
              <style>
            body { 
                font-family: "Helvetica Neue", Arial, sans-serif;
                background-color: #f8f9fa;
            }
            .invoice-container { 
                max-width: 800px; 
                margin: 40px auto;
                padding: 30px;
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            /* Header styles */
            .header {
                position: relative;
                padding: 15px 30px;
                border-bottom: 2px solid #d4af37;
                margin-bottom: 20px;
                background: white;
            }

            .logo-and-info {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                width: 100%;
                margin-bottom: 15px;
            }

            .company-logo {
                width: 50%;
                height: 60%;
                object-fit: contain;
                border-radius: 25px; /* Increased border radius */
                transition: transform 0.3s ease;
            }

            .logo-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .company-details {
                text-align: right;
                max-width: 60%;
                margin-left: auto;
            }

            .company-name {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
                color: #2c3e50;
            }

            .company-address {
                font-size: 14px;
                color: #666;
                line-height: 1.4;
            }

            .invoice-title {
                color: #2c3e50;
                font-size: 28px;
                font-weight: 600;
                margin: 20px 0 10px;
            }
            .invoice-number {
                color: #6c757d;
                font-size: 16px;
            }
            .customer-info {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
            }
            table { 
                width: 100%;
                margin: 25px 0;
            }
            .table {
                border: 1px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
            }
            .table th { 
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
                font-weight: 600;
                color: #495057;
            }
            .table td, .table th {
                padding: 12px 15px;
                vertical-align: middle;
            }
            .amount { 
                color: #dc3545;
                font-weight: 600;
            }
            .total-row {
                background-color: #f8f9fa;
                font-weight: 600;
            }
            .footer { 
                margin-top: 50px;
                padding-top: 20px;
                border-top: 2px solid #eee;
            }
            .signatures { 
                display: flex;
                justify-content: space-between;
                margin-top: 60px;
                padding: 20px 0;
            }
            .signature-line { 
                width: 200px;
                text-align: center;
            }
            .signature-line div:first-child {
                border-top: 2px solid #dee2e6;
                padding-top: 10px;
                margin-bottom: 5px;
            }
            .signature-line div:last-child {
                color: #6c757d;
                font-size: 14px;
            }

                /* Print-specific fixes */
                @media print {
                    body { margin: 0; padding: 0; }
                    .invoice-container { box-shadow: none; margin: 0; padding: 20px; }
                    
                    /* Hide URLs when printing */
                    @page {
                        size: auto;
                        margin: 0mm;
                    }
                    
                    a:link:after, 
                    a:visited:after {
                        content: "" !important;
                        display: none !important;
                    }
                    
                    a[href]:after {
                        content: none !important;
                    }
                }

                /* Header and footer styles */
                .page-footer {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: #fff;
                    border-top: 2px solid #d4af37;
                    padding: 8px 20px;
                    font-size: 14px;
                    box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
                    z-index: 100;
                    height: 40px;
                    display: flex;
                    align-items: center;
                }

                .page-footer > div {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    width: 100%;
                    max-width: 1000px;
                }

                .page-footer a {
                    color: #4169E1;
                    text-decoration: none;
                    margin: 0 15px;
                    font-weight: 500;
                }

                .tagline {
                    color: #b8860b;
                    font-size: 16px;
                    font-weight: 600;
                    margin-top: 10px;
                    font-style: italic;
                    text-transform: capitalize;
                    letter-spacing: 1.5px; /* Increased letter spacing */
                    line-height: 1.4;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.15);
                    font-family: "Georgia", serif;
                    word-spacing: 3px; /* Add word spacing */
                }
              </style>
        </head>';

        // Add footer HTML
        $html .= '<div class="page-footer">
            <div>
                <div style="display: flex; gap: 20px;">
                    <a href="mailto:info@oricado.lk">📧 info@oricado.lk</a>
                    <a href="http://www.oricado.lk">🌐 www.oricado.lk</a>
                    <a href="tel:+94112270588">📞 +94 112 270 588</a>
                </div>
                <div style="color: #999; font-size: 12px;">
                    Generated on ' . date('d/m/Y H:i') . '
                </div>
            </div>
        </div>';

        echo $html . '<script>window.onload = function() { window.print(); }</script></body></html>';
        exit;
    }
}
