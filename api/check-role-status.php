<?php
// Prevent any output before JSON
ob_start();

session_start();
require_once '../database/db.php';

// Clear any output buffer
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['isAdmin' => false, 'message' => 'Not logged in']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ? AND u.deleted = 0');
    $stmt->execute([$user_id]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If user not found or has been deleted
    if (!$currentUser) {
        echo json_encode(['isAdmin' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Check if user is still admin
    $isAdmin = ($currentUser['role'] === 'admin');
    
    // Update session with current data
    $_SESSION['role'] = $currentUser['role'];
    $_SESSION['admin_level'] = $currentUser['admin_level'] ?? null;
    
    echo json_encode([
        'isAdmin' => $isAdmin,
        'role' => $currentUser['role'],
        'adminLevel' => $currentUser['admin_level'],
        'message' => $isAdmin ? 'Admin access confirmed' : 'No longer admin'
    ]);
    
} catch (PDOException $e) {
    error_log("Role status check error: " . $e->getMessage());
    echo json_encode(['isAdmin' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    error_log("Role status check general error: " . $e->getMessage());
    echo json_encode(['isAdmin' => false, 'message' => 'System error']);
}
?> 