<?php
require_once 'db.php';

echo "<h2>Fix Admin Levels</h2>";

// Check if admin table exists
$stmt = $pdo->query("SHOW TABLES LIKE 'admin'");
if ($stmt->rowCount() == 0) {
    echo "<p style='color: red;'>❌ Admin table does not exist! Creating it...</p>";
    
    // Create admin table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            admin_level INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "<p style='color: green;'>✅ Admin table created successfully!</p>";
} else {
    echo "<p style='color: green;'>✅ Admin table exists</p>";
}

// Check current admin users
echo "<h3>Current Admin Users:</h3>";
$stmt = $pdo->query("
    SELECT u.id, u.username, u.role, a.admin_level 
    FROM users u 
    LEFT JOIN admin a ON u.id = a.user_id 
    WHERE u.role = 'admin'
");
$adminUsers = $stmt->fetchAll();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Admin Level</th><th>Action</th></tr>";

foreach ($adminUsers as $user) {
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['username']}</td>";
    echo "<td>{$user['role']}</td>";
    echo "<td>" . ($user['admin_level'] ?? 'NULL') . "</td>";
    
    if ($user['admin_level'] === null) {
        echo "<td><a href='?fix_user={$user['id']}' style='color: blue;'>Fix Level</a></td>";
    } else {
        echo "<td>OK</td>";
    }
    
    echo "</tr>";
}
echo "</table>";

// Fix admin level if requested
if (isset($_GET['fix_user'])) {
    $userId = intval($_GET['fix_user']);
    
    // Check if user exists in admin table
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE user_id = ?");
    $stmt->execute([$userId]);
    $adminRecord = $stmt->fetch();
    
    if (!$adminRecord) {
        // Insert admin record with level 2 (SuperAdmin)
        $stmt = $pdo->prepare("INSERT INTO admin (user_id, admin_level) VALUES (?, 2)");
        $stmt->execute([$userId]);
        echo "<p style='color: green;'>✅ Added admin level 2 for user ID {$userId}</p>";
    } else {
        // Update existing record to level 2
        $stmt = $pdo->prepare("UPDATE admin SET admin_level = 2 WHERE user_id = ?");
        $stmt->execute([$userId]);
        echo "<p style='color: green;'>✅ Updated admin level to 2 for user ID {$userId}</p>";
    }
    
    // Redirect to refresh the page
    echo "<script>setTimeout(() => window.location.href = 'fix_admin_levels.php', 1000);</script>";
}

// Test is_admin function
echo "<h3>Testing is_admin() Function:</h3>";
require_once 'admin-check.php';

foreach ($adminUsers as $user) {
    $userData = ['role' => $user['role'], 'admin_level' => $user['admin_level']];
    $isAdmin1 = is_admin($userData, 1);
    $isAdmin2 = is_admin($userData, 2);
    
    echo "<p><strong>{$user['username']}:</strong> ";
    echo "Level 1: " . ($isAdmin1 ? 'TRUE' : 'FALSE') . ", ";
    echo "Level 2: " . ($isAdmin2 ? 'TRUE' : 'FALSE') . "</p>";
}
?> 