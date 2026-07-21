<?php
require 'db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE password_reset_token = ? AND token_expiry > NOW()');
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ?, password_reset_token = NULL, token_expiry = NULL WHERE id = ?');
            $stmt->execute([$hash, $user['id']]);
            $message = 'Password reset successful! You can now <a href="login.php">login</a>.';
            $success = true;
        } else {
            $message = 'Invalid or expired token.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - LA Consolacion Jewelry</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <div class="form-bg">
      <div class="form-container fade-in">
        <a href="index.php" class="form-logo-wrap">
          <img
            src="Image/LCJ.png"
            alt="LA Consolacion Jewelry Logo"
            class="form-logo"
          />
        </a>
        <h2>Reset Password</h2>
        <p class="form-welcome">Enter your new password below.</p>
        
        <?php if ($message): ?>
          <div style="color: <?php echo $success ? '#155724' : '#721c24'; ?>; text-align: center; margin-bottom: 20px; padding: 10px; background: <?php echo $success ? '#d4edda' : '#f8d7da'; ?>; border-radius: 5px;">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
          <form action="reset-password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>" />
            <div class="input-group">
              <input
                type="password"
                name="new_password"
                placeholder="New Password"
                required
                minlength="6"
              />
            </div>
            <div class="input-group">
              <input
                type="password"
                name="confirm_password"
                placeholder="Confirm Password"
                required
                minlength="6"
              />
            </div>
            <button type="submit">Reset Password</button>
          </form>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: center;">
          <a href="login.php">Back to Login</a>
        </div>
        <div id="error-message" style="color: red"></div>
      </div>
    </div>
  </body>
</html>