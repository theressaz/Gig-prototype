<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['siapkerja_email']) && empty($_SESSION['username'])) {
    header('Location: siapkerja-login.php?redirect=karirhub-home');
    exit;
}

require_once __DIR__ . '/includes/admin-store.php';
$email = strtolower((string)($_SESSION['siapkerja_email'] ?? ''));
$isEmployer = ($_SESSION['role'] ?? '') === 'employer'
    || in_array($email, ['employer@pasker.id', 'calon.employer@pasker.id'], true);

if (!$isEmployer) {
    header('Location: karirhub-home.php');
    exit;
}

$_SESSION['role'] = 'employer';
if (!isset($_SESSION['company_registered'])) {
    $_SESSION['company_registered'] = $email === 'employer@pasker.id';
}

header('Location: dashboard-employer.php');
exit;
