CREATE TABLE IF NOT EXISTS roller_door_measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    section1 DECIMAL(10,2),
    section2 DECIMAL(10,2),
    outside_width DECIMAL(10,2),
    inside_width DECIMAL(10,2),
    door_width DECIMAL(10,2),
    tower_height DECIMAL(10,2),
    tower_type VARCHAR(50),
    coil_color VARCHAR(50),
    thickness VARCHAR(10),
    covering VARCHAR(50),
    side_lock VARCHAR(10),
    motor VARCHAR(10),
    fixing VARCHAR(50),
    down_lock TINYINT(1),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE IF NOT EXISTS wicket_door_measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    point1 DECIMAL(10,2),
    point2 DECIMAL(10,2),
    point3 DECIMAL(10,2),
    point4 DECIMAL(10,2),
    point5 DECIMAL(10,2),
    thickness VARCHAR(10),
    door_opening VARCHAR(50),
    handle TINYINT(1),
    letter_box TINYINT(1),
    coil_color VARCHAR(50),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Add column to quotations table to track updates
ALTER TABLE quotations ADD COLUMN is_updated TINYINT(1) DEFAULT 0;

-- Update orders table schema
ALTER TABLE orders 
ADD COLUMN quotation_id INT,
ADD COLUMN total_price DECIMAL(10,2) DEFAULT 0,
ADD COLUMN material_cost DECIMAL(10,2) DEFAULT 0,
ADD COLUMN admin_approved TINYINT(1) DEFAULT 0,
ADD COLUMN admin_approved_by INT,
ADD COLUMN admin_approved_at DATETIME,
ADD FOREIGN KEY (quotation_id) REFERENCES quotations(id),
ADD FOREIGN KEY (admin_approved_by) REFERENCES users(id);

-- Add column to quotations table for quotation type
ALTER TABLE quotations 
ADD COLUMN quotation_type ENUM('sell', 'buy') DEFAULT 'sell' AFTER type;

-- Create supplier_quotations table
CREATE TABLE IF NOT EXISTS supplier_quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT,
    supplier_name VARCHAR(255),
    supplier_contact VARCHAR(100),
    total_amount DECIMAL(10,2),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
