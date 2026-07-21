USE lcj;

-- Add missing columns to users table if they don't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS mobile_number VARCHAR(20) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS deleted TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add missing columns for password reset functionality
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_token VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS token_expiry DATETIME DEFAULT NULL;

-- Add missing columns for email verification
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_token VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS token_expires_at DATETIME DEFAULT NULL;

-- Ensure all users have the deleted column properly set
UPDATE users SET deleted = 0 WHERE deleted IS NULL;

-- Add unique constraint on username if it doesn't exist
ALTER TABLE users ADD UNIQUE KEY IF NOT EXISTS username (username); 

-- Step 1: Add admin_level column to users table (if it doesn't exist)
ALTER TABLE users ADD COLUMN IF NOT EXISTS admin_level INT DEFAULT 0;

-- Step 2: Create Admin User SQL Command
-- Username: admin
-- Password: admin123
-- Admin Level: 1 (Full Admin)

INSERT INTO users (username, email, password, admin_level, created_at) 
VALUES ('admin', 'dakdekdikdokduk123@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());

-- Alternative: If you want to check the table structure first:
-- DESCRIBE users;

-- To verify the user was created:
-- SELECT id, username, email, admin_level, created_at FROM users WHERE username = 'admin'; 