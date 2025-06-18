<?php
class InvoiceGenerator {
    private $id;
    private $logoPath;  // Add this property

    public function __construct($id) {
        $this->id = $id;
        $this->logoPath = __DIR__ . '/../assets/images/oricado logo.png';  // Set logo path in constructor
    }

    public function generateInvoicePDF($invoice, $type = 'standard') {
        header('Content-Type: text/html; charset=utf-8');
        
        $logoHtml = file_exists($this->logoPath) ? 
            '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($this->logoPath)) . '" 
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
                width: 200px;
                height: auto;
                border-radius: 25px;
                object-fit: contain;
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
                border: 2px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 40px;
                border-collapse: collapse;
                font-size: 13px; /* Smaller font size */
            }
            .table th, .table td {
                border: none;
                border-bottom: 1px solid #dee2e6;
                padding: 8px 10px; /* Reduced padding */
                vertical-align: middle;
            }
            .table thead th {
                background-color: #2c3e50;
                color: white;
                font-weight: 500;
                border-bottom: 2px solid #2c3e50;
                white-space: nowrap; /* Prevent header wrapping */
            }
            .table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .table tbody tr:hover {
                background-color: #f0f2f5;
            }
            .table tfoot tr {
                border-top: 2px solid #dee2e6;
                background-color: #fff;
            }
            .table tfoot tr:last-child {
                font-weight: bold;
                background-color: #f8f9fa;
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
                    @page {
                        margin: 0;
                        size: A4;
                    }
                    
                    body { 
                        margin: 0;
                        padding: 0 20px 60px 20px;
                        height: 100%;
                        position: relative;
                    }
                    
                    .invoice-container {
                        margin: 0;
                        padding: 20px 20px 100px 20px; /* Increased bottom padding */
                    }
                    
                    .page-footer {
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        background-color: white !important;
                        border-top: 2px solid #d4af37 !important;
                        padding: 8px 20px !important;
                        margin: 0 !important;
                        height: 40px !important;
                        z-index: 1000 !important;
                        display: block !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    .footer * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    .table {
                        page-break-inside: avoid;
                        font-size: 11px; /* Even smaller for print */
                    }
                    
                    .table th, .table td {
                        padding: 6px 8px; /* Further reduced padding for print */
                    }

                    /* Force footer to show */
                    .page-footer * {
                        visibility: visible !important;
                        display: flex !important;
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
                    font-family: "Georgia", serif;
                }

                .suggestions-dropdown {
                    position: absolute;
                    width: 100%;
                    background: white;
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
                    z-index: 1050; /* Increased z-index to appear above other elements */
                    max-height: 200px;
                    overflow-y: auto;
                }

                .suggestions-dropdown div {
                    padding: 0.5rem 1rem;
                    cursor: pointer;
                }

                .suggestions-dropdown div:hover {
                    background-color: #f8f9fa;
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

    private function formatInvoiceNumber($invoiceId, $createdAt) {
        $date = new DateTime($createdAt);
        return sprintf(
            "INV/%s/%s/%s/%05d",
            $date->format('d'),
            $date->format('m'),
            $date->format('y'),
            $invoiceId
        );
    }

    private function formatOrderNumber($orderId, $createdAt) {
        $date = new DateTime($createdAt);
        return sprintf(
            "SO/%s/%s/%s/%05d",
            $date->format('d'),
            $date->format('m'),
            $date->format('y'),
            $orderId
        );
    }

    private function generateAdvanceInvoiceHTML($invoice, $logoHtml) {
        return '
        <div class="invoice-container">
            <div class="header">
                <div class="logo-and-info">
                    <div class="logo-container">
                        <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($this->logoPath)) . '" 
                             alt="Oricado Logo" 
                             class="company-logo">
                        <div class="tagline">Strength Style And Security In Every Roll</div>
                    </div>
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
                <p><strong>Invoice #:</strong> ' . $this->formatInvoiceNumber($invoice['id'], $invoice['invoice_created_at']) . '</p>
                <p><strong>Order #:</strong> ' . $this->formatOrderNumber($invoice['order_id'], $invoice['order_created_at']) . '</p>
                <p><strong>Created By:</strong> ' . htmlspecialchars($invoice['created_by_name']) . '</p>
            </div>

            <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>Specifications</th>
                    <th>Quantity</th>
                    <th>Unit Price (Rs.)</th>
                    <th>Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                ' . $this->generateOrderItemsRows($invoice) . '
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;"><strong>Total Amount:</strong></td>
                    <td style="text-align: right;">Rs. ' . number_format($invoice['total_price'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right; color: #28a745;">Amount Paid (Advance):</td>
                    <td style="text-align: right; color: #28a745;">Rs. ' . number_format($invoice['amount'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;">Balance Amount:</td>
                    <td style="text-align: right;">Rs. ' . number_format($invoice['balance_amount'], 2) . '</td>
                </tr>
            </tfoot>
        </table>

            <div class="footer">
                <div class="signature-block" style="margin-top: 50px;">
                    <div class="signature-line">Customer Signature: _______________________</div>
                    <div class="signature-line">Authorized Signature: _______________________</div>
                </div>
            </div>
        </div>';
    }

    private function generateFinalInvoiceHTML($invoice, $logoHtml) {
        return '
        <div class="invoice-container">
            <div class="header">
                <div class="logo-and-info">
                    <div class="logo-container">
                        <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($this->logoPath)) . '" 
                             alt="Oricado Logo" 
                             class="company-logo">
                        <div class="tagline">Strength Style And Security In Every Roll</div>
                    </div>
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
                <p><strong>Invoice #:</strong> ' . $this->formatInvoiceNumber($invoice['id'], $invoice['invoice_created_at']) . '</p>
                <p><strong>Order #:</strong> ' . $this->formatOrderNumber($invoice['order_id'], $invoice['order_created_at']) . '</p>
                <p><strong>Created By:</strong> ' . htmlspecialchars($invoice['created_by_name']) . '</p>
            </div>

            <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th>Specifications</th>
                <th>Quantity</th>
                <th>Unit Price (Rs.)</th>
                <th>Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            ' . $this->generateOrderItemsRows($invoice) . '
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">Advance Paid:</td>
                <td style="text-align: right;">Rs. ' . number_format($invoice['advance_amount'], 2) . '</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right; color: #ff0000;">Balance Amount Paid:</td>
                <td style="text-align: right; color: #ff0000;">Rs. ' . number_format($invoice['amount'], 2) . '</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">Total Amount Paid:</td>
                <td style="text-align: right; font-weight: bold;">Rs. ' . number_format($invoice['total_price'], 2) . '</td>
            </tr>
        </tfoot>
    </table>

            <div class="footer">
                <div class="signature-block" style="margin-top: 50px;">
                    <div class="signature-line">Customer Signature: _______________________</div>
                    <div class="signature-line">Authorized Signature: _______________________</div>
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
                <div class="signature-block" style="margin-top: 50px;">
                    <div class="signature-line">Customer Signature: _______________________</div>
                    <div class="signature-line">Authorized Signature: _______________________</div>
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
        return file_exists($this->logoPath) ? 
            '<div class="logo-container">
                <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($this->logoPath)) . '" 
                     alt="Oricado Logo" 
                     class="company-logo">
                <div class="tagline">Strength Style And Security In Every Roll</div>
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
                width: 200px;
                height: auto;
                border-radius: 25px;
                object-fit: contain;
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
                border: 2px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 40px;
                border-collapse: collapse;
                font-size: 13px; /* Smaller font size */
            }
            .table th, .table td {
                border: none;
                border-bottom: 1px solid #dee2e6;
                padding: 8px 10px; /* Reduced padding */
                vertical-align: middle;
            }
            .table thead th {
                background-color: #2c3e50;
                color: white;
                font-weight: 500;
                border-bottom: 2px solid #2c3e50;
                white-space: nowrap; /* Prevent header wrapping */
            }
            .table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .table tbody tr:hover {
                background-color: #f0f2f5;
            }
            .table tfoot tr {
                border-top: 2px solid #dee2e6;
                background-color: #fff;
            }
            .table tfoot tr:last-child {
                font-weight: bold;
                background-color: #f8f9fa;
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
                    @page {
                        margin: 0;
                        size: A4;
                    }
                    
                    body { 
                        margin: 0;
                        padding: 0 20px 60px 20px;
                        height: 100%;
                        position: relative;
                    }
                    
                    .invoice-container {
                        margin: 0;
                        padding: 20px 20px 100px 20px; /* Increased bottom padding */
                    }
                    
                    .page-footer {
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        background-color: white !important;
                        border-top: 2px solid #d4af37 !important;
                        padding: 8px 20px !important;
                        margin: 0 !important;
                        height: 40px !important;
                        z-index: 1000 !important;
                        display: block !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    .footer * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    .table {
                        page-break-inside: avoid;
                        font-size: 11px; /* Even smaller for print */
                    }
                    
                    .table th, .table td {
                        padding: 6px 8px; /* Further reduced padding for print */
                    }

                    /* Force footer to show */
                    .page-footer * {
                        visibility: visible !important;
                        display: flex !important;
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
                    font-family: "Georgia", serif;
                }

                .suggestions-dropdown {
                    position: absolute;
                    width: 100%;
                    background: white;
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
                    z-index: 1050; /* Increased z-index to appear above other elements */
                    max-height: 200px;
                    overflow-y: auto;
                }

                .suggestions-dropdown div {
                    padding: 0.5rem 1rem;
                    cursor: pointer;
                }

                .suggestions-dropdown div:hover {
                    background-color: #f8f9fa;
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

    public function generateOrderItemsRows($invoice) {
        global $conn;
        
        // Get the quotation_id from orders table
        $sql = "SELECT quotation_id FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $invoice['order_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if (!$order || !$order['quotation_id']) {
            return '<tr><td colspan="6">No items found</td></tr>';
        }

        // Get items from quotation_items table
        $sql = "SELECT qi.name, qi.quantity, qi.unit, qi.price, qi.amount 
                FROM quotation_items qi 
                WHERE qi.quotation_id = ?";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order['quotation_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $quotationItems = $result->fetch_all(MYSQLI_ASSOC);

        if (empty($quotationItems)) {
            return '<tr><td colspan="6">No items found</td></tr>';
        }

        $rows = '';
        $counter = 1;
        
        foreach ($quotationItems as $item) {
            $rows .= '<tr>
                <td>' . $counter++ . '</td>
                <td><strong>' . htmlspecialchars($item['name']) . '</strong></td>
                <td></td>
                <td style="text-align: center;">' . number_format($item['quantity'], 2) . '</td>
                <td style="text-align: right;">' . number_format($item['price'], 2) . '</td>
                <td style="text-align: right;">' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }
        
        return $rows;
    }
}
