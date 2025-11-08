<?php

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'pranisharajk@gmail.com';
        $this->mailer->Password = 'qmvh qqxp yvxw rlxj';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->isHTML(true);
        $this->mailer->SMTPDebug = 0;
        $this->mailer->Timeout = 30;
        $this->mailer->CharSet = 'UTF-8';
    }

    public function send($to, $subject, $body) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->setFrom($this->mailer->Username, 'Trinetra Eye Care');
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            if (!$this->mailer->send()) {
                error_log('Mailer Error: ' . $this->mailer->ErrorInfo);
                return false;
            }
            return true;
        } catch (Exception $e) {
            error_log('Mailer Exception: ' . $e->getMessage());
            return false;
        }
    }
}