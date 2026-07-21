<?php
session_start();
require_once 'database/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check if email is verified
            if (!$user['is_verified']) {
                $error = 'Please verify your email address first. Check your inbox for the verification link.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // If admin, fetch admin level
                if ($user['role'] === 'admin') {
                    $admin_stmt = $pdo->prepare('SELECT admin_level FROM admin WHERE user_id = ?');
                    $admin_stmt->execute([$user['id']]);
                    $admin_data = $admin_stmt->fetch();
                    $_SESSION['admin_level'] = $admin_data['admin_level'] ?? null;
                    
                    header('Location: admin/overview.php');
                    exit();
                } else {
                    header('Location: index.php');
                    exit();
                }
            }
        } else {
            $error = 'Invalid email or password.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}
// Show warning if redirected from admin-check
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'admin':
    $error = 'Only authorized personnel are allowed.';
            break;
        case 'login':
            $error = 'Please login to access this page.';
            break;
        case 'role_changed':
            $error = 'Your role has been changed. Please login again.';
            break;
        case 'user_not_found':
            $error = 'User account not found or has been deleted.';
            break;
        case 'system_error':
            $error = 'System error. Please try again.';
            break;
        default:
            $error = 'An error occurred. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - LA Consolacion Jewelry</title>
  <link rel="stylesheet" href="style.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <div class="form-bg">
    <div class="form-container fade-in">
      <a href="index.php" class="form-logo-wrap" id="formLogoWrap">
        <img src="Image/LCJ.png" alt="LA Consolacion Jewelry Logo" class="form-logo">
      </a>
      <h2>Login</h2>
      <p class="form-welcome">Welcome back! Please login to your account.</p>
      <form action="login.php" method="POST">
        <div class="input-group">
          <input type="email" id="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
        <div style="text-align:right; margin-bottom:10px;">
          <a href="forgot-password.php">Forgot Password?</a>
        </div>
        <button type="submit">Login</button>
        <div class="form-divider"><span>or</span></div>
        <p>Don't have an account? <a href="registration.php">Register here</a>.</p>
      </form>
      <?php if ($error): ?>
        <div id="error-message" style="color:red; margin-top: 10px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
    </div>
  </div>
  <script>
    // Hide logo on input focus
    const formContainer = document.querySelector('.form-container');
    const logoWrap = document.getElementById('formLogoWrap');
    const inputs = formContainer.querySelectorAll('input');
    inputs.forEach(input => {
      input.addEventListener('focus', () => {
        formContainer.classList.add('hide-logo');
      });
      input.addEventListener('blur', () => {
        setTimeout(() => {
          if (![...inputs].some(inp => inp === document.activeElement)) {
            formContainer.classList.remove('hide-logo');
          }
        }, 1);
      });
    });
  </script>
</body>
</html> 