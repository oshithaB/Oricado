CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    bill_number VARCHAR(50), -- Nullable by default
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
