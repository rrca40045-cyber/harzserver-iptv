<?php
// ============================================
// install.php — كتبت اسم المستخدم والباسوورد هنا وهو غيبدلهم
// أوتوماتيكياً فـ config.php (بلا ماتحتاج تولد هاش يدوياً)
// ⚠️ امسح هاد الملف من السيرفر بعد ما تخلص التركيب!
// ============================================

$configPath = __DIR__ . '/config.php';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = '❌ خاصك تعمر اسم المستخدم والباسوورد بجوج.';
    } elseif (!file_exists($configPath) || !is_writable($configPath)) {
        $message = '❌ ماقدرتش نكتب فـ config.php — تأكد أنه موجود وعندو صلاحية الكتابة (chmod 644 أو 664).';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $content = file_get_contents($configPath);

        // بدل ADMIN_USERNAME
        $content = preg_replace_callback(
            "/define\('ADMIN_USERNAME',\s*'[^']*'\);/",
            function() use ($username) {
                return "define('ADMIN_USERNAME', '" . addslashes($username) . "');";
            },
            $content
        );

        // بدل ADMIN_PASSWORD_HASH (نستعمل callback باش ماتتقراش $ فالهاش كـ backreference)
        $content = preg_replace_callback(
            "/define\('ADMIN_PASSWORD_HASH',\s*'[^']*'\);/",
            function() use ($hash) {
                return "define('ADMIN_PASSWORD_HASH', '" . addslashes($hash) . "');";
            },
            $content
        );

        if (file_put_contents($configPath, $content) !== false) {
            $success = true;
            $message = '✅ تم تحديث بيانات الدخول بنجاح! دابا امسح install.php من السيرفر وجرب تدخل من login.php';
        } else {
            $message = '❌ فشلت الكتابة فـ config.php.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تركيب بيانات الدخول</title>
<style>
    body { background:#0d0d0d; color:#fff; font-family: Tahoma, Arial, sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
    .box { background:#161616; padding:32px; border-radius:12px; width:320px; box-shadow:0 0 20px rgba(0,0,0,.5); border:1px solid #2a2a2a; }
    h2 { text-align:center; color:#d4af37; margin-bottom:20px; font-size:18px; }
    input { width:100%; padding:12px; margin-bottom:14px; border-radius:8px; border:1px solid #333; background:#0d0d0d; color:#fff; box-sizing:border-box; font-size:15px; }
    button { width:100%; padding:12px; border:none; border-radius:8px; background:#d4af37; color:#000; font-weight:bold; font-size:15px; cursor:pointer; }
    button:hover { background:#c19b2e; }
    .msg { padding:10px; border-radius:8px; margin-bottom:14px; text-align:center; font-size:13px; line-height:1.6; }
    .ok { background:#14331a; color:#6bff8f; }
    .err { background:#3a1414; color:#ff6b6b; }
    a { color:#6bb5ff; }
</style>
</head>
<body>
    <div class="box">
        <h2>⚙️ تركيب بيانات الدخول</h2>
        <?php if ($message): ?>
            <div class="msg <?= $success ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST">
            <input type="text" name="username" placeholder="اسم المستخدم الجديد" required autofocus autocapitalize="off" autocorrect="off">
            <input type="password" name="password" placeholder="كلمة السر الجديدة" required autocapitalize="off" autocorrect="off">
            <button type="submit">حفظ</button>
        </form>
        <?php else: ?>
            <a href="login.php">➡️ دوز لصفحة تسجيل الدخول</a>
        <?php endif; ?>
    </div>
</body>
</html>
