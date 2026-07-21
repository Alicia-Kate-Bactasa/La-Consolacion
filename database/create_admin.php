<?php
// database/create_admin.php - Dynamically creates the admin credentials in the database

require_once 'db.php';

echo "<h2>Creating SuperAdmin Account</h2>";

$admin_username = 'admin';
$admin_email = 'admin@laconsolacion.com';
$admin_password = 'Admin_12345'; // The password to log in with
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();
    
    // 1. Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$admin_username]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update password and ensure admin role & verified
        $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ?, role = 'admin', is_verified = 1 WHERE id = ?");
        $stmt->execute([$admin_email, $hashed_password, $existing['id']]);
        $user_id = $existing['id'];
        echo "<p>User '$admin_username' already exists. Updated password and verified status.</p>";
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, password, role, is_verified) VALUES ('La', 'Consolacion', ?, ?, ?, 'admin', 1)");
        $stmt->execute([$admin_username, $admin_email, $hashed_password]);
        $user_id = $pdo->lastInsertId();
        echo "<p>Created new user: '$admin_username'.</p>";
    }
    
    // 2. Check if admin record exists
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $admin_existing = $stmt->fetch();
    
    if ($admin_existing) {
        $stmt = $pdo->prepare("UPDATE admin SET admin_level = 3 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo "<p>Admin record already exists. Updated level to 3 (SuperAdmin).</p>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO admin (user_id, admin_level) VALUES (?, 3)");
        $stmt->execute([$user_id]);
        echo "<p>Created admin record at level 3 (SuperAdmin).</p>";
    }
    
    $pdo->commit();
    echo "<p style='color: green; font-weight: bold;'>✅ SuperAdmin account configured successfully!</p>";
    echo "<p><strong>Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($admin_username) . "</li>";
    echo "<li><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</li>";
    echo "</ul>";
    echo "<p>You can now go to <a href='../admin/login.php'>Admin Login</a> and sign in.</p>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color: red; font-weight: bold;'>❌ Failed to create SuperAdmin account: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
