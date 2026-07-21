<?php
// Test script to verify email functionality for order status changes
require_once 'db.php';

// Test order ID (you can change this to a real order ID)
$test_order_id = 1; // Change this to an actual order ID from your database

echo "Testing email functionality for order status change...\n";
echo "Order ID: $test_order_id\n\n";

// Test the update-order-status.php functionality
$post_data = http_build_query([
    'order_id' => $test_order_id,
    'new_status' => 'completed'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/LCJ-ver2/update-order-status.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: $http_code\n";
echo "Response:\n$response\n\n";

// Check if node-mailer server is running
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$mailer_response = curl_exec($ch);
$mailer_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Node Mailer Server Status:\n";
echo "HTTP Code: $mailer_http_code\n";
echo "Response: $mailer_response\n\n";

if ($mailer_http_code == 200) {
    echo "✅ Node Mailer Server is running!\n";
} else {
    echo "❌ Node Mailer Server is not responding. Please start it with: cd node-mailer-server && node server.js\n";
}

echo "\nTest completed. Check the node-mailer server console for email sending logs.\n";
?> 