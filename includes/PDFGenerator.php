<?php
class PDFGenerator {
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function generatePDF($data, $type = 'order', $showSignature = false) {
        header('Content-Type: text/html; charset=utf-8');
        
        $logoPath = __DIR__ . '/../assets/images/oricado logo.png';
        $logoHtml = file_exists($logoPath) ? 
        '<div class="logo-container">
            <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                 alt="Oricado Logo" 
                 class="company-logo">
            <div class="tagline">Strength, Style, and Security in Every Roll</div>
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
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
 

body { 
    font-family: "Helvetica Neue", Arial, sans-serif;
    background-color: #f0f2f5;
    line-height: 1.6;
    color: #333;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    position: relative;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    border: 1px solid #ddd;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    min-height: calc(100vh - 40px); /* Reduced to account for footer */
    position: relative;
    padding-bottom: 50px; /* Space for footer */
}

.content-area {
    position: relative;
    z-index: 1; /* Ensure content stays above footer */
    margin-bottom: 50px; /* Clear space for footer */
}

.header {
    position: relative;
    padding: 15px 30px;
    border-bottom: 2px solid #d4af37;
    margin-bottom: 20px;
    background: white;
}

.content-area {
    margin-top: 10px;
    padding: 0 20px;
    margin-bottom: 80px; /* Ensure space before footer */
}

.content-section {
    margin-top: 10px;
    margin-bottom: 60px; /* Space before footer */
    padding-bottom: 20px;
}

.quotation-content {
    padding: 20px 0; /* Remove horizontal padding since content-area has it */
    margin-top: 10px;
    margin-bottom: 60px; /* Space before footer */
}

/* Document meta styling */
.document-meta {
    text-align: center;
    padding: 15px 0;
    border-top: 1px solid #eee;
    margin-bottom: 20px;
}

.quotation-number {
    font-size: 28px;
    color: #d4af37;
    font-weight: bold;
    margin-bottom: 5px;
}

.document-date {
    font-size: 14px;
    color: #666;
}

/* Quotation details grid */
.quotation-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px 30px;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.quotation-detail-item {
    display: flex;
    gap: 10px;
}

.detail-label {
    font-weight: 600;
    color: #555;
    min-width: 120px;
}

/* Table styling */
.table {
    margin: 25px 0;
    border: 1px solid #dee2e6;
    width: 100%;
}

.table thead th {
    background: #2c3e50;
    color: white;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 14px;
    padding: 12px 15px;
}

.table tbody td {
    padding: 12px 15px;
    vertical-align: middle;
}

.table tfoot {
    background: #f8f9fa;
    font-weight: bold;
}

.table tfoot td {
    padding: 15px;
}

/* Quotation note */
.quotation-note {
    margin: 25px 0;
    padding: 20px;
    background: #f8f9fa;
    border-left: 4px solid #d4af37;
    border-radius: 0 8px 8px 0;
}

/* FIXED: Page break and terms page content */
.page-break {
    page-break-before: always;
    min-height: calc(100vh - 200px); /* Ensure minimum height */
    position: relative;
    padding-bottom: 100px; /* Space for footer */
}

/* Terms page header - same as first page */
.page-break .header {
    margin-top: 0;
    margin-bottom: 20px;
    padding: 15px 30px;
    border-bottom: 2px solid #d4af37;
    background: white;
}

/* FIXED: Terms page content container */
.page-break .quotation-content {
    padding: 0 30px; /* Match header padding */
    margin-top: 0;
    margin-bottom: 80px;
    min-height: 400px; /* Ensure content area */
}

/* Terms and conditions content styling */
.quotation-section {
    margin-bottom: 25px;
}

.quotation-title {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 15px;
    text-transform: uppercase;
    border-bottom: 2px solid #d4af37;
    padding-bottom: 5px;
}

.quotation-subtitle {
    font-weight: 600;
    color: #555;
    margin: 15px 0 10px;
    font-size: 16px;
}

.quotation-list {
    list-style-type: disc;
    padding-left: 25px;
    margin-bottom: 15px;
}

.quotation-list li {
    margin-bottom: 8px;
    line-height: 1.6;
}

/* Bank details styling */
.bank-details {
    margin: 25px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.bank-details .quotation-title {
    color: #d4af37;
    margin-bottom: 15px;
}

/* Signature block */
.signature-block {
    margin: 40px 0;
    padding: 20px;
    border-top: 2px solid #d4af37;
}

/* FIXED: Footer positioning */
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
    z-index: 100; /* Keep footer above content */
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

.page-footer a:hover {
    text-decoration: underline;
}

    .logo-and-info {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        width: 100%;
        margin-bottom: 20px;
        text-align: left;
    }

 

.company-logo {
    width: 50%;
    height: 60%;
    object-fit: contain;
    border-radius: 15px; /* Round the actual image corners */
    transition: transform 0.3s ease; /* Optional: Add hover effect */
}

/* Optional: Add hover effect for more professional look */
.company-logo:hover {
    transform: scale(1.02);
}

.logo-and-info {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 15px;
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

/* New tagline styles */
.logo-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.tagline {
    color: #b8860b; /* Darker, richer gold color */
    font-size: 16px;
    font-weight: 600;
    margin-top: 10px;
    font-style: italic;
    text-transform: capitalize;
    letter-spacing: 0.5px;
    line-height: 1.4;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.15);
    font-family: "Georgia", serif; /* More elegant font */
}

/* Print-specific fixes */
@media print {
    body {
        margin: 0;
        padding: 0;
    }
    
    .container {
        box-shadow: none;
        border: none;
        padding-bottom: 40px;
        min-height: auto;
    }
    
    .content-area {
        margin-bottom: 40px;
    }
    
    .content-section {
        margin-bottom: 20px;
    }
    
    .quotation-content {
        margin-bottom: 40px;
    }
    
    .page-break {
        page-break-before: always;
        padding-bottom: 60px;
    }
    
    .page-break .quotation-content {
        margin-bottom: 40px;
    }
    
    .page-footer {
        position: fixed;
        height: 20px; /* Reduced from 30px */
        padding: 5px 10px; /* Removed top/bottom padding */
        font-size: 10px; /* Smaller font size */
        line-height: 20px; /* Match height for vertical centering */
    }
    
    /* Prevent page breaks inside content */
    .quotation-section,
    .bank-details,
    .signature-block {
        page-break-inside: avoid;
    }
    
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

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .container {
        margin: 0 10px;
        padding-bottom: 120px;
    }
    
    .content-area {
        padding: 0 15px;
    }
    
    .page-break .quotation-content {
        padding: 0 15px;
    }
    
    .quotation-details {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .logo-and-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .company-details {
        text-align: center;
        max-width: 100%;
        margin: 15px 0 0 0;
    }
    
    .page-footer > div {
        flex-direction: column;
        gap: 10px;
    }
    
    .page-footer > div > div:first-child {
        flex-wrap: wrap;
        justify-content: center;
    }
}
    </style>
        </head>
        <body>
           <div class="container">
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
            <div class="content-area">
                <div class="section">';

        switch ($type) {
            case 'supplier_quotation':
                echo $this->getSupplierQuotationHTML($data);
                break;
            case 'quotation':
                echo $this->getQuotationHTML($data, $title);
                break;
            case 'invoice_advance':
                echo $this->generateAdvanceInvoiceHTML($data);
                break;
            case 'invoice_final':
                echo $this->generateFinalInvoiceHTML($data);
                break;
            default:
                echo $this->getOrderDetailsHTML($data, $showSignature);
        }

        echo '</div></div><script>window.onload = function() { window.print(); }</script></body></html>';
        exit;
    }

    private function formatQuotationNumber($quotationId, $createdAt) {
        $date = new DateTime($createdAt);
        return sprintf(
            "QT/%s/%s/%s/%05d",
            $date->format('d'),
            $date->format('m'),
            $date->format('y'),
            $quotationId
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

  private function getQuotationHTML($quotation, $docTitle) {
    // Get logo HTML for consistent use
    $logoPath = __DIR__ . '/../assets/images/oricado logo.png';
    $logoHtml = file_exists($logoPath) ? 
        '<div class="logo-container">
            <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                 alt="Oricado Logo" 
                 class="company-logo">
            <div class="tagline">Strength, Style, and Security in Every Roll</div>
        </div>' : '';

    $html = '<div class="content-section">';
    
    // Document meta information with professional styling
    $html .= '<div class="document-meta">
        <div class="quotation-number">Quotation #' . $this->formatQuotationNumber($this->id, $quotation['created_at']) . '</div>
        <div class="document-date">' . date('d F Y', strtotime($quotation['created_at'])) . '</div>
    </div>';

    // If order exists, show order number
    if (isset($quotation['order_id'])) {
        $html .= '<div class="order-number">Order #' . $this->formatOrderNumber($quotation['order_id'], $quotation['order_created_at']) . '</div>';
    }

    // Customer and quotation details in professional grid layout
    $html .= '<div class="quotation-details">
        <div class="quotation-detail-item">
            <span class="detail-label">Customer Name:</span>
            <span>' . htmlspecialchars($quotation['customer_name']) . '</span>
        </div>
        <div class="quotation-detail-item">
            <span class="detail-label">Contact Number:</span>
            <span>' . htmlspecialchars($quotation['customer_contact']) . '</span>
        </div>
        <div class="quotation-detail-item">
            <span class="detail-label">Quotation Date:</span>
            <span>' . date('d/m/Y', strtotime($quotation['created_at'])) . '</span>
        </div>
        <div class="quotation-detail-item">
            <span class="detail-label">Valid Until:</span>
            <span>' . date('d/m/Y', strtotime($quotation['created_at'] . ' + 7 days')) . '</span>
        </div>
        <div class="quotation-detail-item">
            <span class="detail-label">Prepared By:</span>
            <span>' . htmlspecialchars($quotation['prepared_by_name']) . '</span>
        </div>
        <div class="quotation-detail-item">
            <span class="detail-label">Reference:</span>
            <span>QT-' . str_pad($this->id, 4, '0', STR_PAD_LEFT) . '</span>
        </div>
    </div>';

    // Professional items table
    $html .= '<table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Item Description</th>
                <th style="width: 20%;">Specifications</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 10%; text-align: center;">Unit</th>
                <th style="width: 15%; text-align: right;">Unit Price (Rs.)</th>
                <th style="width: 15%; text-align: right;">Total Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>';

    $itemCounter = 1;
    foreach ($quotation['items'] as $item) {
        $specifications = '';
        if ($item['type'] == 'coil') {
            $specifications = 'Color: ' . str_replace('_', ' ', ucwords($item['color'])) . '<br>Thickness: ' . $item['thickness'] . 'mm';
        }
        
        $html .= '<tr>
            <td style="text-align: center; font-weight: 600;">' . $itemCounter++ . '</td>
            <td><strong>' . htmlspecialchars($item['name']) . '</strong></td>
            <td style="font-size: 12px; color: #666;">' . $specifications . '</td>
            <td style="text-align: center;">' . number_format($item['quantity'], 2) . '</td>
            <td style="text-align: center;">' . htmlspecialchars($item['unit']) . '</td>
            <td style="text-align: right;">' . number_format($item['price'], 2) . '</td>
            <td style="text-align: right; font-weight: 600;">' . number_format($item['amount'], 2) . '</td>
        </tr>';
    }

    // Professional totals section
    $subtotal = $quotation['total_amount'];
    $html .= '</tbody>
        <tfoot>
            <tr style="border-top: 2px solid #dee2e6;">
                <th colspan="6" style="text-align: right; padding-top: 15px;">Subtotal:</th>
                <th style="text-align: right; padding-top: 15px;">Rs. ' . number_format($subtotal, 2) . '</th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: right; font-size: 18px; color: #d4af37;">TOTAL AMOUNT:</th>
                <th style="text-align: right; font-size: 18px; color: #d4af37;">Rs. ' . number_format($quotation['total_amount'], 2) . '</th>
            </tr>
        </tfoot>
    </table>';

    // Professional note section
   

    $html .= '</div>'; // Close first page content-section

    // FIXED: Terms and conditions page with proper content structure
    if (!empty($quotation['quotation_text'])) {
        $html .= '<div class="page-break">';
        
        // Include header with logo for terms page
        $html .= '<div class="header">
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
            <div class="document-meta">
                <div class="document-title" style="font-size: 20px; font-weight: bold; color: #2c3e50;">Terms & Conditions</div>
                <div class="document-date">Quotation #' . $this->id . '</div>
            </div>
        </div>';
        
        // FIXED: Terms content with proper container
        $html .= '<div class="quotation-content">';
        $html .= $this->formatQuotationText($quotation['quotation_text']);
        $html .= '</div>'; // Close quotation-content
        
        $html .= '</div>'; // Close page-break
    }

    // Professional footer
    $html .= '<div class="page-footer">
        <div>
            <div style="display: flex; gap: 20px;">
                <a href="mailto:info@oricado.lk">
                    📧 info@oricado.lk
                </a>
                <a href="http://www.oricado.lk">
                    🌐 www.oricado.lk
                </a>
                <a href="tel:+94112270588">
                    📞 +94 112 270 588
                </a>
            </div>
            <div style="color: #999; font-size: 12px;">
                Generated on ' . date('d/m/Y H:i') . '
            </div>
        </div>
    </div>';

    return $html;
}
    private function formatQuotationText($text) {
        $sections = preg_split('/\n\s*\n/', $text);
        $html = '';
        
        foreach ($sections as $section) {
            $lines = explode("\n", trim($section));
            
            if (preg_match('/^[A-Z\s&]+:?$/', trim($lines[0]))) {
                $html .= '<div class="quotation-section">';
                $html .= '<div class="quotation-title">' . trim($lines[0]) . '</div>';
                array_shift($lines);
                
                $html .= '<ul class="quotation-list">';
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        if (strpos($line, ':') !== false && ctype_upper(substr($line, 0, 1))) {
                            if (!empty($currentSubsection)) {
                                $html .= '</ul>';
                            }
                            $html .= '<div class="quotation-subtitle">' . $line . '</div>';
                            $html .= '<ul class="quotation-list">';
                        } else {
                            $line = preg_replace('/^[\s•-]+/', '', $line);
                            $html .= '<li>' . $line . '</li>';
                        }
                    }
                }
                
                $html .= '</ul></div>';
            } elseif (strpos(strtoupper($lines[0]), 'BANK DETAILS') !== false) {
                $html .= '<div class="bank-details">';
                $html .= '<div class="quotation-title">Bank Details</div>';
                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        $html .= '<div>' . trim($line) . '</div>';
                    }
                }
                $html .= '</div>';
            } else {
                $html .= '<p>' . implode('<br>', array_map('trim', $lines)) . '</p>';
            }
        }

       

      



    // Add signature section
   

    return $html;
}

