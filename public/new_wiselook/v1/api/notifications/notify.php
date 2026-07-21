<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

include_once './notification_class.php';

$message = "hello";
// $token_receiver = "fkVb3bSjAkmctpbdAgTkjV:APA91bGGB3fzNWRa0P4Pa-bbmkJJ9fGHPp0QA-BsyFE7bj4cvKeHb3-kF-KCzkVvYo_zqoP8dXyXEzeCjUZ3by4IDsmFcseKXm4E0CZNX8dno7eJkTBXf6g"; //ios token /
$token_receiver ="f0MRWM7hSaWmHtYMP9jAhg:APA91bHdryu_SkZ7T1BAXYzeflFevwmK8Ey2z9PFTmVzUpPsfasnE0fa9WqCTpUu6k_ZJrgF6XsYeTHVqAd1cOh72wLi3hlYbWorG1Y12U5tpba_ISq3qr0"; // android token
$content_type = "post";
$content_id = "1";

// نداء الدالة بشكل static
$result = NotificationClass::sendStaticNotification($message, $token_receiver, $content_type, $content_id);

var_dump($result);
