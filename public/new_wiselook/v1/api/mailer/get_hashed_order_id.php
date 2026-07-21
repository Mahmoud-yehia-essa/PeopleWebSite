<?php

ini_set("display_errors", 1);
include_once './UrlParameterEncryptor.php';



$order_id = $_POST["order_id"];


$encrypt_word_class = new UrlParameterEncryptor();
$encrypted_order_id  = $encrypt_word_class->encrypt($order_id);

echo $encrypted_order_id ;

?>