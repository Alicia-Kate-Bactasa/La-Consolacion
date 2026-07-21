-- Step 1: Create the user in the users table first
INSERT INTO users (username, email, password, created_at) 
VALUES ('admin', 'dakdekdikdokduk123@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

-- Step 2: Get the user_id that was just created
-- (You'll need to note this ID from the result of Step 1)

-- Step 3: Create the admin record linking to the user
-- Replace 'X' with the actual user_id from Step 1
INSERT INTO admin (user_id, admin_level, created_at) 
VALUES (LAST_INSERT_ID(), 1, NOW());

-- Alternative: If you want to do it in one transaction
START TRANSACTION;

INSERT INTO users (username, email, password, created_at) 
VALUES ('admin', 'dakdekdikdokduk123@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

INSERT INTO admin (user_id, admin_level, created_at) 
VALUES (LAST_INSERT_ID(), 1, NOW());

COMMIT;

-- To verify both records were created:
-- SELECT u.id, u.username, u.email, a.admin_level, a.created_at 
-- FROM users u 
-- JOIN admin a ON u.id = a.user_id 
-- WHERE u.username = 'admin'; 