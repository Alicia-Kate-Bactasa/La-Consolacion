<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../database/db.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No payment ID provided']);
    exit;
}

$payment_id = intval($_GET['id']);
$stmt = $pdo->prepare('SELECT p.reference_number, p.service, p.uploaded_at, p.image, p.mobile, p.amount, o.id as order_id, o.date_ordered
    FROM payments p
    JOIN orders o ON o.payment_id = p.id
    WHERE p.id = ?');
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($payment) {
    // Handle cases where payment data might be incomplete
    $payment['image_url'] = !empty($payment['image']) ? '../Image/payment-upload/' . $payment['image'] : '';
    $payment['reference_number'] = $payment['reference_number'] ?? null;
    $payment['service'] = $payment['service'] ?? null;
    $payment['uploaded_at'] = $payment['uploaded_at'] ?? null;
    $payment['mobile'] = $payment['mobile'] ?? null;
    $payment['amount'] = $payment['amount'] ?? 0;
    
    // Fetch order total
    $order_id = $payment['order_id'];
    $total_stmt = $pdo->prepare('SELECT SUM(oi.price_at_purchase * oi.quantity) as order_total FROM order_items oi WHERE oi.order_id = ?');
    $total_stmt->execute([$order_id]);
    $order_total = $total_stmt->fetchColumn();
    $payment['order_total'] = $order_total ? floatval($order_total) : 0.0;
    echo json_encode($payment);
} else {
    echo json_encode(['error' => 'Payment not found']);
} 