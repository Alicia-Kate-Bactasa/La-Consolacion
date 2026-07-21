<?php
session_start();
require_once 'db.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin-overview.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        // Check if user exists and is admin
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND role = "admin"');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username']; // Add this line
            header('Location: admin-overview.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - LA Consolacion Jewelry</title>
  <link rel="stylesheet" href="style.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <div class="form-bg">
    <div class="form-container fade-in">
      <a href="index.php" class="form-logo-wrap" id="formLogoWrap">
        <img src="Image/LCJ.png" alt="LA Consolacion Jewelry Logo" class="form-logo">
      </a>
      <h2>Admin Login</h2>
      <p class="form-welcome">Welcome! Please login to access admin panel.</p>
      <form action="admin-login.php" method="POST">
        <div class="input-group">
          <input type="text" id="username" name="username" placeholder="Username" required>
        </div>
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit">Login to Admin Panel</button>
        <div class="form-divider"><span>or</span></div>
        <p>Back to <a href="index.php">main site</a>.</p>
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