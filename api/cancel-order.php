<?php
session_start();
require_once '../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// Check if order ID is provided
if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'Order ID is required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'];

try {
    // Check if order exists and belongs to user
    $stmt = $pdo->prepare("
        SELECT o.id, o.status, SUM(oi.quantity * oi.price_at_purchase) as total_amount
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ? AND o.user_id = ? AND o.deleted = 0
        GROUP BY o.id
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit();
    }
    
    // Check if order can be cancelled
    $cancellableStatuses = ['pending', 'processing', 'confirmed', 'waiting_for_buyer'];
    if (!in_array($order['status'], $cancellableStatuses)) {
        echo json_encode(['success' => false, 'error' => 'Order cannot be cancelled in its current status (' . $order['status'] . ')']);
        exit();
    }
    
    // Check if order is already cancelled
    if ($order['status'] === 'cancelled') {
        echo json_encode(['success' => false, 'error' => 'Order is already cancelled']);
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update order status to cancelled and set completion date
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'cancelled', date_completed = NOW() 
        WHERE id = ?
    ");
    $result = $stmt->execute([$order_id]);
    
    if (!$result) {
        throw new Exception('Failed to update order status');
    }
    
    // Get order items to restore stock
    $stmt = $pdo->prepare("
        SELECT oi.product_id, oi.quantity, p.stock 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if order has items
    if (empty($orderItems)) {
        echo json_encode(['success' => false, 'error' => 'Order has no items to restore']);
        exit();
    }
    
    // Restore stock for each item
    foreach ($orderItems as $item) {
        $newStock = $item['stock'] + $item['quantity'];
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock = ? 
            WHERE id = ?
        ");
        $result = $stmt->execute([$newStock, $item['product_id']]);
        
        if (!$result) {
            throw new Exception('Failed to restore stock for product ID: ' . $item['product_id']);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order #' . $order_id . ' cancelled successfully. Stock has been restored.',
        'order_id' => $order_id,
        'items_restored' => count($orderItems)
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 