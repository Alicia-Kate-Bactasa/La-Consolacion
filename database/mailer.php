<?php
// database/mailer.php - Direct SMTP email sending utility using Gmail SMTP

function send_smtp_email($to, $subject, $message, $from_name = 'LA Consolacion Jewelry') {
    // Include SMTP configuration
    $config_path = __DIR__ . '/config.php';
    if (!file_exists($config_path)) {
        error_log("SMTP config file not found. Please create database/config.php using database/config.example.php as a template.");
        return [
            'success' => false,
            'error' => "SMTP Configuration not found."
        ];
    }
    require_once $config_path;
    
    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 465; // SSL port
    $username = SMTP_USER;
    $password = SMTP_PASS;
    
    // Open connection to SMTP server
    $socket = @fsockopen('ssl://' . $smtp_host, $smtp_port, $errno, $errstr, 15);
    if (!$socket) {
        error_log("SMTP connection failed: $errstr ($errno)");
        return [
            'success' => false,
            'error' => "Connection failed: $errstr ($errno)"
        ];
    }
    
    function get_smtp_response($socket) {
        $response = "";
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) == " ") {
                break;
            }
        }
        return $response;
    }
    
    // Read greeting
    get_smtp_response($socket);
    
    // Send EHLO
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    fwrite($socket, "EHLO " . $host . "\r\n");
    get_smtp_response($socket);
    
    // Send AUTH LOGIN
    fwrite($socket, "AUTH LOGIN\r\n");
    get_smtp_response($socket);
    
    // Send Username (base64 encoded)
    fwrite($socket, base64_encode($username) . "\r\n");
    get_smtp_response($socket);
    
    // Send Password (base64 encoded)
    fwrite($socket, base64_encode($password) . "\r\n");
    $auth_response = get_smtp_response($socket);
    if (strpos($auth_response, '235') === false) {
        error_log("SMTP Authentication failed: " . $auth_response);
        fclose($socket);
        return [
            'success' => false,
            'error' => "Authentication failed: " . trim($auth_response)
        ];
    }
    
    // Send MAIL FROM
    fwrite($socket, "MAIL FROM:<$username>\r\n");
    get_smtp_response($socket);
    
    // Send RCPT TO
    fwrite($socket, "RCPT TO:<$to>\r\n");
    $rcpt_response = get_smtp_response($socket);
    if (strpos($rcpt_response, '250') === false && strpos($rcpt_response, '251') === false) {
        error_log("SMTP RCPT TO failed: " . $rcpt_response);
        fclose($socket);
        return [
            'success' => false,
            'error' => "Recipient rejected: " . trim($rcpt_response)
        ];
    }
    
    // Send DATA
    fwrite($socket, "DATA\r\n");
    get_smtp_response($socket);
    
    // Prepare headers and body
    $headers = [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "From: =?UTF-8?B?" . base64_encode($from_name) . "?= <$username>",
        "To: <$to>",
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "Date: " . date('r'),
        "Message-ID: <" . time() . "-" . uniqid() . "@gmail.com>"
    ];
    
    // Convert plain text to HTML paragraphs if HTML is not already present
    $html_body = $message;
    if (strpos($message, '<html') === false && strpos($message, '<body') === false) {
        $html_body = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">' . 
                     nl2br(htmlspecialchars($message)) . 
                     '</body></html>';
    }
    
    $email_data = implode("\r\n", $headers) . "\r\n\r\n" . $html_body . "\r\n.\r\n";
    
    fwrite($socket, $email_data);
    $data_response = get_smtp_response($socket);
    
    // Send QUIT
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
    
    $success = strpos($data_response, '250') !== false;
    return [
        'success' => $success,
        'error' => $success ? null : "Failed to send data: " . trim($data_response)
    ];
}
