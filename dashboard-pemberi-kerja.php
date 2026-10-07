<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION['role'] = 'employer';
if (empty($_SESSION['username'])) {
    $_SESSION['username'] = !empty($_SESSION['siapkerja_name']) ? (string)$_SESSION['siapkerja_name'] : 'PT Talenta Digital Indonesia';
}

header('Location: dashboard-employer.php');
exit;
