<?php
// ============================================
// live.php — بروكسي حقيقي: كيجيب البث من المصدر الأصلي وكيمرره للمستخدم
// عبر دومين السيرفر ديالنا (بلا ما يبين الرابط الأصلي للتطبيق)
// ============================================
require_once 'config.php';

$username = $_GET['u'] ?? '';
$password = $_GET['p'] ?? '';
$streamId = (int)($_GET['id'] ?? 0);

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM subscribers WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || strtotime($user['expire_date']) < time() || $user['status'] !== 'active') {
    http_response_code(403);
    die('Invalid or expired account');
}

$stmt = $pdo->prepare("SELECT stream_url FROM channels WHERE id = ?");
$stmt->execute([$streamId]);
$channel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$channel) {
    http_response_code(404);
    die('Channel not found');
}

// كنعطلو أي حد زمني ونديرو streaming حقيقي
set_time_limit(0);
ignore_user_abort(true);
while (ob_get_level()) ob_end_flush();

header('Content-Type: video/mp2t');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // يمنع أي buffering إضافي

$ch = curl_init($channel['stream_url']);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_USERAGENT, 'VLC/3.0.18 LibVLC/3.0.18'); // بحال VLC باش نتفادو bot detection
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curlHandle, $data) {
    echo $data;
    flush();
    return strlen($data);
});
curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(502);
    error_log('Proxy error: ' . curl_error($ch));
}
curl_close($ch);
