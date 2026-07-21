<?php
require_once 'admin-check.php';
require_once 'db.php';

// Handle AJAX status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['new_status'];
    $allowed = ['pending', 'processing', 'waiting_for_buyer', 'completed', 'cancelled'];
    
    if (in_array($newStatus, $allowed)) {
        try {
            // Get the current status before updating
            $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ?');
            $stmt->execute([$orderId]);
            $currentOrder = $stmt->fetch();
            $oldStatus = $currentOrder['status'] ?? 'unknown';
            
            // Update the order status and set date_completed for completed/cancelled orders
            if ($newStatus === 'completed' || $newStatus === 'cancelled') {
                $stmt = $pdo->prepare('UPDATE orders SET status = ?, date_completed = NOW() WHERE id = ?');
                $stmt->execute([$newStatus, $orderId]);
            } else {
                $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
                $stmt->execute([$newStatus, $orderId]);
            }
            
            // Log the status change
            if (isset($_SESSION['username'])) {
                $details = "Order ID: {$orderId}; Status changed from '{$oldStatus}' to '{$newStatus}'";
                log_action($pdo, $_SESSION['username'], 'Change Order Status', $details);
            }
            
            // Send email notification for different statuses
            if (in_array($newStatus, ['processing', 'waiting_for_buyer', 'completed', 'cancelled'])) {
                // Get user email and order details
                $stmt = $pdo->prepare('SELECT u.email, u.first_name, o.id as order_id FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?');
                $stmt->execute([$orderId]);
                $orderInfo = $stmt->fetch();
                
                if ($orderInfo) {
                    // Get products in the order
                    $stmt = $pdo->prepare('SELECT oi.quantity, p.name, p.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
                    $stmt->execute([$orderId]);
                    $orderItems = $stmt->fetchAll();
                    
                    // Build products list
                    $productsList = '';
                    foreach ($orderItems as $item) {
                        $productsList .= "• {$item['name']} (Qty: {$item['quantity']})\n";
                    }
                    
                    // Define email content based on status
                    $emailConfig = [
                        'processing' => [
                            'subject' => 'Your Order is Being Processed - LA Consolacion Jewelry',
                            'message' => "Dear {$orderInfo['first_name']},\n\nWe are processing your order #{$orderInfo['order_id']}.\n\nYour order includes:\n{$productsList}\nWe're working hard to prepare your jewelry items with care and attention to detail.\n\nThank you for choosing LA Consolacion Jewelry!\n\nBest regards,\nThe LA Consolacion Jewelry Team"
                        ],
                        'waiting_for_buyer' => [
                            'subject' => 'Your Order is Ready for Pickup - LA Consolacion Jewelry',
                            'message' => "Dear {$orderInfo['first_name']},\n\nReady for pickup! Your order #{$orderInfo['order_id']} is ready.\n\nYour order includes:\n{$productsList}\nContact us or go to the location to collect your jewelry items.\n\nYou can reach us through:\n• Phone: [kenzho.suarez@gmail.com]\n• Email: [kenzho.suarez@gmail.com]\n• Visit our store\n\nThank you for choosing LA Consolacion Jewelry!\n\nBest regards,\nThe LA Consolacion Jewelry Team"
                        ],
                        'completed' => [
                            'subject' => 'Your Order is Completed - LA Consolacion Jewelry',
                            'message' => "Dear {$orderInfo['first_name']},\n\nThe order #{$orderInfo['order_id']} is completed.\n\nYour order includes:\n{$productsList}\nThank you for choosing LA Consolacion Jewelry!\n\nBest regards,\nThe LA Consolacion Jewelry Team"
                        ],
                        'cancelled' => [
                            'subject' => 'Your Order Has Been Cancelled - LA Consolacion Jewelry',
                            'message' => "Dear {$orderInfo['first_name']},\n\nYou cancelled your order #{$orderInfo['order_id']}.\n\nYour order included:\n{$productsList}\nIf you have any questions, please contact us.\n\nThank you for considering LA Consolacion Jewelry!\n\nBest regards,\nThe LA Consolacion Jewelry Team"
                        ]
                    ];
                    
                    $emailData = [
                        'to' => $orderInfo['email'],
                        'subject' => $emailConfig[$newStatus]['subject'],
                        'text' => $emailConfig[$newStatus]['message']
                    ];
                    
                    // Send email via node-mailer server
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/send-order-notification');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);
                    
                    // Log email attempt (success or failure)
                    if ($httpCode === 200) {
                        $responseData = json_decode($response, true);
                        log_action($pdo, $_SESSION['username'] ?? 'system', 'Email Notification', "{$newStatus} notification sent to {$orderInfo['email']} for Order #{$orderId}. Message ID: " . ($responseData['messageId'] ?? 'unknown'));
                    } else {
                        log_action($pdo, $_SESSION['username'] ?? 'system', 'Email Notification Failed', "Failed to send {$newStatus} notification to {$orderInfo['email']} for Order #{$orderId}. HTTP Code: {$httpCode}, Response: {$response}, CURL Error: {$curlError}");
                    }
                }
            }
            
            // Return success response
            http_response_code(200);
            echo "Status updated successfully";
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error updating status: " . $e->getMessage();
        }
    } else {
        http_response_code(400);
        echo "Invalid status";
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}
?> 