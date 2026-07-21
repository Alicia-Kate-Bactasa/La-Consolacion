<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?error=login');
    exit();
}

// Real-time role verification - check database on every page load
require_once '../database/db.php';

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ? AND u.deleted = 0');
    $stmt->execute([$user_id]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If user not found or has been deleted
    if (!$currentUser) {
        session_destroy();
        header('Location: ../login.php?error=user_not_found');
        exit();
    }
    
    // Check if role has changed since session started
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $currentUser['role']) {
        // Role has changed - update session and check if still admin
        $_SESSION['role'] = $currentUser['role'];
        $_SESSION['admin_level'] = $currentUser['admin_level'] ?? null;
        
        // If role is no longer admin, redirect to login
        if ($currentUser['role'] !== 'admin') {
            session_destroy();
            header('Location: ../login.php?error=role_changed');
            exit();
        }
    }
    
    // Verify current user is still admin
    if ($currentUser['role'] !== 'admin') {
        session_destroy();
        header('Location: ../login.php?error=admin');
        exit();
    }
    
    // Update session with current admin level
    $_SESSION['admin_level'] = $currentUser['admin_level'] ?? null;
    
    // Defensive check for $currentAdmin before using it
    if (!isset($currentUser) || !is_array($currentUser) || !isset($currentUser['admin_level'])) {
        header('Location: login.php');
        exit();
    }
    
} catch (PDOException $e) {
    // Database error - log it and redirect to login
    error_log("Admin check database error: " . $e->getMessage());
    session_destroy();
    header('Location: ../login.php?error=system_error');
    exit();
}

// Function to check admin level (can be used in other files)
function is_admin($user, $level = null) {
    if ($user['role'] !== 'admin') return false;
    if ($level === null) return true;
    return isset($user['admin_level']) && $user['admin_level'] >= $level;
}
?> 