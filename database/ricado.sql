CREATE DATABASE ricado;
USE ricado;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'supervisor', 'office_staff') NOT NULL,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('coil', 'other') NOT NULL,
    thickness DECIMAL(4,2),
    color VARCHAR(50),
    quantity DECIMAL(10,2),
    unit VARCHAR(20),
    price DECIMAL(10,2) DEFAULT 0.00
);

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    customer_contact VARCHAR(50) NOT NULL,
    customer_address TEXT NOT NULL,
    prepared_by INT,
    checked_by INT,
    approved_by INT,
    status ENUM('pending', 'reviewed', 'confirmed', 'completed', 'done') DEFAULT 'pending',
    total_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prepared_by) REFERENCES users(id),
    FOREIGN KEY (checked_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE roller_door_measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    outside_width DECIMAL(10,2),
    inside_width DECIMAL(10,2),
    door_width DECIMAL(10,2),
    tower_height DECIMAL(10,2),
    tower_type ENUM('small', 'large'),
    coil_color VARCHAR(50),
    thickness DECIMAL(4,2),
    covering ENUM('full', 'side'),
    side_lock BOOLEAN,
    motor ENUM('R', 'L', 'manual'),
    fixing ENUM('inside', 'outside'),
    down_lock BOOLEAN,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE wicket_door_measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    point1 DECIMAL(10,2),
    point2 DECIMAL(10,2),
    point3 DECIMAL(10,2),
    point4 DECIMAL(10,2),
    point5 DECIMAL(10,2),
    thickness DECIMAL(4,2),
    door_opening ENUM('inside_left', 'inside_right', 'outside_left', 'outside_right'),
    handle BOOLEAN,
    letter_box BOOLEAN,
    coil_color VARCHAR(50),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE order_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    material_id INT,
    quantity DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('individual', 'company') NOT NULL,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    tax_number VARCHAR(50),
    website VARCHAR(100),
    profile_picture VARCHAR(255) DEFAULT 'profilepic.jpg',
    tags VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE quotations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('raw_materials', 'order') NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_contact VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    coil_thickness DECIMAL(4,2),
    quotation_text TEXT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE quotation_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quotation_id INT,
    material_id INT,
    name VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    discount DECIMAL(5,2) DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    taxes DECIMAL(5,2) DEFAULT 0,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);
