<?php
class InvoiceGenerator {
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function generateInvoicePDF($invoice, $type = 'standard') {
        header('Content-Type: text/html; charset=utf-8');
        
        $logoPath = __DIR__ . '/../assets/images/oricado logo.jpg';
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
        echo '<style>
            body { font-family: Arial, sans-serif; }
            .invoice-container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; border: 1px solid #ddd; }
            .amount { color: #ff0000; }
            .footer { margin-top: 50px; text-align: center; }
            .signatures { display: flex; justify-content: space-between; margin-top: 100px; }
            .signature-line { width: 200px; text-align: center; }
        </style>';
        echo '</head><body>' . $html;
        echo '<script>window.onload = function() { window.print(); }</script>';
        echo '</body></html>';
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
                        <div>________________</div>
                        <div>Supplier Signature</div>
                    </div>
                    <div class="signature-line">
                        <div>________________</div>
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
                ' . $logoHtml . '
                <h2>Advance Payment Invoice</h2>
                <p>Invoice #' . $this->id . '</p>
                <p>Date: ' . date('Y-m-d') . '</p>
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
                        <div>________________</div>
                        <div>Customer Signature</div>
                    </div>
                    <div class="signature-line">
                        <div>________________</div>
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
                ' . $logoHtml . '
                <h2>Final Payment Invoice</h2>
                <p>Invoice #' . $this->id . '</p>
                <p>Date: ' . date('Y-m-d') . '</p>
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
                        <div>________________</div>
                        <div>Customer Signature</div>
                    </div>
                    <div class="signature-line">
                        <div>________________</div>
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
                ' . $logoHtml . '
                <h2>Material Sales Invoice</h2>
                <p>Invoice #' . $this->id . '</p>
                <p>Date: ' . date('Y-m-d') . '</p>
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
                        <div>________________</div>
                        <div>Customer Signature</div>
                    </div>
                    <div class="signature-line">
                        <div>________________</div>
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
        $logoPath = __DIR__ . '/../assets/images/oricado logo.jpg';
        return file_exists($logoPath) ? 
            '<img src="data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) . '" 
                  style="width: 120px; height: auto; margin: 20px 0;">' : '';
    }

    private function outputPDF($html) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head>';
        echo '<style>
            body { font-family: Arial, sans-serif; }
            .invoice-container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; border: 1px solid #ddd; }
            .amount { color: #ff0000; }
            .footer { margin-top: 50px; text-align: center; }
            .signatures { display: flex; justify-content: space-between; margin-top: 100px; }
            .signature-line { width: 200px; text-align: center; }
        </style>';
        echo '</head><body>' . $html;
        echo '<script>window.onload = function() { window.print(); }</script>';
        echo '</body></html>';
    }
}
