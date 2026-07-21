<?php
session_start();
require_once '../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);

try {
    // Fetch order details with customer and payment info - only for the logged-in user
    $stmt = $pdo->prepare('
        SELECT o.*, u.username, u.email, u.first_name, u.last_name, pay.reference_number, pay.service, pay.mobile, pay.amount, pay.uploaded_at
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN payments pay ON o.payment_id = pay.id
        WHERE o.id = ? AND o.user_id = ? AND o.deleted = 0
    ');
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Fetch order items with product details
    $item_stmt = $pdo->prepare('
        SELECT oi.*, p.name as product_name, p.description as product_description, p.image as product_image, p.type as product_type
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ');
    $item_stmt->execute([$order_id]);
    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price_at_purchase'] * $item['quantity'];
    }

    // Determine status styling
    $statusConfig = [
        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'dot' => 'bg-yellow-400', 'label' => 'Pending'],
        'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'dot' => 'bg-blue-400', 'label' => 'Processing'],
        'waiting_for_buyer' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'dot' => 'bg-purple-400', 'label' => 'Waiting for Buyer'],
        'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'dot' => 'bg-green-400', 'label' => 'Completed'],
        'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'dot' => 'bg-red-400', 'label' => 'Cancelled'],
        'confirmed' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'dot' => 'bg-indigo-400', 'label' => 'Confirmed']
    ];

    $currentStatus = $order['status'] ?? 'pending';
    $statusStyle = $statusConfig[$currentStatus] ?? $statusConfig['pending'];

    // Prepare response
    $response = [
        'order' => [
            'id' => $order['id'],
            'date_ordered' => $order['date_ordered'],
            'date_completed' => $order['date_completed'],
            'status' => $currentStatus,
            'status_style' => $statusStyle,
            'reference_number' => $order['reference_number'],
            'service' => $order['service']
        ],
        'customer' => [
            'name' => $order['first_name'] . ' ' . $order['last_name'],
            'email' => $order['email'] ?? 'N/A',
            'mobile' => $order['mobile'] ?? 'N/A'
        ],
        'products' => array_map(function($item) {
            return [
                'id' => $item['product_id'],
                'name' => $item['product_name'],
                'description' => $item['product_description'],
                'image' => $item['product_image'],
                'type' => $item['product_type'],
                'quantity' => $item['quantity'],
                'price' => $item['price_at_purchase'],
                'total' => $item['price_at_purchase'] * $item['quantity']
            ];
        }, $items),
        'payment' => [
            'reference_number' => $order['reference_number'],
            'service' => $order['service'],
            'amount' => $order['amount'],
            'uploaded_at' => $order['uploaded_at'],
            'mobile' => $order['mobile']
        ],
        'total' => $total,
        'can_cancel' => in_array($currentStatus, ['pending', 'processing', 'confirmed', 'waiting_for_buyer'])
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 