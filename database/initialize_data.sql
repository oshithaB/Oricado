-- Insert users
INSERT INTO users (username, password, role, name, contact, created_at) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Admin', '0000000000', '2025-04-09 18:52:57'),
('oshitha', '$2y$10$Fh.fHMizcqJLv7bV6objwOdPdphccy6v/i2l/2KXDTeq.MY/rVmYi', 'supervisor', 'oshitha', '0766961189', '2025-04-09 18:55:23'),
('sameera', '$2y$10$RmZjJvYajbmN/S.sCZ64L.nmmrZTJWkLYHwfneF5RXHUAtiUYrle2', 'office_staff', 'sameera', '076678567', '2025-04-09 18:57:34');

-- Insert contacts
INSERT INTO contacts (type, name, address, phone, mobile, email, tax_number, website, profile_picture, tags, created_at, created_by) VALUES
('individual', 'sameera', 'test', '12345678', '1234567', 'abc@mail.com', '', '', '6807e62a57502.jpeg', '', '2025-04-22 18:55:38', 3),
('individual', 'sameeraa', 'test', '07324563', '07634532', 'abc@mail.com', '', '', '6807ec2e4764f.jpeg', '', '2025-04-22 19:21:18', 3),
('individual', 'oshitha', 'piliyandala', '0766961189', '11111133333', 'oxxikala@gmail.com', '', '', '680a1559f3c73.jpeg', 'cs', '2025-04-24 10:41:29', 3),
('company', 'kalhara', '114/7', '0766961234', '123456789', 'oxx@gmail.com', '', '', '680e68918383c.jpeg', 'good', '2025-04-27 17:25:37', 3);

-- Insert materials
INSERT INTO materials (name, type, thickness, color, quantity, unit, price) VALUES
-- Coils 0.60mm
('Coil', 'coil', 0.60, 'coffee_brown', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.60, 'black_shine', 90.00, 'sqft', 100.00),
('Coil', 'coil', 0.60, 'blue_color', 90.00, 'sqft', 100.00),
('Coil', 'coil', 0.60, 'butter_milk', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.60, 'chocolate_brown', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.60, 'black_mate', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.60, 'beige', 100.00, 'sqft', 100.00),

-- Coils 0.47mm  
('Coil', 'coil', 0.47, 'coffee_brown', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.47, 'black_shine', 250.00, 'sqft', 114.00),
('Coil', 'coil', 0.47, 'blue_color', 1000.00, 'sqft', 100.00),
('Coil', 'coil', 0.47, 'butter_milk', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.47, 'chocolate_brown', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.47, 'black_mate', 100.00, 'sqft', 100.00),
('Coil', 'coil', 0.47, 'beige', 136.00, 'sqft', 109.49);

-- Insert other materials
INSERT INTO materials (name, type, unit, quantity, price) VALUES
('Motors', 'other', 'pieces', 86, 100.00),
('Roller door', 'other', 'sqft', 63, 1000.00),
('Pulley', 'other', 'pieces', 86, 100.00),
('Springs', 'other', 'pieces', 86, 0.00),
('Belt chains', 'other', 'pieces', 86, 0.00),
('Washers', 'other', 'pieces', 86, 0.00),
('L angles', 'other', 'pieces', 86, 0.00),
('U belts', 'other', 'pieces', 86, 0.00),
('Anchor belts', 'other', 'pieces', 85, 0.00),
('U channel coils', 'other', 'pieces', 86, 0.00),
('Screw nails', 'other', 'pieces', 86, 0.00),
('Nylon strips', 'other', 'meters', 86, 0.00),
('Stepler rails', 'other', 'pieces', 86, 0.00),
('Tomb bar', 'other', 'pieces', 86, 0.00),
('Door hooks', 'other', 'pieces', 54, 0.00),
('Center lock', 'other', 'pieces', 84, 0.00),
('Down lock', 'other', 'pieces', 86, 0.00),
('Side lock', 'other', 'pieces', 86, 0.00);

-- Insert orders and measurements
``INSERT INTO orders (customer_name, customer_contact, customer_address, prepared_by, checked_by, status, total_price, material_cost, quotation_id, total_sqft, admin_approved, admin_approved_by, admin_approved_at) VALUES
('sameera', '1234567', 'test', 3, 2, 'done', 6098.00, 218.98, 1, 6.00, 1, 1, '2025-04-27 23:11:02'),
('sameeraa', '07634532', 'test', 3, 2, 'confirmed', 18000.00, 1204.39, 2, 18.00, 1, 1, '2025-04-25 03:04:30'),
('sameeraa', '07634532', 'test', 3, 2, 'done', 19000.00, 2000.00, 3, 18.00, 1, 1, '2025-04-25 02:10:03');

-- Insert quotations
INSERT INTO quotations (type, quotation_type, customer_name, customer_contact, total_amount, created_by, coil_thickness, is_updated) VALUES 
('order', 'sell', 'sameera', '1234567', 6098.00, 3, 0.47, 1),
('order', 'sell', 'sameeraa', '07634532', 18000.00, 3, 0.60, 1),
('order', 'sell', 'sameeraa', '07634532', 19000.00, 3, 0.60, 1);

-- Insert quotation items
INSERT INTO quotation_items (quotation_id, material_id, name, quantity, unit, price, discount, taxes, amount) VALUES
(1, 1, 'Roller Door', 6.00, 'sqft', 1000.00, 0.00, 0.00, 6000.00),
(1, 2, 'Motors', 1.00, 'pieces', 100.00, 2.00, 0.00, 98.00),
(2, 1, 'Roller Door', 18.00, 'sqft', 1000.00, 0.00, 0.00, 18000.00),
(3, 1, 'Roller Door', 18.00, 'sqft', 1000.00, 0.00, 0.00, 18000.00),
(3, 4, 'Door handle', 10.00, 'pieces', 100.00, 0.00, 0.00, 1000.00);

-- Insert supplier quotations 
INSERT INTO supplier_quotations (quotation_id, supplier_name, supplier_contact, total_amount, created_by) VALUES
(64, 'sameera', '1234567', 4328.47, 3),
(68, 'sameera', '1234567', 22000.00, 3);

-- Insert order materials relationships
INSERT INTO order_materials (order_id, material_id, quantity) VALUES
(1, 122, 2.00),
(1, 79, 2.00),
(1, 108, 2.00),
(1, 83, 2.00),
(1, 76, 2.00),
(1, 107, 2.00),
(2, 122, 11.00),
(2, 102, 4.00),
(2, 103, 3.00),
(2, 84, 3.00),
(3, 109, 20.00),
(3, 102, 2.00),
(3, 84, 2.00),
(3, 85, 2.00),
(3, 93, 2.00),
(3, 79, 2.00),
(3, 108, 2.00);
