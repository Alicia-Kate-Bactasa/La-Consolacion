<?php
require 'database/db.php';

date_default_timezone_set('Asia/Manila');

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $pdo->prepare('UPDATE users SET password_reset_token = ?, token_expiry = ? WHERE email = ?');
        $stmt->execute([$token, $expiry, $email]);

        // Send email via direct Gmail SMTP
        require_once 'database/mailer.php';
        $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=$token";
        $message_body = "Click this link to reset your password:

$reset_link

If you did not request this, please ignore this email.";
        send_smtp_email($email, 'Password Reset', $message_body);
        
        $message = "If this email exists in our system, a reset link will be sent.";
        $message_type = 'success';
    } else {
        $message = "If this email exists in our system, a reset link will be sent.";
        $message_type = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password - LA Consolacion Jewelry</title>
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
        <h2>Forgot Password</h2>
        <p class="form-welcome">
          Enter your email to receive a password reset link.
        </p>
        
        <?php if ($message): ?>
          <div style="color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; text-align: center; margin-bottom: 20px; padding: 10px; background: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; border-radius: 5px;">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>
        
        <form action="forgot-password.php" method="POST">
          <div class="input-group">
            <input
              type="email"
              name="email"
              placeholder="Enter your email"
              required
              value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
            />
          </div>
          <button type="submit">Send Reset Link</button>
        </form>
        <div style="margin-top: 20px; text-align: center;">
          <a href="login.php">Back to Login</a>
        </div>
        <div id="error-message" style="color: red"></div>
      </div>
    </div>
  </body>
</html>
