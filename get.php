<?php
// ============================================
// get.php — كيدير redirect لرابط البث الحقيقي
// أو كيولد M3U كامل إلا طلب type=m3u_plus
// ============================================
require_once 'config.php';

$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';
$type     = $_GET['type'] ?? '';

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM subscribers WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || strtotime($user['expire_date']) < time() || $user['status'] !== 'active') {
    http_response_code(403);
    die('Invalid or expired account');
}

// ------- توليد M3U كامل -------
if ($type === 'm3u_plus' || $type === 'm3u') {
    header('Content-Type: audio/x-mpegurl');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');

    $channels = $pdo->query("SELECT c.*, cat.name AS cat_name FROM channels c LEFT JOIN categories cat ON c.category_id = cat.id")->fetchAll(PDO::FETCH_ASSOC);

    echo "#EXTM3U\n";
    foreach ($channels as $ch) {
        echo '#EXTINF:-1 tvg-id="' . htmlspecialchars($ch['epg_id']) . '" tvg-logo="' . htmlspecialchars($ch['logo']) . '" group-title="' . htmlspecialchars($ch['cat_name']) . '",' . htmlspecialchars($ch['name']) . "\n";
        // رابط البث الأصلي مباشرة (بلا ما يمر عبر /live/ redirect)
        echo $ch['stream_url'] . "\n";
    }
    exit;
}

http_response_code(400);
echo 'Missing or invalid type parameter';
