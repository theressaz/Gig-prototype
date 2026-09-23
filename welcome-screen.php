<?php
declare(strict_types=1);
session_start();

if (isset($_SESSION["username"]) && isset($_SESSION["role"])) {
    if ($_SESSION["role"] === 'employer') {
        header("Location: dashboard-employer.php");
        exit;
    } elseif ($_SESSION["role"] === 'worker') {
        header("Location: dashboard-worker.php");
        exit;
    }
}

// Handle Logout action explicitly
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk atau Daftar · Karirhub</title>
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

        /* Top Brand Header */
        .page-header {
            width: 100%;
            padding-top: 50px;
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
            width: 36px;
            height: 36px;
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

        /* Main Container */
        .main-wrapper {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Card Base */
        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1f5f9;
            width: 100%;
            max-width: 460px;
            padding: 40px 36px;
            text-align: center;
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .card-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.5;
            font-weight: 400;
        }

        .btn-action-primary {
            background-color: #18b5ea;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 20px;
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
            margin-bottom: 12px;
        }
        .btn-action-primary:hover {
            background-color: #0fa1d2;
            box-shadow: 0 4px 12px rgba(24, 181, 234, 0.35);
        }

        .btn-action-secondary {
            background-color: #ffffff;
            color: #18b5ea;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            padding: 13px 20px;
            border-radius: 10px;
            border: 2px solid #18b5ea;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-action-secondary:hover {
            background-color: #f0f9ff;
        }

        .text-divider {
            margin: 16px 0;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
        }

        .card-link-wrapper {
            margin-top: 24px;
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

        /* Page Footer */
        .page-footer {
            padding: 24px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
            width: 100%;
        }
    </style>
</head>
<body>

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
        <div class="auth-card">
            <h1 class="card-title">Masuk ke Platform Pemberi Kerja</h1>
            <p class="card-subtitle">
                Pilih apakah Anda ingin langsung masuk dengan akun yang sudah ada atau melakukan pendaftaran baru.
            </p>

            <a href="siapkerja-login.php" class="btn-action-primary">
                Masuk Langsung dengan SIAPkerja
            </a>

            <div class="text-divider">atau</div>

            <a href="pilih-pendaftaran.php" class="btn-action-secondary">
                Daftar Akun Baru
            </a>

            <div class="card-link-wrapper">
                Belum punya akun? <a href="pilih-pendaftaran.php">Daftar akun di sini</a>
            </div>
        </div>
    </main>

    <footer class="page-footer">
        © 2026 Karirhub
    </footer>

</body>
</html>
