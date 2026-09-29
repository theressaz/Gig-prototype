<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin-session.php';

gig_admin_session_normalize();

if (empty($_SESSION['siapkerja_email']) && empty($_SESSION['username'])) {
    header('Location: siapkerja-login.php?redirect=admin-dashboard');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: karirhub-home.php');
    exit;
}

header('Location: dashboard-admin.php');
exit;
