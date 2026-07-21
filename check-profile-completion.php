<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit();
}

try {
    // Get user data
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT mobile_number FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $hasPhoneNumber = !empty($user['mobile_number']) && strlen(trim($user['mobile_number'])) >= 10;
        
        echo json_encode([
            'success' => true,
            'hasPhoneNumber' => $hasPhoneNumber,
            'mobileNumber' => $user['mobile_number'] ?? null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'User not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 