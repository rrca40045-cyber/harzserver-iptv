<?php
// ============================================
// debug_stream.php — كيبين رسالة الخطأ الحقيقية مالي كيحاول يتصل بالمصدر
// استعمال: https://domain/debug_stream.php?id=1
// ============================================
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

$id = (int)($_GET['id'] ?? 1);
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM channels WHERE id = ?");
$stmt->execute([$id]);
$channel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$channel) {
    die("❌ ماكاينش قناة بهاد id: $id");
}

echo "=== القناة ===\n";
echo "الاسم: " . $channel['name'] . "\n";
echo "الرابط: " . $channel['stream_url'] . "\n\n";

echo "=== محاولة الاتصال ===\n";
$ch = curl_init($channel['stream_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_USERAGENT, 'VLC/3.0.18 LibVLC/3.0.18');
curl_setopt($ch, CURLOPT_RANGE, '0-2000'); // نجربو نجيبو غير أول 2000 بايت

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
$error = curl_error($ch);
$errno = curl_errno($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "وقت الاتصال: {$totalTime}s\n";
echo "curl error code: $errno\n";
echo "curl error message: " . ($error ?: '(لا يوجد)') . "\n\n";

echo "=== أول 1000 حرف من الرد ===\n";
echo substr($response ?? '', 0, 1000);
