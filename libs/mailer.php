<?php
require_once __DIR__ . '/auth.php';

function sendEmail($to, $subject, $body) {
    $env = getEnvConfig();
    $fromName = $env['SMTP_FROM_NAME'] ?? 'SAPG System';
    $fromEmail = $env['SMTP_FROM_EMAIL'] ?? 'noreply@example.com';
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $fromName <$fromEmail>" . "\r\n";

    return mail($to, $subject, $body, $headers);
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
