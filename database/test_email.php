<?php
// database/test_email.php - Test direct SMTP email functionality

echo "<h2>Testing Direct SMTP Email System</h2>";

require_once 'mailer.php';

// Test data
$to = 'kenzho.suarez@gmail.com'; // Default test address
if (isset($_GET['to'])) {
    $to = $_GET['to'];
}

$subject = 'Test Direct SMTP - LA Consolacion Jewelry';
$message = "This is a direct SMTP test email to verify the email system is working without Node.js.\n\nBest regards,\nThe LA Consolacion Jewelry Team";

echo "<p>Attempting to send test email directly using Gmail SMTP to: <strong>" . htmlspecialchars($to) . "</strong></p>";
echo "<p>You can specify a target address via URL query: <code>test_email.php?to=your_email@gmail.com</code></p>";

$result = send_smtp_email($to, $subject, $message);

if ($result['success']) {
    echo "<p style='color: green; font-weight: bold;'>✅ Email test successful! The message has been sent directly via Google SMTP.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Email test failed!</p>";
    echo "<p>Error Details: <pre>" . htmlspecialchars($result['error']) . "</pre></p>";
    echo "<p><strong>Troubleshooting Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Verify that the Gmail account: <code>kenzho.suarez@gmail.com</code> is active.</li>";
    echo "<li>Verify that the App Password: <code>ouuk papy uruz ndig</code> is valid. (If you changed your Google account password, you need to generate a new App Password).</li>";
    echo "<li>Ensure that outgoing port 465 (SSL) is not blocked by your hosting provider or local network firewall.</li>";
    echo "</ul>";
}
?>