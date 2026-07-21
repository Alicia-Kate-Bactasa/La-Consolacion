<?php
// database/mailer.php - Direct SMTP email sending utility using Gmail SMTP with premium HTML template

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
    
    // --- BUILD PREMIUM HTML BODY DECORATOR ---
    $clean_content = $message;
    
    // 1. Convert bullet lists (e.g. • Product (Qty: 1)) into a premium card component
    if (strpos($clean_content, '•') !== false) {
        $clean_content = preg_replace_callback('/(?:•\s+[^\n]+(?:\n|$))+/s', function($matches) {
            $items_html = '';
            $lines = explode("\n", trim($matches[0]));
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed)) continue;
                $item_text = preg_replace('/^•\s*/', '', $trimmed);
                $items_html .= '<li style="margin-bottom: 10px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 8px; list-style-type: none; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #475569; font-weight: 500;">' . htmlspecialchars($item_text) . '</span>
                                </li>';
            }
            return '<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin: 25px 0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                      <h4 style="margin: 0 0 16px 0; color: #1e293b; font-size: 15px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Order Summary:</h4>
                      <ul style="margin: 0; padding: 0; font-size: 14px;">' . $items_html . '</ul>
                    </div>';
        }, $clean_content);
    }
    
    // 2. Parse URL and turn it into a CTA button
    $has_link = preg_match('/https?:\/\/[^\s]+/', $clean_content, $url_matches);
    if ($has_link) {
        $url = $url_matches[0];
        
        $btn_text = 'Click Here';
        $btn_bg = '#1f488a';
        $btn_gradient = 'linear-gradient(135deg, #1f488a 0%, #123366 100%)';
        
        if (strpos($url, 'verify-email.php') !== false) {
            $btn_text = 'Verify Email Address';
            $btn_bg = '#10b981';
            $btn_gradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
        } elseif (strpos($url, 'reset-password.php') !== false) {
            $btn_text = 'Reset Password';
            $btn_bg = '#f59e0b';
            $btn_gradient = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
        } elseif (strpos($url, 'payment-form.php') !== false) {
            $btn_text = 'Proceed to Payment';
            $btn_bg = '#4f46e5';
            $btn_gradient = 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)';
        }
        
        // Premium centered table button wrapper for 100% email client compatibility
        $btn_html = '<table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 35px auto; border-collapse: collapse;">
                       <tr>
                         <td align="center" style="border-radius: 50px; background: ' . $btn_bg . '; background: ' . $btn_gradient . '; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);" bgcolor="' . $btn_bg . '">
                           <a href="' . $url . '" target="_blank" style="padding: 14px 38px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; color: #ffffff; text-decoration: none; font-weight: 700; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 50px;">
                             ' . $btn_text . '
                           </a>
                         </td>
                       </tr>
                     </table>';
                     
        $clean_content = str_replace($url, $btn_html, $clean_content);
    }
    
    // 3. Format lines
    $formatted_body = nl2br($clean_content);
    
    // 4. Wrap inside premium template HTML
    $html_body = '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LA Consolacion Jewelry</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 40px 10px;">
    <tr>
      <td align="center">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 24px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05); overflow: hidden; border: 1px solid #e2e8f0;">
          <!-- Header -->
          <tr>
            <td style="padding: 45px 40px 35px 40px; text-align: center; background: linear-gradient(135deg, #1f488a 0%, #123366 100%);">
              <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase;">LA CONSOLACION</h1>
              <div style="color: #93c5fd; font-size: 11px; text-transform: uppercase; letter-spacing: 3px; margin-top: 6px; font-weight: 700;">Jewelry Store</div>
            </td>
          </tr>
          
          <!-- Content Body -->
          <tr>
            <td style="padding: 45px 40px; color: #334155; font-size: 16px; line-height: 1.8;">
              ' . $formatted_body . '
            </td>
          </tr>
          
          <!-- Footer -->
          <tr>
            <td style="padding: 35px 40px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; color: #64748b; font-size: 12px; line-height: 1.6;">
              <p style="margin: 0 0 8px 0; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">La Consolacion Jewelry</p>
              <p style="margin: 0 0 8px 0;">Established 1980 | High Quality Handcrafted Jewelry</p>
              <p style="margin: 0 0 20px 0; color: #94a3b8;">This is an automated notification, please do not reply directly to this email.</p>
              <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">
                &copy; 1980 - 2026 La Consolacion Jewelry. All rights reserved.
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    // --- END PREMIUM HTML BODY DECORATOR ---

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
