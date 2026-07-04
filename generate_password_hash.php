<?php
// ============================================
// شغل هاد الملف مرة وحدة من المتصفح، سيفط الهاش لـ config.php، ثم امسح هاد الملف من السيرفر
// https://harziptv.site.je/generate_password_hash.php?pass=YourNewPassword
// ============================================
$pass = $_GET['pass'] ?? '';
if ($pass === '') {
    die('استعمل: generate_password_hash.php?pass=كلمة_السر_الجديدة');
}
echo 'الهاش ديالك:<br><br><b>' . password_hash($pass, PASSWORD_DEFAULT) . '</b>';
echo '<br><br>⚠️ نسخ هاد الهاش لـ config.php فـ ADMIN_PASSWORD_HASH، بعدها امسح هاد الملف (generate_password_hash.php) من السيرفر.';
