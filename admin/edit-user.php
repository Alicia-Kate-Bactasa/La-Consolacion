<?php
// Prevent any output before JSON
ob_start();

require_once 'check.php';
require_once '../database/db.php';

// Clear any output buffer
ob_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'] ?? null; // Role is optional for Admin-1 users
    
    try {
        // Get original user data before updating (only non-deleted users)
        $stmt = $pdo->prepare('SELECT first_name, last_name, username, email, role FROM users WHERE id = ? AND deleted = 0');
        $stmt->execute([$id]);
        $originalUser = $stmt->fetch();
        
        if ($originalUser) {
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Update the user - only update role if provided
                if ($role !== null) {
                    $dbRole = ($role === 'admin-1' || $role === 'admin-2') ? 'admin' : $role;
                    $stmt = $pdo->prepare('UPDATE users SET first_name=?, last_name=?, username=?, email=?, role=? WHERE id=?');
                    $success = $stmt->execute([$first_name, $last_name, $username, $email, $dbRole, $id]);
                } else {
                    // Admin-1 can only update name, username and email
                    $stmt = $pdo->prepare('UPDATE users SET first_name=?, last_name=?, username=?, email=? WHERE id=?');
                    $success = $stmt->execute([$first_name, $last_name, $username, $email, $id]);
                }
                
                if ($success) {
                    // Handle admin table updates only if role is provided
                    if ($role !== null) {
                        if ($role === 'user') {
                            // Remove from admin table if role is user
                            $stmt = $pdo->prepare('DELETE FROM admin WHERE user_id = ?');
                            $stmt->execute([$id]);
                        } else {
                            // Set admin level based on role
                            $adminLevel = ($role === 'admin-2') ? 2 : 1;
                            
                            // Check if admin record exists
                            $stmt = $pdo->prepare('SELECT user_id FROM admin WHERE user_id = ?');
                            $stmt->execute([$id]);
                            $adminExists = $stmt->fetch();
                            
                            if ($adminExists) {
                                // Update existing admin record
                                $stmt = $pdo->prepare('UPDATE admin SET admin_level = ? WHERE user_id = ?');
                                $stmt->execute([$adminLevel, $id]);
                            } else {
                                // Insert new admin record
                                $stmt = $pdo->prepare('INSERT INTO admin (user_id, admin_level) VALUES (?, ?)');
                                $stmt->execute([$id, $adminLevel]);
                            }
                        }
                    }
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    // Update session if admin is changing their own username
                    if (isset($_SESSION['username']) && $_SESSION['username'] === $originalUser['username'] && $originalUser['username'] !== $username) {
                        $_SESSION['username'] = $username;
                    }
                    
                    // Log the changes
                    if (isset($_SESSION['username'])) {
                        $changes = [];
                        if ($originalUser['first_name'] !== $first_name) {
                            $changes[] = "First name changed from '{$originalUser['first_name']}' to '{$first_name}'";
                        }
                        if ($originalUser['last_name'] !== $last_name) {
                            $changes[] = "Last name changed from '{$originalUser['last_name']}' to '{$last_name}'";
                        }
                        if ($originalUser['username'] !== $username) {
                            $changes[] = "Username changed from '{$originalUser['username']}' to '{$username}'";
                        }
                        if ($originalUser['email'] !== $email) {
                            $changes[] = "Email changed from '{$originalUser['email']}' to '{$email}'";
                        }
                        if ($role !== null && $originalUser['role'] !== $role) {
                            $changes[] = "Role changed from '{$originalUser['role']}' to '{$role}'";
                        }
                        
                        $details = !empty($changes) ? implode('; ', $changes) : "No changes made to user: {$username}";
                        // Log with affected user information for history tracking
                        $affectedUser = $originalUser['username'];
                        $adminUser = $_SESSION['username'];
                        
                        // Create clean log details without redundant information
                        $logDetails = $details;
                        // Log with the admin who performed the action, not the affected user
                        log_action($pdo, $_SESSION['username'], 'Edit User', $logDetails);
                    }
                    
                    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                } else {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'error' => 'Failed to update user']);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'System error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?> 