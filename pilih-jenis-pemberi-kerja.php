<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Jenis Pemberi Kerja · Karirhub</title>
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
            max-width: 680px;
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

        /* Highlight for Gig Worker Option */
        .option-item-box.gig-highlight {
            border: 2px solid #18b5ea;
            background: #f0f9ff;
        }
        .option-item-box.gig-highlight:hover {
            box-shadow: 0 8px 20px rgba(24, 181, 234, 0.2);
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

        .gig-icon-box {
            background: #18b5ea;
            color: #ffffff;
        }

        .option-content-body {
            flex: 1;
        }

        .option-item-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-recommended {
            background: #18b5ea;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .back-link-wrapper {
            text-align: center;
            margin-top: 28px;
        }
        .back-link-btn {
            color: #475569;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .back-link-btn:hover {
            color: #0284c7;
            text-decoration: underline;
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

        .footer-contacts {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 13px;
            color: #64748b;
        }
        .footer-contacts span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        @media (max-width: 640px) {
            .top-navbar {
                padding: 14px 16px;
            }
            .registration-card {
                padding: 28px 20px;
            }
            .footer-contacts {
                flex-direction: column;
                gap: 8px;
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
            <h1 class="card-header-title">Pilih jenis pemberi kerja</h1>
            <p class="card-header-subtitle">Pilih jenis pemberi kerja yang sesuai dengan kondisi Anda untuk melanjutkan.</p>

            <!-- Option 1: Pemberi Kerja Individu -->
            <div class="option-item-box">
                <a href="#" class="option-item-link">
                    <div class="option-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="option-content-body">
                        <div class="option-item-title">Pemberi Kerja Individu</div>
                        <div class="option-item-desc">
                            Perorangan yang membutuhkan tenaga kerja seperti asisten rumah tangga, pengasuh, sopir pribadi atau kebutuhan pekerjaan perorangan lainnya.
                        </div>
                    </div>
                    <div class="option-chevron">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <div class="option-footer-link">
                    Apa itu Pemberi Kerja Individu ?
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>

            <!-- Option 2: Pemberi Kerja Badan Usaha/Instansi/Lembaga -->
            <div class="option-item-box">
                <a href="#" class="option-item-link">
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
                        <div class="option-item-title">Pemberi Kerja Badan Usaha/Instansi/Lembaga</div>
                        <div class="option-item-desc">
                            Untuk perusahaan, instansi pemerintah, yayasan, organisasi atau lembaga yang memiliki pegawai atau membuka lowongan kerja atas nama entitas.
                        </div>
                    </div>
                    <div class="option-chevron">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <div class="option-footer-link">
                    Apa itu Pemberi Kerja Badan Usaha/Instansi/Lembaga ?
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>

            <!-- ⭐ Option 3 (Requested by User): Pemberi Kerja Gig Workers -->
            <div class="option-item-box gig-highlight">
                <a href="siapkerja-login.php?redirect=employer-register" class="option-item-link">
                    <div class="option-icon-box gig-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </div>
                    <div class="option-content-body">
                        <div class="option-item-title">
                            Pemberi Kerja Gig Workers
                            <span class="badge-recommended">Proyek Gig</span>
                        </div>
                        <div class="option-item-desc">
                            Untuk perusahaan, instansi, atau perorangan yang ingin mempublikasikan proyek dan merekrut Gig Worker.
                        </div>
                    </div>
                    <div class="option-chevron">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
                <div class="option-footer-link">
                    Apa itu Pemberi Kerja Gig Workers ?
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>

            <!-- Bottom link: Sebelumnya -->
            <div class="back-link-wrapper">
                <a href="pilih-pendaftaran.php" class="back-link-btn">Sebelumnya</a>
            </div>

        </div>
    </main>

    <!-- Page Footer -->
    <footer class="page-footer">
        <div>Butuh bantuan? <a href="#">Kunjungi Pusat Bantuan</a> atau hubungi kami</div>
        <div class="footer-contacts">
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                0811-871-2018
            </span>
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                pusatpasarkerja@kemnaker.go.id
            </span>
        </div>
    </footer>

</body>
</html>
