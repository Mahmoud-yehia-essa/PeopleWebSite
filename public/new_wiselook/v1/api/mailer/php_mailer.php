<?php
include_once(__DIR__ . '/UrlParameterEncryptor.php');
include_once(__DIR__ . '/vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class php_mailer
{
    // private $smtp_host = 'mail.amcserver.com';
    // private $smtp_user = 'teamsdeveloper@amcserver.com';
    // private $smtp_pass = '69_Bm0;)Aanz{E3';
    // private $smtp_port = 587;



    private $smtp_host = 'smtp.gmail.com';
    private $smtp_user = 'amctagcompany@gmail.com';
    private $smtp_pass = 'eljs ykzy lapg slad'; 
    private $smtp_port = 587;
    
    
    public function __construct() {}

    private function createMailInstance()
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_user;
            $mail->Password = $this->smtp_pass;
            $mail->Port = $this->smtp_port;
            $mail->setFrom($this->smtp_user, 'Wise Look');
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            return $mail;
        } catch (Exception $e) {
            return null;
        }
    }

    public function send_email($title, $receiver_email, $content)
    {
        $mail = $this->createMailInstance();
        if (!$mail) {
            return false;
        }

        try {
            $mail->addAddress($receiver_email, 'Dear Customer');
            $mail->Subject = $title;
            $mail->Body = '<html><head><meta charset="utf-8"><title>Survey Completed</title></head><body>' . $content . '</body></html>';
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    public function send_verification_code($receiver_email, $code)
    {
        $mail = $this->createMailInstance();
        if (!$mail) {
            return false;
        }

        try {
            $mail->addAddress($receiver_email, 'Dear Customer');
            $mail->Subject = '{Business} Verification code';
            $mail->Body = '<html><head><meta charset="utf-8"><title>Verification Code</title></head><body><div><h2>Verification Code: ' . $code . '</h2></div><div>Please use this code to reset your password.</div></body></html>';
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    public function sendMail($email, $subject, $body)
    {
        $mail = $this->createMailInstance();
        if (!$mail) {
            return "Error initializing PHPMailer";
        }

        try {
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $body;
            return $mail->send();
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    
    
        public function send_otpcode($receiver_email, $code)
    {
        $mail = $this->createMailInstance();
        if (!$mail) {
            return false;
        }

        try {
            $mail->addAddress($receiver_email, 'Dear Customer');
            $mail->Subject = '{Business} Verification code';
            $mail->Body = '<html><head><meta charset="utf-8"><title>Verification Code</title></head><body><div><h2>Verification Code: ' . $code . '</h2></div><div>Please use this code to reset your password.</div></body></html>';
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
}
