<?php
// Check if Composer autoloader exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Please run "composer require phpmailer/phpmailer" first');
}

require_once 'config/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email_config = require_once 'config/email_config.php';

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->SMTPDebug = 2;                        // Enable verbose debug output
    $mail->isSMTP();                             // Send using SMTP
    $mail->Host       = $email_config['smtp_host'];
    $mail->SMTPAuth   = true;                    // Enable SMTP authentication
    $mail->Username   = $email_config['smtp_username'];
    $mail->Password   = $email_config['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $email_config['smtp_port'];
    
    // Check credentials before proceeding
    if (empty($mail->Username) || empty($mail->Password)) {
        throw new Exception('Email credentials are not configured properly');
    }

    // Recipients
    $mail->setFrom($email_config['from_email'], $email_config['from_name']);
    $mail->addAddress($email_config['smtp_username']); // Send to yourself for testing

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Oricado';
    $mail->Body    = 'This is a test email. If you receive this, your email configuration is working.';

    // Send email
    $mail->send();
    echo '<div style="color: green;">Test email sent successfully!</div>';
    
} catch (Exception $e) {
    echo '<div style="color: red;">Error: ' . $mail->ErrorInfo . '</div>';
    error_log('Mailer Error: ' . $mail->ErrorInfo);
}