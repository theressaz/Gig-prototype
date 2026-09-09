<?php
declare(strict_types=1);
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Gig');

$message = "";
$messageType = "";

if (isset($_SESSION["username"])) {
    header("Location: dashboard.php");
    exit;
}

$dbOffline = false;
$pdo = null;

try {
    // Connect to MySQL server with a 2-second timeout to ensure instant rendering.
    $serverDsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
    ]);
    $serverPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    // Connect to the target project database.
    $databaseDsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($databaseDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
    ]);

    // Create "Login" table if it does not exist.
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `Login` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // Seed default account once.
    $seedUser = "Tessa";
    $seedPasswordHash = password_hash("12345", PASSWORD_DEFAULT);
    $seedStmt = $pdo->prepare(
        "INSERT INTO `Login` (`username`, `password`)
         SELECT :username, :password
         FROM DUAL
         WHERE NOT EXISTS (
            SELECT 1 FROM `Login` WHERE `username` = :check_username
         )"
    );
    $seedStmt->execute([
        ":username" => $seedUser,
        ":password" => $seedPasswordHash,
        ":check_username" => $seedUser
    ]);
} catch (Throwable $e) {
    $dbOffline = true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim((string)($_POST["username"] ?? ""));
    $password = (string)($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $message = "Silakan isi nama pengguna (username) dan kata sandi.";
        $messageType = "error";
    } else {
        if ($pdo !== null) {
            try {
                $loginStmt = $pdo->prepare("SELECT `password` FROM `Login` WHERE `username` = :username LIMIT 1");
                $loginStmt->execute([":username" => $username]);
                $userRow = $loginStmt->fetch(PDO::FETCH_ASSOC);

                if ($userRow && isset($userRow["password"]) && password_verify($password, $userRow["password"])) {
                    $_SESSION["username"] = $username;
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $message = "Nama pengguna atau kata sandi tidak sesuai.";
                    $messageType = "error";
                }
            } catch (Throwable $e) {
                $message = "Kesalahan query basis data: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            // Fallback prototype mode when MySQL is offline
            if ($username === "Tessa" && $password === "12345") {
                $_SESSION["username"] = $username;
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Akun tidak ditemukan. Gunakan kredensial demo: Tessa / 12345 (atau aktifkan MySQL untuk akun kustom).";
                $messageType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Sistem Informasi dan Autentikasi Gig Worker Prototype - Kementerian Ketenagakerjaan Republik Indonesia" />
  <title>Masuk Sistem | Gig Worker Prototype - Kemnaker RI</title>
  
  <!-- Google Fonts: Plus Jakarta Sans for clean official digital government typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <style>
    :root {
      /* Kemnaker RI Design Palette */
      --kemnaker-navy-dark: #061d33;
      --kemnaker-navy: #092c4c;
      --kemnaker-navy-light: #0f3d68;
      --primary-blue: #1657c1;
      --primary-blue-hover: #1247a3;
      --hero-blue-start: #104899;
      --hero-blue-mid: #1b62d4;
      --hero-blue-end: #2d79f8;
      --bg-page: #f1f5f9;
      --bg-surface: #ffffff;
      --border-subtle: #e2e8f0;
      --border-light: #cbd5e1;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --text-soft: #475569;
      --alert-bg: #e0f2fe;
      --alert-border: #bae6fd;
      --alert-text: #0284c7;
      --badge-bg: rgba(6, 29, 51, 0.45);
      --success-green: #10b981;
      --success-bg: #ecfdf5;
      --cyan-badge: #0891b2;
      --cyan-bg: #ecfeff;
      --amber-badge: #d97706;
      --amber-bg: #fffbeb;
      --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06);
      --shadow-md: 0 4px 14px -1px rgba(15, 23, 42, 0.08);
      --shadow-lg: 0 12px 28px -4px rgba(9, 44, 76, 0.12);
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-pill: 9999px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    body {
      min-height: 100vh;
      background-color: var(--bg-page);
      color: var(--text-main);
      display: flex;
      flex-direction: column;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    /* ==========================================================================
       TOP APP BAR / OFFICIAL KEMNAKER HEADER
       ========================================================================== */
    .kemnaker-topbar {
      background: var(--kemnaker-navy-dark);
      color: #ffffff;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 6px 24px;
      font-size: 0.78rem;
    }

    .kemnaker-topbar-inner {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
    }

    .topbar-left {
      display: flex;
      align-items: center;
      gap: 12px;
      color: #94a3b8;
    }

    .topbar-badge {
      background: rgba(37, 99, 235, 0.25);
      border: 1px solid rgba(96, 165, 250, 0.35);
      color: #93c5fd;
      padding: 2px 8px;
      border-radius: var(--radius-pill);
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.02em;
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 16px;
      color: #cbd5e1;
    }

    .topbar-link {
      color: #cbd5e1;
      text-decoration: none;
      transition: color 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .topbar-link:hover {
      color: #ffffff;
    }

    /* MAIN HEADER */
    .kemnaker-header {
      background: var(--kemnaker-navy);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .header-inner {
      max-width: 1280px;
      margin: 0 auto;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
    }

    .brand-section {
      display: flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
      color: #ffffff;
    }

    .kemnaker-emblem {
      width: 44px;
      height: 44px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
    }

    .brand-title {
      font-size: 0.76rem;
      font-weight: 800;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #ffffff;
      line-height: 1.2;
    }

    .brand-sub {
      font-size: 0.84rem;
      font-weight: 500;
      color: #93c5fd;
      line-height: 1.3;
    }

    .header-nav {
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .nav-item {
      color: #cbd5e1;
      text-decoration: none;
      font-size: 0.88rem;
      font-weight: 500;
      padding: 6px 12px;
      border-radius: var(--radius-sm);
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .nav-item:hover {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.08);
    }

    .nav-item.active {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.14);
      font-weight: 600;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .portal-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 5px 12px;
      border-radius: var(--radius-pill);
      font-size: 0.78rem;
      color: #f1f5f9;
      font-weight: 600;
    }

    .portal-tag-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 6px #10b981;
    }

    /* ==========================================================================
       MAIN CONTENT CONTAINER
       ========================================================================== */
    .page-main {
      flex: 1;
      max-width: 1280px;
      width: 100%;
      margin: 0 auto;
      padding: 28px 24px 48px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    /* ==========================================================================
       HERO BANNER (MATCHING SCREENSHOT'S "RINGKASAN WLLP" CARD)
       ========================================================================== */
    .hero-banner {
      background: linear-gradient(135deg, var(--hero-blue-start) 0%, var(--hero-blue-mid) 50%, var(--hero-blue-end) 100%);
      border-radius: var(--radius-lg);
      padding: 32px 36px;
      color: #ffffff;
      box-shadow: var(--shadow-lg), 0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      position: relative;
      overflow: hidden;
    }

    .hero-banner::after {
      content: "";
      position: absolute;
      top: -30%;
      right: -10%;
      width: 450px;
      height: 450px;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      position: relative;
      z-index: 1;
    }

    .hero-content {
      max-width: 720px;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--badge-bg);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      padding: 4px 14px;
      border-radius: var(--radius-pill);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #ffffff;
      margin-bottom: 12px;
    }

    .hero-badge svg {
      width: 14px;
      height: 14px;
      color: #60a5fa;
    }

    .hero-title {
      font-size: clamp(1.6rem, 3vw, 2.15rem);
      font-weight: 800;
      letter-spacing: -0.02em;
      color: #ffffff;
      line-height: 1.25;
      margin-bottom: 8px;
    }

    .hero-desc {
      font-size: 0.98rem;
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.6;
    }

    .hero-actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .btn-hero-glass {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #ffffff;
      padding: 10px 18px;
      border-radius: var(--radius-pill);
      font-size: 0.88rem;
      font-weight: 600;
      text-decoration: none;
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      transition: all 0.2s ease;
    }

    .btn-hero-glass:hover {
      background: rgba(255, 255, 255, 0.25);
      border-color: rgba(255, 255, 255, 0.45);
      transform: translateY(-1px);
    }

    .btn-hero-solid {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #ffffff;
      border: 1px solid #ffffff;
      color: var(--primary-blue);
      padding: 10px 20px;
      border-radius: var(--radius-pill);
      font-size: 0.88rem;
      font-weight: 700;
      text-decoration: none;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .btn-hero-solid:hover {
      background: #f8fafc;
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    }

    /* ==========================================================================
       NOTICE CARD / ALERT (MATCHING SCREENSHOT'S SKY-BLUE BAR)
       ========================================================================== */
    .notice-bar {
      background: var(--alert-bg);
      border: 1px solid var(--alert-border);
      border-radius: var(--radius-md);
      padding: 12px 18px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--alert-text);
      font-size: 0.88rem;
      font-weight: 500;
      box-shadow: var(--shadow-sm);
    }

    .notice-bar svg {
      flex-shrink: 0;
      width: 18px;
      height: 18px;
    }

    /* ==========================================================================
       STATS GRID (MATCHING 4 CARDS IN SCREENSHOT)
       ========================================================================== */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    @media (max-width: 900px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 540px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }

    .stat-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 18px 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 110px;
      box-shadow: var(--shadow-sm);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
      border-color: #cbd5e1;
    }

    .stat-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 4px;
    }

    .stat-label {
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--text-muted);
    }

    .stat-icon-wrapper {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .stat-icon-wrapper.blue {
      background: #eff6ff;
      color: #2563eb;
    }

    .stat-icon-wrapper.cyan {
      background: var(--cyan-bg);
      color: var(--cyan-badge);
    }

    .stat-icon-wrapper.green {
      background: var(--success-bg);
      color: var(--success-green);
    }

    .stat-icon-wrapper.amber {
      background: var(--amber-bg);
      color: var(--amber-badge);
    }

    .stat-icon-wrapper svg {
      width: 18px;
      height: 18px;
    }

    .stat-number {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--text-main);
      line-height: 1.2;
      margin: 2px 0;
    }

    .stat-caption {
      font-size: 0.78rem;
      color: var(--text-muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* ==========================================================================
       TWO-COLUMN INTERACTIVE HUB (LOGIN ON RIGHT + INFO ON LEFT)
       ========================================================================== */
    .portal-workspace {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 24px;
      align-items: stretch;
    }

    @media (max-width: 960px) {
      .portal-workspace {
        grid-template-columns: 1fr;
      }
    }

    /* LEFT WIDGETS (Matching Akses Cepat & Status from screenshot) */
    .workspace-info {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .white-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 24px;
      box-shadow: var(--shadow-sm);
    }

    .card-title-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
    }

    .card-title {
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-subtitle {
      font-size: 0.8rem;
      color: var(--text-muted);
      margin-top: 2px;
    }

    .quick-access-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    @media (max-width: 600px) {
      .quick-access-grid {
        grid-template-columns: 1fr;
      }
    }

    .quick-access-tile {
      background: #f8fafc;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 14px 16px;
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text-main);
      transition: all 0.2s ease;
    }

    .quick-access-tile:hover {
      background: #ffffff;
      border-color: #93c5fd;
      box-shadow: var(--shadow-sm);
      transform: translateY(-1px);
    }

    .tile-icon {
      width: 38px;
      height: 38px;
      border-radius: 8px;
      background: #eff6ff;
      color: var(--primary-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .tile-text h4 {
      font-size: 0.88rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .tile-text p {
      font-size: 0.74rem;
      color: var(--text-muted);
    }

    /* Distribution / Feature List */
    .feature-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 14px;
      background: #f8fafc;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border-subtle);
    }

    .feature-item-left {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-soft);
    }

    .feature-pill {
      font-size: 0.72rem;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: var(--radius-pill);
    }

    .feature-pill.green {
      background: #dcfce7;
      color: #15803d;
    }

    .feature-pill.blue {
      background: #dbeafe;
      color: #1d4ed8;
    }

    /* ==========================================================================
       AUTHENTICATION CARD (LOGIN FORM)
       ========================================================================== */
    .auth-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 32px 28px;
      box-shadow: var(--shadow-md);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .auth-header {
      margin-bottom: 24px;
      text-align: left;
    }

    .auth-header-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      color: var(--primary-blue);
      padding: 4px 10px;
      border-radius: var(--radius-pill);
      font-size: 0.75rem;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .auth-title {
      font-size: 1.45rem;
      font-weight: 800;
      color: var(--kemnaker-navy);
      letter-spacing: -0.01em;
      margin-bottom: 6px;
    }

    .auth-subtitle {
      font-size: 0.88rem;
      color: var(--text-muted);
      line-height: 1.5;
    }

    .login-form {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      text-align: left;
    }

    .form-label {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-main);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      color: #94a3b8;
      pointer-events: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .input-icon svg {
      width: 18px;
      height: 18px;
    }

    .form-control {
      width: 100%;
      height: 46px;
      padding: 10px 14px 10px 42px;
      font-size: 0.94rem;
      border: 1.5px solid #cbd5e1;
      border-radius: var(--radius-md);
      background: #ffffff;
      color: var(--text-main);
      outline: none;
      transition: all 0.2s ease;
    }

    .form-control:focus {
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(22, 87, 193, 0.15);
    }

    .toggle-pwd {
      position: absolute;
      right: 12px;
      background: transparent;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 6px;
      border-radius: 4px;
      transition: color 0.15s ease;
    }

    .toggle-pwd:hover {
      color: var(--text-main);
    }

    .toggle-pwd svg {
      width: 18px;
      height: 18px;
    }

    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.82rem;
      color: var(--text-soft);
    }

    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      user-select: none;
    }

    .checkbox-label input[type="checkbox"] {
      width: 16px;
      height: 16px;
      accent-color: var(--primary-blue);
      cursor: pointer;
    }

    .forgot-link {
      color: var(--primary-blue);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.15s ease;
    }

    .forgot-link:hover {
      text-decoration: underline;
      color: var(--primary-blue-hover);
    }

    .btn-submit {
      width: 100%;
      height: 48px;
      background: linear-gradient(135deg, var(--hero-blue-mid) 0%, var(--hero-blue-start) 100%);
      color: #ffffff;
      border: none;
      border-radius: var(--radius-md);
      font-size: 0.98rem;
      font-weight: 700;
      letter-spacing: 0.01em;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(22, 87, 193, 0.28);
      transition: all 0.2s ease;
      margin-top: 4px;
    }

    .btn-submit:hover {
      background: linear-gradient(135deg, var(--hero-blue-end) 0%, var(--hero-blue-mid) 100%);
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(22, 87, 193, 0.38);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    /* Seed credentials box */
    .seed-box {
      margin-top: 20px;
      background: #f8fafc;
      border: 1px dashed #94a3b8;
      border-radius: var(--radius-md);
      padding: 12px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      font-size: 0.8rem;
    }

    .seed-info {
      color: var(--text-soft);
      line-height: 1.4;
    }

    .seed-info strong {
      color: var(--text-main);
    }

    .btn-autofill {
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: var(--radius-sm);
      padding: 6px 10px;
      font-size: 0.74rem;
      font-weight: 700;
      color: var(--primary-blue);
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
    }

    .btn-autofill:hover {
      background: #eff6ff;
      border-color: #93c5fd;
    }

    /* Alerts / Messages */
    .alert-message {
      padding: 12px 16px;
      border-radius: var(--radius-md);
      font-size: 0.86rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
      text-align: left;
    }

    .alert-message.error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
    }

    .alert-message.success {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
    }

    .alert-message svg {
      flex-shrink: 0;
      width: 18px;
      height: 18px;
    }

    /* ==========================================================================
       FOOTER (OFFICIAL KEMNAKER STYLE)
       ========================================================================== */
    .kemnaker-footer {
      background: var(--kemnaker-navy-dark);
      color: #94a3b8;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 24px 24px;
      margin-top: auto;
      font-size: 0.82rem;
    }

    .footer-inner {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .footer-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .footer-right {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .footer-link {
      color: #cbd5e1;
      text-decoration: none;
      transition: color 0.15s ease;
    }

    .footer-link:hover {
      color: #ffffff;
    }
  </style>
</head>
<body>

  <!-- ========================================================================
       TOP UTILITY BAR
       ======================================================================== -->
  <div class="kemnaker-topbar" role="region" aria-label="Portal Navigation Bar">
    <div class="kemnaker-topbar-inner">
      <div class="topbar-left">
        <span class="topbar-badge">SIAPKERJA</span>
        <span>Kementerian Ketenagakerjaan Republik Indonesia</span>
      </div>
      <div class="topbar-right">
        <span class="topbar-link">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          Bantuan &amp; Regulasi
        </span>
        <span>|</span>
        <span class="topbar-link">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
          Bahasa Indonesia
        </span>
      </div>
    </div>
  </div>

  <!-- ========================================================================
       MAIN KEMNAKER BRAND HEADER (Identical aesthetic to screenshot)
       ======================================================================== -->
  <header class="kemnaker-header">
    <div class="header-inner">
      <a href="welcome-screen.php" class="brand-section" aria-label="Beranda Kemnaker">
        <!-- SVG Kemnaker 9-loop Emblem -->
        <div class="kemnaker-emblem" aria-hidden="true">
          <svg width="42" height="42" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="46" fill="#092c4c" stroke="#3b82f6" stroke-width="2.5"/>
            <!-- Stylized Ministry Emblem Star & Loops in White & Cyan -->
            <g stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none">
              <path d="M50 15 L50 85" stroke="#60a5fa" stroke-width="2.5"/>
              <path d="M15 50 L85 50" stroke="#60a5fa" stroke-width="2.5"/>
              <path d="M25 25 L75 75" stroke="#93c5fd" stroke-width="2"/>
              <path d="M25 75 L75 25" stroke="#93c5fd" stroke-width="2"/>
            </g>
            <!-- Central Interlocking Woven Gear / Rings Motif -->
            <circle cx="50" cy="50" r="22" stroke="#ffffff" stroke-width="3.5" fill="#134e9e"/>
            <circle cx="50" cy="50" r="12" fill="#38bdf8"/>
            <circle cx="50" cy="50" r="5" fill="#ffffff"/>
            <!-- Outer 9 accent dots representing 9 values of Kemnaker -->
            <circle cx="50" cy="20" r="3" fill="#ffffff"/>
            <circle cx="71" cy="28" r="3" fill="#ffffff"/>
            <circle cx="80" cy="50" r="3" fill="#ffffff"/>
            <circle cx="71" cy="72" r="3" fill="#ffffff"/>
            <circle cx="50" cy="80" r="3" fill="#ffffff"/>
            <circle cx="29" cy="72" r="3" fill="#ffffff"/>
            <circle cx="20" cy="50" r="3" fill="#ffffff"/>
            <circle cx="29" cy="28" r="3" fill="#ffffff"/>
          </svg>
        </div>
        <div class="brand-text">
          <span class="brand-title">Kementerian Ketenagakerjaan</span>
          <span class="brand-title" style="color: #93c5fd; font-weight: 700;">Republik Indonesia</span>
          <span class="brand-sub">Portal Ekosistem &amp; Perlindungan Gig Worker</span>
        </div>
      </a>

      <!-- Status tag on right side -->
      <div class="header-actions">
        <div class="portal-tag">
          <span class="portal-tag-dot"></span>
          <span>Prototipe WLLP &bull; Gig v1.0</span>
        </div>
      </div>
    </div>
  </header>

  <!-- ========================================================================
       PAGE MAIN CONTENT
       ======================================================================== -->
  <main class="page-main">

    <!-- 1. HERO BANNER (Matches "Ringkasan WLLP" Royal-Blue Gradient Banner) -->
    <section class="hero-banner" aria-labelledby="hero-title">
      <div class="hero-header">
        <div class="hero-content">
          <div class="hero-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            Sistem Informasi Gig Worker Indonesia
          </div>
          <h1 id="hero-title" class="hero-title">Ekosistem &amp; Perlindungan Gig Worker</h1>
          <p class="hero-desc">
            Pantau pendataan mitra gig, status kepatuhan jaminan sosial ketenagakerjaan, serta integrasi pelaporan platform digital ketenagakerjaan dalam satu portal terpadu.
          </p>
        </div>

        <div class="hero-actions">
          <span class="btn-hero-glass">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard Riset
          </span>
          <a href="#login-box" class="btn-hero-solid">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Masuk Portal
          </a>
        </div>
      </div>
    </section>

    <!-- 2. PROTOTYPE NOTICE BAR (Exact match to screenshot's sky-blue bar) -->
    <div class="notice-bar" role="status">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <span>Halaman ini merupakan prototipe resmi alur autentikasi Gig Worker Kementerian Ketenagakerjaan RI dan terhubung ke database lokal.</span>
    </div>

    <!-- 3. STATS GRID (Matching the 4 metric cards from the screenshot) -->
    <section class="stats-grid" aria-label="Statistik Utama Gig Worker">
      <!-- Card 1: Lowongan / Mitra Terdaftar -->
      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Mitra Gig Terdata</span>
          <div class="stat-icon-wrapper blue" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
          </div>
        </div>
        <div class="stat-number">14.8K</div>
        <div class="stat-caption">Total pekerja gig terdaftar di sistem</div>
      </article>

      <!-- Card 2: Status Aktif -->
      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Platform Terhubung</span>
          <div class="stat-icon-wrapper cyan" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
          </div>
        </div>
        <div class="stat-number">28</div>
        <div class="stat-caption">Aplikasi &amp; platform on-demand aktif</div>
      </article>

      <!-- Card 3: Sudah Terisi / Jamsos Terpenuhi -->
      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Jaminan Sosial (BPJS)</span>
          <div class="stat-icon-wrapper green" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
              <polyline points="17 11 19 13 23 9"/>
            </svg>
          </div>
        </div>
        <div class="stat-number">94.2%</div>
        <div class="stat-caption">Tercakup program JKK &amp; JKM</div>
      </article>

      <!-- Card 4: Belum Terisi / Butuh Verifikasi -->
      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Perlu Verifikasi</span>
          <div class="stat-icon-wrapper amber" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
        </div>
        <div class="stat-number">0</div>
        <div class="stat-caption">Semua data terverifikasi otomatis</div>
      </article>
    </section>

    <!-- 4. TWO-COLUMN WORKSPACE: LEFT INFO + RIGHT LOGIN CARD -->
    <div class="portal-workspace">
      
      <!-- LEFT COLUMN: QUICK ACCESS & SYSTEM INFO -->
      <div class="workspace-info">
        
        <!-- Akses Cepat (matching the screenshot's "Akses Cepat" block) -->
        <section class="white-card" aria-labelledby="quick-access-title">
          <div class="card-title-row">
            <div>
              <h2 id="quick-access-title" class="card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1657c1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Akses Layanan Gig Worker
              </h2>
              <p class="card-subtitle">Fitur ekosistem ketenagakerjaan yang tersedia dalam prototipe</p>
            </div>
          </div>

          <div class="quick-access-grid">
            <div class="quick-access-tile">
              <div class="tile-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              </div>
              <div class="tile-text">
                <h4>Pelaporan Mandiri</h4>
                <p>Registrasi &amp; data jam kerja</p>
              </div>
            </div>

            <div class="quick-access-tile">
              <div class="tile-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
              <div class="tile-text">
                <h4>Status Jamsostek</h4>
                <p>Perlindungan risiko kerja</p>
              </div>
            </div>

            <div class="quick-access-tile">
              <div class="tile-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
              </div>
              <div class="tile-text">
                <h4>Sertifikasi Keahlian</h4>
                <p>Standardisasi kompetensi</p>
              </div>
            </div>

            <div class="quick-access-tile">
              <div class="tile-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              </div>
              <div class="tile-text">
                <h4>Integrasi Platform</h4>
                <p>Sinkronisasi API kemitraan</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Status Distribusi & Keamanan Kemnaker -->
        <section class="white-card" aria-labelledby="status-dist-title">
          <div class="card-title-row">
            <div>
              <h2 id="status-dist-title" class="card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1657c1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Standar Kepatuhan Sistem
              </h2>
              <p class="card-subtitle">Pedoman perlindungan tenaga kerja digital non-formal</p>
            </div>
          </div>

          <div class="feature-list">
            <div class="feature-item">
              <div class="feature-item-left">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Integrasi Single Sign-On (SSO) Siapkerja Kemnaker</span>
              </div>
              <span class="feature-pill green">Aktif</span>
            </div>

            <div class="feature-item">
              <div class="feature-item-left">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Enkripsi Kredensial Standar Password Hashing PHP PDO</span>
              </div>
              <span class="feature-pill green">Aman</span>
            </div>

            <div class="feature-item">
              <div class="feature-item-left">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1657c1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Lingkungan Prototipe Berkelanjutan (Continuous Testing)</span>
              </div>
              <span class="feature-pill blue">Siap Uji</span>
            </div>
          </div>
        </section>

      </div>

      <!-- RIGHT COLUMN: OFFICIAL AUTHENTICATION / LOGIN CARD -->
      <section id="login-box" class="auth-card" aria-labelledby="auth-form-title">
        <div class="auth-header">
          <div class="auth-header-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Autentikasi Akun Petugas &amp; Mitra
          </div>
          <h2 id="auth-form-title" class="auth-title">Masuk ke Portal</h2>
          <p class="auth-subtitle">Gunakan nama pengguna dan kata sandi Anda untuk mengakses dashboard pengelolaan Gig Worker.</p>
        </div>

        <?php if ($message !== ""): ?>
          <div class="alert-message <?php echo htmlspecialchars($messageType, ENT_QUOTES, "UTF-8"); ?>" role="alert">
            <?php if ($messageType === "error"): ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?php else: ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?php endif; ?>
            <span><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></span>
          </div>
        <?php endif; ?>

        <form method="post" action="" class="login-form" autocomplete="on">
          <div class="form-group">
            <label for="username" class="form-label">
              Nama Pengguna (Username)
            </label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </span>
              <input 
                id="username" 
                name="username" 
                type="text" 
                class="form-control" 
                placeholder="Contoh: Tessa" 
                autocomplete="username" 
                required 
              />
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">
              Kata Sandi (Password)
            </label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input 
                id="password" 
                name="password" 
                type="password" 
                class="form-control" 
                placeholder="Masukkan kata sandi akun" 
                autocomplete="current-password" 
                required 
              />
              <button type="button" class="toggle-pwd" id="togglePwdBtn" aria-label="Lihat kata sandi" title="Lihat kata sandi">
                <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" name="remember" id="remember" />
              <span>Ingat saya di perangkat ini</span>
            </label>
            <a href="#" class="forgot-link" onclick="alert('Untuk prototipe ini, silakan gunakan akun bawaan yang tersedia di bawah.'); return false;">Lupa Sandi?</a>
          </div>

          <button type="submit" class="btn-submit" id="submitBtn">
            <span>Masuk ke Dashboard</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </button>
        </form>

        <!-- Quick 1-Click Auto-Fill Demo Credentials -->
        <div class="seed-box">
          <div class="seed-info">
            Akun Prototipe: Pengguna <strong>Tessa</strong> &bull; Sandi <strong>12345</strong>
          </div>
          <button type="button" class="btn-autofill" id="autofillBtn" title="Isi form otomatis untuk pengujian">
            Isi Otomatis
          </button>
        </div>
      </section>

    </div>

  </main>

  <!-- ========================================================================
       OFFICIAL KEMNAKER FOOTER
       ======================================================================== -->
  <footer class="kemnaker-footer">
    <div class="footer-inner">
      <div class="footer-left">
        <span>&copy; <?php echo date("Y"); ?> Kementerian Ketenagakerjaan Republik Indonesia</span>
        <span>&bull;</span>
        <span>Direktorat Jenderal PHI dan Jamsos</span>
      </div>
      <div class="footer-right">
        <span class="footer-link">Kebijakan Privasi</span>
        <span class="footer-link">Syarat &amp; Ketentuan</span>
        <span class="footer-link">Pusat Bantuan Siapkerja</span>
      </div>
    </div>
  </footer>

  <!-- Interactivity Script for Show/Hide Password and Autofill -->
  <script>
    (function() {
      // Toggle password visibility
      const togglePwdBtn = document.getElementById('togglePwdBtn');
      const pwdInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');

      if (togglePwdBtn && pwdInput) {
        togglePwdBtn.addEventListener('click', function() {
          const isPassword = pwdInput.getAttribute('type') === 'password';
          pwdInput.setAttribute('type', isPassword ? 'text' : 'password');
          
          if (isPassword) {
            // Crossed eye icon
            eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            togglePwdBtn.setAttribute('title', 'Sembunyikan kata sandi');
          } else {
            // Normal eye icon
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            togglePwdBtn.setAttribute('title', 'Lihat kata sandi');
          }
        });
      }

      // Autofill demo button
      const autofillBtn = document.getElementById('autofillBtn');
      const usernameInput = document.getElementById('username');

      if (autofillBtn && usernameInput && pwdInput) {
        autofillBtn.addEventListener('click', function() {
          usernameInput.value = 'Tessa';
          pwdInput.value = '12345';
          usernameInput.focus();
          
          // Visual feedback
          const originalText = autofillBtn.textContent;
          autofillBtn.textContent = '✓ Terisi!';
          autofillBtn.style.color = '#15803d';
          autofillBtn.style.borderColor = '#86efac';
          setTimeout(() => {
            autofillBtn.textContent = originalText;
            autofillBtn.style.color = '';
            autofillBtn.style.borderColor = '';
          }, 1500);
        });
      }
    })();
  </script>
</body>
</html>
