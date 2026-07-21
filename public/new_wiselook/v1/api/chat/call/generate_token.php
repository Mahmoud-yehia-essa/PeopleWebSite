<?php
header("Content-Type: application/json");

// ✅ استدعاء مكتبة Agora
require_once "./php/src/RtcTokenBuilder.php"; // غير المسار حسب مكان الملف

// 🔑 بيانات من Agora Console
$appID = "657e7c06f9dc44fc999a7c5d797a3cdc";
$appCertificate = "1baf02eee8f145158bf10d926e27bbba"; // ضع الـ Primary Certificate

// ⚡ باراميترات جاي من Flutter (POST أو GET)
$channelName = $_GET['channelName'] ?? null;
$uid = intval($_GET['uid'] ?? 0); // ممكن تخلي كل مستخدم ياخد UID خاص فيه
$expireTimeInSeconds = 3600; // صلاحية التوكين ساعة وحدة

if (!$channelName) {
    echo json_encode([
        "success" => false,
        "message" => "channelName required"
    ]);
    exit;
}

// ⏱️ حساب وقت الانتهاء
$currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
$privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

// 🛠️ توليد التوكين
$token = RtcTokenBuilder::buildTokenWithUid(
    $appID,
    $appCertificate,
    $channelName,
    $uid,
    RtcTokenBuilder::RolePublisher,
    $privilegeExpiredTs
);

// ✅ رجّع JSON
echo json_encode([
    "success" => true,
    "token" => $token,
    "channelName" => $channelName,
    "uid" => $uid,
    "expiresIn" => $expireTimeInSeconds
]);
