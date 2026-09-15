<?php
require_once __DIR__ . '/auth.php';

if (!function_exists('sapg_smtp_read_response')) {
    function sapg_smtp_read_response($socket, $expectedCode) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        $code = substr($response, 0, 3);
        if ($code !== $expectedCode) {
            error_log("SMTP Response Error: Expected $expectedCode, got $response");
            return false;
        }
        return $response;
    }
}

function sendEmail($to, $subject, $body) {
    $env = getEnvConfig();
    $host = $env['SMTP_HOST'] ?? '';
    $port = intval($env['SMTP_PORT'] ?? 587);
    $user = $env['SMTP_USER'] ?? '';
    $pass = $env['SMTP_PASS'] ?? '';
    $fromEmail = $env['SMTP_FROM_EMAIL'] ?? 'noreply@example.com';
    $fromName = $env['SMTP_FROM_NAME'] ?? 'SAPG System';

    // Fall back to built-in mail() if SMTP_HOST is empty or fsockopen is disabled by hosting provider
    if (empty($host) || !function_exists('fsockopen')) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $fromName <$fromEmail>" . "\r\n";
        return mail($to, $subject, $body, $headers);
    }

    $encryption = ($port === 465) ? 'ssl://' : '';
    $socket = @fsockopen($encryption . $host, $port, $errno, $errstr, 15);
    if (!$socket) {
        error_log("SMTP connection failed: $errstr ($errno). Falling back to php mail().");
        // Fall back to built-in mail() if connection fails
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $fromName <$fromEmail>" . "\r\n";
        return mail($to, $subject, $body, $headers);
    }

    try {
        if (sapg_smtp_read_response($socket, '220') === false) {
            fclose($socket);
            return false;
        }

        $helloHost = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        fwrite($socket, "EHLO $helloHost\r\n");
        if (sapg_smtp_read_response($socket, '250') === false) {
            fclose($socket);
            return false;
        }

        // STARTTLS upgrade on port 587
        if ($port === 587) {
            fwrite($socket, "STARTTLS\r\n");
            if (sapg_smtp_read_response($socket, '220') === false) {
                fclose($socket);
                return false;
            }
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log("SMTP STARTTLS failed");
                fclose($socket);
                return false;
            }
            fwrite($socket, "EHLO $helloHost\r\n");
            if (sapg_smtp_read_response($socket, '250') === false) {
                fclose($socket);
                return false;
            }
        }

        // Authentication
        if (!empty($user) && !empty($pass)) {
            fwrite($socket, "AUTH LOGIN\r\n");
            if (sapg_smtp_read_response($socket, '334') === false) {
                fclose($socket);
                return false;
            }

            fwrite($socket, base64_encode($user) . "\r\n");
            if (sapg_smtp_read_response($socket, '334') === false) {
                fclose($socket);
                return false;
            }

            fwrite($socket, base64_encode($pass) . "\r\n");
            if (sapg_smtp_read_response($socket, '235') === false) {
                fclose($socket);
                return false;
            }
        }

        fwrite($socket, "MAIL FROM: <$fromEmail>\r\n");
        if (sapg_smtp_read_response($socket, '250') === false) {
            fclose($socket);
            return false;
        }

        fwrite($socket, "RCPT TO: <$to>\r\n");
        if (sapg_smtp_read_response($socket, '250') === false) {
            fclose($socket);
            return false;
        }

        fwrite($socket, "DATA\r\n");
        if (sapg_smtp_read_response($socket, '354') === false) {
            fclose($socket);
            return false;
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromEmail>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "\r\n";

        fwrite($socket, $headers . $body . "\r\n.\r\n");
        if (sapg_smtp_read_response($socket, '250') === false) {
            fclose($socket);
            return false;
        }

        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        return true;
    } catch (Exception $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        @fclose($socket);
        return false;
    }
}

function sendWelcomeEmail($toEmail, $userName, $userId, $password) {
    $env = getEnvConfig();
    $siteName = $env['SITE_NAME'] ?? 'SAPG';
    $siteUrl = $env['SITE_URL'] ?? 'http://localhost';
    
    $templatePath = __DIR__ . '/templates/welcome_email.html';
    if (!file_exists($templatePath)) {
        error_log("Welcome email template not found.");
        return false;
    }
    
    $template = file_get_contents($templatePath);
    
    $placeholders = [
        '{{USER_NAME}}' => htmlspecialchars($userName),
        '{{USER_ID}}' => htmlspecialchars($userId),
        '{{PASSWORD}}' => htmlspecialchars($password),
        '{{SITE_NAME}}' => htmlspecialchars($siteName),
        '{{SITE_URL}}' => htmlspecialchars($siteUrl)
    ];
    
    $body = str_replace(array_keys($placeholders), array_values($placeholders), $template);
    
    $subject = "Welcome to $siteName - Registration Successful";
    return sendEmail($toEmail, $subject, $body);
}
?>
