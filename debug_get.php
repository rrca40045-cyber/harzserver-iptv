<?php
// ============================================
// debug_get.php — كيبين محتوى get.php كنص عادي (بلا مشغل صوت)
// استعمال: https://domain/debug_get.php?username=test1&password=test123
// ⚠️ امسحها بعد ما تحل المشكل
// ============================================
$username = $_GET['username'] ?? 'test1';
$password = $_GET['password'] ?? 'test123';

$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);
$url = "$protocol://$host$path/get.php?username=" . urlencode($username) . "&password=" . urlencode($password) . "&type=m3u_plus";

header('Content-Type: text/plain; charset=utf-8');
echo "=== الرابط اللي تجرب ===\n$url\n\n";

$context = stream_context_create(['http' => ['timeout' => 15, 'ignore_errors' => true]]);
$output = @file_get_contents($url, false, $context);

if ($output === false) {
    echo "❌ ماقدرتش نوصل للرابط (file_get_contents فشل، يمكن allow_url_fopen معطل فالسيرفر).\n";
} else {
    echo "=== HTTP Response Headers ===\n";
    if (isset($http_response_header)) {
        echo implode("\n", $http_response_header) . "\n\n";
    }
    echo "=== أول 2000 حرف من المحتوى ===\n\n";
    echo mb_substr($output, 0, 2000);
    echo "\n\n=== الطول الكامل: " . strlen($output) . " حرف ===\n";
}
