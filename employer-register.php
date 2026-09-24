<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/includes/db.php';

// Require user to be logged in with SIAPkerja account first
if (!isset($_SESSION['siapkerja_email']) && !isset($_SESSION['username'])) {
    header("Location: siapkerja-login.php?redirect=employer-register");
    exit;
}

$userEmail = $_SESSION['siapkerja_email'] ?? 'theressaz@pasker.id';
$userName  = $_SESSION['siapkerja_name'] ?? $_SESSION['username'] ?? 'Theressa Zaratrusha';
$userNik   = $_SESSION['siapkerja_nik'] ?? '1471 0252 0803 0001';
$userPhone = $_SESSION['siapkerja_phone'] ?? '08117671208';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $industry = trim((string)($_POST['industry'] ?? ''));
    $namaPic = trim((string)($_POST['nama_pic'] ?? $userName));
    $nikPic = trim((string)($_POST['nik_pic'] ?? $userNik));
    $emailPic = trim((string)($_POST['email_pic'] ?? $userEmail));
    $phonePic = trim((string)($_POST['phone_pic'] ?? $userPhone));

    // Set session user as logged-in employer
    $_SESSION['username'] = $namaPic !== '' ? $namaPic : $userName;
    $_SESSION['role'] = 'employer';
    $_SESSION['company_registered'] = true;

    header("Location: dashboard-employer.php?registered=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar sebagai pemberi kerja · Karirhub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .top-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-link {
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .header-link:hover {
            color: #0f172a;
        }

        .karirhub-logo-mark {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .karirhub-text {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }
        .karirhub-subtext {
            display: block;
            font-size: 10px;
            color: #64748b;
            font-weight: 500;
            margin-top: -3px;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: #2563eb;
            background-image: url('https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            border: 2px solid #3b82f6;
        }

        /* Main Container */
        .main-container {
            flex: 1;
            max-width: 620px;
            width: 100%;
            margin: 30px auto 40px auto;
            padding: 0 20px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
            text-align: left;
        }

        .reg-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 28px 32px;
        }

        /* Section Header with Step Indicator */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .step-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }
        .step-icon {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2.5px solid #2563eb;
            display: inline-block;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .required-star {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 14px;
            background-color: #f3f4f6;
            border: 1px solid transparent;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            color: #1e293b;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            cursor: pointer;
            color: #64748b;
        }
        .form-select option {
            color: #1e293b;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .helper-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
        }

        /* Action Buttons */
        .btn-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 28px;
        }

        .btn-secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 700;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .btn-primary {
            background: #3b82f6;
            border: 1px solid #3b82f6;
            color: #ffffff;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 700;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        /* Footer */
        .page-footer {
            margin-top: auto;
            padding: 24px 20px 32px 20px;
            text-align: center;
            font-size: 13.5px;
            color: #64748b;
        }
        .footer-link {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }

        .footer-contacts {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 24px;
            margin-top: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        @media (max-width: 640px) {
            .top-header {
                padding: 14px 20px;
            }
            .form-row, .btn-row {
                grid-template-columns: 1fr;
            }
            .reg-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="top-header">
        <a href="welcome-screen.php" class="header-link">Kembali ke Halaman Utama</a>
        
        <a href="welcome-screen.php" class="karirhub-logo-mark">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                <path d="M10 10 H28 Q32 10 32 14 V18 L20 30 H10 Z" fill="#18b5ea"/>
                <circle cx="28" cy="12" r="3" fill="#38bdf8"/>
            </svg>
            <div>
                <span class="karirhub-text">Karir<span style="color: #18b5ea;">hub</span></span>
                <span class="karirhub-subtext">oleh Kemnaker</span>
            </div>
        </a>

        <div class="header-user">
            <span>Dasbor Pengelola</span>
            <div class="user-avatar"></div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <h1 class="page-title">Daftar sebagai pemberi kerja</h1>

        <div class="reg-card">
            <div class="section-header">
                <h2 class="section-title">Informasi dasar & PIC</h2>
            </div>

            <form method="POST" action="employer-register.php">
                <!-- Industri -->
                <div class="form-group">
                    <label class="form-label" for="industry">Industri <span class="required-star">*</span></label>
                    <select id="industry" name="industry" class="form-select" required>
                        <option value="" disabled selected>Pilih industri</option>
                        <option value="Teknologi Informasi & Perangkat Lunak">Teknologi Informasi & Perangkat Lunak</option>
                        <option value="Desain, Kreatif & Media">Desain, Kreatif & Media</option>
                        <option value="Pemasaran & Komunikasi">Pemasaran & Komunikasi</option>
                        <option value="Jasa Profesional & Konsultan">Jasa Profesional & Konsultan</option>
                        <option value="Perdagangan & Manufaktur">Perdagangan & Manufaktur</option>
                    </select>
                </div>

                <!-- Nama PIC & NIK PIC -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nama_pic">Nama PIC <span class="required-star">*</span></label>
                        <input type="text" id="nama_pic" name="nama_pic" class="form-control" value="<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nik_pic">NIK PIC <span class="required-star">*</span></label>
                        <input type="text" id="nik_pic" name="nik_pic" class="form-control" value="<?php echo htmlspecialchars($userNik, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <!-- Email PIC -->
                <div class="form-group">
                    <label class="form-label" for="email_pic">Email PIC <span class="required-star">*</span></label>
                    <input type="email" id="email_pic" name="email_pic" class="form-control" value="<?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?>" required>
                    <div class="helper-text">Contoh: xxxxx@gmail.com</div>
                </div>

                <!-- Nomor telepon PIC -->
                <div class="form-group">
                    <label class="form-label" for="phone_pic">Nomor telepon PIC <span class="required-star">*</span></label>
                    <input type="text" id="phone_pic" name="phone_pic" class="form-control" value="<?php echo htmlspecialchars($userPhone, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <!-- Action Buttons -->
                <div class="btn-row">
                    <a href="pilih-jenis-pemberi-kerja.php" class="btn-secondary">Kembali</a>
                    <button type="submit" class="btn-primary">Daftar</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="page-footer">
        <div>
            Butuh bantuan? <a href="#" class="footer-link">Kunjungi Pusat Bantuan</a> atau hubungi kami
        </div>
        <div class="footer-contacts">
            <div class="contact-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <span>0811-871-2018</span>
            </div>
            <div class="contact-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <span>pusatpasarkerja@kemnaker.go.id</span>
            </div>
        </div>
    </footer>

</body>
</html>
