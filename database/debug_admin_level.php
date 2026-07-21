<?php
session_start();
require_once '../database/db.php';
require_once '../admin/check.php';

echo "<h2>Debug Admin Level</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Not logged in</p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p><strong>User ID:</strong> {$user_id}</p>";

// Get user data
$stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ?');
$stmt->execute([$user_id]);
$currentAdmin = $stmt->fetch();

echo "<h3>User Data:</h3>";
echo "<pre>" . print_r($currentAdmin, true) . "</pre>";

// Test is_admin function
echo "<h3>is_admin() Function Tests:</h3>";
echo "<p><strong>is_admin(\$currentAdmin):</strong> " . (is_admin($currentAdmin) ? 'TRUE' : 'FALSE') . "</p>";
echo "<p><strong>is_admin(\$currentAdmin, 1):</strong> " . (is_admin($currentAdmin, 1) ? 'TRUE' : 'FALSE') . "</p>";
echo "<p><strong>is_admin(\$currentAdmin, 2):</strong> " . (is_admin($currentAdmin, 2) ? 'TRUE' : 'FALSE') . "</p>";

// Check admin table
echo "<h3>Admin Table Data:</h3>";
$stmt = $pdo->query('SELECT * FROM admin');
$adminData = $stmt->fetchAll();
echo "<pre>" . print_r($adminData, true) . "</pre>";

// Check if current user exists in admin table
$stmt = $pdo->prepare('SELECT * FROM admin WHERE user_id = ?');
$stmt->execute([$user_id]);
$userAdminData = $stmt->fetch();

echo "<h3>Current User in Admin Table:</h3>";
if ($userAdminData) {
    echo "<pre>" . print_r($userAdminData, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ User not found in admin table</p>";
}

// Check users table
echo "<h3>Current User in Users Table:</h3>";
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$userData = $stmt->fetch();
echo "<pre>" . print_r($userData, true) . "</pre>";
?> 