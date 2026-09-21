<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

if (!isset($_SESSION["username"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'worker') {
    header("Location: welcome-screen.php");
    exit;
}

$username = (string)$_SESSION["username"];
$siapkerja = gig_get_siapkerja_profile($username);
$isRegistered = gig_is_worker_registered($username);

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "register_gig_worker") {
    $bidangKeahlian = trim((string)($_POST["bidang_keahlian"] ?? ""));
    $skillsRaw = trim((string)($_POST["skills"] ?? ""));
    $contactChoice = trim((string)($_POST["contact_choice"] ?? "siapkerja"));
    $contactEmail = $contactChoice === 'new' ? trim((string)($_POST["contact_email_new"] ?? "")) : $siapkerja['email'];
    $contactWa = $contactChoice === 'new' ? trim((string)($_POST["contact_wa_new"] ?? "")) : $siapkerja['wa'];
    $videoUrl = trim((string)($_POST["video_url"] ?? ""));

    // Process previous projects
    $projects = [];
    if (!empty($_POST["project_title"]) && is_array($_POST["project_title"])) {
        foreach ($_POST["project_title"] as $idx => $title) {
            $t = trim((string)$title);
            if ($t !== "") {
                $projects[] = [
                    'role'    => trim((string)($_POST["project_role"][$idx] ?? $t)),
                    'project' => $t,
                    'period'  => trim((string)($_POST["project_period"][$idx] ?? date('Y'))),
                    'summary' => trim((string)($_POST["project_summary"][$idx] ?? "")),
                ];
            }
        }
    }
    // Include SIAPKerja default experience if selected
    if (!empty($_POST["include_siapkerja_exp"])) {
        foreach ($siapkerja['pengalaman_siapkerja'] as $skExp) {
            array_unshift($projects, [
                'role'    => $skExp['role'],
                'project' => $skExp['institution'],
                'period'  => $skExp['period'],
                'summary' => $skExp['summary'],
            ]);
        }
    }

    // Process portfolios
    $portfolios = [];
    if (!empty($_POST["portfolio_title"]) && is_array($_POST["portfolio_title"])) {
        foreach ($_POST["portfolio_title"] as $idx => $pTitle) {
            $pt = trim((string)$pTitle);
            if ($pt !== "") {
                $portfolios[] = [
                    'id'          => 'port-' . uniqid(),
                    'title'       => $pt,
                    'type'        => trim((string)($_POST["portfolio_type"][$idx] ?? "Proyek Portfolio")),
                    'deliverable' => trim((string)($_POST["portfolio_desc"][$idx] ?? "")),
                    'url'         => trim((string)($_POST["portfolio_url"][$idx] ?? "#")),
                    'client'      => 'Klien Terverifikasi',
                    'year'        => date('Y')
                ];
            }
        }
    }

    if ($bidangKeahlian === "") {
        $errorMessage = "Harap pilih Bidang Keahlian Anda.";
    } elseif ($skillsRaw === "") {
        $errorMessage = "Harap cantumkan minimal 1 Skill / Keahlian spesifik.";
    } else {
        $skillsList = array_map('trim', explode(',', $skillsRaw));
        
        $registrationData = [
            'username'          => $username,
            'bidang_keahlian'   => $bidangKeahlian,
            'skills'            => $skillsList,
            'contact_choice'    => $contactChoice,
            'contact_email'     => $contactEmail,
            'contact_wa'        => $contactWa,
            'previous_projects' => $projects,
            'portfolio'         => $portfolios,
            'video_url'         => $videoUrl,
            'registered_at'     => date('Y-m-d H:i:s')
        ];

        gig_save_worker_registration($username, $registrationData);
        $isRegistered = true;
        $successMessage = "Selamat! Akun Gig Worker Anda berhasil didaftarkan dan diverifikasi dengan akun SIAPKerja.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pendaftaran Gig Worker &bull; Kemnaker RI</title>

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <style>
    :root {
      --kemnaker-navy-dark: #061d33;
      --kemnaker-navy: #092c4c;
      --kemnaker-navy-light: #0f3d68;
      --primary-blue: #1657c1;
      --primary-blue-hover: #1247a3;
      --hero-blue-start: #0f3d68;
      --hero-blue-mid: #1657c1;
      --hero-blue-end: #2563eb;
      --bg-page: #f8fafc;
      --bg-surface: #ffffff;
      --border-subtle: #e2e8f0;
      --border-light: #cbd5e1;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --success-green: #10b981;
      --success-bg: #ecfdf5;
      --amber-badge: #d97706;
      --amber-bg: #fffbeb;
      --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06);
      --shadow-md: 0 4px 14px -1px rgba(15, 23, 42, 0.08);
      --shadow-lg: 0 12px 28px -4px rgba(9, 44, 76, 0.12);
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-pill: 9999px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    body {
      min-height: 100vh;
      background-color: var(--bg-page);
      color: var(--text-main);
      display: flex;
      flex-direction: column;
      line-height: 1.5;
    }

    /* TOPBAR */
    .kemnaker-topbar {
      background: var(--kemnaker-navy-dark);
      color: #ffffff;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 7px 24px;
      font-size: 0.78rem;
    }

    .topbar-inner {
      max-width: 1120px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
    }

    .topbar-nav {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .topbar-nav a {
      color: #cbd5e1;
      text-decoration: none;
    }

    /* HEADER */
    .kemnaker-header {
      background: var(--kemnaker-navy);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .header-inner {
      max-width: 1120px;
      margin: 0 auto;
      padding: 14px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
    }

    .brand-section {
      display: flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
      color: #ffffff;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
    }

    .brand-title {
      font-size: 0.76rem;
      font-weight: 800;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #ffffff;
    }

    .brand-sub {
      font-size: 0.84rem;
      font-weight: 500;
      color: #93c5fd;
    }

    .btn-back-dash {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #ffffff;
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 6px 14px;
      border-radius: var(--radius-pill);
      font-size: 0.82rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }

    .btn-back-dash:hover {
      background: rgba(255, 255, 255, 0.22);
    }

    /* MAIN CONTAINER */
    .page-main {
      flex: 1;
      max-width: 1120px;
      width: 100%;
      margin: 0 auto;
      padding: 28px 24px 60px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    /* BANNER */
    .hero-banner {
      background: linear-gradient(135deg, var(--hero-blue-start) 0%, var(--hero-blue-mid) 50%, var(--hero-blue-end) 100%);
      border-radius: var(--radius-lg);
      padding: 32px 36px;
      color: #ffffff;
      box-shadow: var(--shadow-lg);
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      padding: 4px 14px;
      border-radius: var(--radius-pill);
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 12px;
    }

    .hero-title {
      font-size: clamp(1.5rem, 3vw, 2rem);
      font-weight: 800;
      margin-bottom: 8px;
    }

    .hero-desc {
      font-size: 0.95rem;
      color: rgba(255, 255, 255, 0.9);
      max-width: 780px;
    }

    /* SIAPKERJA ACCOUNT CARD */
    .siapkerja-card {
      background: #ffffff;
      border: 1.5px solid #bfdbfe;
      border-radius: var(--radius-lg);
      padding: 24px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: 16px;
      position: relative;
    }

    .siapkerja-badge {
      position: absolute;
      top: 20px;
      right: 24px;
      background: #eff6ff;
      color: var(--primary-blue);
      border: 1px solid #bfdbfe;
      padding: 4px 12px;
      border-radius: var(--radius-pill);
      font-size: 0.75rem;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .siapkerja-header {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .siapkerja-avatar {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      font-weight: 800;
      box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
    }

    .siapkerja-info h3 {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--text-main);
    }

    .siapkerja-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      font-size: 0.84rem;
      color: var(--text-muted);
      margin-top: 4px;
    }

    .siapkerja-meta span {
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .siapkerja-exp-box {
      background: #f8fafc;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 14px 16px;
    }

    .exp-title {
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--text-muted);
      margin-bottom: 8px;
    }

    .exp-item {
      font-size: 0.86rem;
      color: var(--text-main);
      padding: 4px 0;
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }

    /* FORM STYLES */
    .form-card {
      background: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 32px;
      box-shadow: var(--shadow-md);
      display: flex;
      flex-direction: column;
      gap: 28px;
    }

    .section-title {
      font-size: 1.08rem;
      font-weight: 800;
      color: var(--kemnaker-navy);
      padding-bottom: 10px;
      border-bottom: 2px solid #f1f5f9;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-icon {
      width: 28px;
      height: 28px;
      border-radius: 6px;
      background: #eff6ff;
      color: var(--primary-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-label {
      font-size: 0.88rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .form-hint {
      font-size: 0.78rem;
      color: var(--text-muted);
    }

    .form-input, .form-select, .form-textarea {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-sm);
      font-size: 0.9rem;
      color: var(--text-main);
      background-color: #ffffff;
      transition: all 0.2s;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
      outline: none;
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(22, 87, 193, 0.12);
    }

    /* CONTACT CHOICE OPTIONS */
    .contact-options {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-top: 4px;
    }

    @media (max-width: 640px) {
      .contact-options {
        grid-template-columns: 1fr;
      }
    }

    .contact-option-card {
      border: 1.5px solid var(--border-light);
      border-radius: var(--radius-md);
      padding: 16px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: flex-start;
      gap: 12px;
      background: #f8fafc;
    }

    .contact-option-card:hover {
      border-color: #93c5fd;
      background: #ffffff;
    }

    .contact-option-card.active {
      border-color: var(--primary-blue);
      background: #eff6ff;
    }

    .contact-radio {
      margin-top: 3px;
      accent-color: var(--primary-blue);
    }

    .new-contact-fields {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-top: 14px;
      padding: 16px;
      background: #f8fafc;
      border: 1px dashed #bfdbfe;
      border-radius: var(--radius-md);
    }

    @media (max-width: 640px) {
      .new-contact-fields {
        grid-template-columns: 1fr;
      }
    }

    /* DYNAMIC PORTFOLIO / EXPERIENCE CARDS */
    .dynamic-item {
      background: #f8fafc;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 18px;
      position: relative;
      margin-bottom: 12px;
    }

    .btn-remove-item {
      position: absolute;
      top: 14px;
      right: 14px;
      background: #fee2e2;
      color: #dc2626;
      border: none;
      padding: 4px 10px;
      border-radius: var(--radius-sm);
      font-size: 0.75rem;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-add-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #eff6ff;
      color: var(--primary-blue);
      border: 1px dashed #bfdbfe;
      padding: 10px 18px;
      border-radius: var(--radius-sm);
      font-size: 0.85rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-add-item:hover {
      background: #dbeafe;
      border-color: var(--primary-blue);
    }

    /* SUBMIT BUTTON */
    .btn-submit {
      background: linear-gradient(135deg, var(--hero-blue-mid) 0%, var(--hero-blue-end) 100%);
      color: #ffffff;
      border: none;
      padding: 14px 28px;
      border-radius: var(--radius-pill);
      font-size: 1rem;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-submit:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
    }

    /* MESSAGES */
    .alert {
      padding: 14px 18px;
      border-radius: var(--radius-md);
      font-size: 0.9rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-success {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }

    .alert-error {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fca5a5;
    }

    /* FOOTER */
    .kemnaker-footer {
      background: var(--kemnaker-navy-dark);
      color: #94a3b8;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 24px;
      margin-top: auto;
      font-size: 0.82rem;
    }

    .footer-inner {
      max-width: 1120px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  </style>
</head>
<body>

  <!-- TOPBAR -->
  <div class="kemnaker-topbar">
    <div class="topbar-inner">
      <div class="topbar-nav">
        <strong>Pendaftaran Gig Worker Kemnaker</strong>
        <a href="dashboard-worker.php">Dashboard</a>
        <a href="#">SIAPKerja Integration</a>
      </div>
      <div>
        <span>Pengguna: <strong><?php echo htmlspecialchars($siapkerja['nama'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
      </div>
    </div>
  </div>

  <!-- HEADER -->
  <header class="kemnaker-header">
    <div class="header-inner">
      <a href="dashboard-worker.php" class="brand-section">
        <svg width="36" height="36" viewBox="0 0 100 100" fill="none">
          <circle cx="50" cy="50" r="46" fill="#092c4c" stroke="#3b82f6" stroke-width="2.5"/>
          <g stroke="#ffffff" stroke-width="3" stroke-linecap="round" fill="none">
            <path d="M50 15 L50 85"/>
            <path d="M15 50 L85 50"/>
          </g>
          <circle cx="50" cy="50" r="20" fill="#1657c1" stroke="#ffffff" stroke-width="2"/>
          <circle cx="50" cy="50" r="8" fill="#38bdf8"/>
        </svg>
        <div class="brand-text">
          <span class="brand-title">KEMENTERIAN KETENAGAKERJAAN RI</span>
          <span class="brand-sub">Formulir Pendaftaran &bull; Ekosistem Gig Worker</span>
        </div>
      </a>
      <a href="dashboard-worker.php" class="btn-back-dash">← Kembali ke Dashboard</a>
    </div>
  </header>

  <!-- MAIN -->
  <main class="page-main">

    <!-- HERO BANNER -->
    <section class="hero-banner">
      <div class="hero-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Pendaftaran Resmi Gig Worker
      </div>
      <h1 class="hero-title">Bergabung Sebagai Gig Worker Kemnaker</h1>
      <p class="hero-desc">
        Gunakan akun SIAPKerja Anda untuk melengkapi profil profesional Gig Worker. Dapatkan akses ke berbagai penawaran proyek dari Pemberi Kerja terverifikasi dan perlindungan ekosistem tenaga kerja mandiri.
      </p>
    </section>

    <?php if ($successMessage !== ""): ?>
      <div class="alert alert-success">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <div>
          <div><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
          <div style="margin-top: 6px;">
            <a href="dashboard-worker.php" style="color:#15803d; font-weight:800; text-decoration:underline;">Lihat Dashboard Gig Worker Anda &rarr;</a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ""): ?>
      <div class="alert alert-error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    <?php endif; ?>

    <!-- SIAPKERJA ACCOUNT PROFILE CARD -->
    <section class="siapkerja-card">
      <div class="siapkerja-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        Terintegrasi SIAPKerja
      </div>
      
      <div class="siapkerja-header">
        <div class="siapkerja-avatar">
          <?php echo strtoupper(substr($siapkerja['nama'], 0, 2)); ?>
        </div>
        <div class="siapkerja-info">
          <h3><?php echo htmlspecialchars($siapkerja['nama'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <div class="siapkerja-meta">
            <span><strong>ID SIAPKerja:</strong> <?php echo htmlspecialchars($siapkerja['siapkerja_id'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span>&bull;</span>
            <span><strong>Status Akun:</strong> <?php echo htmlspecialchars($siapkerja['status_akun'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span>&bull;</span>
            <span><strong>Domisili:</strong> <?php echo htmlspecialchars($siapkerja['lokasi'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>
      </div>

      <div class="siapkerja-exp-box">
        <div class="exp-title">Informasi Otomatis Terhubung Dari Akun SIAPKerja</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
          <div style="font-size: 0.85rem;">
            <strong style="color: var(--text-muted);">Nama Lengkap:</strong><br />
            <span><?php echo htmlspecialchars($siapkerja['nama'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div style="font-size: 0.85rem;">
            <strong style="color: var(--text-muted);">Kontak Default SIAPKerja:</strong><br />
            <span>WA: <?php echo htmlspecialchars($siapkerja['wa'], ENT_QUOTES, 'UTF-8'); ?> &bull; Email: <?php echo htmlspecialchars($siapkerja['email'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>
        <div class="exp-title" style="margin-top: 12px;">Pengalaman Kerja / Proyek Tercatat di SIAPKerja:</div>
        <?php foreach ($siapkerja['pengalaman_siapkerja'] as $exp): ?>
          <div class="exp-item">
            <span style="color: var(--primary-blue); font-weight:700;">&bull;</span>
            <div>
              <strong><?php echo htmlspecialchars($exp['role'], ENT_QUOTES, 'UTF-8'); ?></strong> &mdash; 
              <span><?php echo htmlspecialchars($exp['institution'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($exp['period'], ENT_QUOTES, 'UTF-8'); ?>)</span>
              <div style="font-size:0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($exp['summary'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- FORM PENDAFTARAN GIG WORKER -->
    <form method="POST" action="" class="form-card">
      <input type="hidden" name="action" value="register_gig_worker" />

      <!-- BAGIAN 1: PILIHAN KONTAK -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">1</span>
          Informasi Kontak Gig Worker
        </h2>
        <p class="form-hint" style="margin-top: 6px; margin-bottom: 12px;">
          Pilih apakah Anda ingin menggunakan informasi kontak resmi dari SIAPKerja atau mencantumkan kontak baru khusus layanan Gig Worker.
        </p>

        <div class="contact-options">
          <label class="contact-option-card active" id="opt-siapkerja" onclick="selectContactOption('siapkerja')">
            <input type="radio" name="contact_choice" value="siapkerja" class="contact-radio" checked />
            <div>
              <strong style="font-size:0.9rem; color:var(--text-main);">Gunakan Kontak Akun SIAPKerja</strong>
              <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">
                Email: <?php echo htmlspecialchars($siapkerja['email'], ENT_QUOTES, 'UTF-8'); ?><br />
                WhatsApp: <?php echo htmlspecialchars($siapkerja['wa'], ENT_QUOTES, 'UTF-8'); ?>
              </div>
            </div>
          </label>

          <label class="contact-option-card" id="opt-new" onclick="selectContactOption('new')">
            <input type="radio" name="contact_choice" value="new" class="contact-radio" />
            <div>
              <strong style="font-size:0.9rem; color:var(--text-main);">Input Informasi Kontak Baru</strong>
              <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">
                Gunakan alamat email atau nomor WA alternatif khusus untuk proyek Gig Worker.
              </div>
            </div>
          </label>
        </div>

        <div class="new-contact-fields" id="newContactContainer" style="display: none;">
          <div class="form-group">
            <label class="form-label" for="contact_email_new">Email Kontak Baru</label>
            <input type="email" id="contact_email_new" name="contact_email_new" class="form-input" placeholder="contoh: tessa.gig@email.com" />
          </div>

          <div class="form-group">
            <label class="form-label" for="contact_wa_new">Nomor WhatsApp / HP Baru</label>
            <input type="text" id="contact_wa_new" name="contact_wa_new" class="form-input" placeholder="contoh: 0812-9988-7766" />
          </div>
        </div>
      </section>

      <!-- BAGIAN 2: BIDANG KEAHLIAN & SKILL -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">2</span>
          Bidang Keahlian &amp; Skill Spesifik
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 14px;">
          <div class="form-group">
            <label class="form-label" for="bidang_keahlian">Bidang Keahlian Utama <span style="color:#ef4444;">*</span></label>
            <select id="bidang_keahlian" name="bidang_keahlian" class="form-select" required>
              <option value="">-- Pilih Bidang Keahlian --</option>
              <option value="UI/UX Design & Product Interface">UI/UX Design &amp; Product Interface</option>
              <option value="Web Development (Frontend / Backend / Fullstack)">Web Development (Frontend / Backend / Fullstack)</option>
              <option value="Mobile Application Development">Mobile Application Development</option>
              <option value="Digital Marketing & Social Media Strategy">Digital Marketing &amp; Social Media Strategy</option>
              <option value="Data Analytics & Data Entry">Data Analytics &amp; Data Entry</option>
              <option value="Copywriting, Content Writing & Translation">Copywriting, Content Writing &amp; Translation</option>
              <option value="Graphic Design, Video Editing & Multimedia">Graphic Design, Video Editing &amp; Multimedia</option>
              <option value="Administrative & Virtual Assistant">Administrative &amp; Virtual Assistant</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="skills">Skill / Keahlian Spesifik <span style="color:#ef4444;">*</span></label>
            <input type="text" id="skills" name="skills" class="form-input" placeholder="Contoh: Figma, Wireframing, React, Node.js, Copywriting" required />
            <span class="form-hint">Pisahkan skill dengan tanda koma ( , )</span>
          </div>
        </div>
      </section>

      <!-- BAGIAN 3: PROJECT SEBELUMNYA -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">3</span>
          Project &amp; Pengalaman Kerja Sebelumnya
        </h2>
        <p class="form-hint" style="margin-top: 6px; margin-bottom: 12px;">
          Anda dapat menyertakan pengalaman SIAPKerja dan menambahkan proyek-proyek independen lainnya.
        </p>

        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 14px;">
          <input type="checkbox" name="include_siapkerja_exp" value="1" checked accent-color="var(--primary-blue)" />
          Sertakan 2 riwayat pengalaman dari akun SIAPKerja ke profil Gig Worker
        </label>

        <div id="projectContainer">
          <!-- Additional dynamic project items will be added here -->
        </div>

        <button type="button" class="btn-add-item" onclick="addProjectItem()">
          + Tambah Proyek Lainnya
        </button>
      </section>

      <!-- BAGIAN 4: PORTOFOLIO -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">4</span>
          Portofolio Hasil Pekerjaan
        </h2>
        <p class="form-hint" style="margin-top: 6px; margin-bottom: 12px;">
          Tampilkan contoh hasil proyek terbaik Anda (link berkas, desain Figma, atau repositori code) agar calon Pemberi Kerja dapat menilai kualitas kerja Anda.
        </p>

        <div id="portfolioContainer">
          <div class="dynamic-item">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
              <div class="form-group">
                <label class="form-label">Judul Portofolio 1</label>
                <input type="text" name="portfolio_title[]" class="form-input" placeholder="contoh: Redesign Mobile App E-Commerce" required />
              </div>
              <div class="form-group">
                <label class="form-label">Tipe / Kategori Deliverable</label>
                <input type="text" name="portfolio_type[]" class="form-input" placeholder="contoh: Figma UI Kit / Web Prototype" required />
              </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div class="form-group">
                <label class="form-label">Link Berkas / Deliverable (URL)</label>
                <input type="url" name="portfolio_url[]" class="form-input" placeholder="https://figma.com/@project atau https://github.com/..." />
              </div>
              <div class="form-group">
                <label class="form-label">Deskripsi Singkat Portofolio</label>
                <input type="text" name="portfolio_desc[]" class="form-input" placeholder="Ringkasan deliverable dan peran Anda" />
              </div>
            </div>
          </div>
        </div>

        <button type="button" class="btn-add-item" onclick="addPortfolioItem()">
          + Tambah Portofolio
        </button>
      </section>

      <!-- BAGIAN 5: LINK VIDEO PROFIL -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">5</span>
          Link Video Profil Gig Worker
        </h2>
        <p class="form-hint" style="margin-top: 6px; margin-bottom: 12px;">
          Sampaikan perkenalan singkat diri dan keahlian Anda melalui video (misal: YouTube, Loom, atau Google Drive Video).
        </p>

        <div class="form-group">
          <label class="form-label" for="video_url">Tautan / URL Video Profil</label>
          <input type="url" id="video_url" name="video_url" class="form-input" placeholder="https://www.youtube.com/watch?v=... atau https://www.loom.com/share/..." oninput="checkVideoPreview(this.value)" />
          <div id="videoPreviewStatus" style="font-size: 0.8rem; margin-top: 4px; display: none;"></div>
        </div>
      </section>

      <!-- SUBMIT -->
      <div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 10px;">
        <a href="dashboard-worker.php" style="padding: 14px 24px; text-decoration: none; color: var(--text-muted); font-weight: 700; font-size: 0.9rem;">Batal</a>
        <button type="submit" class="btn-submit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Daftar &amp; Aktifkan Profil Gig Worker
        </button>
      </div>
    </form>
  </main>

  <!-- FOOTER -->
  <footer class="kemnaker-footer">
    <div class="footer-inner">
      <div>&copy; <?php echo date("Y"); ?> Kementerian Ketenagakerjaan Republik Indonesia</div>
      <div>Ekosistem Digital Gig Worker SIAPKerja</div>
    </div>
  </footer>

  <script>
    function selectContactOption(choice) {
      const optSiapkerja = document.getElementById('opt-siapkerja');
      const optNew = document.getElementById('opt-new');
      const container = document.getElementById('newContactContainer');

      if (choice === 'new') {
        optSiapkerja.classList.remove('active');
        optNew.classList.add('active');
        container.style.display = 'grid';
      } else {
        optNew.classList.remove('active');
        optSiapkerja.classList.add('active');
        container.style.display = 'none';
      }
    }

    let projectCount = 0;
    function addProjectItem() {
      projectCount++;
      const container = document.getElementById('projectContainer');
      const div = document.createElement('div');
      div.className = 'dynamic-item';
      div.id = 'proj-item-' + projectCount;
      div.innerHTML = `
        <button type="button" class="btn-remove-item" onclick="document.getElementById('proj-item-${projectCount}').remove()">Hapus</button>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
          <div class="form-group">
            <label class="form-label">Nama Proyek / Perusahaan</label>
            <input type="text" name="project_title[]" class="form-input" placeholder="contoh: Portal E-Government" required />
          </div>
          <div class="form-group">
            <label class="form-label">Peran / Posisi Anda</label>
            <input type="text" name="project_role[]" class="form-input" placeholder="contoh: UI Designer / Web Dev" />
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
          <div class="form-group">
            <label class="form-label">Periode Waktu</label>
            <input type="text" name="project_period[]" class="form-input" placeholder="contoh: 2025 (6 Bulan)" />
          </div>
          <div class="form-group">
            <label class="form-label">Ringkasan Tugas & Hasil</label>
            <input type="text" name="project_summary[]" class="form-input" placeholder="Deskripsikan peran dan pencapaian Anda" />
          </div>
        </div>
      `;
      container.appendChild(div);
    }

    let portfolioCount = 1;
    function addPortfolioItem() {
      portfolioCount++;
      const container = document.getElementById('portfolioContainer');
      const div = document.createElement('div');
      div.className = 'dynamic-item';
      div.id = 'port-item-' + portfolioCount;
      div.innerHTML = `
        <button type="button" class="btn-remove-item" onclick="document.getElementById('port-item-${portfolioCount}').remove()">Hapus</button>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
          <div class="form-group">
            <label class="form-label">Judul Portofolio ${portfolioCount}</label>
            <input type="text" name="portfolio_title[]" class="form-input" placeholder="contoh: Web App Dashboard" required />
          </div>
          <div class="form-group">
            <label class="form-label">Tipe / Kategori Deliverable</label>
            <input type="text" name="portfolio_type[]" class="form-input" placeholder="contoh: React Code / Figma Spec" />
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div class="form-group">
            <label class="form-label">Link Berkas / Deliverable (URL)</label>
            <input type="url" name="portfolio_url[]" class="form-input" placeholder="https://..." />
          </div>
          <div class="form-group">
            <label class="form-label">Deskripsi Singkat Portofolio</label>
            <input type="text" name="portfolio_desc[]" class="form-input" placeholder="Ringkasan deliverable dan fitur utama" />
          </div>
        </div>
      `;
      container.appendChild(div);
    }

    function checkVideoPreview(url) {
      const statusDiv = document.getElementById('videoPreviewStatus');
      if (!url.trim()) {
        statusDiv.style.display = 'none';
        return;
      }
      statusDiv.style.display = 'block';
      if (url.includes('youtube.com') || url.includes('youtu.be') || url.includes('loom.com') || url.includes('drive.google.com')) {
        statusDiv.style.color = '#10b981';
        statusDiv.innerHTML = '✓ Format video terdeteksi. Video profil siap ditampilkan di profil Gig Worker Anda.';
      } else {
        statusDiv.style.color = '#d97706';
        statusDiv.innerHTML = 'ℹ Tautan video terdaftar. Pastikan akses video diset ke Publik/Unlisted.';
      }
    }
  </script>
</body>
</html>
