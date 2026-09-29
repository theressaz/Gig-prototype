<?php
declare(strict_types=1);

function gig_admin_session_normalize(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $email = strtolower(trim((string)($_SESSION['siapkerja_email'] ?? '')));
    $username = strtolower(trim((string)($_SESSION['username'] ?? '')));

    if (
        $email === 'admin@kemnaker.go.id'
        || $username === 'admin@kemnaker.go.id'
        || $username === 'admin'
    ) {
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_name'] = (string)($_SESSION['admin_name'] ?? 'Admin KarirHub');
        if ($email === '') {
            $_SESSION['siapkerja_email'] = 'admin@kemnaker.go.id';
        }
    }
}
