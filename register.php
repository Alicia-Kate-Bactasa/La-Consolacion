<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $errors = [];
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
            // Send verification email
            $verification_link = "http://localhost/LCJ-ver2/verify-email.php?token=$token";
            $mailer_url = "http://localhost:3000/send-reset"; // Updated to match NodeMailer endpoint
            $postdata = http_build_query([
                'to' => $email,
                'subject' => 'Verify your account',
                'text' => "Click this link to verify your account: $verification_link"
            ]);
            $opts = ['http' =>
                [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                ]
            ];
            $context  = stream_context_create($opts);
            $mail_result = @file_get_contents($mailer_url, false, $context);
            if ($mail_result === FALSE) {
                echo '<div style="color:red;">Warning: Verification email could not be sent. Please contact support or try again later.</div>';
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
            echo "Registration successful! Please check your email to verify your account.";
            exit();
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
    if (count($errors) > 0) {
        echo '<div style="color:red;">' . implode('<br>', $errors) . '</div>';
        echo '<a href="registration.html">Back to registration</a>';
    }
} else {
    header('Location: registration.html');
    exit();
}
?> 