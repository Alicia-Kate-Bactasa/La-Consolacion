CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    payment_id INT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    date_ordered DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_completed DATETIME,
    deleted TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (payment_id) REFERENCES payments(id)
); 