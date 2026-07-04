<?php
// ============================================
// auth.php — خاص تنضاف فوق كل صفحة بغيتي تحميها (بحال import_m3u.php)
// require_once 'auth.php';
// ============================================
session_start();

function requireLogin() {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
    // خروج تلقائي بعد 30 دقيقة بلا نشاط
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}
