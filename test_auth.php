<?php
require_once __DIR__ . '/database.php'; // Adjust this line if necessary
require_once __DIR__ . '/../vendor/autoload.php'; // Include the Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Your Auth class code here
class Auth {
    private $conn;
    private $mail;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->initializeMailer();
    }

    private function initializeMailer() {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'your-email@gmail.com'; // Replace with your email
        $this->mail->Password = 'your-app-password'; // Replace with your app password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->setFrom('your-email@gmail.com', 'Trinetra Eye Care');
    }

    // Add other methods (register, login, etc.) here
}

// Example usage
$auth = new Auth();
// You can call methods on the $auth object here
?>