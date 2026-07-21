-- Create premade_products table (for shop display)
CREATE TABLE IF NOT EXISTS premade_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    type ENUM('ring', 'bracelet', 'earring', 'charm') NOT NULL,
    material VARCHAR(100),
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted TINYINT(1) DEFAULT 0
);

-- Create custom_orders table (for custom order requests)
CREATE TABLE IF NOT EXISTS custom_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    description TEXT NOT NULL,
    status ENUM('pending', 'reviewing', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted TINYINT(1) DEFAULT 0
);

-- Add indexes for better performance
ALTER TABLE premade_products ADD INDEX idx_type (type);
ALTER TABLE premade_products ADD INDEX idx_deleted (deleted);
ALTER TABLE custom_orders ADD INDEX idx_status (status);
ALTER TABLE custom_orders ADD INDEX idx_email (email);
ALTER TABLE custom_orders ADD INDEX idx_deleted (deleted);

-- Insert some sample premade products
INSERT INTO premade_products (name, price, stock, type, material, description, image) VALUES
('Classic Gold Ring', 15000.00, 10, 'ring', 'Gold', 'Elegant classic gold ring with timeless design', 'classic_gold_ring.jpg'),
('Silver Bracelet', 8000.00, 15, 'bracelet', 'Silver', 'Beautiful silver bracelet with intricate details', 'silver_bracelet.jpg'),
('Pearl Earrings', 12000.00, 8, 'earring', 'Pearl', 'Sophisticated pearl earrings for special occasions', 'pearl_earrings.jpg'),
('Diamond Charm', 25000.00, 5, 'charm', 'Diamond', 'Exquisite diamond charm pendant', 'diamond_charm.jpg');

-- Insert sample custom order
INSERT INTO custom_orders (customer_name, email, phone, description) VALUES
('John Doe', 'john@example.com', '09123456789', 'I want a custom ring with my initials engraved and a specific gemstone');

-- Show table structures
DESCRIBE premade_products;
DESCRIBE custom_orders;

-- Show sample data
SELECT * FROM premade_products;
SELECT * FROM custom_orders; 