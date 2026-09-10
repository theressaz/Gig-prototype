<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["username"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'employer') {
    header("Location: welcome-screen.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            (bool)$params["secure"],
            (bool)$params["httponly"]
        );
    }
    session_destroy();
    header("Location: welcome-screen.php");
    exit;
}

$username = (string)$_SESSION["username"];
