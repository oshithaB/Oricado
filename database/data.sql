ALTER TABLE quotation_items
ADD COLUMN coil_inches DECIMAL(10,2) AFTER newsaleprice,
ADD COLUMN peaces INT AFTER coil_inches;

ALTER TABLE quotation_items
ADD COLUMN newsaleprice DECIMAL(10,2) AFTER price;

ALTER TABLE supplier_quotation
ADD COLUMN status ENUM('requested', 'request_confirmed', 'receved') NOT NULL DEFAULT 'requested';
