<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

// Validate inputs
if (!$name || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Store in database
    $stmt = $conn->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $subject, $message);
    $stmt->execute();

    // Send email notification
    $mail = new PHPMailer(true);

    $mail->SMTPDebug = 2; // Enable detailed debugging
    $mail->Debugoutput = function($str, $level) {
        error_log("SMTP Debug: $str");
    };
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'eyecaretrinetra@gmail.com';
    $mail->Password = 'pomm yfiu rtvd crdm'; // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SSL;
    $mail->Port = 465;
    $mail->Timeout = 30;
    $mail->SMTPKeepAlive = true;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false
        )
    );

    $mail->setFrom('eyecaretrinetra@gmail.com', 'Trinetra Eye Care');
    $mail->addAddress('eyecaretrinetra@gmail.com');
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission: ' . $subject;
    $mail->Body = "<h3>New Contact Form Submission</h3>
                   <p><strong>Name:</strong> {$name}</p>
                   <p><strong>Email:</strong> {$email}</p>
                   <p><strong>Subject:</strong> {$subject}</p>
                   <p><strong>Message:</strong></p>
                   <p>{$message}</p>";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    error_log('Detailed error info: ' . $mail->ErrorInfo);
    
    $errorMessage = 'Failed to send email: ' . $e->getMessage();
    if (strpos($e->getMessage(), 'Could not authenticate') !== false) {
        $errorMessage = 'Email authentication failed. Please check SMTP credentials.';
    } else if (strpos($e->getMessage(), 'Connection timed out') !== false) {
        $errorMessage = 'Connection to email server failed. Please try again.';
    }
    
    echo json_encode(['success' => false, 'message' => $errorMessage]);
}