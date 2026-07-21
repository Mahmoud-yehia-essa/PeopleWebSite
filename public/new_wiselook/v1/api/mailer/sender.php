<?php

ini_set("display_errors", 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 include_once './php_mailer.php';

$php_mailer = new php_mailer();

//$order_id =  //$_POST["order_id"];
$receiver_email = "developer@amctag.com"; // $_POST["receiver_email"];
$title = "Survey Complete";
$subject = "Testing Mailer";
$body ="test test <br> more test";

// echo $php_mailer->send_email($title, $receiver_email, 15);
echo $php_mailer->send_verification_code($receiver_email, 123456);
// $php_mailer->sendMail($receiver_email, $subject, $body);

?>