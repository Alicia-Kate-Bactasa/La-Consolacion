-- Add missing columns to existing users table
ALTER TABLE users 
ADD COLUMN first_name VARCHAR(100) NOT NULL DEFAULT 'User' AFTER id,
ADD COLUMN last_name VARCHAR(100) NOT NULL DEFAULT 'Name' AFTER first_name,
ADD COLUMN mobile_number VARCHAR(20) AFTER email,
ADD COLUMN profile_image VARCHAR(255) AFTER role,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER profile_image,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Make username unique if not already
ALTER TABLE users ADD UNIQUE KEY unique_username (username);

-- Update existing admin user if exists
UPDATE users SET 
  first_name = 'Admin',
  last_name = 'User'
WHERE username = 'admin' AND role = 'admin'; 