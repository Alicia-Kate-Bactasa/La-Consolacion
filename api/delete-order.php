<?php
// Prevent any output before JSON
ob_start();

// Disable error display to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Start session to get admin username
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/db.php';

// Clear any output buffer
ob_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'No order ID provided']);
        exit;
    }
    
    try {
        // First, fetch order details for logging
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND deleted = 0');
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            echo json_encode(['success' => false, 'error' => 'Order not found or already deleted']);
            exit;
        }
        
        // Soft delete the order
        $stmt = $pdo->prepare('UPDATE orders SET deleted = 1 WHERE id = ?');
        $success = $stmt->execute([$id]);
        
        if ($success) {
            // Simple logging without external function
            try {
                $details = "Deleted Order ID: {$order['id']}";
                $stmt = $pdo->prepare("INSERT INTO logs (user, action, details, created_at) VALUES (?, ?, ?, NOW())");
                // Use session username if available, otherwise use 'Admin'
                $adminUser = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
                $stmt->execute([$adminUser, 'Delete Order', $details]);
            } catch (Exception $logError) {
                // Log error silently - don't fail the deletion for logging issues
                error_log("Log action failed: " . $logError->getMessage());
            }
        }
        
        echo json_encode(['success' => $success]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 