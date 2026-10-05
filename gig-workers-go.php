<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$isLoggedIn = !empty($_SESSION['siapkerja_email']) || !empty($_SESSION['username']);
if (!$isLoggedIn) {
    header('Location: siapkerja-login.php?redirect=gig-workers');
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
if ($username === '' && in_array($siapkerjaEmail, ['theressaz@pasker.id', 'tessa'], true)) {
    $username = 'Theressa Zaratrusha';
}

$isWorkerRegistered = $username !== '' && gig_is_worker_registered($username);
$isWorkerAccount = ($_SESSION['role'] ?? '') === 'worker'
    || in_array($siapkerjaEmail, ['theressaz@pasker.id', 'tessa'], true)
    || $isWorkerRegistered;

if ($isWorkerAccount) {
    $_SESSION['role'] = 'worker';
    $_SESSION['username'] = $username !== '' ? $username : 'Theressa Zaratrusha';
    if (empty($_SESSION['siapkerja_email'])) {
        $_SESSION['siapkerja_email'] = 'theressaz@pasker.id';
    }
    if ($isWorkerRegistered) {
        $_SESSION['gig_worker_registered_' . $_SESSION['username']] = true;
        header('Location: worker-bursa.php');
        exit;
    }
    header('Location: welcome-screen.php');
    exit;
}

header('Location: worker-register.php');
exit;
