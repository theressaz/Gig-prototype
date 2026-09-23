<?php
declare(strict_types=1);
session_start();

// If logout clicked
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header("Location: welcome-screen.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberi Kerja Belum Terdaftar · Karirhub</title>
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

        .main-wrapper {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

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
            margin-bottom: 30px;
            line-height: 1.6;
            font-weight: 400;
        }

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
            box-shadow: 0 2px 6px rgba(24, 181, 234, 0.25);
        }
        .btn-cyan:hover {
            background-color: #0fa1d2;
            box-shadow: 0 4px 12px rgba(24, 181, 234, 0.35);
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

        .text-divider {
            margin: 14px 0;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
        }

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

    <!-- SCREEN 3: PEMBERI KERJA BELUM TERDAFTAR -->
    <main class="main-wrapper">
        <div class="auth-card">
            <!-- Karirhub Top Logo Icon -->
            <div class="card-top-icon">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                    <path d="M10 10 H28 Q32 10 32 14 V18 L20 30 H10 Z" fill="#18b5ea"/>
                    <circle cx="28" cy="12" r="3" fill="#38bdf8"/>
                </svg>
            </div>

            <h1 class="card-title">Pemberi Kerja Belum Terdaftar</h1>
            <p class="card-subtitle">
                Akun ini belum memiliki data pemberi kerja. Silahkan klik tombol dibawah ini untuk mendaftar sebagai pemberi kerja.
            </p>

            <a href="employer-register.php" class="btn-cyan">
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

</body>
</html>
