<?php
require_once 'admin-check.php';
require_once 'db.php';

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

$order_id = intval($_GET['order_id']);

try {
    // Fetch order details with customer and payment info (including orders without payments)
    $stmt = $pdo->prepare('
        SELECT o.*, u.username, u.email, pay.reference_number, pay.service, pay.mobile, pay.amount
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN payments pay ON o.payment_id = pay.id
        WHERE o.id = ?
    ');
    $stmt->execute([$order_id]);
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

    // Prepare response
    $response = [
        'order' => [
            'id' => $order['id'],
            'date_ordered' => $order['date_ordered'],
            'date_completed' => $order['date_completed'],
            'status' => $order['status'] ?? 'pending', // Default to 'pending' if null
            'reference_number' => $order['reference_number'],
            'service' => $order['service']
        ],
        'customer' => [
            'name' => $order['username'],
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
        'total' => $total
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 