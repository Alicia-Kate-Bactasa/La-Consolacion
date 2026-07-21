<?php
require_once 'check.php';
require '../database/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'No user ID provided']);
        exit;
    }
    
    try {
        // Check if deleted column exists in users table
        $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'deleted'");
        $hasDeletedColumn = $checkColumn->rowCount() > 0;
        
        if (!$hasDeletedColumn) {
            // Add deleted column if it doesn't exist
            $pdo->exec("ALTER TABLE users ADD COLUMN deleted TINYINT(1) DEFAULT 0");
        }
        
        // Get user details before soft deletion for logging
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = ? AND deleted = 0');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'User not found or already deleted']);
            exit;
        }
        
        // Check if user has any orders
        $stmt = $pdo->prepare('SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?');
        $stmt->execute([$id]);
        $orderCount = $stmt->fetch()['order_count'];
        
        // Soft delete the user (set deleted = 1)
        $stmt = $pdo->prepare('UPDATE users SET deleted = 1 WHERE id = ?');
        $success = $stmt->execute([$id]);
        
        if ($success) {
            // Log the soft deletion
            if (isset($_SESSION['username'])) {
                $details = "Soft deleted user: {$user['username']} ({$user['email']})";
                if ($orderCount > 0) {
                    $details .= " - User has {$orderCount} orders (preserved for history)";
                }
                log_action($pdo, $_SESSION['username'], 'Soft Delete User', $details);
            }
            
            echo json_encode(['success' => true]);
        } else {
            $pdoError = $stmt->errorInfo();
            echo json_encode([
                'success' => false,
                'error' => 'Failed to soft delete user (DB error)',
                'pdo_error' => $pdoError
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'PDOException: ' . $e->getMessage(),
            'code' => $e->getCode()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?> 