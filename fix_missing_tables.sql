USE lcj;

-- Create admin table if it doesn't exist
CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  admin_level INT NOT NULL DEFAULT 1,
  last_signed_in DATETIME DEFAULT NULL,
  last_signed_out DATETIME DEFAULT NULL,
  deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create cart table if it doesn't exist
CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_product (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Add deleted column to orders table if it doesn't exist
ALTER TABLE orders ADD COLUMN IF NOT EXISTS deleted TINYINT(1) DEFAULT 0;

-- Update order_items table to have price_at_purchase column if it doesn't exist
ALTER TABLE order_items ADD COLUMN IF NOT EXISTS price_at_purchase DECIMAL(10,2) NOT NULL DEFAULT 0.00;

-- Update payments table to have proper column names
ALTER TABLE payments MODIFY COLUMN reference_number VARCHAR(255) NOT NULL;
ALTER TABLE payments MODIFY COLUMN service VARCHAR(100) NOT NULL;
ALTER TABLE payments MODIFY COLUMN image VARCHAR(255) NOT NULL;
ALTER TABLE payments MODIFY COLUMN mobile VARCHAR(20) NOT NULL;
ALTER TABLE payments MODIFY COLUMN amount DECIMAL(10,2) NOT NULL; 