<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['siapkerja_email']) && empty($_SESSION['username'])) {
    header('Location: siapkerja-login.php?redirect=gig-workers');
    exit;
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$username = (string)($_SESSION['username'] ?? $_SESSION['siapkerja_name'] ?? '');

if ($username !== '' && gig_is_worker_registered($username)) {
    $_SESSION['role'] = 'worker';
    $_SESSION['username'] = $username;
    header('Location: dashboard-worker.php');
    exit;
}

header('Location: worker-register.php');
exit;
