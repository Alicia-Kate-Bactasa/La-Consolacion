<?php
require 'database/db.php';

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    }
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    }
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $errors[] = 'Only Gmail accounts are allowed.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email already registered.';
    }
    
    if (count($errors) === 0) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 day"));
        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, username, email, password, role, is_verified, verification_token, token_expires_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)');
        if ($stmt->execute([$first_name, $last_name, $username, $email, $hash, $role, $token, $expires])) {
            // Send verification email via direct Gmail SMTP
            require_once 'database/mailer.php';
            $verification_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify-email.php?token=$token";
            $message_body = "Thank you for registering at LA Consolacion Jewelry!

Please click this link to verify your email address and activate your account:

$verification_link";
            $mail_result = send_smtp_email($email, 'Verify Your Email Address', $message_body);
            if (!$mail_result['success']) {
                $errors[] = 'Warning: Verification email could not be sent. Please contact support or try again later. Error: ' . $mail_result['error'];
            } else {
                $success_message = 'Registration successful! Please check your email to verify your account.';
            }
            
            // Log registration
            session_start();
            $session_username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
            $session_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
            if ($session_username && $session_role === 'admin') {
                log_action($pdo, $session_username, 'Admin Create User', "Admin created new user: $username ($email)");
            } else {
                log_action($pdo, $username, 'Register User', "Registered new user: $username ($email)");
            }
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - LA Consolacion Jewelry</title>
    <link rel="stylesheet" href="style.css" />
    <link
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
      rel="stylesheet"
    />
    <script>
    (function() {
      const isLoggedIn = <?php echo (isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['admin_logged_in'])) ? 'true' : 'false'; ?>;
      if (isLoggedIn) {
        if (!sessionStorage.getItem('session_active')) {
          const pathname = window.location.pathname;
          let logoutUrl = 'logout.php';
          if (pathname.includes('/admin/')) {
            logoutUrl = '../logout.php';
          } else if (pathname.includes('/payment/')) {
            logoutUrl = '../logout.php';
          }
          window.location.href = logoutUrl;
        } else {
          sessionStorage.setItem('session_active', 'true');
        }
      } else {
        sessionStorage.setItem('session_active', 'true');
      }
    })();
    </script>
  </head>
  <body>
    <div class="form-bg">
      <div class="form-container fade-in">
        <a href="index.php" class="form-logo-wrap" id="formLogoWrap">
          <img
            src="Image/LCJ.png"
            alt="LA Consolacion Jewelry Logo"
            class="form-logo"
          />
        </a>
        <h2>Register</h2>
        <p class="form-welcome">
          Create your account to start your jewelry journey.
        </p>
        
        <?php if ($success_message): ?>
          <div style="color: green; text-align: center; margin-bottom: 20px; padding: 10px; background: #d4edda; border-radius: 5px;">
            <?php echo htmlspecialchars($success_message); ?>
          </div>
        <?php endif; ?>
        
        <?php if (count($errors) > 0): ?>
          <div style="color: red; text-align: center; margin-bottom: 20px; padding: 10px; background: #f8d7da; border-radius: 5px;">
            <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
          </div>
        <?php endif; ?>
        
        <form
          action="registration.php"
          method="POST"
          onsubmit="return validateForm()"
        >
          <div class="input-group">
            <input
              type="text"
              id="first_name"
              name="first_name"
              placeholder="First Name"
              required
              value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
            />
          </div>
          <div class="input-group">
            <input
              type="text"
              id="last_name"
              name="last_name"
              placeholder="Last Name"
              required
              value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
            />
          </div>
          <div class="input-group">
            <input
              type="email"
              id="email"
              name="email"
              placeholder="Gmail only"
              required
              pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
              title="Gmail only"
              value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
            />
          </div>
          <div class="input-group">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Password"
              required
              minlength="6"
            />
          </div>
          <div class="input-group">
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              placeholder="Confirm Password"
              required
              minlength="6"
            />
          </div>
          <button type="submit">Register</button>
          <div class="form-divider"><span>or</span></div>
          <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
        <div id="error-message" style="color: red"></div>
      </div>
    </div>
    <script>
      function validateForm() {
        var first_name = document.getElementById("first_name").value.trim();
        var last_name = document.getElementById("last_name").value.trim();
        var email = document.getElementById("email").value;
        var password = document.getElementById("password").value;
        var confirm = document.getElementById("confirm_password").value;
        var error = "";

        if (first_name === "") {
          error = "First name is required.";
        } else if (last_name === "") {
          error = "Last name is required.";
        } else if (!email.endsWith("@gmail.com")) {
          error = "Only Gmail accounts are allowed.";
        } else if (password !== confirm) {
          error = "Passwords do not match.";
        } else if (password.length < 6) {
          error = "Password must be at least 6 characters.";
        }

        document.getElementById("error-message").innerText = error;
        return error === "";
      }

      // Hide logo on input focus
      const formContainer = document.querySelector(".form-container");
      const logoWrap = document.getElementById("formLogoWrap");
      const inputs = formContainer.querySelectorAll("input");
      inputs.forEach((input) => {
        input.addEventListener("focus", () => {
          formContainer.classList.add("hide-logo");
        });
        input.addEventListener("blur", () => {
          // Only show logo if no input is focused
          setTimeout(() => {
            if (![...inputs].some((inp) => inp === document.activeElement)) {
              formContainer.classList.remove("hide-logo");
            }
          }, 1);
        });
      });
    </script>
  </body>
</html> 