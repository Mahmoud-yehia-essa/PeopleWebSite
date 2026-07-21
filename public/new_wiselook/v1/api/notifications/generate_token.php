<?php
require_once(__DIR__ . '/vendor/autoload.php');

use Google\Auth\Credentials\ServiceAccountCredentials;

function getAccessToken() {
    $keyFilePath = __DIR__ . '/wiselook-f161f-5616c492f6ce.json'; 
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    try {
        $credentials = new ServiceAccountCredentials($scopes, $keyFilePath);
        $accessToken = $credentials->fetchAuthToken();
        if (isset($accessToken['access_token'])) {
            return $accessToken['access_token'];
        } else {
            throw new Exception('Failed to retrieve access token.');
        }
    } catch (Exception $e) {
        // echo 'Error: ' . $e->getMessage();
        return null;
    }
}

$token = getAccessToken();

if ($token) {
    // echo "Your OAuth 2.0 Bearer Token: " . $token;
} else {
    // echo "Failed to retrieve the OAuth 2.0 Bearer Token.";
}
?>
