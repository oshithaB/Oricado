<?php
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

class OrderPDF extends TCPDF {
    protected $order_id;

    public function __construct($order_id) {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->order_id = $order_id;
        
        // Set document information
        $this->SetCreator('Ricado System');
        $this->SetTitle('Order #' . $order_id);
        
        // Set margins
        $this->SetMargins(15, 50, 15);
        $this->SetAutoPageBreak(TRUE, 25);
    }

    public function Header() {
        // Logo
        $logo_url = 'https://marketplace.canva.com/EAFaFUz4aKo/3/0/1600w/canva-yellow-abstract-cooking-fire-free-logo-tn1zF-_cG9c.jpg';
        $this->Image($logo_url, 10, 10, 50);
        
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        
        // Title
        $this->Cell(0, 15, 'Order #' . $this->order_id, 0, false, 'R', 0, '', 0, false, 'M', 'M');
        
        // Line
        $this->Line(15, 45, 195, 45);
    }

    public function generateNewOrderPDF($order_data) {
        $this->AddPage();
        $this->SetFont('helvetica', '', 12);
        
        // Customer Details
        $this->writeHTML($this->getCustomerDetailsHTML($order_data), true, false, true, false, '');
        
        // Measurements
        $this->writeHTML($this->getMeasurementsHTML($order_data), true, false, true, false, '');
        
        return $this;
    }

    public function generateMaterialListPDF($materials) {
        $this->AddPage();
        $this->SetFont('helvetica', '', 12);
        
        // Materials Table
        $this->writeHTML($this->getMaterialsTableHTML($materials), true, false, true, false, '');
        
        return $this;
    }

    public function generateCompletedOrderPDF($order_data, $materials) {
        $this->AddPage();
        $this->SetFont('helvetica', '', 12);
        
        // Full Order Details
        $this->writeHTML($this->getCompleteOrderHTML($order_data, $materials), true, false, true, false, '');
        
        return $this;
    }

    private function getCustomerDetailsHTML($order) {
        // HTML template for customer details
        // Add your HTML structure here
    }

    private function getMeasurementsHTML($order) {
        // HTML template for measurements
        // Add your HTML structure here
    }

    private function getMaterialsTableHTML($materials) {
        // HTML template for materials list
        // Add your HTML structure here
    }

    private function getCompleteOrderHTML($order, $materials) {
        // HTML template for complete order
        // Add your HTML structure here
    }
}
