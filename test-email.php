<?php
// Test email functionality
echo "<h2>Testing Email System</h2>";

// Test data
$emailData = [
    'to' => 'test@example.com', // Replace with your email for testing
    'subject' => 'Test Email - LA Consolacion Jewelry',
    'text' => "This is a test email to verify the email system is working.\n\nBest regards,\nThe LA Consolacion Jewelry Team"
];

echo "<p>Sending test email to: " . $emailData['to'] . "</p>";

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

echo "<p>HTTP Code: " . $httpCode . "</p>";
echo "<p>Response: " . $response . "</p>";
echo "<p>CURL Error: " . $curlError . "</p>";

if ($httpCode === 200) {
    echo "<p style='color: green;'>✅ Email test successful!</p>";
} else {
    echo "<p style='color: red;'>❌ Email test failed!</p>";
}

// Check if node server is running
echo "<h3>Checking Node Server Status</h3>";
$nodeCheck = curl_init();
curl_setopt($nodeCheck, CURLOPT_URL, 'http://localhost:3000');
curl_setopt($nodeCheck, CURLOPT_RETURNTRANSFER, true);
curl_setopt($nodeCheck, CURLOPT_TIMEOUT, 5);
$nodeResponse = curl_exec($nodeCheck);
$nodeHttpCode = curl_getinfo($nodeCheck, CURLINFO_HTTP_CODE);
curl_close($nodeCheck);

echo "<p>Node Server HTTP Code: " . $nodeHttpCode . "</p>";
if ($nodeHttpCode === 200 || $nodeHttpCode === 404) {
    echo "<p style='color: green;'>✅ Node server is running!</p>";
} else {
    echo "<p style='color: red;'>❌ Node server is not running!</p>";
    echo "<p>Please start the node server by running: <code>cd node-mailer-server && node server.js</code></p>";
}
?> 