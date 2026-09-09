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
  <meta name="description" content="Dashboard Pemberi Kerja - Ekosistem Gig Workers KarirHub Kemnaker RI" />
  <title>Dashboard Pemberi Kerja | KarirHub Gig Workers - Kemnaker RI</title>

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
      --primary-blue-light: #e8f0fe;
      --hero-blue-start: #0a325c;
      --hero-blue-mid: #1554b7;
      --hero-blue-end: #2777f2;
      --bg-page: #f4f6fa;
      --bg-surface: #ffffff;
      --border-subtle: #e2e8f0;
      --border-light: #cbd5e1;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --text-soft: #475569;
      --success-green: #10b981;
      --success-green-hover: #059669;
      --success-bg: #ecfdf5;
      --warning-amber: #d97706;
      --warning-bg: #fffbeb;
      --danger-red: #ef4444;
      --danger-bg: #fef2f2;
      --cyan-badge: #0891b2;
      --cyan-bg: #ecfeff;
      --purple-badge: #7c3aed;
      --purple-bg: #f5f3ff;
      --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
      --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06);
      --shadow-md: 0 4px 14px -1px rgba(15, 23, 42, 0.08);
      --shadow-lg: 0 14px 30px -4px rgba(9, 44, 76, 0.12);
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 20px;
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

    .topbar-badge {
      background: rgba(59, 130, 246, 0.25);
      border: 1px solid rgba(147, 197, 253, 0.3);
      color: #93c5fd;
      padding: 2px 8px;
      border-radius: var(--radius-pill);
      font-weight: 700;
      font-size: 0.72rem;
      letter-spacing: 0.03em;
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
      padding: 4px 12px;
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
      font-weight: 600;
      color: #93c5fd;
      line-height: 1.3;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .brand-sub-badge {
      background: #f59e0b;
      color: #000000;
      font-size: 0.65rem;
      font-weight: 800;
      padding: 1px 6px;
      border-radius: var(--radius-pill);
      text-transform: uppercase;
    }

    .nav-tabs-container {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .nav-tab-btn {
      color: #cbd5e1;
      background: transparent;
      border: none;
      font-size: 0.86rem;
      font-weight: 600;
      padding: 8px 14px;
      border-radius: var(--radius-sm);
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .nav-tab-btn:hover {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.08);
    }

    .nav-tab-btn.active {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.18);
      font-weight: 700;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
    }

    .tab-badge {
      background: rgba(255, 255, 255, 0.25);
      font-size: 0.7rem;
      padding: 2px 6px;
      border-radius: var(--radius-pill);
    }

    .nav-tab-btn.active .tab-badge {
      background: #3b82f6;
      color: #ffffff;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-create-post {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.2);
      font-size: 0.84rem;
      font-weight: 700;
      padding: 8px 16px;
      border-radius: var(--radius-pill);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
      transition: all 0.2s ease;
    }

    .btn-create-post:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
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
      width: 26px;
      height: 26px;
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
      padding: 24px 24px 48px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    /* SYSTEM INFO NOTICE */
    .system-info-notice {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: var(--radius-md);
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      font-size: 0.84rem;
      color: #1e40af;
    }

    .notice-icon-text {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* HERO BANNER */
    .hero-banner {
      background: linear-gradient(135deg, var(--hero-blue-start) 0%, var(--hero-blue-mid) 55%, var(--hero-blue-end) 100%);
      border-radius: var(--radius-lg);
      padding: 28px 32px;
      color: #ffffff;
      box-shadow: var(--shadow-lg), 0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      position: relative;
      overflow: hidden;
    }

    .hero-banner::after {
      content: '';
      position: absolute;
      right: -40px;
      top: -40px;
      width: 260px;
      height: 260px;
      background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
      pointer-events: none;
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
      background: rgba(6, 29, 51, 0.45);
      border: 1px solid rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(8px);
      padding: 4px 14px;
      border-radius: var(--radius-pill);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #ffffff;
      margin-bottom: 8px;
    }

    .hero-title {
      font-size: clamp(1.5rem, 2.5vw, 1.95rem);
      font-weight: 800;
      letter-spacing: -0.02em;
      color: #ffffff;
      margin-bottom: 6px;
    }

    .hero-desc {
      font-size: 0.94rem;
      color: rgba(255, 255, 255, 0.92);
      line-height: 1.5;
      max-width: 680px;
    }

    .hero-quick-stats {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .hero-stat-pill {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: var(--radius-md);
      padding: 10px 16px;
      display: flex;
      flex-direction: column;
      min-width: 130px;
    }

    .hero-stat-val {
      font-size: 1.4rem;
      font-weight: 800;
      line-height: 1.2;
    }

    .hero-stat-lbl {
      font-size: 0.72rem;
      color: rgba(255, 255, 255, 0.85);
      font-weight: 500;
    }

    /* STATS SUMMARY GRID */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    @media (max-width: 960px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 580px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }

    .stat-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: var(--shadow-sm);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      cursor: pointer;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
      border-color: #93c5fd;
    }

    .stat-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .stat-label {
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: var(--text-muted);
    }

    .stat-icon-wrapper {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .stat-icon-wrapper.blue { background: #eff6ff; color: #2563eb; }
    .stat-icon-wrapper.cyan { background: var(--cyan-bg); color: var(--cyan-badge); }
    .stat-icon-wrapper.green { background: var(--success-bg); color: var(--success-green); }
    .stat-icon-wrapper.amber { background: var(--warning-bg); color: var(--warning-amber); }

    .stat-number {
      font-size: 1.85rem;
      font-weight: 800;
      color: var(--text-main);
      line-height: 1.2;
    }

    .stat-caption {
      font-size: 0.78rem;
      color: var(--text-muted);
      margin-top: 4px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .stat-trend-positive {
      color: var(--success-green);
      font-weight: 700;
    }

    /* CARD WRAPPER */
    .white-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 24px;
      box-shadow: var(--shadow-sm);
    }

    .card-header-flex {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 14px;
      margin-bottom: 20px;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--border-subtle);
    }

    .card-title-group h2 {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-title-group p {
      font-size: 0.82rem;
      color: var(--text-muted);
      margin-top: 2px;
    }

    /* TAB VIEWS */
    .tab-view-content {
      display: none;
    }

    .tab-view-content.active-view {
      display: block;
      animation: fadeIn 0.25s ease forwards;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* FILTER BUTTONS & TOOLBAR */
    .toolbar-filter {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 18px;
    }

    .filter-btn-pill {
      background: #f8fafc;
      border: 1px solid var(--border-light);
      padding: 6px 14px;
      border-radius: var(--radius-pill);
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-soft);
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .filter-btn-pill:hover {
      background: #e2e8f0;
      color: var(--text-main);
    }

    .filter-btn-pill.active {
      background: var(--primary-blue);
      border-color: var(--primary-blue);
      color: #ffffff;
      box-shadow: 0 2px 6px rgba(22, 87, 193, 0.25);
    }

    .search-input-box {
      margin-left: auto;
      display: flex;
      align-items: center;
      background: #ffffff;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-pill);
      padding: 4px 12px;
      gap: 8px;
      width: 260px;
    }

    .search-input-box input {
      border: none;
      outline: none;
      font-size: 0.82rem;
      width: 100%;
      background: transparent;
    }

    /* SECTION 1: PROJECT POSTS GRID & LIST */
    .project-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
      gap: 18px;
    }

    @media (max-width: 768px) {
      .project-grid {
        grid-template-columns: 1fr;
      }
    }

    .project-card {
      background: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 20px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 14px;
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }

    .project-card:hover {
      border-color: #93c5fd;
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .project-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--primary-blue);
    }

    .project-card.status-review::before { background: var(--warning-amber); }
    .project-card.status-closed::before { background: var(--text-muted); }

    .project-top-meta {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 10px;
    }

    .project-category-tag {
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--primary-blue);
      background: #eff6ff;
      padding: 3px 8px;
      border-radius: 6px;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .badge-status {
      font-size: 0.72rem;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: var(--radius-pill);
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .badge-status.active {
      background: var(--success-bg);
      color: var(--success-green);
      border: 1px solid #a7f3d0;
    }

    .badge-status.review {
      background: var(--warning-bg);
      color: var(--warning-amber);
      border: 1px solid #fde68a;
    }

    .badge-status.closed {
      background: #f1f5f9;
      color: var(--text-muted);
      border: 1px solid var(--border-light);
    }

    .project-card-title {
      font-size: 1.05rem;
      font-weight: 800;
      color: var(--text-main);
      line-height: 1.35;
      margin-top: 4px;
    }

    .project-card-desc {
      font-size: 0.84rem;
      color: var(--text-muted);
      line-height: 1.45;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .project-skill-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin: 4px 0;
    }

    .skill-tag-item {
      font-size: 0.72rem;
      background: #f1f5f9;
      color: var(--text-soft);
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: 500;
    }

    .project-card-metrics {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      background: #f8fafc;
      padding: 10px 12px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border-subtle);
    }

    .proj-metric-item {
      display: flex;
      flex-direction: column;
    }

    .proj-metric-lbl {
      font-size: 0.7rem;
      color: var(--text-muted);
      font-weight: 600;
      text-transform: uppercase;
    }

    .proj-metric-val {
      font-size: 0.92rem;
      font-weight: 800;
      color: var(--text-main);
    }

    .project-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 10px;
      border-top: 1px solid var(--border-subtle);
      gap: 10px;
    }

    .applicants-count-badge {
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--primary-blue);
      display: flex;
      align-items: center;
      gap: 6px;
      background: #eff6ff;
      padding: 6px 10px;
      border-radius: var(--radius-pill);
      cursor: pointer;
    }

    .btn-action-sm {
      background: #ffffff;
      border: 1px solid var(--border-light);
      color: var(--text-main);
      padding: 6px 12px;
      border-radius: var(--radius-sm);
      font-size: 0.8rem;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.15s ease;
    }

    .btn-action-sm:hover {
      background: var(--primary-blue);
      color: #ffffff;
      border-color: var(--primary-blue);
    }

    /* SECTION 2: ACTIVE PROJECTS (IN PROGRESS) */
    .active-projects-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .active-project-card {
      background: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 20px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: 16px;
      transition: border-color 0.2s ease;
    }

    .active-project-card:hover {
      border-color: #93c5fd;
    }

    .active-proj-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 12px;
    }

    .active-proj-title {
      font-size: 1.1rem;
      font-weight: 800;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .active-proj-body {
      display: grid;
      grid-template-columns: 1.2fr 1fr 1fr;
      gap: 20px;
      background: #f8fafc;
      padding: 16px;
      border-radius: var(--radius-md);
      border: 1px solid var(--border-subtle);
    }

    @media (max-width: 860px) {
      .active-proj-body {
        grid-template-columns: 1fr;
        gap: 14px;
      }
    }

    .freelancer-profile-box {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .fl-avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #1e40af;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1rem;
      flex-shrink: 0;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .fl-info-name {
      font-size: 0.95rem;
      font-weight: 800;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .fl-info-sub {
      font-size: 0.78rem;
      color: var(--text-muted);
    }

    .fl-rating-badge {
      color: #d97706;
      font-weight: 700;
      font-size: 0.75rem;
      display: inline-flex;
      align-items: center;
      gap: 3px;
    }

    .progress-milestone-box {
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 6px;
    }

    .progress-header-label {
      display: flex;
      justify-content: space-between;
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .progress-track-bg {
      height: 10px;
      background: #e2e8f0;
      border-radius: var(--radius-pill);
      overflow: hidden;
    }

    .progress-fill-bar {
      height: 100%;
      background: linear-gradient(90deg, #2563eb, #3b82f6);
      border-radius: var(--radius-pill);
      transition: width 0.3s ease;
    }

    .payment-direct-box {
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 4px;
    }

    .payment-direct-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--primary-blue);
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .payment-direct-amount {
      font-size: 1.05rem;
      font-weight: 800;
      color: var(--text-main);
    }

    .active-proj-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
    }

    .milestone-stepper {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.78rem;
      color: var(--text-muted);
    }

    .step-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #eff6ff;
      color: var(--primary-blue);
      padding: 3px 8px;
      border-radius: 4px;
      font-weight: 700;
    }

    /* SECTION 3: APPLICANTS / GIG WORKERS */
    .applicants-grid {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .applicant-card {
      background: #ffffff;
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-md);
      padding: 18px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 18px;
      transition: all 0.15s ease;
    }

    .applicant-card:hover {
      background: #fbfcfe;
      border-color: #93c5fd;
      box-shadow: var(--shadow-sm);
    }

    .applicant-left-info {
      display: flex;
      align-items: center;
      gap: 16px;
      flex: 1;
      min-width: 300px;
    }

    .applicant-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: #2563eb;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.1rem;
      flex-shrink: 0;
      position: relative;
    }

    .verified-icon-badge {
      position: absolute;
      bottom: -2px;
      right: -2px;
      background: #10b981;
      color: #ffffff;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #ffffff;
      font-size: 10px;
    }

    .applicant-details {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .applicant-name-row {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .applicant-name {
      font-size: 1rem;
      font-weight: 800;
      color: var(--text-main);
    }

    .applicant-applied-role {
      font-size: 0.82rem;
      color: var(--text-muted);
    }

    .applicant-contact-meta {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 0.76rem;
      color: var(--text-soft);
      margin-top: 2px;
    }

    .applicant-contact-tag {
      background: #f1f5f9;
      padding: 2px 8px;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      color: #334155;
      font-weight: 600;
    }

    .applicant-proposal-snippet {
      font-size: 0.82rem;
      color: var(--text-soft);
      margin-top: 4px;
      background: #f8fafc;
      padding: 6px 10px;
      border-radius: 6px;
      border-left: 3px solid #3b82f6;
      max-width: 500px;
      font-style: italic;
    }

    .applicant-center-meta {
      display: flex;
      flex-direction: column;
      gap: 4px;
      min-width: 180px;
    }

    .bid-amount {
      font-size: 1.05rem;
      font-weight: 800;
      color: var(--primary-blue);
    }

    .bid-time {
      font-size: 0.78rem;
      color: var(--text-muted);
    }

    .applicant-right-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-hire {
      background: var(--success-green);
      color: #ffffff;
      border: none;
      padding: 8px 16px;
      border-radius: var(--radius-sm);
      font-size: 0.82rem;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: background 0.15s ease;
    }

    .btn-hire:hover {
      background: var(--success-green-hover);
    }

    .btn-outline-blue {
      background: #ffffff;
      color: var(--primary-blue);
      border: 1px solid var(--primary-blue);
      padding: 8px 14px;
      border-radius: var(--radius-sm);
      font-size: 0.82rem;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.15s ease;
    }

    .btn-outline-blue:hover {
      background: #eff6ff;
    }

    /* RECRUITMENT FUNNEL & OVERVIEW GRID */
    .overview-dual-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 20px;
    }

    @media (max-width: 900px) {
      .overview-dual-grid {
        grid-template-columns: 1fr;
      }
    }

    .funnel-container {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 10px;
    }

    .funnel-step {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 14px;
      background: #f8fafc;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border-subtle);
    }

    .funnel-step-name {
      font-size: 0.84rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .funnel-step-bar-wrap {
      flex: 1;
      max-width: 140px;
      height: 8px;
      background: #e2e8f0;
      border-radius: var(--radius-pill);
      margin: 0 14px;
      overflow: hidden;
    }

    .funnel-step-bar-fill {
      height: 100%;
      background: #2563eb;
      border-radius: var(--radius-pill);
    }

    .funnel-step-count {
      font-size: 0.9rem;
      font-weight: 800;
      min-width: 40px;
      text-align: right;
    }

    /* MODAL: POST PROJECT */
    .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 100;
      padding: 16px;
    }

    .modal-backdrop.open {
      display: flex;
    }

    .modal-window {
      background: #ffffff;
      border-radius: var(--radius-lg);
      width: 100%;
      max-width: 650px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      display: flex;
      flex-direction: column;
      animation: modalSlide 0.25s ease-out;
    }

    @keyframes modalSlide {
      from { transform: scale(0.96) translateY(10px); opacity: 0; }
      to { transform: scale(1) translateY(0); opacity: 1; }
    }

    .modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--border-subtle);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f8fafc;
    }

    .modal-header h3 {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .modal-close-btn {
      background: transparent;
      border: none;
      font-size: 1.5rem;
      color: var(--text-muted);
      cursor: pointer;
      line-height: 1;
      padding: 4px;
    }

    .modal-close-btn:hover {
      color: var(--danger-red);
    }

    .modal-body {
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .form-row {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .form-row label {
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--text-main);
    }

    .form-row input,
    .form-row select,
    .form-row textarea {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid var(--border-light);
      border-radius: var(--radius-sm);
      font-size: 0.88rem;
      color: var(--text-main);
      outline: none;
      transition: border-color 0.2s;
    }

    .form-row input:focus,
    .form-row select:focus,
    .form-row textarea:focus {
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    @media (max-width: 500px) {
      .form-grid-2 {
        grid-template-columns: 1fr;
      }
    }

    .modal-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--border-subtle);
      background: #f8fafc;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }

    .btn-secondary {
      background: #ffffff;
      border: 1px solid var(--border-light);
      padding: 8px 16px;
      border-radius: var(--radius-sm);
      font-size: 0.86rem;
      font-weight: 600;
      color: var(--text-soft);
      cursor: pointer;
    }

    .btn-primary {
      background: var(--primary-blue);
      color: #ffffff;
      border: none;
      padding: 8px 20px;
      border-radius: var(--radius-sm);
      font-size: 0.86rem;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-primary:hover {
      background: var(--primary-blue-hover);
    }

    /* TOAST NOTIFICATION */
    .toast-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 200;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .toast-card {
      background: #0f172a;
      color: #ffffff;
      padding: 12px 18px;
      border-radius: var(--radius-md);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.86rem;
      font-weight: 600;
      animation: slideToast 0.3s ease-out;
      border-left: 4px solid var(--success-green);
    }

    @keyframes slideToast {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

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
        <strong>KarirHub</strong>
        <span class="topbar-badge">Pemberi Kerja / Employer</span>
        <a href="#">Beranda ▾</a>
        <a href="#">Manajemen Talenta ▾</a>
        <a href="#">Bantuan &amp; Panduan ▾</a>
      </div>
      <div class="topbar-user">
        <span style="color: #94a3b8;">ID Perusahaan: <strong>EMP-99201</strong></span>
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
          <span class="brand-title">KEMENTERIAN KETENAGAKERJAAN RI</span>
          <span class="brand-sub">
            KarirHub Gig Workers
            <span class="brand-sub-badge">Fitur Baru</span>
          </span>
        </div>
      </div>

      <!-- MAIN TABS NAVIGATION -->
      <nav class="nav-tabs-container">
        <button class="nav-tab-btn active" onclick="switchMainTab('overview', this)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Ringkasan
        </button>
        <button class="nav-tab-btn" onclick="switchMainTab('vacancies', this)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Lowongan Proyek
          <span class="tab-badge" id="badge-vacancies-count">3</span>
        </button>
        <button class="nav-tab-btn" onclick="switchMainTab('active-projects', this)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          Proyek Aktif
          <span class="tab-badge" id="badge-active-count">2</span>
        </button>
        <button class="nav-tab-btn" onclick="switchMainTab('applicants', this)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Pelamar Proyek
          <span class="tab-badge" id="badge-applicants-count">6</span>
        </button>
      </nav>

      <!-- HEADER ACTIONS -->
      <div class="header-actions">
        <button class="btn-create-post" onclick="openPostProjectModal()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          + Pasang Proyek Gig
        </button>
        <div class="user-pill">
          <span class="user-avatar"><?php echo strtoupper(substr($username, 0, 2)); ?></span>
          <span><?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?></span>
          <span style="background: rgba(245, 158, 11, 0.25); color: #fde68a; font-weight: 700; font-size: 0.72rem; padding: 2px 7px; border-radius: var(--radius-pill); display: inline-flex; align-items: center; gap: 3px;" title="Rating Pemberi Kerja dari Mitra Gig Worker">
            ★ 4.9
          </span>
        </div>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="page-main">

    <!-- NOTICE: DIRECT COMMUNICATION & DIRECT PAYMENT -->
    <div class="system-info-notice">
      <div class="notice-icon-text">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>
          <strong>Informasi Operasional Fitur Gig:</strong> Komunikasi (wawancara/diskusi) dan transaksi pembayaran honor proyek dilakukan <strong>secara langsung di luar sistem</strong> antara Pemberi Kerja dan Gig Worker.
        </span>
      </div>
    </div>

    <!-- HERO BANNER -->
    <section class="hero-banner">
      <div class="hero-header">
        <div>
          <div class="hero-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            PORTAL PEMBERI KERJA &bull; GIG WORKERS KARIRHUB
          </div>
          <h1 class="hero-title">Halo, <?php echo htmlspecialchars($username, ENT_QUOTES, "UTF-8"); ?>! 👋</h1>
          <p class="hero-desc">
            Temukan talenta lepas (freelancer) terverifikasi untuk kebutuhan proyek jangka pendek perusahaan Anda. Publikasikan lowongan, seleksi proposal, dan hubungi kandidat langsung via kontak resmi.
          </p>
        </div>

        <div class="hero-quick-stats">
          <div class="hero-stat-pill" style="border-color: rgba(245, 158, 11, 0.45); background: rgba(245, 158, 11, 0.18);">
            <div style="display: flex; align-items: baseline; gap: 4px;">
              <span class="hero-stat-val" style="color: #fef08a;">4.9</span>
              <span style="color: #fbbf24; font-size: 1.1rem; font-weight: 800;">★</span>
              <span style="font-size: 0.72rem; color: rgba(255,255,255,0.85); font-weight: 600; margin-left: 2px;">/ 5.0</span>
            </div>
            <span class="hero-stat-lbl">Rating Employer (18 Ulasan Mitra)</span>
          </div>
          <div class="hero-stat-pill">
            <span class="hero-stat-val" id="hero-stat-vacancies">3</span>
            <span class="hero-stat-lbl">Lowongan Terbuka</span>
          </div>
          <div class="hero-stat-pill">
            <span class="hero-stat-val" id="hero-stat-applicants">6</span>
            <span class="hero-stat-lbl">Pelamar Masuk</span>
          </div>
          <div class="hero-stat-pill">
            <span class="hero-stat-val" id="hero-stat-active">2</span>
            <span class="hero-stat-lbl">Proyek Berjalan</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 4 TOP METRIC CARDS -->
    <section class="stats-grid">
      <article class="stat-card" onclick="switchMainTab('vacancies')">
        <div class="stat-card-header">
          <span class="stat-label">LOWONGAN PROYEK</span>
          <div class="stat-icon-wrapper blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
        </div>
        <div class="stat-number" id="card-stat-vacancies">3</div>
        <div class="stat-caption">
          <span class="stat-trend-positive">+2 tayang aktif</span> &bull; 1 menunggu verifikasi
        </div>
      </article>

      <article class="stat-card" onclick="switchMainTab('applicants')">
        <div class="stat-card-header">
          <span class="stat-label">TOTAL PELAMAR GIG</span>
          <div class="stat-icon-wrapper cyan">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
          </div>
        </div>
        <div class="stat-number" id="card-stat-applicants">6</div>
        <div class="stat-caption">
          <span class="stat-trend-positive">+4 proposal baru</span> minggu ini
        </div>
      </article>

      <article class="stat-card" onclick="switchMainTab('active-projects')">
        <div class="stat-card-header">
          <span class="stat-label">PROYEK AKTIF</span>
          <div class="stat-icon-wrapper green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          </div>
        </div>
        <div class="stat-number" id="card-stat-active">2</div>
        <div class="stat-caption">
          Sedang dikerjakan oleh mitra gig
        </div>
      </article>

      <article class="stat-card" onclick="switchMainTab('applicants')">
        <div class="stat-card-header">
          <span class="stat-label">DITERIMA</span>
          <div class="stat-icon-wrapper amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
          </div>
        </div>
        <div class="stat-number">5 Pelamar</div>
        <div class="stat-caption">
          Pelamar yang disetujui &amp; direkrut
        </div>
      </article>
    </section>

    <!-- ==================== TAB 1: OVERVIEW / RINGKASAN ==================== -->
    <div id="tab-overview" class="tab-view-content active-view">
      <div class="overview-dual-grid">
        
        <!-- LEFT: CORONG SELEKSI & DISTRIBUSI -->
        <section class="white-card">
          <div class="card-header-flex">
            <div class="card-title-group">
              <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Corong Seleksi Freelancer
              </h2>
              <p>Progres seleksi kandidat gig worker di semua proyek aktif Anda</p>
            </div>
            <button class="btn-action-sm" onclick="switchMainTab('applicants')">Lihat Semua Pelamar &rarr;</button>
          </div>

          <div class="funnel-container">
            <div class="funnel-step">
              <span class="funnel-step-name">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span>
                Proposal Masuk
              </span>
              <div class="funnel-step-bar-wrap">
                <div class="funnel-step-bar-fill" style="width: 100%;"></div>
              </div>
              <span class="funnel-step-count" id="funnel-inbox">6 Pelamar</span>
            </div>

            <div class="funnel-step">
              <span class="funnel-step-name">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                Review Portofolio &amp; Shortlist
              </span>
              <div class="funnel-step-bar-wrap">
                <div class="funnel-step-bar-fill" style="width: 66%; background: #f59e0b;"></div>
              </div>
              <span class="funnel-step-count">4 Kandidat</span>
            </div>

            <div class="funnel-step">
              <span class="funnel-step-name">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #8b5cf6; display: inline-block;"></span>
                Kontak Eksternal (WA / Email)
              </span>
              <div class="funnel-step-bar-wrap">
                <div class="funnel-step-bar-fill" style="width: 33%; background: #8b5cf6;"></div>
              </div>
              <span class="funnel-step-count">2 Kandidat</span>
            </div>

            <div class="funnel-step">
              <span class="funnel-step-name">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                Diterima &amp; Kontrak Aktif
              </span>
              <div class="funnel-step-bar-wrap">
                <div class="funnel-step-bar-fill" style="width: 33%; background: #10b981;"></div>
              </div>
              <span class="funnel-step-count" id="funnel-hired">2 Diterima</span>
            </div>
          </div>

          <!-- Employer Rating Reputation Summary -->
          <div style="margin-top: 16px; padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="font-size: 1.2rem; color: #d97706;">★</span>
              <div>
                <strong style="font-size: 0.84rem; color: #92400e;">Rating Perusahaan: 4.9 / 5.0 (18 Ulasan Gig Worker)</strong>
                <div style="font-size: 0.74rem; color: #b45309;">Penilaian dari pekerja lepas: <em>Brief Jelas &bull; Komunikasi Responsif &bull; Kerjasama Profesional</em></div>
              </div>
            </div>
            <span style="font-size: 0.72rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 3px 8px; border-radius: var(--radius-pill); border: 1px solid #a7f3d0;">
              ✓ Employer Terpercaya
            </span>
          </div>
        </section>

        <!-- RIGHT: RECENT APPLICANTS HIGHLIGHT -->
        <section class="white-card">
          <div class="card-header-flex">
            <div class="card-title-group">
              <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                Pelamar Terbaru Siap Direview
              </h2>
              <p>Kandidat gig worker dengan rating dan kecocokan tertinggi</p>
            </div>
          </div>

          <div style="display: flex; flex-direction: column; gap: 10px;">
            <!-- Applicant 1 Quick -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">T</div>
                <div>
                  <div style="font-size: 0.88rem; font-weight: 800; color: var(--text-main);">Tessa</div>
                  <div style="font-size: 0.74rem; color: var(--text-muted);">UI/UX Designer &bull; ⭐ 4.9 (18 Proyek)</div>
                </div>
              </div>
              <button class="btn-action-sm" onclick="switchMainTab('applicants')">Review Proposal</button>
            </div>

            <!-- Applicant 2 Quick -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #0891b2; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">R</div>
                <div>
                  <div style="font-size: 0.88rem; font-weight: 800; color: var(--text-main);">Rian Ardiansyah</div>
                  <div style="font-size: 0.74rem; color: var(--text-muted);">Fullstack Web Dev &bull; ⭐ 4.8 (24 Proyek)</div>
                </div>
              </div>
              <button class="btn-action-sm" onclick="switchMainTab('applicants')">Review Proposal</button>
            </div>

            <!-- Applicant 3 Quick -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #7c3aed; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">S</div>
                <div>
                  <div style="font-size: 0.88rem; font-weight: 800; color: var(--text-main);">Siti Nurhaliza</div>
                  <div style="font-size: 0.74rem; color: var(--text-muted);">Social Media Specialist &bull; ⭐ 5.0 (12 Proyek)</div>
                </div>
              </div>
              <button class="btn-action-sm" onclick="switchMainTab('applicants')">Review Proposal</button>
            </div>
          </div>
        </section>
      </div>

      <!-- ONGOING PROJECT POSTS PREVIEW -->
      <section class="white-card" style="margin-top: 20px;">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
              Postingan Lowongan Proyek Terkini
            </h2>
            <p>Pantau lowongan proyek freelance yang sedang dibuka untuk publik</p>
          </div>
          <div style="display: flex; gap: 8px;">
            <button class="btn-action-sm" onclick="switchMainTab('vacancies')">Kelola Semua Lowongan &rarr;</button>
            <button class="btn-create-post" style="padding: 6px 14px; font-size: 0.78rem;" onclick="openPostProjectModal()">+ Pasang Proyek</button>
          </div>
        </div>

        <div class="project-grid" id="overview-project-grid">
          <!-- Rendered dynamically -->
        </div>
      </section>
    </div>

    <!-- ==================== TAB 2: POSTINGAN LOWONGAN PROYEK ==================== -->
    <div id="tab-vacancies" class="tab-view-content">
      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              Daftar Postingan Lowongan Proyek Gig
            </h2>
            <p>Kelola semua lowongan proyek jangka pendek yang Anda publikasikan untuk para freelancer</p>
          </div>
          <button class="btn-create-post" onclick="openPostProjectModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            + Buat Lowongan Baru
          </button>
        </div>

        <!-- Toolbar filter -->
        <div class="toolbar-filter">
          <button class="filter-btn-pill active" onclick="filterProjects('all', this)">Semua Lowongan (<span id="count-filter-all">3</span>)</button>
          <button class="filter-btn-pill" onclick="filterProjects('active', this)">Tayang &amp; Aktif (<span id="count-filter-active">2</span>)</button>
          <button class="filter-btn-pill" onclick="filterProjects('review', this)">Menunggu Verifikasi (<span id="count-filter-review">1</span>)</button>
          <button class="filter-btn-pill" onclick="filterProjects('closed', this)">Selesai Rekrutmen (0)</button>

          <div class="search-input-box">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Cari judul proyek atau skill..." id="search-project-input" onkeyup="searchProjects(this.value)" />
          </div>
        </div>

        <!-- Project Cards Grid -->
        <div class="project-grid" id="main-vacancies-grid">
          <!-- Rendered by JS -->
        </div>
      </section>
    </div>

    <!-- ==================== TAB 3: PROYEK AKTIF (IN PROGRESS) ==================== -->
    <div id="tab-active-projects" class="tab-view-content">
      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              Proyek Aktif Sedang Dikerjakan
            </h2>
            <p>Pantau progres pengerjaan milestone dan koordinasi pengerjaan proyek dengan freelancer</p>
          </div>
          <div style="font-size: 0.82rem; color: var(--text-muted); background: #f1f5f9; padding: 6px 12px; border-radius: var(--radius-pill);">
            🤝 Status Kolaborasi: <strong>Koordinasi Langsung Perusahaan &amp; Mitra</strong>
          </div>
        </div>

        <div class="active-projects-list" id="active-projects-container">
          <!-- Active Project 1 -->
          <div class="active-project-card">
            <div class="active-proj-header">
              <div>
                <div class="active-proj-title">
                  Redesign UI/UX Dashboard Prototype KarirHub
                  <span class="badge-status active">Sedang Berjalan</span>
                </div>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                  No. Kontrak: <strong>CTR-GIG-2026-0811</strong> &bull; Durasi: <strong>3 Minggu (Sisa 8 Hari)</strong>
                </div>
              </div>
              <div style="text-align: right;">
                <span style="font-size: 0.74rem; color: var(--text-muted); display: block;">Tipe Kesepakatan</span>
                <span style="font-size: 0.95rem; font-weight: 800; color: var(--primary-blue);">Kontrak Mandiri Lepas</span>
              </div>
            </div>

            <div class="active-proj-body">
              <!-- Freelancer Info -->
              <div class="freelancer-profile-box">
                <div class="fl-avatar" style="background: #2563eb;">T</div>
                <div>
                  <div class="fl-info-name">
                    Tessa
                    <span class="fl-rating-badge">★ 4.9</span>
                  </div>
                  <div class="fl-info-sub">Lead UI/UX Designer</div>
                  <div style="font-size: 0.72rem; color: #10b981; font-weight: 700; margin-top: 2px;">✓ Terverifikasi Kemnaker RI</div>
                </div>
              </div>

              <!-- Progress & Milestone -->
              <div class="progress-milestone-box">
                <div class="progress-header-label">
                  <span>Kemajuan Proyek</span>
                  <span style="color: #2563eb;">65% (Milestone 2/3)</span>
                </div>
                <div class="progress-track-bg">
                  <div class="progress-fill-bar" style="width: 65%;"></div>
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                  Target Selesai: <strong>22 September 2026</strong>
                </div>
              </div>

              <!-- Scope / Delivery Info -->
              <div class="payment-direct-box">
                <span class="payment-direct-label">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  Status Deliverable
                </span>
                <span class="payment-direct-amount" style="font-size: 0.95rem;">Review Prototype</span>
                <span style="font-size: 0.72rem; color: var(--text-muted);">Sedang pengujian internal perusahaan</span>
              </div>
            </div>

            <div class="active-proj-actions">
              <div class="milestone-stepper">
                <span class="step-badge" style="background: #ecfdf5; color: #059669;">✓ M1: Wireframe &amp; Flow (Selesai)</span>
                <span>&rarr;</span>
                <span class="step-badge" style="background: #eff6ff; color: #2563eb;">🔄 M2: Interactive Prototype (Reviewing)</span>
                <span>&rarr;</span>
                <span class="step-badge" style="background: #f1f5f9; color: #64748b;">⏳ M3: Final Asset &amp; Handover</span>
              </div>
              <div style="display: flex; gap: 8px;">
                <button class="btn-action-sm" onclick="copyContact('Tessa', '0812-3456-7890', 'tessa.design@email.com')">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                  Kontak Freelancer (WA/Email)
                </button>
                <button class="btn-hire" style="padding: 6px 14px; font-size: 0.78rem;" onclick="showToast('Deliverable Milestone 2 telah disetujui secara internal!')">
                  ✓ Setujui Milestone 2
                </button>
              </div>
            </div>
          </div>

          <!-- Active Project 2 -->
          <div class="active-project-card">
            <div class="active-proj-header">
              <div>
                <div class="active-proj-title">
                  Integrasi REST API Modul Notifikasi SMS &amp; WhatsApp
                  <span class="badge-status active">Sedang Berjalan</span>
                </div>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                  No. Kontrak: <strong>CTR-GIG-2026-0819</strong> &bull; Durasi: <strong>2 Minggu (Sisa 4 Hari)</strong>
                </div>
              </div>
              <div style="text-align: right;">
                <span style="font-size: 0.74rem; color: var(--text-muted); display: block;">Tipe Kesepakatan</span>
                <span style="font-size: 0.95rem; font-weight: 800; color: var(--primary-blue);">Kontrak Mandiri Lepas</span>
              </div>
            </div>

            <div class="active-proj-body">
              <!-- Freelancer Info -->
              <div class="freelancer-profile-box">
                <div class="fl-avatar" style="background: #0891b2;">R</div>
                <div>
                  <div class="fl-info-name">
                    Rian Ardiansyah
                    <span class="fl-rating-badge">★ 4.8</span>
                  </div>
                  <div class="fl-info-sub">Backend API Developer</div>
                  <div style="font-size: 0.72rem; color: #10b981; font-weight: 700; margin-top: 2px;">✓ Terverifikasi Kemnaker RI</div>
                </div>
              </div>

              <!-- Progress & Milestone -->
              <div class="progress-milestone-box">
                <div class="progress-header-label">
                  <span>Kemajuan Proyek</span>
                  <span style="color: #2563eb;">90% (Milestone 2/2)</span>
                </div>
                <div class="progress-track-bg">
                  <div class="progress-fill-bar" style="width: 90%;"></div>
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                  Target Selesai: <strong>14 September 2026</strong>
                </div>
              </div>

              <!-- Scope / Delivery Info -->
              <div class="payment-direct-box">
                <span class="payment-direct-label">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  Status Deliverable
                </span>
                <span class="payment-direct-amount" style="font-size: 0.95rem;">UAT &amp; Deploy Selesai</span>
                <span style="font-size: 0.72rem; color: var(--text-muted);">Menunggu verifikasi akhir</span>
              </div>
            </div>

            <div class="active-proj-actions">
              <div class="milestone-stepper">
                <span class="step-badge" style="background: #ecfdf5; color: #059669;">✓ M1: API Architecture &amp; Webhook Setup</span>
                <span>&rarr;</span>
                <span class="step-badge" style="background: #eff6ff; color: #2563eb;">🔄 M2: Stress Testing &amp; Production Deploy</span>
              </div>
              <div style="display: flex; gap: 8px;">
                <button class="btn-action-sm" onclick="copyContact('Rian Ardiansyah', '0813-8899-7711', 'rian.dev@email.com')">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                  Kontak Freelancer (WA/Email)
                </button>
                <button class="btn-hire" style="padding: 6px 14px; font-size: 0.78rem;" onclick="showToast('Pekerjaan selesai! Kontrak berhasil diselesaikan.')">
                  ✓ Selesaikan Proyek &amp; Beri Rating
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- ==================== TAB 4: GIG WORKERS THAT APPLIED ==================== -->
    <div id="tab-applicants" class="tab-view-content">
      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
              Daftar Pelamar Proyek (Gig Worker Applicants)
            </h2>
            <p>Review proposal penawaran, portofolio kerja, dan hubungi freelancer via kontak langsung (WhatsApp / Email)</p>
          </div>
          <div style="font-size: 0.82rem; color: var(--text-muted);">
            Menampilkan <strong id="applicants-visible-count">6</strong> proposal kandidat
          </div>
        </div>

        <!-- Filter bar -->
        <div class="toolbar-filter">
          <button class="filter-btn-pill active" onclick="filterApplicants('all', this)">Semua Pelamar (6)</button>
          <button class="filter-btn-pill" onclick="filterApplicants('ui-ux', this)">UI/UX Proyek (2)</button>
          <button class="filter-btn-pill" onclick="filterApplicants('backend', this)">Backend &amp; API (2)</button>
          <button class="filter-btn-pill" onclick="filterApplicants('marketing', this)">Pemasaran Konten (2)</button>

          <div class="search-input-box">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Cari nama freelancer atau skill..." id="search-applicant-input" onkeyup="searchApplicants(this.value)" />
          </div>
        </div>

        <!-- Applicants Grid / List -->
        <div class="applicants-grid" id="applicants-container">
          
          <!-- Applicant 1 -->
          <div class="applicant-card" data-category="ui-ux">
            <div class="applicant-left-info">
              <div class="applicant-avatar">
                T
                <span class="verified-icon-badge" title="Terverifikasi Kemnaker">✓</span>
              </div>
              <div class="applicant-details">
                <div class="applicant-name-row">
                  <span class="applicant-name">Tessa</span>
                  <span class="fl-rating-badge">★ 4.9 (18 Ulasan)</span>
                  <span class="badge-status active" style="font-size: 0.68rem; padding: 1px 6px;">Top Rated Gig</span>
                </div>
                <div class="applicant-applied-role">
                  Melamar untuk proyek: <strong>Redesign UI/UX Dashboard Prototype KarirHub</strong>
                </div>
                <div class="applicant-contact-meta">
                  <span class="applicant-contact-tag">📱 WA: 0812-3456-7890</span>
                  <span class="applicant-contact-tag">✉️ tessa.design@email.com</span>
                </div>
                <div class="applicant-proposal-snippet">
                  "Halo! Saya berpengalaman 4+ tahun dalam merancang antarmuka sistem web pemerintahan dan B2B SaaS dengan design system yang rapi di Figma..."
                </div>
                <div class="project-skill-tags" style="margin-top: 6px;">
                  <span class="skill-tag-item">Figma Design</span>
                  <span class="skill-tag-item">UI/UX Prototyping</span>
                  <span class="skill-tag-item">Design System</span>
                </div>
              </div>
            </div>

            <div class="applicant-center-meta">
              <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Penawaran Biaya</span>
              <span class="bid-amount">Rp 8.000.000</span>
              <span class="bid-time">⏱️ Estimasi: 14 Hari Kerja</span>
            </div>

            <div class="applicant-right-actions">
              <button class="btn-outline-blue" onclick="copyContact('Tessa', '0812-3456-7890', 'tessa.design@email.com')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak WA/Email
              </button>
              <button class="btn-hire" onclick="hireApplicant('Tessa', 'Redesign UI/UX Dashboard')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Rekrut Freelancer
              </button>
            </div>
          </div>

          <!-- Applicant 2 -->
          <div class="applicant-card" data-category="backend">
            <div class="applicant-left-info">
              <div class="applicant-avatar" style="background: #0891b2;">
                R
                <span class="verified-icon-badge" title="Terverifikasi Kemnaker">✓</span>
              </div>
              <div class="applicant-details">
                <div class="applicant-name-row">
                  <span class="applicant-name">Rian Ardiansyah</span>
                  <span class="fl-rating-badge">★ 4.8 (24 Ulasan)</span>
                </div>
                <div class="applicant-applied-role">
                  Melamar untuk proyek: <strong>Integrasi REST API Modul Notifikasi</strong>
                </div>
                <div class="applicant-contact-meta">
                  <span class="applicant-contact-tag">📱 WA: 0813-8899-7711</span>
                  <span class="applicant-contact-tag">✉️ rian.dev@email.com</span>
                </div>
                <div class="applicant-proposal-snippet">
                  "Siap mengintegrasikan webhook gateway dan memastikan load testing API mampu menangani 5000+ request per menit dengan aman..."
                </div>
                <div class="project-skill-tags" style="margin-top: 6px;">
                  <span class="skill-tag-item">PHP / Laravel</span>
                  <span class="skill-tag-item">REST API</span>
                  <span class="skill-tag-item">MySQL</span>
                </div>
              </div>
            </div>

            <div class="applicant-center-meta">
              <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Penawaran Biaya</span>
              <span class="bid-amount">Rp 6.000.000</span>
              <span class="bid-time">⏱️ Estimasi: 10 Hari Kerja</span>
            </div>

            <div class="applicant-right-actions">
              <button class="btn-outline-blue" onclick="copyContact('Rian Ardiansyah', '0813-8899-7711', 'rian.dev@email.com')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak WA/Email
              </button>
              <button class="btn-hire" onclick="hireApplicant('Rian Ardiansyah', 'Integrasi REST API')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Rekrut Freelancer
              </button>
            </div>
          </div>

          <!-- Applicant 3 -->
          <div class="applicant-card" data-category="marketing">
            <div class="applicant-left-info">
              <div class="applicant-avatar" style="background: #7c3aed;">
                S
                <span class="verified-icon-badge" title="Terverifikasi Kemnaker">✓</span>
              </div>
              <div class="applicant-details">
                <div class="applicant-name-row">
                  <span class="applicant-name">Siti Nurhaliza</span>
                  <span class="fl-rating-badge">★ 5.0 (12 Ulasan)</span>
                </div>
                <div class="applicant-applied-role">
                  Melamar untuk proyek: <strong>Kampanye Media Sosial &amp; Copywriting Peluncuran Fitur</strong>
                </div>
                <div class="applicant-contact-meta">
                  <span class="applicant-contact-tag">📱 WA: 0857-1122-3344</span>
                  <span class="applicant-contact-tag">✉️ siti.marketing@email.com</span>
                </div>
                <div class="applicant-proposal-snippet">
                  "Menyediakan paket 20 konten carousel edukatif, naskah reels/TikTok, dan kalender konten terstruktur untuk meningkatkan awareness..."
                </div>
                <div class="project-skill-tags" style="margin-top: 6px;">
                  <span class="skill-tag-item">Copywriting</span>
                  <span class="skill-tag-item">Social Media</span>
                </div>
              </div>
            </div>

            <div class="applicant-center-meta">
              <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Penawaran Biaya</span>
              <span class="bid-amount">Rp 4.500.000</span>
              <span class="bid-time">⏱️ Estimasi: 20 Hari Kerja</span>
            </div>

            <div class="applicant-right-actions">
              <button class="btn-outline-blue" onclick="copyContact('Siti Nurhaliza', '0857-1122-3344', 'siti.marketing@email.com')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak WA/Email
              </button>
              <button class="btn-hire" onclick="hireApplicant('Siti Nurhaliza', 'Kampanye Media Sosial')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Rekrut Freelancer
              </button>
            </div>
          </div>

          <!-- Applicant 4 -->
          <div class="applicant-card" data-category="ui-ux">
            <div class="applicant-left-info">
              <div class="applicant-avatar" style="background: #059669;">
                B
                <span class="verified-icon-badge" title="Terverifikasi Kemnaker">✓</span>
              </div>
              <div class="applicant-details">
                <div class="applicant-name-row">
                  <span class="applicant-name">Budi Wicaksono</span>
                  <span class="fl-rating-badge">★ 4.7 (9 Ulasan)</span>
                </div>
                <div class="applicant-applied-role">
                  Melamar untuk proyek: <strong>Redesign UI/UX Dashboard Prototype KarirHub</strong>
                </div>
                <div class="applicant-contact-meta">
                  <span class="applicant-contact-tag">📱 WA: 0819-2233-4455</span>
                  <span class="applicant-contact-tag">✉️ budi.design@email.com</span>
                </div>
                <div class="applicant-proposal-snippet">
                  "Saya siap membantu deliver cepat dalam 10 hari lengkap dengan usability testing dan panduan style guide..."
                </div>
                <div class="project-skill-tags" style="margin-top: 6px;">
                  <span class="skill-tag-item">UI Design</span>
                  <span class="skill-tag-item">Wireframing</span>
                </div>
              </div>
            </div>

            <div class="applicant-center-meta">
              <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Penawaran Biaya</span>
              <span class="bid-amount">Rp 7.500.000</span>
              <span class="bid-time">⏱️ Estimasi: 10 Hari Kerja</span>
            </div>

            <div class="applicant-right-actions">
              <button class="btn-outline-blue" onclick="copyContact('Budi Wicaksono', '0819-2233-4455', 'budi.design@email.com')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak WA/Email
              </button>
              <button class="btn-hire" onclick="hireApplicant('Budi Wicaksono', 'Redesign UI/UX Dashboard')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Rekrut Freelancer
              </button>
            </div>
          </div>

          <!-- Applicant 5 -->
          <div class="applicant-card" data-category="backend">
            <div class="applicant-left-info">
              <div class="applicant-avatar" style="background: #ea580c;">
                D
                <span class="verified-icon-badge" title="Terverifikasi Kemnaker">✓</span>
              </div>
              <div class="applicant-details">
                <div class="applicant-name-row">
                  <span class="applicant-name">Dimas Prasetyo</span>
                  <span class="fl-rating-badge">★ 4.9 (31 Ulasan)</span>
                </div>
                <div class="applicant-applied-role">
                  Melamar untuk proyek: <strong>Integrasi REST API Modul Notifikasi</strong>
                </div>
                <div class="applicant-contact-meta">
                  <span class="applicant-contact-tag">📱 WA: 0821-9988-7766</span>
                  <span class="applicant-contact-tag">✉️ dimas.code@email.com</span>
                </div>
                <div class="applicant-proposal-snippet">
                  "Spesialis integrasi cloud API dan microservices. Telah menyelesaikan puluhan integrasi gateway serupa..."
                </div>
                <div class="project-skill-tags" style="margin-top: 6px;">
                  <span class="skill-tag-item">Node.js</span>
                  <span class="skill-tag-item">REST API</span>
                </div>
              </div>
            </div>

            <div class="applicant-center-meta">
              <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Penawaran Biaya</span>
              <span class="bid-amount">Rp 6.500.000</span>
              <span class="bid-time">⏱️ Estimasi: 7 Hari Kerja</span>
            </div>

            <div class="applicant-right-actions">
              <button class="btn-outline-blue" onclick="copyContact('Dimas Prasetyo', '0821-9988-7766', 'dimas.code@email.com')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak WA/Email
              </button>
              <button class="btn-hire" onclick="hireApplicant('Dimas Prasetyo', 'Integrasi REST API')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Rekrut Freelancer
              </button>
            </div>
          </div>

          <!-- Applicant 6 -->
          <div class="applicant-card" data-category="marketing">
            <div class="applicant-left-info">
              <div class="applicant-avatar" style="background: #db2777;">
                M
                <span class="verified-icon-badge" title="Terverifikasi Kemnaker">✓</span>
              </div>
              <div class="applicant-details">
                <div class="applicant-name-row">
                  <span class="applicant-name">Mega Lestari</span>
                  <span class="fl-rating-badge">★ 4.9 (15 Ulasan)</span>
                </div>
                <div class="applicant-applied-role">
                  Melamar untuk proyek: <strong>Kampanye Media Sosial &amp; Copywriting Peluncuran Fitur</strong>
                </div>
                <div class="applicant-contact-meta">
                  <span class="applicant-contact-tag">📱 WA: 0878-3344-5566</span>
                  <span class="applicant-contact-tag">✉️ mega.content@email.com</span>
                </div>
                <div class="applicant-proposal-snippet">
                  "Portfolio mencakup campaign viral BUMN dan startup teknologi. Siap mulai riset audience segera..."
                </div>
                <div class="project-skill-tags" style="margin-top: 6px;">
                  <span class="skill-tag-item">Digital Campaign</span>
                  <span class="skill-tag-item">SEO Writing</span>
                </div>
              </div>
            </div>

            <div class="applicant-center-meta">
              <span style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Penawaran Biaya</span>
              <span class="bid-amount">Rp 5.000.000</span>
              <span class="bid-time">⏱️ Estimasi: 14 Hari Kerja</span>
            </div>

            <div class="applicant-right-actions">
              <button class="btn-outline-blue" onclick="copyContact('Mega Lestari', '0878-3344-5566', 'mega.content@email.com')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Kontak WA/Email
              </button>
              <button class="btn-hire" onclick="hireApplicant('Mega Lestari', 'Kampanye Media Sosial')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Rekrut Freelancer
              </button>
            </div>
          </div>

        </div>
      </section>
    </div>

  </main>

  <!-- ==================== MODAL: POST PROJECT VACANCY ==================== -->
  <div class="modal-backdrop" id="postProjectModal" onclick="handleBackdropClick(event)">
    <div class="modal-window">
      <div class="modal-header">
        <h3>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Pasang Lowongan Proyek Gig Baru
        </h3>
        <button class="modal-close-btn" onclick="closePostProjectModal()">&times;</button>
      </div>

      <form id="newProjectForm" onsubmit="handleCreateProject(event)">
        <div class="modal-body">
          <div class="form-row">
            <label for="proj_title">Judul Proyek Freelance *</label>
            <input type="text" id="proj_title" required placeholder="Contoh: Pembuatan Landing Page Interaktif &amp; Integrasi Payment" />
          </div>

          <div class="form-grid-2">
            <div class="form-row">
              <label for="proj_category">Kategori Keahlian *</label>
              <select id="proj_category" required>
                <option value="IT & Pemrograman">IT &amp; Pemrograman Web</option>
                <option value="Desain & Kreatif">Desain Grafis &amp; UI/UX</option>
                <option value="Pemasaran & Konten">Pemasaran Digital &amp; Konten</option>
                <option value="Penulisan & Penerjemahan">Penulisan &amp; Penerjemahan</option>
                <option value="Video & Animasi">Video &amp; Animasi</option>
              </select>
            </div>

            <div class="form-row">
              <label for="proj_duration">Estimasi Durasi Proyek *</label>
              <select id="proj_duration" required>
                <option value="1 Minggu">1 Minggu (Cepat)</option>
                <option value="2 Minggu" selected>2 Minggu</option>
                <option value="1 Bulan">1 Bulan</option>
                <option value="2 - 3 Bulan">2 - 3 Bulan</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-row">
              <label for="proj_budget">Anggaran / Fee Proyek (Rp) *</label>
              <input type="text" id="proj_budget" required placeholder="Contoh: Rp 7.500.000" />
              <span style="font-size: 0.72rem; color: var(--text-muted);">Pembayaran honor ditransfer langsung oleh perusahaan ke freelancer.</span>
            </div>

            <div class="form-row">
              <label for="proj_deadline">Batas Waktu Lamaran *</label>
              <input type="date" id="proj_deadline" required value="2026-09-25" />
            </div>
          </div>

          <div class="form-row">
            <label for="proj_skills">Keahlian yang Dibutuhkan (Pisahkan dengan koma)</label>
            <input type="text" id="proj_skills" placeholder="Contoh: Figma, React.js, Tailwind CSS, API Integration" />
          </div>

          <div class="form-row">
            <label for="proj_desc">Deskripsi Pekerjaan &amp; Target Deliverables *</label>
            <textarea id="proj_desc" rows="4" required placeholder="Jelaskan ruang lingkup proyek, target hasil pengerjaan, dan kriteria freelancer yang Anda cari..."></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closePostProjectModal()">Batal</button>
          <button type="submit" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Publikasikan Lowongan Proyek
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- TOAST CONTAINER -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- FOOTER -->
  <footer class="kemnaker-footer">
    <div class="footer-inner">
      <div>
        <strong>Kementerian Ketenagakerjaan Republik Indonesia</strong> &bull; KarirHub Gig Workers Prototype
      </div>
      <div>
        Layanan resmi informasi ketenagakerjaan dan ekosistem pekerja lepas Indonesia
      </div>
    </div>
  </footer>

  <!-- SCRIPT LOGIC -->
  <script>
    // In-memory state for initial demo projects
    let projectVacancies = [
      {
        id: "GIG-2026-09-001",
        title: "Redesign UI/UX Dashboard Prototype KarirHub",
        category: "Desain & Kreatif",
        status: "active",
        statusLabel: "Tayang Aktif",
        budget: "Rp 8.500.000",
        duration: "3 Minggu",
        applicantsCount: 2,
        skills: ["Figma", "UI/UX", "Design System", "Prototyping"],
        desc: "Dibutuhkan UI/UX designer berpengalaman untuk merancang prototype interaktif dashboard KarirHub yang responsif dan modern.",
        deadline: "20 Sep 2026"
      },
      {
        id: "GIG-2026-09-002",
        title: "Integrasi REST API Modul Notifikasi SMS & WhatsApp",
        category: "IT & Pemrograman",
        status: "active",
        statusLabel: "Tayang Aktif",
        budget: "Rp 6.000.000",
        duration: "2 Minggu",
        applicantsCount: 2,
        skills: ["PHP", "REST API", "Webhook", "MySQL"],
        desc: "Mengembangkan endpoint webhook dan mengintegrasikan provider SMS gateway serta WA notification service ke core sistem.",
        deadline: "18 Sep 2026"
      },
      {
        id: "GIG-2026-09-003",
        title: "Kampanye Media Sosial & Copywriting Peluncuran Fitur",
        category: "Pemasaran & Konten",
        status: "review",
        statusLabel: "Menunggu Verifikasi",
        budget: "Rp 4.500.000",
        duration: "1 Bulan",
        applicantsCount: 2,
        skills: ["Copywriting", "Social Media", "Content Plan"],
        desc: "Menyusun strategi konten dan copywriting peluncuran fitur Gig Worker untuk meningkatkan awareness para pekerja lepas.",
        deadline: "28 Sep 2026"
      }
    ];

    // Render Project Vacancies
    function renderProjectCards() {
      const mainContainer = document.getElementById('main-vacancies-grid');
      const overviewContainer = document.getElementById('overview-project-grid');
      
      let html = '';
      projectVacancies.forEach(proj => {
        const statusClass = proj.status === 'active' ? 'active' : (proj.status === 'review' ? 'review' : 'closed');
        const cardBorderClass = proj.status === 'review' ? 'status-review' : (proj.status === 'closed' ? 'status-closed' : '');
        
        const skillTagsHtml = proj.skills.map(s => `<span class="skill-tag-item">${s}</span>`).join('');

        html += `
          <div class="project-card ${cardBorderClass}">
            <div>
              <div class="project-top-meta">
                <span class="project-category-tag">${proj.category}</span>
                <span class="badge-status ${statusClass}">
                  <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block;"></span>
                  ${proj.statusLabel}
                </span>
              </div>
              <h3 class="project-card-title">${proj.title}</h3>
              <p class="project-card-desc">${proj.desc}</p>
              <div class="project-skill-tags">${skillTagsHtml}</div>
            </div>

            <div class="project-card-metrics">
              <div class="proj-metric-item">
                <span class="proj-metric-lbl">Anggaran Proyek</span>
                <span class="proj-metric-val" style="color: var(--primary-blue);">${proj.budget}</span>
              </div>
              <div class="proj-metric-item">
                <span class="proj-metric-lbl">Durasi</span>
                <span class="proj-metric-val">${proj.duration}</span>
              </div>
            </div>

            <div class="project-card-footer">
              <span class="applicants-count-badge" onclick="switchMainTab('applicants')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                ${proj.applicantsCount} Pelamar
              </span>
              <div style="display: flex; gap: 6px;">
                <button class="btn-action-sm" onclick="switchMainTab('applicants')">Kelola Pelamar</button>
              </div>
            </div>
          </div>
        `;
      });

      if (mainContainer) mainContainer.innerHTML = html;
      if (overviewContainer) overviewContainer.innerHTML = html;

      // Update counters
      updateCounts();
    }

    function updateCounts() {
      const total = projectVacancies.length;
      const active = projectVacancies.filter(p => p.status === 'active').length;
      const review = projectVacancies.filter(p => p.status === 'review').length;

      document.getElementById('badge-vacancies-count').innerText = total;
      document.getElementById('hero-stat-vacancies').innerText = total;
      document.getElementById('card-stat-vacancies').innerText = total;

      const countAll = document.getElementById('count-filter-all');
      if (countAll) countAll.innerText = total;
      const countAct = document.getElementById('count-filter-active');
      if (countAct) countAct.innerText = active;
      const countRev = document.getElementById('count-filter-review');
      if (countRev) countRev.innerText = review;
    }

    // Switch Tabs
    function switchMainTab(tabName, clickedBtn) {
      document.querySelectorAll('.tab-view-content').forEach(el => el.classList.remove('active-view'));
      document.querySelectorAll('.nav-tab-btn').forEach(el => el.classList.remove('active'));

      const targetView = document.getElementById('tab-' + tabName);
      if (targetView) targetView.classList.add('active-view');

      if (clickedBtn) {
        clickedBtn.classList.add('active');
      } else {
        // Find matching header button
        const buttons = document.querySelectorAll('.nav-tab-btn');
        if (tabName === 'overview' && buttons[0]) buttons[0].classList.add('active');
        if (tabName === 'vacancies' && buttons[1]) buttons[1].classList.add('active');
        if (tabName === 'active-projects' && buttons[2]) buttons[2].classList.add('active');
        if (tabName === 'applicants' && buttons[3]) buttons[3].classList.add('active');
      }

      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Modal Handlers
    function openPostProjectModal() {
      document.getElementById('postProjectModal').classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closePostProjectModal() {
      document.getElementById('postProjectModal').classList.remove('open');
      document.body.style.overflow = 'auto';
    }

    function handleBackdropClick(e) {
      if (e.target.id === 'postProjectModal') {
        closePostProjectModal();
      }
    }

    // Handle Project Creation
    function handleCreateProject(e) {
      e.preventDefault();
      const title = document.getElementById('proj_title').value;
      const category = document.getElementById('proj_category').value;
      const duration = document.getElementById('proj_duration').value;
      const budget = document.getElementById('proj_budget').value;
      const deadline = document.getElementById('proj_deadline').value;
      const skillsRaw = document.getElementById('proj_skills').value;
      const desc = document.getElementById('proj_desc').value;

      const skills = skillsRaw ? skillsRaw.split(',').map(s => s.trim()).filter(Boolean) : ['Freelance', 'Gig'];

      const newProj = {
        id: 'GIG-2026-09-' + String(Math.floor(Math.random() * 900 + 100)),
        title: title,
        category: category,
        status: 'active',
        statusLabel: 'Tayang Aktif',
        budget: budget.startsWith('Rp') ? budget : 'Rp ' + budget,
        duration: duration,
        applicantsCount: 0,
        skills: skills,
        desc: desc,
        deadline: deadline
      };

      projectVacancies.unshift(newProj);
      renderProjectCards();
      closePostProjectModal();
      document.getElementById('newProjectForm').reset();

      showToast('🎉 Lowongan Proyek "' + title + '" berhasil dipublikasikan!');
      switchMainTab('vacancies');
    }

    // Filter Project Vacancies
    function filterProjects(status, btn) {
      document.querySelectorAll('#tab-vacancies .filter-btn-pill').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const cards = document.querySelectorAll('#main-vacancies-grid .project-card');
      projectVacancies.forEach((proj, index) => {
        const card = cards[index];
        if (!card) return;
        if (status === 'all' || proj.status === status) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Search Projects
    function searchProjects(query) {
      const q = query.toLowerCase();
      const cards = document.querySelectorAll('#main-vacancies-grid .project-card');
      projectVacancies.forEach((proj, index) => {
        const card = cards[index];
        if (!card) return;
        const match = proj.title.toLowerCase().includes(q) || 
                      proj.category.toLowerCase().includes(q) ||
                      proj.skills.some(s => s.toLowerCase().includes(q));
        card.style.display = match ? 'flex' : 'none';
      });
    }

    // Filter Applicants
    function filterApplicants(category, btn) {
      document.querySelectorAll('#tab-applicants .filter-btn-pill').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const cards = document.querySelectorAll('.applicant-card');
      let count = 0;
      cards.forEach(card => {
        const cat = card.getAttribute('data-category');
        if (category === 'all' || cat === category) {
          card.style.display = 'flex';
          count++;
        } else {
          card.style.display = 'none';
        }
      });
      document.getElementById('applicants-visible-count').innerText = count;
    }

    // Search Applicants
    function searchApplicants(query) {
      const q = query.toLowerCase();
      const cards = document.querySelectorAll('.applicant-card');
      let count = 0;
      cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        if (text.includes(q)) {
          card.style.display = 'flex';
          count++;
        } else {
          card.style.display = 'none';
        }
      });
      document.getElementById('applicants-visible-count').innerText = count;
    }

    // Copy Contact Function
    function copyContact(name, phone, email) {
      const textToCopy = `Nama: ${name}\nWhatsApp: ${phone}\nEmail: ${email}`;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(textToCopy);
      }
      showToast(`📋 Kontak ${name} berhasil disalin! (WA: ${phone} | Email: ${email})`);
    }

    // Hire Applicant Action
    function hireApplicant(name, projTitle) {
      showToast('🎉 Berhasil menandai ' + name + ' sebagai mitra terpilih! Silakan lakukan koordinasi kontrak & pembayaran langsung.');
      setTimeout(() => {
        switchMainTab('active-projects');
      }, 1200);
    }

    // Toast Notification System
    function showToast(message) {
      const container = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      toast.className = 'toast-card';
      toast.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>${message}</span>
      `;
      container.appendChild(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 3500);
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => {
      renderProjectCards();
    });
  </script>
</body>
</html>
