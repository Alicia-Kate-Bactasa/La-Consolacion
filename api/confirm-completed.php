<?php
require_once '../admin/check.php';
require_once '../database/db.php';

if (!isset($_POST['order_id'])) exit('Missing order_id');
$order_id = intval($_POST['order_id']);
$status = $_POST['status'] ?? 'completed'; // get status from POST, default to completed

try {
    // Update the order status
    $stmt = $pdo->prepare('UPDATE orders SET status = ?, date_completed = NOW() WHERE id = ?');
    $stmt->execute([$status, $order_id]);
    
    // Log the action
    if (isset($_SESSION['username'])) {
        $details = "Order ID: {$order_id}; Status: {$status}";
        log_action($pdo, $_SESSION['username'], 'Complete Order', $details);
    }
    
    echo 'OK';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?> 