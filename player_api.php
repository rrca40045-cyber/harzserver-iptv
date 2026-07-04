<?php
// ============================================
// player_api.php — هادا القلب ديال Xtream Codes API
// التطبيقات (IPTV Smarters, TiviMate) كتطلب هاد الملف تلقائياً
// ============================================
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';
$action   = $_GET['action'] ?? '';

$pdo = getDB();

// ------- التحقق من المشترك -------
$stmt = $pdo->prepare("SELECT * FROM subscribers WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(['user_info' => ['auth' => 0, 'status' => 'Invalid credentials']]);
    exit;
}

$isExpired = strtotime($user['expire_date']) < time();
$status = ($isExpired || $user['status'] !== 'active') ? 'Expired' : 'Active';
$expTimestamp = strtotime($user['expire_date']);

// ------- بلا action = طلب تسجيل الدخول (login check) -------
if ($action === '') {
    echo json_encode([
        'user_info' => [
            'username' => $user['username'],
            'password' => $user['password'],
            'auth' => 1,
            'status' => $status,
            'exp_date' => (string)$expTimestamp,
            'is_trial' => '0',
            'active_cons' => '0',
            'max_connections' => (string)$user['max_connections'],
        ],
        'server_info' => [
            'url' => SERVER_URL,
            'port' => (string)SERVER_PORT,
            'https_port' => '443',
            'server_protocol' => 'https',
            'timezone' => 'Africa/Casablanca',
        ]
    ]);
    exit;
}

// إلا كان الحساب منتهي، مايبانش ليه شي قنوات
if ($status === 'Expired') {
    echo json_encode([]);
    exit;
}

// ------- جلب التصنيفات -------
if ($action === 'get_live_categories') {
    $rows = $pdo->query("SELECT id AS category_id, name AS category_name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
    exit;
}

// ------- جلب القنوات (بالتصنيف أو الكل) -------
if ($action === 'get_live_streams') {
    $categoryId = $_GET['category_id'] ?? null;
    if ($categoryId) {
        $stmt = $pdo->prepare("SELECT * FROM channels WHERE category_id = ?");
        $stmt->execute([$categoryId]);
    } else {
        $stmt = $pdo->query("SELECT * FROM channels");
    }
    $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($channels as $ch) {
        $result[] = [
            'num' => (int)$ch['id'],
            'name' => $ch['name'],
            'stream_type' => 'live',
            'stream_id' => (int)$ch['id'],
            'stream_icon' => $ch['logo'],
            'epg_channel_id' => $ch['epg_id'],
            'category_id' => (string)$ch['category_id'],
            'custom_sid' => '',
            'tv_archive' => 0,
            'direct_source' => '',
        ];
    }
    echo json_encode($result);
    exit;
}

// أي action آخر ماشي مدعوم دابا
echo json_encode([]);
