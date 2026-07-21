USE lcj;

-- Ensure the deleted column exists in products table
ALTER TABLE products ADD COLUMN IF NOT EXISTS deleted TINYINT(1) DEFAULT 0;

-- Update existing products to have deleted = 0 if they don't have it set
UPDATE products SET deleted = 0 WHERE deleted IS NULL;

-- Add missing columns that might be needed
ALTER TABLE products ADD COLUMN IF NOT EXISTS source_type VARCHAR(20) DEFAULT NULL;
ALTER TABLE products ADD COLUMN IF NOT EXISTS source_id INT(11) DEFAULT NULL;
ALTER TABLE products ADD COLUMN IF NOT EXISTS user_email VARCHAR(255) DEFAULT NULL;

-- Ensure all products have the deleted column properly set
UPDATE products SET deleted = 0 WHERE deleted IS NULL OR deleted = 1; 