<?php
$host = 'localhost';
$db   = 's22104079_La_Consolacion';
$user = 's22104079_La_Consolacion';
$pass = 'John_8884';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Log admin actions to the logs table (simple audit log style)
if (!function_exists('log_action')) {
    function log_action($pdo, $user, $action, $details = '') {
        $stmt = $pdo->prepare("INSERT INTO logs (user, action, details, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user, $action, $details]);
    }
} 