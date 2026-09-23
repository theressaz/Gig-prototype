<?php
declare(strict_types=1);
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Gig');

$message = "";
$messageType = "";

// Determine active step from GET or POST (default: gateway)
// Step 1: gateway ("Masuk ke Platform Pemberi Kerja")
// Step 2: siapkerja ("Masuk ke SIAPkerja ID")
// Step 3: unregistered ("Pemberi Kerja Belum Terdaftar")
$step = (string)($_GET['step'] ?? $_POST['step'] ?? 'gateway');

if (isset($_SESSION["username"]) && isset($_SESSION["role"]) && !isset($_GET['preview'])) {
    if ($_SESSION["role"] === 'employer') {
        header("Location: dashboard-employer.php");
        exit;
    } elseif ($_SESSION["role"] === 'worker') {
        header("Location: dashboard-worker.php");
        exit;
    }
}

$dbOffline = false;
$pdo = null;

try {
    $serverDsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
    ]);
    $serverPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    $databaseDsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($databaseDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
    ]);

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `Login` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('worker', 'employer') NOT NULL DEFAULT 'worker'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    try {
        $pdo->exec("ALTER TABLE `Login` ADD COLUMN `role` ENUM('worker', 'employer') NOT NULL DEFAULT 'worker'");
    } catch (Throwable $e) {
        // Ignore if column exists
    }

    $workerPasswordHash = password_hash("12345", PASSWORD_DEFAULT);
    $employerPasswordHash = password_hash("00000", PASSWORD_DEFAULT);
    
    $seedStmt = $pdo->prepare(
        "INSERT IGNORE INTO `Login` (`username`, `password`, `role`) VALUES 
         ('Tessa', :pass1, 'worker'),
         ('PT ABC', :pass2, 'employer')"
    );
    $seedStmt->execute([
        ":pass1" => $workerPasswordHash,
        ":pass2" => $employerPasswordHash
    ]);

} catch (Throwable $e) {
    $dbOffline = true;
}

// Handle Logout action explicitly
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    session_start();
    header("Location: welcome-screen.php?step=gateway");
    exit;
}

