<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Pendaftaran · Karirhub</title>
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

        /* Top Header Navigation Bar */
        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-link-left {
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: color 0.2s ease;
        }
        .nav-link-left:hover {
            color: #18b5ea;
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

        .nav-user-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nav-user-label {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }
        .nav-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #0284c7;
            border: 2px solid #e0f2fe;
        }

        /* Main Content Container */
        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .registration-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 640px;
            padding: 40px 36px;
        }

        .card-header-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .card-header-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
        }

        /* Option Box Item */
        .option-item-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 24px;
            transition: all 0.2s ease;
            overflow: hidden;
            position: relative;
        }
        .option-item-box:hover {
            border-color: #18b5ea;
            box-shadow: 0 6px 16px rgba(24, 181, 234, 0.12);
            background: #ffffff;
        }

        .option-item-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 24px;
        }

        .option-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #e0f2fe;
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .option-content-body {
            flex: 1;
        }

        .option-item-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .option-item-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.5;
            padding-right: 20px;
        }

        .option-chevron {
            color: #94a3b8;
            align-self: center;
            flex-shrink: 0;
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .option-item-box:hover .option-chevron {
            color: #18b5ea;
            transform: translateX(4px);
        }

        .option-footer-link {
            padding: 12px 24px;
            background: #f1f5f9;
            border-top: 1px solid #e2e8f0;
            font-size: 12.5px;
            color: #475569;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .option-footer-link svg {
            color: #64748b;
        }

        /* Page Footer */
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

        @media (max-width: 640px) {
            .top-navbar {
                padding: 14px 16px;
            }
            .registration-card {
                padding: 28px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Bar -->
    <header class="top-navbar">
        <a href="welcome-screen.php" class="nav-link-left">Kembali ke Halaman Utama</a>

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

        <div style="min-width: 170px;"></div>
    </header>

    <!-- Main Container -->
    <main class="main-container">
        <div class="registration-card">
            <h1 class="card-header-title">Pilih pendaftaran</h1>
            <p class="card-header-subtitle">Pilih pendaftaran yang Anda inginkan pada akun Anda.</p>

            <!-- Option 1: Pencari Kerja -->
            <div class="option-item-box">
                <a href="#" class="option-item-link">
                    <div class="option-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="option-content-body">
                        <div class="option-item-title">Pencari Kerja</div>
                        <div class="option-item-desc">
                            Perorangan yang membutuhkan pekerjaan, dan dapat melamar pada lowongan pekerjaan yang tersedia.
                        </div>
                    </div>
                    <div class="option-chevron">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <div class="option-footer-link">
                    Apa itu Pencari Kerja ?
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>

            <!-- Option 1.5: Gig Workers (New) -->
            <div class="option-item-box">
                <a href="siapkerja-login.php?redirect=worker-register" class="option-item-link">
                    <div class="option-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 9.36l-7.1 7.1a1 1 0 0 1-1.42 0l-1.4-1.4a1 1 0 0 1 0-1.42l7.1-7.1a6 6 0 0 1 9.36-7.94l-3.77 3.77a1 1 0 0 0 0 1.4z"></path>
                        </svg>
                    </div>
                    <div class="option-content-body">
                        <div class="option-item-title">Gig Workers</div>
                        <div class="option-item-desc">
                            Pekerja lepas atau profesional independen yang menawarkan jasa untuk proyek jangka pendek.
                        </div>
                    </div>
                    <div class="option-chevron">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <div class="option-footer-link">
                    Apa itu Gig Workers ?
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>

            <!-- Option 2: Pemberi Kerja (Directs to pilih-jenis-pemberi-kerja.php) -->
            <div class="option-item-box">
                <a href="pilih-jenis-pemberi-kerja.php" class="option-item-link">
                    <div class="option-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                            <path d="M9 22v-4h6v4"></path>
                            <line x1="8" y1="6" x2="8.01" y2="6"></line>
                            <line x1="16" y1="6" x2="16.01" y2="6"></line>
                            <line x1="12" y1="6" x2="12.01" y2="6"></line>
                            <line x1="12" y1="10" x2="12.01" y2="10"></line>
                            <line x1="12" y1="14" x2="12.01" y2="14"></line>
                        </svg>
                    </div>
                    <div class="option-content-body">
                        <div class="option-item-title">Pemberi Kerja</div>
                        <div class="option-item-desc">
                            Perorangan atau instansi yang dapat mengelola lowongan pekerjaan yang tersedia.
                        </div>
                    </div>
                    <div class="option-chevron">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <div class="option-footer-link">
                    Apa itu Pemberi Kerja ?
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>

        </div>
    </main>

    <!-- Page Footer -->
    <footer class="page-footer">
        Butuh bantuan? <a href="#">Kunjungi Pusat Bantuan</a> atau hubungi kami
    </footer>

</body>
</html>
