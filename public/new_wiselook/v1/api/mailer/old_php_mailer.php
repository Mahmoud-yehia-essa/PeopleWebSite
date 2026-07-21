<?php /* <meta charset="utf-8">  */ ?>
<?php
//ini_set("display_errors", 1);
include_once(__DIR__ . '/UrlParameterEncryptor.php');
include_once(__DIR__ .'/vendor/autoload.php');

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
//include_once './vendor/autoload.php';


class php_mailer{
    public function __construct()
    {

    }
// preparing mail content
//$messagecontent = "Message content";
function send_email($title, $receiver_email, $content){
//Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'mail.mhh.ae';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'sender@mhh.ae';                     //SMTP username
        $mail->Password   = 'pass@pass.123';                               //SMTP password
       // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('sender@mhh.ae', 'MHH');
        $mail->addAddress($receiver_email, 'Dear Customer');     //Add a recipient
       // $mail->addAddress('ellen@example.com');               //Name is optional
        $mail->addReplyTo('info@mhh.ae', 'Information');
     //   $mail->addCC('cc@example.com');
      //  $mail->addBCC('bcc@example.com');
    
        //Attachments
    
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
       // $mail->addAttachment('photo.jpeg', 'photo.jpeg');    //Optional name
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
      
        //$encrypt_word_class = new UrlParameterEncryptor();
        //$encrypted_order_id  = $encrypt_word_class->encrypt($order_id);

       
            $mail->Subject = $title;// email subject
            $messagecontent = '
            <html>
            <head>
                <title>Survey Completed</title>
                <meta charset="utf-8">
            </head>
            <body>'.$content.'</body>
            </html>
        ';
        

      //  $mail->Subject = 'New Order Form Makancom, NB: #'.$order_id;// email subject
        // HTML content
        $mail->Body    = $messagecontent;
       $mail_result =  $mail->send();
      //  echo 'Message has been sent';
     //   echo "<br>";
       // echo $mail_result;
        return $mail_result;;
    } catch (Exception $e) {
        // echo $e;
        return 0;
       // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function send_verification_code($receiver_email, $code){


    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'mail.makancom.co';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'sender@makancom.co';                     //SMTP username
        $mail->Password   = 'pass@pass.123';                               //SMTP password
       // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('sender@makancom.co', 'Makancom');
        $mail->addAddress($receiver_email, 'Dear Customer');     //Add a recipient
       // $mail->addAddress('ellen@example.com');               //Name is optional
        $mail->addReplyTo('info@makancom.co', 'Information');
     //   $mail->addCC('cc@example.com');
      //  $mail->addBCC('bcc@example.com');
    
        //Attachments
    
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
       // $mail->addAttachment('photo.jpeg', 'photo.jpeg');    //Optional name
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
      //  $mail->Subject = 'New Order Form Makancom, NB: #'.$order_id;// email subject
        // HTML content
        $mail->Subject = 'Verification code'.$code;// email subject
        $messagecontent = '
        <html>
        <head>
            <title></title>
            <meta charset="utf-8">
        </head>
        <body>
         <div><h2>Verification Code: '.$code.'</h2></div>
         <div>Please use this code to reset your password.</div>
        </body>
        </html>
    ';
        
       
    $mail->Body    = $messagecontent;    
    $mail_result =  $mail->send();
    return $mail_result;

    } catch (Exception $e) {
        // echo $e;
        return 0;
       // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }




}




}