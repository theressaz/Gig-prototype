<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/includes/db.php';

$success = false;
$error = "";

$email = $_SESSION['siapkerja_email'] ?? 'theressasilaban@gmail.com';
$name = $_SESSION['siapkerja_name'] ?? 'Theressa Silaban';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim((string)($_POST['company_name'] ?? ''));
    $industry = trim((string)($_POST['industry'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));

    if ($companyName === '') {
        $error = "Nama Perusahaan / Pemberi Kerja wajib diisi.";
    } else {
        // Set session user as logged-in employer
        $_SESSION['username'] = $companyName;
        $_SESSION['role'] = 'employer';
        $_SESSION['company_registered'] = true;

        header("Location: dashboard-employer.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pemberi Kerja · Karirhub</title>
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

        .header-nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .main-container {
            flex: 1;
            max-width: 680px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
        }

        .reg-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            padding: 36px;
        }

        .reg-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .reg-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .badge-siapkerja {
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .form-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #18b5ea;
            box-shadow: 0 0 0 3px rgba(24, 181, 234, 0.15);
        }

        .btn-submit {
            background: #18b5ea;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            padding: 14px 24px;
            border-radius: 10px;
            border: none;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(24, 181, 234, 0.25);
        }

        .btn-submit:hover {
            background: #0fa1d2;
        }

        .error-banner {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <header class="header-nav">
        <a href="welcome-screen.php" class="logo-box">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                <path d="M10 10 H28 Q32 10 32 14 V18 L20 30 H10 Z" fill="#18b5ea"/>
                <circle cx="28" cy="12" r="3" fill="#38bdf8"/>
            </svg>
            <span style="font-size: 20px; font-weight: 800; color: #0f172a;">Karir<span style="color: #18b5ea;">hub</span></span>
        </a>
    </header>

    <main class="main-container">
        <div class="reg-card">
            <h1 class="reg-title">Pendaftaran Pemberi Kerja</h1>
            <p class="reg-subtitle">
                Lengkapi profil usaha/perusahaan Anda untuk mempublikasikan proyek dan merekrut Gig Worker melalui platform Karirhub SIAPkerja.
            </p>

            <div class="badge-siapkerja">
                <svg width="24" height="24" viewBox="0 0 36 36" fill="none">
                    <circle cx="12" cy="12" r="6" fill="#09bda4"/>
                    <circle cx="24" cy="12" r="6" fill="#09bda4"/>
                    <circle cx="18" cy="24" r="6" fill="#09bda4"/>
                </svg>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #0369a1;">Tersambung dengan Akun SIAPkerja</div>
                    <div style="font-size: 13px; color: #0c4a6e; font-weight: 600;"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>)</div>
                </div>
            </div>

            <?php if ($error !== ""): ?>
                <div class="error-banner"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" action="employer-register.php">
                <div class="form-section-title">Informasi Perusahaan / Pemberi Kerja</div>

                <div class="form-group">
                    <label class="form-label" for="company_name">Nama Perusahaan / Instansi / Usaha <span style="color:red;">*</span></label>
                    <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Contoh: PT Talenta Digital Nusantara / Studio Kreatif Mandiri" value="PT ABC" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="industry">Kategori / Sektor Industri</label>
                    <select id="industry" name="industry" class="form-control">
                        <option value="Teknologi Informasi & Perangkat Lunak">Teknologi Informasi & Perangkat Lunak</option>
                        <option value="Desain, Kreatif & Media">Desain, Kreatif & Media</option>
                        <option value="Pemasaran & Komunikasi">Pemasaran & Komunikasi</option>
                        <option value="Jasa Profesional & Konsultan">Jasa Profesional & Konsultan</option>
                        <option value="Perdagangan & Manufaktur">Perdagangan & Manufaktur</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon / WhatsApp Kantor</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="0812XXXXXXXX">
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Alamat Domisili Perusahaan</label>
                    <textarea id="address" name="address" class="form-control" rows="3" placeholder="Jl. Jendral Sudirman No. 45, Jakarta Selatan"></textarea>
                </div>

                <button type="submit" class="btn-submit">Daftar Sebagai Pemberi Kerja</button>
            </form>
        </div>
    </main>

</body>
</html>
