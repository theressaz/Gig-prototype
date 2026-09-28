<?php
declare(strict_types=1);
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard-admin.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');
    $validUser = strtolower($user) === 'admin@kemnaker.go.id' || strtolower($user) === 'admin';
    $validPass = $pass === 'admin123';

    if ($validUser && $validPass) {
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_name'] = 'Admin KarirHub';
        $_SESSION['username'] = 'admin@kemnaker.go.id';
        header('Location: dashboard-admin.php');
        exit;
    }
    $message = 'Email atau kata sandi admin tidak valid.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Masuk Admin · Karirhub</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: grid; place-items: center; background: #0f172a; color: #e2e8f0; padding: 20px; }
    .card { width: 100%; max-width: 420px; background: #fff; color: #0f172a; border-radius: 16px; padding: 36px 32px; box-shadow: 0 20px 50px rgba(0,0,0,.35); }
    h1 { font-size: 1.35rem; margin-bottom: 6px; }
    p { color: #64748b; font-size: 0.9rem; margin-bottom: 24px; }
    label { display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 6px; color: #334155; }
    input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font: inherit; margin-bottom: 14px; }
    button { width: 100%; border: none; background: #1657c1; color: #fff; font-weight: 700; padding: 13px; border-radius: 10px; cursor: pointer; font: inherit; }
    button:hover { background: #1247a3; }
    .msg { padding: 10px 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 14px; }
    .msg.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .back { display: block; text-align: center; margin-top: 16px; color: #64748b; text-decoration: none; font-size: 0.85rem; }
    .hint { margin-top: 18px; font-size: 0.75rem; color: #94a3b8; line-height: 1.5; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Panel Admin Kemnaker</h1>
    <p>Verifikasi pendaftaran Gig Worker, pemberi kerja, dan pengajuan lowongan proyek.</p>
    <?php if ($message !== ''): ?>
      <div class="msg <?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
      <label for="username">Email Admin</label>
      <input type="text" id="username" name="username" required placeholder="admin@kemnaker.go.id" autocomplete="username" />
      <label for="password">Kata Sandi</label>
      <input type="password" id="password" name="password" required autocomplete="current-password" />
      <button type="submit">Masuk ke Dashboard Admin</button>
    </form>
    <a class="back" href="welcome-screen.php">← Kembali ke halaman utama</a>
    <p class="hint">Demo: <strong>admin@kemnaker.go.id</strong> / <strong>admin123</strong></p>
  </div>
</body>
</html>
