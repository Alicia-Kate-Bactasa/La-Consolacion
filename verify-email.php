<?php
require 'database/db.php';

$verified = false;
$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare('SELECT id, token_expires_at FROM users WHERE verification_token = ? AND is_verified = 0');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user && strtotime($user['token_expires_at']) > time()) {
        // Mark as verified
        $stmt = $pdo->prepare('UPDATE users SET is_verified = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?');
        $stmt->execute([$user['id']]);
        $verified = true;
        $message = "Your account has been verified! You may now <a href='login.php'>log in</a>.";
    } else {
        $message = "Invalid or expired verification link.";
    }
} else {
    $message = "No verification token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background: #f7f7f7; }
        .verify-container { background: #fff; padding: 2rem 3rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .verify-success { color: #27ae60; font-size: 1.2rem; }
        .verify-error { color: #c0392b; font-size: 1.2rem; }
        a { color: #2980b9; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="verify-container">
        <?php if ($verified): ?>
            <div class="verify-success"><?php echo $message; ?></div>
        <?php else: ?>
            <div class="verify-error"><?php echo $message; ?></div>
            <div style="margin-top:1rem;">
                <a href="login.php">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 