-- Create products table for LCJ Jewelry Shop
USE lcj;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    type VARCHAR(100) NOT NULL,
    material VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add 'deleted' column if not exists
ALTER TABLE products ADD COLUMN IF NOT EXISTS deleted TINYINT(1) DEFAULT 0;
-- No changes needed for foreign keys here, as products is the parent table.

-- Insert some sample products for testing
INSERT INTO products (name, price, stock, type, material, description, image) VALUES
('Diamond Ring', 15000.00, 5, 'Ring', 'Gold, Diamond', 'Beautiful diamond ring with 18k gold setting', '494324954_1183859846336540_5343954234173565708_n.jpg'),
('Pearl Necklace', 8000.00, 10, 'Necklace', 'Silver, Pearl', 'Elegant pearl necklace with silver chain', '494325142_544255902069492_8661555517179686959_n.jpg'),
('Gold Bracelet', 12000.00, 3, 'Bracelet', 'Gold', 'Classic gold bracelet with intricate design', '494325148_1842487459659992_5693493788136324930_n.jpg'),
('Silver Earrings', 5000.00, 8, 'Earrings', 'Silver, Crystal', 'Sparkling silver earrings with crystal accents', '494325250_681972694322251_1425333947113573545_n.jpg'); 