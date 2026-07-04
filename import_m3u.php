<?php
// ============================================
// استيراد قنوات من ملف/رابط M3U إلى قاعدة البيانات
// استعمال: افتح هاد الصفحة من المتصفح مرة وحدة (أو بعد كل تحديث للـ M3U)
// https://harziptv.site.je/import_m3u.php?source=LINK_OR_LOCAL_PATH
// ============================================
require_once 'config.php';
require_once 'auth.php';
requireLogin();

$source = $_GET['source'] ?? 'playlist.m3u'; // رابط أو مسار محلي للملف

$content = @file_get_contents($source);
if ($content === false) {
    die("❌ ماقدرتش نقرا الملف: $source");
}

$lines = preg_split('/\r\n|\r|\n/', $content);
$pdo = getDB();
$pdo->exec("DELETE FROM channels"); // كنمسحو القديم ونعمرو بالجديد فكل استيراد

$insert = $pdo->prepare(
    "INSERT INTO channels (name, category_id, logo, stream_url, epg_id) VALUES (?, ?, ?, ?, ?)"
);
$catStmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");

$count = 0;
$pendingName = '';
$pendingLogo = '';
$pendingCategory = 'عام';
$pendingEpg = '';

foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '#EXTINF') === 0) {
        // استخراج الاسم
        if (preg_match('/,(.+)$/', $line, $m)) {
            $pendingName = trim($m[1]);
        }
        // استخراج الشعار
        if (preg_match('/tvg-logo="([^"]*)"/', $line, $m)) {
            $pendingLogo = $m[1];
        } else {
            $pendingLogo = '';
        }
        // استخراج التصنيف
        if (preg_match('/group-title="([^"]*)"/', $line, $m)) {
            $pendingCategory = $m[1] ?: 'عام';
        } else {
            $pendingCategory = 'عام';
        }
        // استخراج EPG id
        if (preg_match('/tvg-id="([^"]*)"/', $line, $m)) {
            $pendingEpg = $m[1];
        } else {
            $pendingEpg = '';
        }
    } elseif ($line !== '' && strpos($line, '#') !== 0) {
        // هادي رابط البث
        $catStmt->execute([$pendingCategory]);
        $catId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM categories WHERE name=" . $pdo->quote($pendingCategory))->fetchColumn();

        $insert->execute([$pendingName ?: 'قناة بدون اسم', $catId, $pendingLogo, $line, $pendingEpg]);
        $count++;
        $pendingName = '';
        $pendingLogo = '';
        $pendingEpg = '';
    }
}

echo "✅ تم استيراد $count قناة بنجاح.";
echo '<br><br><a href="logout.php">تسجيل الخروج</a>';
