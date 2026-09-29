<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$isLoggedIn = !empty($_SESSION['siapkerja_email']) || !empty($_SESSION['username']);
if (!$isLoggedIn) {
    header('Location: welcome-screen.php');
    exit;
}

$siapkerjaEmail = strtolower((string)($_SESSION['siapkerja_email'] ?? ''));
$isEmployerAccount = ($_SESSION['role'] ?? '') === 'employer'
    || in_array($siapkerjaEmail, ['employer@pasker.id', 'calon.employer@pasker.id'], true);

if ($isEmployerAccount) {
    $_SESSION['role'] = 'employer';
    if (!isset($_SESSION['company_registered'])) {
        $_SESSION['company_registered'] = $siapkerjaEmail === 'employer@pasker.id';
    }
    header('Location: dashboard-employer.php');
    exit;
}

$username = (string)($_SESSION['username'] ?? $_SESSION['siapkerja_name'] ?? '');
if ($username !== '' && gig_is_worker_registered($username)) {
    $_SESSION['role'] = 'worker';
    $_SESSION['username'] = $username;
    header('Location: dashboard-worker.php');
    exit;
}

header('Location: welcome-screen.php');
exit;
