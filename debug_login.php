<?php
// ============================================
// debug_login.php — أداة تشخيص مؤقتة
// استعمال: https://domain/debug_login.php?u=USERNAME&p=PASSWORD
// ⚠️ امسحها بعد ما تحل المشكل!
// ============================================
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

$u = $_GET['u'] ?? '';
$p = $_GET['p'] ?? '';

echo "=== معلومات config.php ===\n";
echo "ADMIN_USERNAME المخزن: [" . ADMIN_USERNAME . "]\n";
echo "طول ADMIN_USERNAME: " . strlen(ADMIN_USERNAME) . "\n";
echo "ADMIN_PASSWORD_HASH المخزن: " . ADMIN_PASSWORD_HASH . "\n";
echo "طول الهاش: " . strlen(ADMIN_PASSWORD_HASH) . " (المفروض يكون 60)\n\n";

echo "=== المعلومات اللي دخلتي ===\n";
echo "username المدخل: [" . $u . "]\n";
echo "طول username المدخل: " . strlen($u) . "\n";
echo "password المدخل: [" . $p . "]\n";
echo "طول password المدخل: " . strlen($p) . "\n\n";

echo "=== نتيجة المقارنة ===\n";
echo "username متطابق؟ " . ($u === ADMIN_USERNAME ? "✅ نعم" : "❌ لا") . "\n";
echo "password_verify نتيجة؟ " . (password_verify($p, ADMIN_PASSWORD_HASH) ? "✅ نعم" : "❌ لا") . "\n";
