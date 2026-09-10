<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'employer') {
    header("Location: welcome-screen.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            (bool)$params["secure"],
            (bool)$params["httponly"]
        );
    }
    session_destroy();
    header("Location: welcome-screen.php");
    exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'worker-profiles.php';

$workerId = trim((string)($_GET['id'] ?? ''));
$worker = $workerId !== '' ? gig_find_worker($workerId) : null;

if ($worker === null) {
    header("Location: dashboard-employer.php");
    exit;
}

$username = (string)$_SESSION["username"];
$contactUnlocked = !empty($worker['agreed']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?> | Profil Gig Worker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --kemnaker-navy-dark: #061d33;
      --kemnaker-navy: #092c4c;
      --kemnaker-navy-light: #0f3d68;
      --primary-blue: #1657c1;
      --bg-page: #f4f6fa;
      --bg-surface: #ffffff;
      --border-subtle: #e2e8f0;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --text-soft: #475569;
      --success-green: #10b981;
      --warning-amber: #d97706;
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-pill: 9999px;
      --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06);
      --shadow-md: 0 4px 14px -1px rgba(15, 23, 42, 0.08);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    body { min-height: 100vh; background: var(--bg-page); color: var(--text-main); display: flex; flex-direction: column; }

    .kemnaker-topbar { background: var(--kemnaker-navy-dark); color: #fff; padding: 7px 24px; font-size: 0.78rem; }
    .topbar-inner, .header-inner, .page-main, .footer-inner {
      max-width: 1100px; margin: 0 auto; width: 100%;
    }
    .topbar-inner { display: flex; justify-content: space-between; align-items: center; }
    .topbar-badge { background: rgba(59,130,246,0.25); border: 1px solid rgba(147,197,253,0.3); color: #93c5fd; padding: 2px 8px; border-radius: var(--radius-pill); font-weight: 700; font-size: 0.72rem; }
    .btn-logout { background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 4px 12px; border-radius: var(--radius-sm); font-size: 0.74rem; font-weight: 600; cursor: pointer; }

    .kemnaker-header { background: var(--kemnaker-navy); color: #fff; }
    .header-inner { padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .brand-title { font-size: 0.76rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
    .brand-sub { font-size: 0.84rem; font-weight: 600; color: #93c5fd; }
    .btn-back { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 8px 14px; border-radius: var(--radius-pill); font-size: 0.82rem; font-weight: 700; text-decoration: none; }

    .page-main { flex: 1; padding: 24px 24px 48px; display: flex; flex-direction: column; gap: 18px; }

    .privacy-banner {
      background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
      border-radius: var(--radius-md); padding: 12px 16px; font-size: 0.84rem; display: flex; gap: 10px; align-items: flex-start;
    }
    .privacy-banner.unlocked { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }

    .profile-hero {
      background: #fff; border: 1px solid var(--border-subtle); border-radius: var(--radius-lg);
      padding: 24px; display: flex; gap: 22px; align-items: center; box-shadow: var(--shadow-sm); flex-wrap: wrap;
    }
    .profile-photo-wrap { position: relative; flex-shrink: 0; }
    .profile-photo {
      width: 96px; height: 96px; border-radius: 50%; object-fit: cover;
      border: 3px solid #fff; box-shadow: 0 0 0 2px <?php echo htmlspecialchars($worker['color'], ENT_QUOTES, 'UTF-8'); ?>;
      background: <?php echo htmlspecialchars($worker['color'], ENT_QUOTES, 'UTF-8'); ?>;
    }
    .verified-dot {
      position: absolute; bottom: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%;
      background: var(--success-green); color: #fff; font-size: 11px; font-weight: 800;
      display: flex; align-items: center; justify-content: center; border: 2px solid #fff;
    }
    .profile-id { flex: 1; min-width: 220px; }
    .profile-name { font-size: 1.55rem; font-weight: 800; letter-spacing: -0.02em; }
    .profile-title { color: var(--text-soft); font-size: 0.95rem; margin-top: 2px; }
    .profile-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .chip { background: #f1f5f9; color: #334155; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-pill); }
    .chip.gold { background: #fffbeb; color: #92400e; }
    .stars { color: var(--warning-amber); letter-spacing: 1px; font-size: 1rem; }
    .rating-num { font-weight: 800; }

    .section-card {
      background: #fff; border: 1px solid var(--border-subtle); border-radius: var(--radius-lg);
      padding: 22px 24px; box-shadow: var(--shadow-sm);
    }
    .section-card h2 { font-size: 1.05rem; font-weight: 800; margin-bottom: 14px; }
    .skill-row { display: flex; flex-wrap: wrap; gap: 8px; }
    .skill-tag { background: #eff6ff; color: var(--primary-blue); border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: var(--radius-pill); font-size: 0.8rem; font-weight: 700; }

    .timeline { display: flex; flex-direction: column; gap: 14px; }
    .timeline-item { padding-left: 14px; border-left: 3px solid #bfdbfe; }
    .timeline-item strong { display: block; }
    .timeline-item span { font-size: 0.78rem; color: var(--text-muted); }
    .timeline-item p { font-size: 0.86rem; color: var(--text-soft); margin-top: 4px; }

    .portfolio-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    @media (max-width: 800px) { .portfolio-grid { grid-template-columns: 1fr; } }
    .portfolio-card { border: 1px solid var(--border-subtle); border-radius: var(--radius-md); overflow: hidden; background: #fff; }
    .portfolio-cover {
      height: 92px; display: flex; align-items: flex-end; padding: 12px;
      color: #fff; font-weight: 800; font-size: 0.86rem;
    }
    .portfolio-body { padding: 12px 14px; }
    .portfolio-body h3 { font-size: 0.9rem; font-weight: 800; }
    .portfolio-body p { font-size: 0.78rem; color: var(--text-soft); margin-top: 6px; }
    .muted { font-size: 0.74rem; color: var(--text-muted); }

    .review-list { display: flex; flex-direction: column; gap: 12px; }
    .review-card { background: #f8fafc; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 14px 16px; }
    .review-top { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .review-card p { font-size: 0.86rem; color: var(--text-soft); margin-top: 8px; }

    .contact-box { display: flex; flex-wrap: wrap; gap: 10px; }
    .contact-tag { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 0.84rem; font-weight: 700; }
    .locked-box { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: var(--radius-md); padding: 16px; color: var(--text-muted); font-size: 0.86rem; }

    .kemnaker-footer { background: var(--kemnaker-navy-dark); color: #94a3b8; padding: 20px 24px; margin-top: auto; font-size: 0.8rem; }
  </style>
</head>
<body>
  <div class="kemnaker-topbar">
    <div class="topbar-inner">
      <div><strong>KarirHub</strong> <span class="topbar-badge">Profil Gig Worker</span></div>
      <form method="post" action=""><button type="submit" name="logout" value="1" class="btn-logout">Keluar</button></form>
    </div>
  </div>

  <header class="kemnaker-header">
    <div class="header-inner">
      <div>
        <div class="brand-title">Kementerian Ketenagakerjaan RI</div>
        <div class="brand-sub">Detail akun pelamar</div>
      </div>
      <a class="btn-back" href="dashboard-employer.php">&larr; Kembali ke Dashboard</a>
    </div>
  </header>

  <main class="page-main">
    <?php if ($contactUnlocked): ?>
      <div class="privacy-banner unlocked">
        <span>Kedua belah pihak telah menyetujui kerja sama. Informasi kontak Gig Worker dapat dilihat di bawah.</span>
      </div>
    <?php else: ?>
      <div class="privacy-banner">
        <span>Kontak pelamar disembunyikan. Nomor WhatsApp dan email baru dibuka setelah Pemberi Kerja dan Gig Worker sama-sama menyetujui kerja sama proyek.</span>
      </div>
    <?php endif; ?>

    <section class="profile-hero">
      <div class="profile-photo-wrap">
        <img class="profile-photo" src="<?php echo htmlspecialchars($worker['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto profil <?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?>" />
        <?php if (!empty($worker['verified'])): ?><span class="verified-dot" title="Terverifikasi">✓</span><?php endif; ?>
      </div>
      <div class="profile-id">
        <h1 class="profile-name"><?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="profile-title"><?php echo htmlspecialchars($worker['title'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($worker['location'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="profile-meta">
          <span class="stars"><?php echo gig_stars((float)$worker['rating']); ?></span>
          <span class="rating-num"><?php echo number_format((float)$worker['rating'], 1); ?></span>
          <span class="chip gold"><?php echo (int)$worker['reviews_count']; ?> ulasan pemberi kerja</span>
          <span class="chip"><?php echo (int)$worker['completed_projects']; ?> proyek selesai</span>
          <?php if (!empty($worker['top_rated'])): ?><span class="chip gold">Top Rated</span><?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section-card">
      <h2>Keterampilan</h2>
      <div class="skill-row">
        <?php foreach ($worker['skills'] as $skill): ?>
          <span class="skill-tag"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Pengalaman</h2>
      <div class="timeline">
        <?php foreach ($worker['experience'] as $item): ?>
          <article class="timeline-item">
            <strong><?php echo htmlspecialchars($item['role'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span><?php echo htmlspecialchars($item['project'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($item['period'], ENT_QUOTES, 'UTF-8'); ?></span>
            <p><?php echo htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Portofolio</h2>
      <div class="portfolio-grid">
        <?php foreach ($worker['portfolio'] as $item): ?>
          <article class="portfolio-card">
            <div class="portfolio-cover" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($worker['color'], ENT_QUOTES, 'UTF-8'); ?>, #1e293b);">
              <?php echo htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="portfolio-body">
              <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <div class="muted"><?php echo htmlspecialchars($item['client'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($item['year'], ENT_QUOTES, 'UTF-8'); ?></div>
              <p><?php echo htmlspecialchars($item['deliverable'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Rating &amp; Ulasan Pemberi Kerja</h2>
      <div class="review-list">
        <?php foreach ($worker['reviews'] as $review): ?>
          <article class="review-card">
            <div class="review-top">
              <div>
                <strong><?php echo htmlspecialchars($review['employer'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <div class="muted"><?php echo htmlspecialchars($review['project'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($review['date'], ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div>
                <span class="stars"><?php echo gig_stars((float)$review['rating']); ?></span>
                <strong><?php echo number_format((float)$review['rating'], 1); ?></strong>
              </div>
            </div>
            <p><?php echo htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Informasi Kontak</h2>
      <?php if ($contactUnlocked): ?>
        <div class="contact-box">
          <span class="contact-tag">WhatsApp: <?php echo htmlspecialchars($worker['contact']['wa'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="contact-tag">Email: <?php echo htmlspecialchars($worker['contact']['email'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php else: ?>
        <div class="locked-box">
          Kontak belum dapat dibuka. Setelah Anda dan <?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?> menyetujui kerja sama, nomor WhatsApp dan email akan ditampilkan di sini.
        </div>
      <?php endif; ?>
    </section>
  </main>

  <footer class="kemnaker-footer">
    <div class="footer-inner">Kementerian Ketenagakerjaan RI · KarirHub Gig Workers</div>
  </footer>
</body>
</html>
