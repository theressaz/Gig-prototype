<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['siapkerja_email']) && empty($_SESSION['username'])) {
    header('Location: siapkerja-login.php?redirect=karirhub-home');
    exit;
}

$email = strtolower((string)($_SESSION['siapkerja_email'] ?? ''));
$isEmployer = ($_SESSION['role'] ?? '') === 'employer'
    || in_array($email, ['employer@pasker.id', 'calon.employer@pasker.id'], true);

if (!$isEmployer) {
    header('Location: karirhub-home.php');
    exit;
}

$name = (string)($_SESSION['siapkerja_name'] ?? $_SESSION['username'] ?? 'Pemberi Kerja');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dasbor Pemberi Kerja · Karirhub</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
    .wrap { max-width: 720px; margin: 48px auto; padding: 0 20px; }
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 8px 24px rgba(15,23,42,.06); }
    h1 { font-size: 1.35rem; margin-bottom: 8px; }
    p { color: #64748b; line-height: 1.6; }
    a { color: #1657c1; font-weight: 700; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Dasbor Pemberi Kerja</h1>
      <p>Selamat datang, <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>. Kelola lowongan pekerjaan Karirhub untuk perusahaan yang terdaftar.</p>
      <p style="margin-top:16px;"><a href="karirhub-home.php">← Kembali ke Beranda Karirhub</a></p>
    </div>
  </div>
</body>
</html>
