-- Create premade_products table for LCJ Jewelry Shop
USE lcj;

CREATE TABLE IF NOT EXISTS premade_products (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    type VARCHAR(100) NOT NULL,
    material VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id) REFERENCES products(id)
); 