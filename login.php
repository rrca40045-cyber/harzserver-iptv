<?php
require_once 'config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();
        header('Location: import_m3u.php');
        exit;
    } else {
        $error = 'اسم المستخدم أو كلمة السر غير صحيحة';
    }
}

$timeout = isset($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تسجيل الدخول</title>
<style>
    body { background:#0d0d0d; color:#fff; font-family: Tahoma, Arial, sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
    .box { background:#161616; padding:32px; border-radius:12px; width:320px; box-shadow:0 0 20px rgba(0,0,0,.5); border:1px solid #2a2a2a; }
    h2 { text-align:center; color:#d4af37; margin-bottom:24px; }
    input { width:100%; padding:12px; margin-bottom:14px; border-radius:8px; border:1px solid #333; background:#0d0d0d; color:#fff; box-sizing:border-box; font-size:15px; }
    button { width:100%; padding:12px; border:none; border-radius:8px; background:#d4af37; color:#000; font-weight:bold; font-size:15px; cursor:pointer; }
    button:hover { background:#c19b2e; }
    .error { background:#3a1414; color:#ff6b6b; padding:10px; border-radius:8px; margin-bottom:14px; text-align:center; font-size:14px; }
    .info { background:#1a2a3a; color:#6bb5ff; padding:10px; border-radius:8px; margin-bottom:14px; text-align:center; font-size:14px; }
</style>
</head>
<body>
    <div class="box">
        <h2>🔐 تسجيل الدخول</h2>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($timeout): ?><div class="info">انتهت الجلسة، دخل مرة أخرى</div><?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="اسم المستخدم" required autofocus autocapitalize="off" autocorrect="off">
            <input type="password" name="password" placeholder="كلمة السر" required>
            <button type="submit">دخول</button>
        </form>
    </div>
</body>
</html>
