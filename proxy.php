<?php
/**
 * HARZ IPTV PROXY
 * -----------------------------------------------------------
 * هاد السكريبت خدامتو: يدير الطلب الحقيقي (server-side) نحو
 * سيرفر M3U ولا Xtream Codes API، ويرجع النتيجة للموقع (HTML)
 * باش نتجاوزو مشكل CORS اللي كيبلوكي الطلبات المباشرة من المتصفح.
 *
 * ارفع هاد الملف فنفس السيرفر PHP ديالك على Railway (جنب الباقي).
 * -----------------------------------------------------------
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// -------------------------------------------------------------
// دالة عامة لتنفيذ طلب HTTP بواسطة cURL
// -------------------------------------------------------------
function fetchUrl($url, $timeout = 20) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        // كثرة سيرفرات IPTV كيبلوكيو User-Agent ديال cURL الافتراضي،
        // هادي عبارة عن User-Agent ديال بلاير حقيقي باش السيرفر يقبل الطلب
        CURLOPT_USERAGENT => 'IPTVSmarters/1.0 (Linux; Android)',
        CURLOPT_HTTPHEADER => ['Accept: */*'],
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        return ['ok' => false, 'error' => $err ?: 'فشل الاتصال بالسيرفر', 'code' => 0];
    }
    if ($code >= 400) {
        return ['ok' => false, 'error' => 'السيرفر رجع كود خطأ: ' . $code, 'code' => $code];
    }
    return ['ok' => true, 'body' => $body, 'code' => $code];
}

function fail($msg, $httpCode = 400) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// -------------------------------------------------------------
// قراءة البارامترات
// -------------------------------------------------------------
$mode = $_GET['mode'] ?? '';

if ($mode === 'm3u') {
    // -----------------------------------------------------------
    // جلب ملف M3U من رابط مباشر
    // مثال: proxy.php?mode=m3u&url=https://.../playlist.m3u
    // -----------------------------------------------------------
    $url = $_GET['url'] ?? '';
    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        fail('رابط M3U غير صالح.');
    }
    $r = fetchUrl($url);
    if (!$r['ok']) fail($r['error'], 502);

    header('Content-Type: text/plain; charset=utf-8');
    echo $r['body'];
    exit;

} elseif ($mode === 'xtream') {
    // -----------------------------------------------------------
    // طلب Xtream Codes API (player_api.php)
    // مثال: proxy.php?mode=xtream&host=http://goiptv.co:8080&user=...&pass=...&action=get_live_streams
    // -----------------------------------------------------------
    $host   = rtrim($_GET['host'] ?? '', '/');
    $user   = $_GET['user'] ?? '';
    $pass   = $_GET['pass'] ?? '';
    $action = $_GET['action'] ?? '';

    if (!$host || !$user || !$pass) {
        fail('البيانات (Host/User/Pass) ناقصة.');
    }
    if (!filter_var($host, FILTER_VALIDATE_URL)) {
        fail('الـ Host غير صالح، خاصو يكون بحال: http://domain.com:8080');
    }

    $apiUrl = $host . '/player_api.php?username=' . urlencode($user)
            . '&password=' . urlencode($pass);
    if ($action) {
        $apiUrl .= '&action=' . urlencode($action);
    }

    $r = fetchUrl($apiUrl);
    if (!$r['ok']) fail($r['error'], 502);

    header('Content-Type: application/json; charset=utf-8');
    echo $r['body'];
    exit;

} elseif ($mode === 'xtream_m3u') {
    // -----------------------------------------------------------
    // تحميل ملف M3U كامل مباشرة من get.php (بديل لـ player_api.php)
    // مثال: proxy.php?mode=xtream_m3u&host=...&user=...&pass=...&type=m3u_plus
    // -----------------------------------------------------------
    $host = rtrim($_GET['host'] ?? '', '/');
    $user = $_GET['user'] ?? '';
    $pass = $_GET['pass'] ?? '';
    $type = $_GET['type'] ?? 'm3u_plus';

    if (!$host || !$user || !$pass) {
        fail('البيانات (Host/User/Pass) ناقصة.');
    }
    if (!filter_var($host, FILTER_VALIDATE_URL)) {
        fail('الـ Host غير صالح.');
    }

    $apiUrl = $host . '/get.php?username=' . urlencode($user)
            . '&password=' . urlencode($pass)
            . '&type=' . urlencode($type)
            . '&output=ts';

    $r = fetchUrl($apiUrl, 40);
    if (!$r['ok']) fail($r['error'], 502);

    header('Content-Type: text/plain; charset=utf-8');
    echo $r['body'];
    exit;

} else {
    fail('mode ناقص أو غير معروف. استعمل: m3u أو xtream أو xtream_m3u', 400);
}
