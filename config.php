<?php
// ============================================
// إعدادات قاعدة البيانات
// كيقرا أولاً من متغيرات البيئة (Railway/Docker)، وإلا استعمل القيم يدوياً (استضافة تقليدية)
// ============================================
define('DB_HOST', getenv('MYSQLHOST') ?: 'sqlXXX.infinityfree.com');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'epiz_XXXXXXX_iptv');
define('DB_USER', getenv('MYSQLUSER') ?: 'epiz_XXXXXXX');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'your_db_password');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

// اسم السيرفر اللي غيبان للمستخدم فـ التطبيق (بلا https://)
define('SERVER_URL', getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'harzserver.loveslife.biz'); // بدل بالدومين ديالك إلا تبدل
define('SERVER_PORT', 80);

// ============================================
// بيانات دخول الأدمين لصفحة تسجيل الدخول (login.php)
// استعمل install.php مرة وحدة باش تحط اسم المستخدم والباسوورد بلا تعقيد
// ============================================
define('ADMIN_USERNAME', 'admin'); // بدلو إلا بغيتي اسم آخر
define('ADMIN_PASSWORD_HASH', '$2y$10$cYD.eYjInXZbyHo/0JzFT.wPPwAyfhEisIjR1XHtvRtPhlyrkcBoK');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