// Handle POST actions from SIAPkerja form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'siapkerja_login') {
        $loginTarget = $_POST['simulated_target'] ?? 'employer_registered';
        $usernameInput = trim((string)($_POST["username"] ?? "theressasilaban@gmail.com"));

        if ($loginTarget === 'unregistered') {
            // User has SIAPKerja account, but hasn't registered as Pemberi Kerja -> leads to Picture 3
            header("Location: welcome-screen.php?step=unregistered");
            exit;
        } elseif ($loginTarget === 'worker') {
            // Log in as Gig Worker
            $_SESSION["username"] = $usernameInput !== "" ? $usernameInput : "Tessa";
            $_SESSION["role"] = 'worker';
            header("Location: dashboard-worker.php");
            exit;
        } else {
            // Default: Log in as Registered Employer
            $_SESSION["username"] = "PT ABC";
            $_SESSION["role"] = 'employer';
            header("Location: dashboard-employer.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        if ($step === 'siapkerja') echo 'Masuk ke SIAPkerja ID';
        elseif ($step === 'unregistered') echo 'Pemberi Kerja Belum Terdaftar · Karirhub';
        else echo 'Masuk ke Platform Pemberi Kerja · Karirhub';
    ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #ffffff;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            overflow-x: hidden;
        }

        /* Demo Navigation Bar */
        .demo-nav-bar {
            position: fixed;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 9999;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .demo-nav-label {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-right: 6px;
        }
        .demo-nav-btn {
            color: #e2e8f0;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .demo-nav-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
        .demo-nav-btn.active {
            background: #18b5ea;
            color: #ffffff;
            font-weight: 700;
        }

        /* Top Brand Header */
        .page-header {
            width: 100%;
            padding-top: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .karirhub-logo-mark {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .karirhub-icon {
            width: 32px;
            height: 32px;
        }

        .karirhub-text {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .karirhub-subtext {
            font-size: 10px;
            color: #64748b;
            font-weight: 500;
            display: block;
            line-height: 1;
        }

        /* SIAPkerja ID Header */
        .siapkerja-header-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-top: 40px;
        }

        /* Main Container */
        .main-wrapper {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        /* Card Base */
        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1f5f9;
            width: 100%;
            max-width: 440px;
            padding: 36px 32px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        /* Step 1 & Step 3 Logo Inside Card */
        .card-top-icon {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .card-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 28px;
            line-height: 1.5;
            font-weight: 400;
        }

        /* Buttons */
        .btn-cyan {
            background-color: #18b5ea;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            padding: 13px 20px;
            border-radius: 10px;
            border: none;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(24, 181, 234, 0.25);
        }
        .btn-cyan:hover {
            background-color: #0fa1d2;
            box-shadow: 0 4px 12px rgba(24, 181, 234, 0.35);
        }

        .btn-teal {
            background-color: #09bda4;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            padding: 13px 20px;
            border-radius: 8px;
            border: none;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-teal:hover {
            background-color: #07a690;
        }

        .btn-outline {
            background-color: #ffffff;
            color: #334155;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-outline:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
        }

        .card-link-wrapper {
            margin-top: 20px;
            font-size: 14px;
            color: #64748b;
        }
        .card-link-wrapper a {
            color: #18b5ea;
            text-decoration: none;
            font-weight: 600;
        }
        .card-link-wrapper a:hover {
            text-decoration: underline;
        }

        /* Divider text */
        .text-divider {
            margin: 14px 0;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
        }

        /* SIAPkerja Form Fields */
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .form-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .form-label-row .form-label {
            margin-bottom: 0;
        }
        .link-teal {
            color: #09bda4;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }
        .link-teal:hover {
            text-decoration: underline;
        }

        .input-control {
            width: 100%;
            background-color: #eef5fc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 14px;
            font-family: inherit;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
        }
        .input-control:focus {
            background-color: #ffffff;
            border-color: #09bda4;
            box-shadow: 0 0 0 3px rgba(9, 189, 164, 0.15);
        }

        .password-wrapper {
            position: relative;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Demo Mode Box inside Form */
        .demo-simulation-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 20px;
            text-align: left;
        }
        .demo-simulation-title {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .demo-sim-radio {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 12.5px;
            color: #334155;
        }
        .demo-sim-radio label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        /* Step 2 Full Background Vector Graphic Container */
        .siapkerja-bg-wrapper {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            padding: 0 5%;
        }

        .bg-vector-side {
            opacity: 0.85;
            max-width: 320px;
        }

        /* Page Footer */
        .page-footer {
            padding: 24px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
            width: 100%;
        }

        @media (max-width: 640px) {
            .demo-nav-bar {
                width: 92%;
                justify-content: center;
            }
            .auth-card {
                padding: 28px 20px;
            }
            .bg-vector-side {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Demo Interactive Navigation Bar -->
    <div class="demo-nav-bar">
        <span class="demo-nav-label">Simulasi Tampilan:</span>
        <a href="welcome-screen.php?step=gateway" class="demo-nav-btn <?php echo $step === 'gateway' ? 'active' : ''; ?>">
            1. Gateway Pemberi Kerja
        </a>
        <a href="welcome-screen.php?step=siapkerja" class="demo-nav-btn <?php echo $step === 'siapkerja' ? 'active' : ''; ?>">
            2. Form SIAPkerja ID
        </a>
        <a href="welcome-screen.php?step=unregistered" class="demo-nav-btn <?php echo $step === 'unregistered' ? 'active' : ''; ?>">
            3. Belum Terdaftar
        </a>
    </div>

    <!-- MAIN BODY CONTENT BASED ON STEP -->
    <?php if ($step === 'siapkerja'): ?>

        <!-- ========================================== -->
        <!-- SCREEN 2: MASUK KE SIAPKERJA ID (Form Login)-->
        <!-- ========================================== -->
        <header class="siapkerja-header-brand">
            <svg width="28" height="28" viewBox="0 0 36 36" fill="none">
                <circle cx="12" cy="12" r="6" fill="#09bda4"/>
                <circle cx="24" cy="12" r="6" fill="#09bda4"/>
                <circle cx="18" cy="24" r="6" fill="#09bda4"/>
            </svg>
            <span style="font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">
                SIAP<span style="color: #09bda4;">kerja</span> <span style="font-weight: 700; color: #09bda4;">ID</span>
            </span>
        </header>

        <main class="main-wrapper">
            <!-- Left & Right Vector Illustrations for SIAPkerja Portal Background -->
            <div class="siapkerja-bg-wrapper">
                <!-- Left Vector Illustration (Welder, laptop, certificate) -->
                <svg class="bg-vector-side" viewBox="0 0 350 400" fill="none">
                    <path d="M40 320 Q 150 200 280 340" stroke="#cbd5e1" stroke-width="20" stroke-linecap="round" fill="none" opacity="0.4"/>
                    <!-- Laptop Graphic -->
                    <rect x="50" y="240" width="80" height="50" rx="6" fill="#0284c7" opacity="0.8"/>
                    <rect x="56" y="246" width="68" height="38" rx="3" fill="#e0f2fe"/>
                    <path d="M40 292 H 140" stroke="#0369a1" stroke-width="6" stroke-linecap="round"/>
                    <!-- Robot arm / Industrial SVG element -->
                    <path d="M160 160 L200 220 L240 180 L220 280" stroke="#0284c7" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="160" cy="160" r="14" fill="#0369a1"/>
                    <circle cx="200" cy="220" r="10" fill="#0284c7"/>
                    <!-- Certificate graphic -->
                    <rect x="70" y="300" width="70" height="45" rx="4" fill="#ffffff" stroke="#09bda4" stroke-width="2"/>
                    <path d="M80 315 H120 M80 325 H110" stroke="#cbd5e1" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="125" cy="330" r="8" fill="#10b981"/>
                </svg>

                <!-- Right Vector Illustration (Briefcase, Produce basket, RP icon, Welder mask) -->
                <svg class="bg-vector-side" viewBox="0 0 350 400" fill="none">
                    <!-- Briefcase Graphic -->
                    <rect x="180" y="170" width="100" height="80" rx="10" fill="#0369a1"/>
                    <path d="M210 170 V155 Q 210 145 230 145 Q 250 145 250 155 V170" stroke="#0369a1" stroke-width="6" fill="none"/>
                    <rect x="220" y="195" width="20" height="15" rx="3" fill="#38bdf8"/>
                    <!-- Produce/Basket Graphic -->
                    <rect x="240" y="280" width="80" height="50" rx="6" fill="#0284c7"/>
                    <path d="M250 260 Q 265 240 280 260" stroke="#22c55e" stroke-width="14" stroke-linecap="round"/>
                    <rect x="260" y="295" width="40" height="20" rx="3" fill="#ffffff"/>
                    <text x="272" y="310" fill="#0284c7" font-size="11" font-weight="bold">Rp</text>
                    <!-- Welder Mask -->
                    <path d="M280 130 C 310 130 330 150 330 185 L 300 200 L 280 185 Z" fill="#1e293b"/>
                    <rect x="295" y="150" width="25" height="12" rx="2" fill="#38bdf8"/>
                </svg>
            </div>

            <div class="auth-card">
                <h1 class="card-title" style="margin-bottom: 24px;">Masuk</h1>
                
                <form method="POST" action="welcome-screen.php">
                    <input type="hidden" name="action" value="siapkerja_login">
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Email atau nomor handphone</label>
                        <input type="text" id="username" name="username" class="input-control" value="theressasilaban@gmail.com" required>
                    </div>

                    <div class="form-group">
                        <div class="form-label-row">
                            <label class="form-label" for="password">Password</label>
                            <a href="#" class="link-teal">Kendala Masuk ke Akun?</a>
                        </div>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="input-control" value="00000" required>
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" aria-label="Toggle Password">
                                <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Simulated Target Selector for Prototype Testing -->
                    <div class="demo-simulation-box">
                        <div class="demo-simulation-title">Simulasi Status Akun saat Login:</div>
                        <div class="demo-sim-radio">
                            <label>
                                <input type="radio" name="simulated_target" value="employer_registered" checked>
                                <strong>Pemberi Kerja Terdaftar</strong> (Masuk ke Dashboard)
                            </label>
                            <label>
                                <input type="radio" name="simulated_target" value="unregistered">
                                <strong>Belum Terdaftar Pemberi Kerja</strong> (Menuju Gambar 3)
                            </label>
                            <label>
                                <input type="radio" name="simulated_target" value="worker">
                                <strong>Gig Worker</strong> (Masuk Dashboard Worker)
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-teal">Masuk</button>

                    <div class="card-link-wrapper" style="margin-top: 20px;">
                        Belum memiliki akun? <a href="#" style="color: #09bda4;">Daftar Sekarang</a>
                    </div>

                    <div style="margin-top: 28px;">
                        <a href="#" style="color: #475569; font-size: 13.5px; text-decoration: none; font-weight: 500;">Kunjungi Pusat Bantuan</a>
                    </div>
                </form>
            </div>
        </main>

        <footer class="page-footer">
            ©2026 Kemnaker RI
        </footer>

    <?php elseif ($step === 'unregistered'): ?>

        <!-- ========================================== -->
        <!-- SCREEN 3: PEMBERI KERJA BELUM TERDAFTAR   -->
        <!-- ========================================== -->
        <header class="page-header" style="visibility: hidden;">
            <!-- Placeholder alignment -->
        </header>

        <main class="main-wrapper">
            <div class="auth-card" style="max-width: 460px;">
                <!-- Karirhub Top Logo Icon -->
                <div class="card-top-icon">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                        <path d="M10 10 H28 Q32 10 32 14 V18 L20 30 H10 Z" fill="#18b5ea"/>
                        <circle cx="28" cy="12" r="3" fill="#38bdf8"/>
                    </svg>
                </div>

                <h1 class="card-title">Pemberi Kerja Belum Terdaftar</h1>
                <p class="card-subtitle" style="margin-bottom: 30px;">
                    Akun ini belum memiliki data pemberi kerja. Silahkan klik tombol dibawah ini untuk mendaftar sebagai pemberi kerja.
                </p>

                <a href="employer-register.php" class="btn-cyan" style="margin-bottom: 4px;">
                    Daftar Pemberi Kerja
                </a>

                <div class="text-divider">atau</div>

                <a href="welcome-screen.php?action=logout" class="btn-outline">
                    Keluar
                </a>
            </div>
        </main>

        <footer class="page-footer">
            © 2026 Karirhub
        </footer>

    <?php else: ?>

        <!-- ========================================== -->
        <!-- SCREEN 1: MASUK KE PLATFORM PEMBERI KERJA  -->
        <!-- ========================================== -->
        <header class="page-header">
            <div class="karirhub-logo-mark">
                <svg class="karirhub-icon" viewBox="0 0 40 40" fill="none">
                    <path d="M10 10 H28 Q32 10 32 14 V18 L20 30 H10 Z" fill="#18b5ea"/>
                    <circle cx="28" cy="12" r="3" fill="#38bdf8"/>
                </svg>
                <div>
                    <span class="karirhub-text">Karir<span style="color: #18b5ea;">hub</span></span>
                    <span class="karirhub-subtext">oleh Kemnaker</span>
                </div>
            </div>
        </header>

        <main class="main-wrapper">
            <div class="auth-card" style="max-width: 460px;">
                <h1 class="card-title">Masuk ke Platform Pemberi Kerja</h1>
                <p class="card-subtitle">
                    Silakan masuk menggunakan akun SIAPkerja Anda
                </p>

                <a href="welcome-screen.php?step=siapkerja" class="btn-cyan">
                    Masuk dengan akun SIAPkerja
                </a>

                <div class="card-link-wrapper">
                    Belum punya akun? <a href="welcome-screen.php?step=siapkerja">Daftar di sini</a>
                </div>
            </div>
        </main>

        <footer class="page-footer">
            © 2026 Karirhub
        </footer>

    <?php endif; ?>

    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (!pwdInput) return;
            
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                pwdInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                `;
            }
        }
    </script>
</body>
</html>
