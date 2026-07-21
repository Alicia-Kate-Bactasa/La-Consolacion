-- Create custom_orders table for LCJ Jewelry Shop
USE lcj;

CREATE TABLE IF NOT EXISTS custom_orders (
    id INT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    description TEXT,
    google_form_link VARCHAR(512),
    status VARCHAR(50) DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id) REFERENCES products(id)
); 