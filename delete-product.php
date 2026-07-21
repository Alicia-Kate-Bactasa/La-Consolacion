<?php
session_start();
require_once 'admin-check.php';
require_once 'db.php';

// Check admin level - only SuperAdmin (level 2) can delete products
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ?');
$stmt->execute([$user_id]);
$currentAdmin = $stmt->fetch();

if (!is_admin($currentAdmin, 2)) {
    header('Location: admin-inventory.php?error=insufficient_permissions');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $success = false;
    $error = '';
    
    try {
        // Fetch the full product row
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = 'Product not found';
        } else {
            // Always soft delete the product
            $stmt = $pdo->prepare('UPDATE products SET deleted = 1 WHERE id = ?');
            $success = $stmt->execute([$id]);
            // Also soft delete related custom_orders if they exist
            $stmt = $pdo->prepare('UPDATE custom_orders SET deleted = 1 WHERE id = ?');
            $stmt->execute([$id]);
            if ($success) {
                $details = "Soft deleted product ID: {$id}";
                log_action($pdo, $_SESSION['username'], 'Delete Product', $details);
            } else {
                $error = 'Failed to soft delete product';
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
    
    // Redirect based on result
    if ($success) {
        header('Location: admin-inventory.php?deleted=1');
    } else {
        header('Location: admin-inventory.php?error=delete_failed&message=' . urlencode($error));
    }
    exit();
} 