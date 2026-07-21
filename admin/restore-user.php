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
        // Get user details before restoration for logging
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = ? AND deleted_at IS NOT NULL');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'User not found or not deleted']);
            exit;
        }
        
        // Restore the user (set deleted_at to NULL)
        $stmt = $pdo->prepare('UPDATE users SET deleted_at = NULL WHERE id = ?');
        $success = $stmt->execute([$id]);
        
        if ($success) {
            // Log the restoration
            if (isset($_SESSION['username'])) {
                $details = "Restored user: {$user['username']} ({$user['email']})";
                log_action($pdo, $_SESSION['username'], 'Restore User', $details);
            }
            
            echo json_encode(['success' => true]);
        } else {
            $pdoError = $stmt->errorInfo();
            echo json_encode([
                'success' => false,
                'error' => 'Failed to restore user (DB error)',
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