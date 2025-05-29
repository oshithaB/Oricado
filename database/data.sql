-- Drop existing tables if they exist
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS supplier_quotations, quotation_items, wicket_door_measurements, 
             roller_door_measurements, invoices, order_materials, materials, orders, 
             quotations, contacts, users;
SET FOREIGN_KEY_CHECKS = 1;

-- Create tables with proper constraints and defaults
CREATE TABLE users (
    id int PRIMARY KEY AUTO_INCREMENT,  
    name varchar(100) NOT NULL,
    username varchar(50) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    contact varchar(20) NOT NULL,
    role ENUM('admin', 'supervisor', 'office_staff', 'site_staff') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login timestamp NULL,
    password_reset_token varchar(100) NULL,
    password_reset_expires timestamp NULL,
    failed_login_attempts int DEFAULT 0,
    account_locked_until timestamp NULL,
    KEY idx_username (username),
    KEY idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default users with hashed passwords
INSERT INTO users (name, username, password, contact, role) VALUES
('Admin User', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0766961189', 'admin'),
('Office Manager', 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0766961188', 'supervisor'),
('Staff User', 'staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0766961187', 'office_staff');

CREATE TABLE contacts (
  id int PRIMARY KEY AUTO_INCREMENT,
  type varchar(20),
  name varchar(100),
  address text,
  phone varchar(20),
  mobile varchar(20),
  email varchar(100),
  tax_number varchar(50),
  website varchar(100),
  profile_picture varchar(255),
  tags text,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  created_by int,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE quotations (
  id int PRIMARY KEY AUTO_INCREMENT,
  type varchar(20),
  quotation_type varchar(20), 
  customer_name varchar(100),
  customer_contact varchar(20),
  total_amount decimal(10,2) DEFAULT 0,
  created_by int,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  coil_thickness decimal(10,2),
  quotation_text text,
  order_id int,
  is_updated tinyint(1) DEFAULT 0,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE orders (
  id int PRIMARY KEY AUTO_INCREMENT, 
  customer_name varchar(100),
  customer_contact varchar(20),
  customer_address text,
  prepared_by int,
  checked_by int,
  approved_by int,
  status varchar(20),
  total_price decimal(10,2) DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  quotation_id int,
  total_sqft decimal(10,2),
  admin_approved tinyint(1) DEFAULT 0,
  admin_approved_by int,
  admin_approved_at timestamp NULL,
  material_cost decimal(10,2) DEFAULT 0,
  paid_amount decimal(10,2) DEFAULT 0,
  balance_amount decimal(10,2) DEFAULT 0,
  FOREIGN KEY (quotation_id) REFERENCES quotations(id),
  FOREIGN KEY (prepared_by) REFERENCES users(id),
  FOREIGN KEY (checked_by) REFERENCES users(id),
  FOREIGN KEY (approved_by) REFERENCES users(id),
  FOREIGN KEY (admin_approved_by) REFERENCES users(id)
);

CREATE TABLE materials (
  id int PRIMARY KEY AUTO_INCREMENT,
  name varchar(100),
  type varchar(20),
  thickness decimal(10,2),
  color varchar(50),
  quantity decimal(10,2) DEFAULT 0,
  unit varchar(20),
  price decimal(10,2) DEFAULT 0
);

CREATE TABLE order_materials (
  id int PRIMARY KEY AUTO_INCREMENT,
  order_id int,
  material_id int,
  quantity decimal(10,2),
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE invoices (
  id int PRIMARY KEY AUTO_INCREMENT,
  order_id int,
  invoice_type varchar(20),
  amount decimal(10,2) DEFAULT 0,
  advance_amount decimal(10,2) DEFAULT 0,
  balance_amount decimal(10,2) DEFAULT 0,
  created_by int,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE roller_door_measurements (
  id int PRIMARY KEY AUTO_INCREMENT,
  order_id int,
  section1 decimal(10,2),
  section2 decimal(10,2),
  outside_width decimal(10,2),
  inside_width decimal(10,2),
  door_width decimal(10,2),
  tower_height decimal(10,2),
  tower_type varchar(20),
  coil_color varchar(50),
  thickness decimal(10,2),
  covering varchar(20),
  side_lock varchar(10),
  motor varchar(20),
  fixing varchar(20),
  down_lock tinyint(1),
  FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE wicket_door_measurements (
  id int PRIMARY KEY AUTO_INCREMENT, 
  order_id int,
  point1 decimal(10,2),
  point2 decimal(10,2),
  point3 decimal(10,2),
  point4 decimal(10,2),
  point5 decimal(10,2),
  thickness decimal(10,2),
  door_opening varchar(20),
  handle tinyint(1),
  letter_box tinyint(1),
  coil_color varchar(50),
  FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE quotation_items (
  id int PRIMARY KEY AUTO_INCREMENT,
  quotation_id int,
  material_id int,
  name varchar(100),
  quantity decimal(10,2) DEFAULT 0,
  price decimal(10,2) DEFAULT 0,
  discount decimal(10,2) DEFAULT 0,
  taxes decimal(10,2) DEFAULT 0,
  amount decimal(10,2) DEFAULT 0,
  FOREIGN KEY (quotation_id) REFERENCES quotations(id),
  FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE supplier_quotations (
  id int PRIMARY KEY AUTO_INCREMENT,
  supplier_name varchar(100),
  type varchar(20),
  quotation_type varchar(20),
  material_id int,
  quantity decimal(10,2) DEFAULT 0,
  unit varchar(20),
  unit_price decimal(10,2) DEFAULT 0,
  total_amount decimal(10,2) DEFAULT 0,
  created_by int,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (material_id) REFERENCES materials(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Add triggers for data integrity
DELIMITER //

CREATE TRIGGER before_order_update 
BEFORE UPDATE ON orders
FOR EACH ROW 
BEGIN
    IF NEW.total_price < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Total price cannot be negative';
    END IF;
    
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        SET NEW.admin_approved_at = CURRENT_TIMESTAMP;
    END IF;
END;//

CREATE TRIGGER after_invoice_insert
AFTER INSERT ON invoices
FOR EACH ROW
BEGIN
    UPDATE orders SET 
        paid_amount = paid_amount + NEW.amount,
        balance_amount = total_price - (paid_amount + NEW.amount)
    WHERE id = NEW.order_id;
END;//

CREATE TRIGGER before_material_update
BEFORE UPDATE ON materials
FOR EACH ROW
BEGIN
    IF NEW.quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Material quantity cannot be negative';
    END IF;
END;//

DELIMITER ;

-- Add stored procedures for common operations
DELIMITER //

CREATE PROCEDURE update_order_status(
    IN order_id INT,
    IN new_status VARCHAR(20),
    IN updated_by INT
)
BEGIN
    UPDATE orders SET 
        status = new_status,
        approved_by = updated_by,
        admin_approved = IF(new_status = 'completed', 1, admin_approved)
    WHERE id = order_id;
END //

CREATE PROCEDURE calculate_order_totals(
    IN order_id INT
)
BEGIN
    UPDATE orders o 
    SET o.total_price = (
        SELECT SUM(m.price * om.quantity)
        FROM order_materials om
        JOIN materials m ON om.material_id = m.id
        WHERE om.order_id = order_id
    )
    WHERE o.id = order_id;
END //

DELIMITER ;

-- Add views for reporting
CREATE OR REPLACE VIEW completed_orders_view AS
SELECT 
    o.id, o.customer_name, o.total_price,
    o.created_at, o.admin_approved_at,
    u1.name as prepared_by,
    u2.name as approved_by
FROM orders o
LEFT JOIN users u1 ON o.prepared_by = u1.id
LEFT JOIN users u2 ON o.approved_by = u2.id
WHERE o.status = 'completed';

CREATE OR REPLACE VIEW material_usage_view AS
SELECT 
    m.id, m.name, m.type,
    SUM(om.quantity) as total_used,
    m.quantity as current_stock,
    m.unit
FROM materials m
LEFT JOIN order_materials om ON m.id = om.material_id
GROUP BY m.id;