    private function getSupplierQuotationHTML($quotation) {
        return '<div class="section">
            <h2 class="section-title">Supplier Details</h2>
            <table class="table">
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

            <h3 class="section-title">Materials</h3>
            <table class="table">
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
                    <td colspan="5" style="text-align: right;"><strong>Total Amount:</strong></td>
                    <td><strong>Rs. ' . number_format($quotation['total_amount'], 2) . '</strong></td>
                </tr>
                </tbody>
            </table>
        </div>';
    }

    private function getMaterialsListHTML($materials) {
        $html = '<div class="section">
            <h2 class="section-title">Materials List</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Details</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($materials as $material) {
            $html .= '<tr>
                <td>' . htmlspecialchars($material['name']) . '</td>
                <td>' . ($material['type'] == 'coil' ? 
                    'Color: ' . htmlspecialchars($material['color']) . '<br>Thickness: ' . $material['thickness'] 
                    : '') . '</td>
                <td>' . $material['used_quantity'] . ' ' . $material['unit'] . '</td>
            </tr>';
        }

        $html .= '</tbody>
            </table>
        </div>';
        return $html;
    }

    private function getOrderDetailsHTML($order, $showSignature = false) {
        $html = '<div class="section">
            <h2 class="section-title">Customer Information</h2>
            <table class="table">
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
                </tr>';
               
        
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

        if ($showSignature) {
            $html .= '<div class="signature-section">
                <div class="signature-line">
                    <div></div>
                    <div>Customer Signature</div>
                </div>
                <div class="signature-line">
                    <div></div>
                    <div>Authorized Signature</div>
                </div>
            </div>';
        }

        $html .= '</div>';
        return $html;
    }

    private function getNewOrderDetailsHTML($order) {
        $html = '<div class="section">
            <h2 class="section-title">Customer Information</h2>
            <table class="table">
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
                
            </table>';

        $html .= $this->getMeasurementsHTML($order);
        
        return $html . '</div>';
    }

    private function getMeasurementsHTML($order) {
        $rollerDoorImg = __DIR__ . '/../rollerdoor.jpg';
        $wicketDoorImg = __DIR__ . '/../wicketdoor.jpg';

        $html = '<div class="section">
            <h2 class="section-title">Measurements</h2>
            <div class="measurement-guide">
                <h3>Roller Door Guide</h3>
                ' . (file_exists($rollerDoorImg) ? 
                    '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($rollerDoorImg)) . '" style="max-width: 100%; margin: 10px 0;">' 
                    : '') . '
            </div>
            <h3>Roller Door</h3>
            <table class="table">
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
            <h3>Wicket Door</h3>
            <table class="table">
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
        return '<h3>Wicket Door</h3>
        <table class="table">
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
        // Get logo HTML for consistent use
        $logoPath = __DIR__ . '/../assets/images/oricado logo.png';
        $logoHtml = file_exists($logoPath) ? 
            '<div class="logo-container">
                <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                     alt="Oricado Logo" 
                     class="company-logo">
                <div class="tagline">Strength, Style, and Security in Every Roll</div>
            </div>' : '';

        $html = '<div class="content-section">';
        
        // Add header
        $html .= '<div class="header">
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
        </div>';

        $html .= '<div class="quotation-content">
            <div class="document-meta">
                <div class="document-title" style="color: #d4af37;">Advance Payment Invoice</div>
                <div class="document-date">Invoice #INV-' . str_pad($this->id, 4, '0', STR_PAD_LEFT) . '</div>
                <div class="document-date">Order #' . $invoice['order_id'] . '</div>
            </div>

            <div class="quotation-details">
                <div class="quotation-detail-item">
                    <span class="detail-label">Customer Name:</span>
                    <span>' . htmlspecialchars($invoice['customer_name']) . '</span>
                </div>
                <div class="quotation-detail-item">
                    <span class="detail-label">Contact:</span>
                    <span>' . htmlspecialchars($invoice['customer_contact']) . '</span>
                </div>
                <div class="quotation-detail-item">
                    <span class="detail-label">Address:</span>
                    <span>' . htmlspecialchars($invoice['customer_address']) . '</span>
                </div>
                <div class="quotation-detail-item">
                    <span class="detail-label">Date:</span>
                    <span>' . date('d/m/Y') . '</span>
                </div>
            </div>
            
            <table class="table">
                <tr>
                    <th style="width: 70%;">Description</th>
                    <th style="width: 30%; text-align: right;">Amount (Rs.)</th>
                </tr>
                <tr>
                    <td>Total Order Amount</td>
                    <td style="text-align: right;">' . number_format($invoice['total_price'], 2) . '</td>
                </tr>
                <tr>
                    <td style="color: #28a745;">Amount Paid (Advance)</td>
                    <td style="text-align: right; color: #28a745;">' . number_format($invoice['amount'], 2) . '</td>
                </tr>
                <tr>
                    <td>Balance Amount</td>
                    <td style="text-align: right;">' . number_format($invoice['balance_amount'], 2) . '</td>
                </tr>
            </table>
            
            <div class="signature-block" style="margin-top: 50px;">
                <div class="signature-line">Customer Signature: _______________________</div>
                <div class="signature-line">Authorized Signature: _______________________</div>
                <div style="margin-top: 20px; font-size: 12px; color: #666;">
                    Prepared By: ' . htmlspecialchars($invoice['created_by_name']) . '
                </div>
            </div>
        </div>';

        // Add footer
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

        return $html . '</div>';
    }

    private function generateFinalInvoiceHTML($invoice) {
        // Similar structure as advance invoice but with different content
        $logoPath = __DIR__ . '/../assets/images/oricado logo.png';
        $logoHtml = file_exists($logoPath) ? 
            '<div class="logo-container">
                <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                     alt="Oricado Logo" 
                     class="company-logo">
                <div class="tagline">Strength, Style, and Security in Every Roll</div>
            </div>' : '';

        $html = '<div class="content-section">';
        
        // Add header
        $html .= '<div class="header">
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
        </div>';

        $html .= '<div class="quotation-content">
            <div class="document-meta">
                <div class="document-title" style="color: #d4af37;">Final Payment Invoice</div>
                <div class="document-date">Invoice #INV-' . str_pad($this->id, 4, '0', STR_PAD_LEFT) . '</div>
                <div class="document-date">Order #' . $invoice['order_id'] . '</div>
            </div>

            <div class="quotation-details">
                <div class="quotation-detail-item">
                    <span class="detail-label">Customer Name:</span>
                    <span>' . htmlspecialchars($invoice['customer_name']) . '</span>
                </div>
                <div class="quotation-detail-item">
                    <span class="detail-label">Contact:</span>
                    <span>' . htmlspecialchars($invoice['customer_contact']) . '</span>
                </div>
                <div class="quotation-detail-item">
                    <span class="detail-label">Address:</span>
                    <span>' . htmlspecialchars($invoice['customer_address']) . '</span>
                </div>
                <div class="quotation-detail-item">
                    <span class="detail-label">Date:</span>
                    <span>' . date('d/m/Y') . '</span>
                </div>
            </div>
            
            <table class="table">
                <tr>
                    <th style="width: 70%;">Description</th>
                    <th style="width: 30%; text-align: right;">Amount (Rs.)</th>
                </tr>
                <tr>
                    <td>Advance Paid</td>
                    <td style="text-align: right;">' . number_format($invoice['advance_amount'], 2) . '</td>
                </tr>
                <tr>
                    <td style="color: #ff0000;">Balance Amount Paid</td>
                    <td style="text-align: right; color: #ff0000;">' . number_format($invoice['amount'], 2) . '</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Total Amount Paid</td>
                    <td style="text-align: right; font-weight: bold;">' . number_format($invoice['total_price'], 2) . '</td>
                </tr>
            </table>
            
            <div class="signature-block" style="margin-top: 50px;">
                <div class="signature-line">Customer Signature: _______________________</div>
                <div class="signature-line">Authorized Signature: _______________________</div>
                <div style="margin-top: 20px; font-size: 12px; color: #666;">
                    Prepared By: ' . htmlspecialchars($invoice['created_by_name']) . '
                </div>
            </div>
        </div>';

        // Add footer
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

        return $html . '</div>';
    }
}