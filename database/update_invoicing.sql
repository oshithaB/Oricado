-- Add payment columns to orders table
ALTER TABLE orders 
ADD COLUMN paid_amount DECIMAL(10,2) DEFAULT 0,
ADD COLUMN balance_amount DECIMAL(10,2) DEFAULT 0;

-- Create invoices table
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    invoice_type ENUM('advance', 'final') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    advance_amount DECIMAL(10,2) DEFAULT 0,
    balance_amount DECIMAL(10,2) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
