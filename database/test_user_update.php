<?php
require '../database/db.php';

echo "Testing user update functionality...\n";

try {
    // Get a test user
    $stmt = $pdo->query('SELECT id, username, first_name, last_name FROM users WHERE deleted = 0 LIMIT 1');
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Found user: {$user['username']} ({$user['first_name']} {$user['last_name']})\n";
        
        // Test update
        $newFirstName = $user['first_name'] . '_test';
        $newLastName = $user['last_name'] . '_test';
        $newUsername = $user['username'] . '_test';
        
        $stmt = $pdo->prepare('UPDATE users SET first_name=?, last_name=?, username=? WHERE id=?');
        $result = $stmt->execute([$newFirstName, $newLastName, $newUsername, $user['id']]);
        
        if ($result) {
            echo "Update successful!\n";
            
            // Verify the update
            $stmt = $pdo->prepare('SELECT username, first_name, last_name FROM users WHERE id=?');
            $stmt->execute([$user['id']]);
            $updatedUser = $stmt->fetch();
            
            echo "Updated user: {$updatedUser['username']} ({$updatedUser['first_name']} {$updatedUser['last_name']})\n";
            
            // Revert the changes
            $stmt = $pdo->prepare('UPDATE users SET first_name=?, last_name=?, username=? WHERE id=?');
            $stmt->execute([$user['first_name'], $user['last_name'], $user['username'], $user['id']]);
            echo "Changes reverted.\n";
            
        } else {
            echo "Update failed!\n";
        }
    } else {
        echo "No users found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 