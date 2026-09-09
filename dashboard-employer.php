<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'employer') {
    // If not logged in as employer, redirect back to login
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

$username = (string)$_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Dashboard Pemberi Kerja - Kementerian Ketenagakerjaan RI" />
  <title>Dashboard Employer | Gig Worker Prototype - Kemnaker RI</title>

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
      --hero-blue-start: #104899;
      --hero-blue-mid: #1b62d4;
      --hero-blue-end: #2d79f8;
      --bg-page: #f1f5f9;
      --bg-surface: #ffffff;
      --border-subtle: #e2e8f0;
      --border-light: #cbd5e1;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --text-soft: #475569;
      --alert-bg: #e0f2fe;
      --alert-border: #bae6fd;
      --alert-text: #0284c7;
      --badge-bg: rgba(6, 29, 51, 0.45);
      --success-green: #10b981;
      --success-bg: #ecfdf5;
      --cyan-badge: #0891b2;
      --cyan-bg: #ecfeff;
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
      -webkit-font-smoothing: antialiased;
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
      max-width: 1280px;
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
      flex-wrap: wrap;
    }

    .topbar-nav a {
      color: #cbd5e1;
      text-decoration: none;
      transition: color 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .topbar-nav a:hover {
      color: #ffffff;
    }

    .topbar-user {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-logout {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #ffffff;
      padding: 4px 10px;
      border-radius: var(--radius-sm);
      font-size: 0.74rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s ease;
    }

    .btn-logout:hover {
      background: rgba(239, 68, 68, 0.2);
      border-color: #ef4444;
      color: #fca5a5;
    }

    /* MAIN HEADER */
    .kemnaker-header {
      background: var(--kemnaker-navy);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .header-inner {
      max-width: 1280px;
      margin: 0 auto;
      padding: 12px 24px;
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

    .kemnaker-emblem {
      width: 44px;
      height: 44px;
      flex-shrink: 0;
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
      line-height: 1.2;
    }

    .brand-sub {
      font-size: 0.84rem;
      font-weight: 500;
      color: #93c5fd;
      line-height: 1.3;
    }

    .header-tabs {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .header-tab {
      color: #cbd5e1;
      text-decoration: none;
      font-size: 0.86rem;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: var(--radius-sm);
      transition: all 0.2s;
    }

    .header-tab:hover {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.08);
    }

    .header-tab.active {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.16);
    }

    .user-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 4px 12px;
      border-radius: var(--radius-pill);
      font-size: 0.8rem;
      color: #ffffff;
    }

    .user-avatar {
      width: 24px;
      height: 24px;
      background: #3b82f6;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.72rem;
      font-weight: 800;
    }

    /* MAIN CONTAINER */
    .page-main {
      flex: 1;
      max-width: 1280px;
      width: 100%;
      margin: 0 auto;
      padding: 28px 24px 48px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    /* HERO BANNER */
    .hero-banner {
      background: linear-gradient(135deg, var(--hero-blue-start) 0%, var(--hero-blue-mid) 50%, var(--hero-blue-end) 100%);
      border-radius: var(--radius-lg);
      padding: 32px 36px;
      color: #ffffff;
      box-shadow: var(--shadow-lg), 0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      position: relative;
      overflow: hidden;
    }

    .hero-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      position: relative;
      z-index: 1;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--badge-bg);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(8px);
      padding: 4px 14px;
      border-radius: var(--radius-pill);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #ffffff;
      margin-bottom: 12px;
    }

    .hero-title {
      font-size: clamp(1.6rem, 3vw, 2.15rem);
      font-weight: 800;
      letter-spacing: -0.02em;
      color: #ffffff;
      margin-bottom: 8px;
    }

    .hero-desc {
      font-size: 0.98rem;
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.6;
      max-width: 700px;
    }

    .hero-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-hero-glass {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #ffffff;
      padding: 10px 18px;
      border-radius: var(--radius-pill);
      font-size: 0.88rem;
      font-weight: 600;
      text-decoration: none;
      backdrop-filter: blur(8px);
    }

    .btn-hero-solid {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #ffffff;
      color: var(--primary-blue);
      padding: 10px 20px;
      border-radius: var(--radius-pill);
      font-size: 0.88rem;
      font-weight: 700;
      text-decoration: none;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
    }

    /* NOTICE */
    .notice-bar {
      background: var(--alert-bg);
      border: 1px solid var(--alert-border);
      border-radius: var(--radius-md);
      padding: 12px 18px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--alert-text);
      font-size: 0.88rem;
      font-weight: 500;
    }

    /* STATS GRID */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    @media (max-width: 900px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 540px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }

    .stat-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 18px 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 110px;
      box-shadow: var(--shadow-sm);
    }

    .stat-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 4px;
    }

    .stat-label {
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--text-muted);
    }

    .stat-icon-wrapper {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .stat-icon-wrapper.blue { background: #eff6ff; color: #2563eb; }
    .stat-icon-wrapper.cyan { background: var(--cyan-bg); color: var(--cyan-badge); }
    .stat-icon-wrapper.green { background: var(--success-bg); color: var(--success-green); }
    .stat-icon-wrapper.amber { background: var(--amber-bg); color: var(--amber-badge); }

    .stat-number {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--text-main);
      line-height: 1.2;
    }

    .stat-caption {
      font-size: 0.78rem;
      color: var(--text-muted);
    }

    /* MIDDLE SECTION (2 COLUMNS: DISTRIBUTION + QUICK ACCESS) */
    .middle-grid {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      gap: 20px;
    }

    @media (max-width: 860px) {
      .middle-grid {
        grid-template-columns: 1fr;
      }
    }

    .white-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 24px;
      box-shadow: var(--shadow-sm);
    }

    .card-title {
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--text-main);
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
    }

    /* Distribution bar styles */
    .dist-item {
      margin-bottom: 16px;
    }

    .dist-header {
      display: flex;
      justify-content: space-between;
      font-size: 0.84rem;
      font-weight: 600;
      margin-bottom: 6px;
    }

    .progress-bar-bg {
      height: 8px;
      background: #f1f5f9;
      border-radius: var(--radius-pill);
      overflow: hidden;
    }

    .progress-bar-fill {
      height: 100%;
      border-radius: var(--radius-pill);
    }

    .progress-bar-fill.blue { width: 75%; background: #2563eb; }
    .progress-bar-fill.green { width: 21%; background: #10b981; }
    .progress-bar-fill.amber { width: 4%; background: #f59e0b; }

    /* Quick access 4 tiles */
    .quick-access-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .quick-access-tile {
      background: #f8fafc;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 14px 16px;
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text-main);
      transition: all 0.2s ease;
    }

    .quick-access-tile:hover {
      background: #ffffff;
      border-color: #93c5fd;
      transform: translateY(-1px);
    }

    .tile-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: #eff6ff;
      color: var(--primary-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    /* RECENT ACTIVITIES TABLE */
    .activity-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .activity-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border-subtle);
      background: #ffffff;
      transition: background 0.15s ease;
    }

    .activity-row:hover {
      background: #f8fafc;
    }

    .activity-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .activity-icon {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: #eff6ff;
      color: #2563eb;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .activity-title {
      font-size: 0.88rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .activity-date {
      font-size: 0.74rem;
      color: var(--text-muted);
    }

    .activity-right {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .activity-code {
      font-size: 0.75rem;
      font-family: monospace;
      color: var(--text-muted);
    }

    .status-badge {
      font-size: 0.72rem;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: var(--radius-pill);
    }

    .status-badge.green { background: #dcfce7; color: #15803d; }
    .status-badge.blue { background: #dbeafe; color: #1e40af; }
    .status-badge.amber { background: #fef3c7; color: #92400e; }

    /* FOOTER */
    .kemnaker-footer {
      background: var(--kemnaker-navy-dark);
      color: #94a3b8;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 24px 24px;
      margin-top: auto;
      font-size: 0.82rem;
    }

    .footer-inner {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }
  </style>
</head>
<body>

  <!-- TOPBAR -->
  <div class="kemnaker-topbar">
    <div class="topbar-inner">
      <div class="topbar-nav">
        <strong>Employer Admin</strong>
        <a href="#">Dashboard ▾</a>
        <a href="#">WLLP ▾</a>
        <a href="#">Manajemen Mitra ▾</a>
        <a href="#">API Key ▾</a>
        <a href="#">Settings ▾</a>
      </div>
      <div class="topbar-user">
        <form method="post" action="" style="display:inline;">
          <button type="submit" name="logout" value="1" class="btn-logout" title="Keluar dari sesi">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar (Logout)
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- MAIN BRAND HEADER -->
  <header class="kemnaker-header">
    <div class="header-inner">
      <div class="brand-section">
        <!-- Kemnaker Emblem SVG -->
        <div class="kemnaker-emblem">
          <svg width="42" height="42" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="46" fill="#092c4c" stroke="#3b82f6" stroke-width="2.5"/>
            <g stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none">
              <path d="M50 15 L50 85" stroke="#60a5fa" stroke-width="2.5"/>
              <path d="M15 50 L85 50" stroke="#60a5fa" stroke-width="2.5"/>
              <path d="M25 25 L75 75" stroke="#93c5fd" stroke-width="2"/>
              <path d="M25 75 L75 25" stroke="#93c5fd" stroke-width="2"/>
            </g>
            <circle cx="50" cy="50" r="22" stroke="#ffffff" stroke-width="3.5" fill="#134e9e"/>
            <circle cx="50" cy="50" r="12" fill="#38bdf8"/>
            <circle cx="50" cy="50" r="5" fill="#ffffff"/>
            <circle cx="50" cy="20" r="3" fill="#ffffff"/>
            <circle cx="71" cy="28" r="3" fill="#ffffff"/>
            <circle cx="80" cy="50" r="3" fill="#ffffff"/>
            <circle cx="71" cy="72" r="3" fill="#ffffff"/>
            <circle cx="50" cy="80" r="3" fill="#ffffff"/>
            <circle cx="29" cy="72" r="3" fill="#ffffff"/>
            <circle cx="20" cy="50" r="3" fill="#ffffff"/>
            <circle cx="29" cy="28" r="3" fill="#ffffff"/>
          </svg>
        </div>
        <div class="brand-text">
          <span class="brand-title">KEMENTERIAN KETENAGAKERJAAN</span>
          <span class="brand-title" style="color: #93c5fd;">REPUBLIK INDONESIA</span>
          <span class="brand-sub">Sistem Informasi Pemberi Kerja Gig Worker</span>
        </div>
      </div>

      <nav class="header-tabs">
        <a href="#" class="header-tab active">Daftar Lowongan</a>
        <a href="#" class="header-tab">Profil Perusahaan ▾</a>
        <a href="#" class="header-tab">Kandidat ▾</a>
        <a href="#" class="header-tab">WLLP ▾</a>
      </nav>

      <div class="user-pill">
        <span class="user-avatar"><?php echo strtoupper(substr($username, 0, 2)); ?></span>
        <span>PT. <?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?></span>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="page-main">

    <!-- HERO BANNER -->
    <section class="hero-banner">
      <div class="hero-header">
        <div>
          <div class="hero-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            WAJIB LAPOR KETENAGAKERJAAN &bull; EMPLOYER
          </div>
          <h1 class="hero-title">Ringkasan WLLP & Lowongan</h1>
          <p class="hero-desc">
            Pantau pelaporan WLLP, manajemen lowongan aktif, dan status rekrutmen mitra gig dalam satu dashboard terpadu perusahaan Anda.
          </p>
        </div>

        <div class="hero-actions">
          <span class="btn-hero-glass">Laporan Tahunan</span>
          <span class="btn-hero-solid">+ Buat Lowongan Baru</span>
        </div>
      </div>
    </section>

    <!-- NOTICE BAR -->
    <div class="notice-bar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Halaman ini merupakan prototipe alur Pemberi Kerja dan saat ini aktif dalam sesi login perusahaan: <strong><?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?></strong></span>
    </div>

    <!-- 4 STATS CARDS -->
    <section class="stats-grid">
      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">LOWONGAN DILAPORKAN</span>
          <div class="stat-icon-wrapper blue">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
        </div>
        <div class="stat-number">14</div>
        <div class="stat-caption">Total lowongan dalam WLLP</div>
      </article>

      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">LOWONGAN AKTIF</span>
          <div class="stat-icon-wrapper cyan">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
        </div>
        <div class="stat-number">2</div>
        <div class="stat-caption">Masih dalam masa berlaku pencarian</div>
      </article>

      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">SUDAH TERISI</span>
          <div class="stat-icon-wrapper green">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
          </div>
        </div>
        <div class="stat-number">12</div>
        <div class="stat-caption">Kebutuhan mitra telah terpenuhi</div>
      </article>

      <article class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">KANDIDAT BARU</span>
          <div class="stat-icon-wrapper amber">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
        </div>
        <div class="stat-number">5</div>
        <div class="stat-caption">Perlu direview segera</div>
      </article>
    </section>

    <!-- MIDDLE ROW: DISTRIBUSI STATUS & AKSES CEPAT -->
    <div class="middle-grid">
      <!-- LEFT: DISTRIBUSI STATUS LOWONGAN -->
      <section class="white-card">
        <h2 class="card-title">
          <span>Distribusi Status Rekrutmen</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </h2>
        <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: -10px; margin-bottom: 20px;">Perbandingan terhadap total lowongan dilaporkan</p>

        <div class="dist-item">
          <div class="dist-header">
            <span>Lowongan Aktif</span>
            <span>2 &bull; 14%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill blue" style="width: 14%;"></div>
          </div>
        </div>

        <div class="dist-item">
          <div class="dist-header">
            <span>Sudah Terisi</span>
            <span style="color: #16a34a;">12 &bull; 85%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill green" style="width: 85%;"></div>
          </div>
        </div>

        <div class="dist-item">
          <div class="dist-header">
            <span>Dibatalkan</span>
            <span>0 &bull; 0%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill amber" style="width: 0%;"></div>
          </div>
        </div>
      </section>

      <!-- RIGHT: AKSES CEPAT -->
      <section class="white-card">
        <h2 class="card-title">
          <span>Akses Cepat</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </h2>
        <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: -10px; margin-bottom: 20px;">Fitur layanan WLLP yang paling sering digunakan</p>

        <div class="quick-access-grid">
          <a href="#" class="quick-access-tile">
            <div class="tile-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
              <div style="font-size: 0.85rem; font-weight: 700;">Pelaporan WLLP</div>
              <div style="font-size: 0.72rem; color: var(--text-muted);">Buat laporan tahunan</div>
            </div>
          </a>

          <a href="#" class="quick-access-tile">
            <div class="tile-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg>
            </div>
            <div>
              <div style="font-size: 0.85rem; font-weight: 700;">Status Keterisian</div>
              <div style="font-size: 0.72rem; color: var(--text-muted);">Perbarui status lowongan</div>
            </div>
          </a>

          <a href="#" class="quick-access-tile">
            <div class="tile-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M9 15h6"/><path d="M9 11h6"/></svg>
            </div>
            <div>
              <div style="font-size: 0.85rem; font-weight: 700;">Bukti Lapor</div>
              <div style="font-size: 0.72rem; color: var(--text-muted);">Lihat dokumen pelaporan WLLP</div>
            </div>
          </a>

          <a href="#" class="quick-access-tile">
            <div class="tile-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div>
              <div style="font-size: 0.85rem; font-weight: 700;">Lowongan Karirhub</div>
              <div style="font-size: 0.72rem; color: var(--text-muted);">Kelola posting lowongan</div>
            </div>
          </a>
        </div>
      </section>
    </div>

    <!-- RECENT ACTIVITY CARD -->
    <section class="white-card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
          <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main);">Aktivitas Terbaru WLLP</h2>
          <p style="font-size: 0.78rem; color: var(--text-muted);">Riwayat manajemen lowongan kerja Anda</p>
        </div>
        <a href="#" style="font-size: 0.82rem; font-weight: 700; color: var(--primary-blue); text-decoration: none; border: 1px solid var(--border-light); padding: 5px 12px; border-radius: var(--radius-sm);">
          Lihat Semua &rarr;
        </a>
      </div>

      <div class="activity-list">
        <div class="activity-row">
          <div class="activity-left">
            <div class="activity-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
              <div class="activity-title">Buat Laporan Lowongan - Mitra Logistik &amp; Kurir</div>
              <div class="activity-date">9 September 2026, 11:20 WIB</div>
            </div>
          </div>
          <div class="activity-right">
            <span class="activity-code">WLLP-2026-09-00124</span>
            <span class="status-badge green">Terverifikasi</span>
          </div>
        </div>

        <div class="activity-row">
          <div class="activity-left">
            <div class="activity-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
              <div class="activity-title">Buat Laporan Lowongan - Pengemudi On-Demand</div>
              <div class="activity-date">24 Juni 2026, 14:10 WIB</div>
            </div>
          </div>
          <div class="activity-right">
            <span class="activity-code">WLLP-2026-06-00087</span>
            <span class="status-badge green">Terisi</span>
          </div>
        </div>

        <div class="activity-row">
          <div class="activity-left">
            <div class="activity-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
              <div class="activity-title">Review Kandidat - Spesialis Pemasaran Lepas</div>
              <div class="activity-date">10 Juni 2026, 09:30 WIB</div>
            </div>
          </div>
          <div class="activity-right">
            <span class="activity-code">WLLP-2026-06-00042</span>
            <span class="status-badge blue">Selesai</span>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <footer class="kemnaker-footer">
    <div class="footer-inner">
      <div>
        &copy; <?php echo date("Y"); ?> Kementerian Ketenagakerjaan Republik Indonesia &bull; Direktorat Jenderal PHI dan Jamsos
      </div>
      <div>
        <a href="#" style="color: #cbd5e1; text-decoration: none; margin-left: 16px;">Dokumentasi API</a>
        <a href="#" style="color: #cbd5e1; text-decoration: none; margin-left: 16px;">Pusat Bantuan</a>
      </div>
    </div>
  </footer>

</body>
</html>
