<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Verifikasi Gig Worker · Karirhub</title>
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
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header-nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .nav-center-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .nav-center-logo .logo-text {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .nav-center-logo .logo-subtext {
            font-size: 10px;
            color: #64748b;
            display: block;
            line-height: 1;
        }

        .main-wrapper {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .status-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            width: 100%;
            max-width: 520px;
            padding: 44px 40px;
            text-align: center;
        }

        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #f0f9ff;
            border: 2px solid #bae6fd;
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.35;
        }

        .card-subtitle {
            font-size: 14px;
            color: #475569;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            text-align: left;
            margin-bottom: 28px;
        }

        .btn-primary {
            background-color: #0284c7;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            padding: 13px 24px;
            border-radius: 9999px;
            border: none;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
            margin-bottom: 12px;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }

        .btn-outline {
            background-color: #ffffff;
            color: #475569;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 9999px;
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
            color: #0f172a;
        }

        .page-footer {
            padding: 24px 20px;
            text-align: center;
            font-size: 13.5px;
            color: #64748b;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .page-footer a {
            color: #0284c7;
            font-weight: 600;
            text-decoration: none;
        }
        .page-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Top Navigation Header -->
    <header class="header-nav">
        <a href="welcome-screen.php" class="nav-center-logo">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                <path d="M10 10 H28 Q32 10 32 14 V18 L20 30 H10 Z" fill="#18b5ea"/>
                <circle cx="28" cy="12" r="3" fill="#38bdf8"/>
            </svg>
            <div>
                <span class="logo-text">Karir<span style="color: #18b5ea;">hub</span></span>
                <span class="logo-subtext">oleh Kemnaker</span>
            </div>
        </a>
    </header>

    <!-- Main Content -->
    <main class="main-wrapper">
        <div class="status-card">
            <!-- Animated / Styled Clock Icon -->
            <div class="icon-circle">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>

            <div class="status-badge">
                ⏳ Dalam Verifikasi Admin
            </div>

            <h1 class="card-title">Permohonan Anda Sedang Diproses</h1>
            <p class="card-subtitle">
                Terima kasih telah mendaftar sebagai <strong>Gig Worker</strong>. Pengajuan biodata dan berkas Anda telah diterima dan saat ini sedang dalam tahap peninjauan dan verifikasi oleh tim Admin Kemnaker.
            </p>

            <div class="info-box">
                📌 <strong>Informasi Verifikasi:</strong><br />
                Proses verifikasi membutuhkan waktu 1-3 hari kerja. Setelah verifikasi selesai, akun Anda akan aktif sepenuhnya untuk melamar proyek dan menerima tawaran pekerjaan dari Pemberi Kerja.
            </div>

            <a href="dashboard-worker.php" class="btn-primary">
                Masuk ke Dasbor Gig Worker &rarr;
            </a>

            <a href="welcome-screen.php" class="btn-outline">
                Kembali ke Halaman Utama
            </a>
        </div>
    </main>

    <!-- Page Footer -->
    <footer class="page-footer">
        Butuh bantuan? <a href="#">Kunjungi Pusat Bantuan</a> atau hubungi kami
    </footer>

</body>
</html>
