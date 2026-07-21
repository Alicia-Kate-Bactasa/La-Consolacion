<?php
require '../database/db.php';

// Create a test admin user
try {
    $username = 'admin2';
    $email = 'admin2@test.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $first_name = 'Test';
    $last_name = 'Admin';
    
    // Check if user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo "Admin user '$username' already exists!\n";
        exit;
    }
    
    // Insert new admin user
    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$first_name, $last_name, $username, $email, $password, 'admin']);
    
    $userId = $pdo->lastInsertId();
    
    // Add to admin table
    $stmt = $pdo->prepare('INSERT INTO admin (user_id, admin_level) VALUES (?, ?)');
    $stmt->execute([$userId, 1]);
    
    echo "Test admin user '$username' created successfully!\n";
    echo "Username: $username\n";
    echo "Password: admin123\n";
    echo "You can now login with this account to see different usernames in logs.\n";
    
} catch (Exception $e) {
    echo "Error creating test admin: " . $e->getMessage() . "\n";
}
?> 