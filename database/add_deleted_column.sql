USE lcj;

-- Add deleted column to products table
ALTER TABLE products ADD COLUMN deleted TINYINT(1) DEFAULT 0;

-- Update existing products to have deleted = 0
UPDATE products SET deleted = 0 WHERE deleted IS NULL; 