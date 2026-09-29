<?php
declare(strict_types=1);

require_once __DIR__ . '/admin-session.php';

gig_admin_session_normalize();

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: siapkerja-login.php?redirect=admin-dashboard');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
    header('Location: karirhub-home.php');
    exit;
}

$adminName = (string)($_SESSION['admin_name'] ?? 'Admin Kemnaker');
