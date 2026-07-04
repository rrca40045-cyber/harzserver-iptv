<?php
// ============================================
// live.php — كيستقبل طلب /live/username/password/id.ts
// (عبر .htaccess rewrite) وكيدير redirect لرابط البث الحقيقي
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

// كنديرو redirect حقيقي (302) لرابط البث الأصلي
header('Location: ' . $channel['stream_url']);
exit;
