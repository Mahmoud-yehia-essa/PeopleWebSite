<?php

class UrlParameterEncryptor
{
    private $key = "makancom@pkey!0io";

    public function __construct()
    {
    }

    public function encrypt($data)
    {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($data, $cipher, $this->key, 0, $iv);
        return $this->base64_urlencode($iv . $encrypted);
    }

    public function decrypt($data)
    {
        $data = $this->base64_urldecode($data);
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($data, 0, $ivlen);
        $data = substr($data, $ivlen);
        return openssl_decrypt($data, $cipher, $this->key, 0, $iv);
    }

    private function base64_urlencode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function base64_urldecode($data)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
?>