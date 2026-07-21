<?php 

// PHP example
use Agora\RtcTokenBuilder\RtcTokenBuilder;

$appID = "YOUR_APP_ID";
$appCertificate = "YOUR_APP_CERTIFICATE";
$channelName = $_GET['channel'];
$uid = 0;
$role = RtcTokenBuilder::RoleAttendee;
$expireTimeInSeconds = 3600;

$token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $expireTimeInSeconds);
echo json_encode(['token' => $token]);

?>