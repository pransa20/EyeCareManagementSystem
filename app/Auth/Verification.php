<?php

namespace App\Auth;

use App\Mail\Mailer;

class Verification {
    private $mailer;

    public function __construct() {
        $this->mailer = new Mailer();
    }

    public function sendVerificationEmail($email, $token) {
        $verificationLink = $this->generateVerificationLink($token);
        $subject = 'Verify Your Email - Trinetra Eye Care';
        $body = $this->createEmailTemplate($verificationLink);
        return $this->mailer->send($email, $subject, $body);
    }

    public function generateVerificationToken() {
        return bin2hex(random_bytes(32));
    }

    private function generateVerificationLink($token) {
        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
        return $baseUrl . '/verify.php?token=' . $token;
    }

    private function createEmailTemplate($verificationLink) {
        return "<html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Welcome to Trinetra Eye Care!</h2>
                <p>Thank you for registering with us. Please click the button below to verify your email address:</p>
                <p style='text-align: center;'>
                    <a href='{$verificationLink}' 
                       style='background-color: #4CAF50; 
                              color: white; 
                              padding: 14px 20px; 
                              text-decoration: none; 
                              border-radius: 4px; 
                              display: inline-block;'>
                        Verify Email
                    </a>
                </p>
                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                <p>{$verificationLink}</p>
                <p>This link will expire in 24 hours.</p>
                <p>Best regards,<br>Trinetra Eye Care Team</p>
            </body>
        </html>";
    }
}