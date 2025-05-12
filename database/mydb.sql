-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(20),
    role ENUM('admin', 'supervisor', 'office_staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('individual', 'company', 'supplier') NOT NULL,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    tax_number VARCHAR(50),
    website VARCHAR(255),
    profile_picture VARCHAR(255) DEFAULT 'profilepic.jpg',
    tags VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_contact VARCHAR(20) NOT NULL,
    customer_address TEXT,
    prepared_by INT,
    checked_by INT,
    status VARCHAR(20) DEFAULT 'pending',
    total_sqft DECIMAL(10,2),
    t
-- Materials table
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    thickness VARCHAR(10),
    color VARCHAR(50),
    quantity DECIMAL(10,2) DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
otal_price DECIMAL(10,2) DEFAULT 0,
    material_cost DECIMAL(10,2) DEFAULT 0,
    admin_approved TINYINT(1) DEFAULT 0,
    admin_approved_by INT,
    admin_approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prepared_by) REFERENCES users(id),
    FOREIGN KEY (checked_by) REFERENCES users(id),
    FOREIGN KEY (admin_approved_by) REFERENCES users(id)
);

-- Quotations table
CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    quotation_type ENUM('sell', 'buy') DEFAULT 'sell',
    customer_name VARCHAR(100) NOT NULL,
    customer_contact VARCHAR(20) NOT NULL,
    total_amount DECIMAL(10,2) DEFAULT 0,
    created_by INT,
    coil_thickness VARCHAR(10),
    quotation_text TEXT,
    is_updated TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Quotation items table
CREATE TABLE IF NOT EXISTS quotation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    material_id INT,
    name VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(5,2) DEFAULT 0,
    taxes DECIMAL(5,2) DEFAULT 0,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

-- Order materials table
CREATE TABLE IF NOT EXISTS order_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

-- Roller door measurements table
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

-- Wicket door measurements table
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

-- Supplier quotations table
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

-- Insert default admin user
INSERT INTO users (username, password, name, role) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'Administrator', 'admin');
