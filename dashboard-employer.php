<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';

$workerProfiles = gig_worker_profiles();
$vacancies = gig_project_vacancies();
$pageTitle = 'Ringkasan';
$pageKey = 'overview';
$breadcrumbCurrent = 'Ringkasan';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Ringkasan</h1>
      <button class="btn-primary-add" type="button" onclick="openPostProjectModal()">+ Pasang Proyek</button>
    </div>

    <div class="system-info-notice">
      <div class="notice-icon-text">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span><strong>Privasi pelamar:</strong> kontak WhatsApp dan email baru terbuka setelah kedua belah pihak menyetujui kerja sama.</span>
      </div>
    </div>

    <section class="hero-banner">
      <div class="hero-header">
        <div>
          <div class="hero-badge">PORTAL PEMBERI KERJA</div>
          <h2 class="hero-title">Halo, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></h2>
          <p class="hero-desc">Pantau lowongan, pelamar, dan proyek berjalan dari satu tempat.</p>
        </div>
        <div class="hero-quick-stats">
          <div class="hero-stat-pill"><span class="hero-stat-val">3</span><span class="hero-stat-lbl">Lowongan Terbuka</span></div>
          <div class="hero-stat-pill"><span class="hero-stat-val">6</span><span class="hero-stat-lbl">Pelamar Masuk</span></div>
          <div class="hero-stat-pill"><span class="hero-stat-val">2</span><span class="hero-stat-lbl">Proyek Berjalan</span></div>
        </div>
      </div>
    </section>

    <section class="stats-grid">
      <a class="stat-card" href="employer-lowongan.php">
        <div class="stat-card-header"><span class="stat-label">LOWONGAN PROYEK</span></div>
        <div class="stat-number"><?php echo count($vacancies); ?></div>
        <div class="stat-caption"><span class="stat-trend-positive">2 tayang aktif</span> · 1 verifikasi · 1 draft</div>
      </a>
      <a class="stat-card" href="employer-pelamar.php">
        <div class="stat-card-header"><span class="stat-label">TOTAL PELAMAR</span></div>
        <div class="stat-number"><?php echo count($workerProfiles); ?></div>
        <div class="stat-caption"><span class="stat-trend-positive">+4 proposal baru</span> minggu ini</div>
      </a>
      <a class="stat-card" href="employer-proyek-aktif.php">
        <div class="stat-card-header"><span class="stat-label">PROYEK AKTIF</span></div>
        <div class="stat-number">2</div>
        <div class="stat-caption">Sedang dikerjakan mitra gig</div>
      </a>
      <a class="stat-card" href="employer-pelamar.php">
        <div class="stat-card-header"><span class="stat-label">DITERIMA</span></div>
        <div class="stat-number">2</div>
        <div class="stat-caption">Kesepakatan kerja sama aktif</div>
      </a>
    </section>

    <div class="overview-dual-grid">
      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>Corong Seleksi</h2>
            <p>Progres seleksi kandidat di semua proyek</p>
          </div>
          <a class="btn-action-sm" href="employer-pelamar.php">Lihat Pelamar</a>
        </div>
        <div class="funnel-container">
          <div class="funnel-step"><span class="funnel-step-name">Proposal Masuk</span><div class="funnel-step-bar-wrap"><div class="funnel-step-bar-fill" style="width:100%"></div></div><span class="funnel-step-count">6</span></div>
          <div class="funnel-step"><span class="funnel-step-name">Review Portofolio</span><div class="funnel-step-bar-wrap"><div class="funnel-step-bar-fill" style="width:66%;background:#f59e0b"></div></div><span class="funnel-step-count">4</span></div>
          <div class="funnel-step"><span class="funnel-step-name">Kesepakatan Kerja</span><div class="funnel-step-bar-wrap"><div class="funnel-step-bar-fill" style="width:33%;background:#8b5cf6"></div></div><span class="funnel-step-count">2</span></div>
          <div class="funnel-step"><span class="funnel-step-name">Kontrak Aktif</span><div class="funnel-step-bar-wrap"><div class="funnel-step-bar-fill" style="width:33%;background:#10b981"></div></div><span class="funnel-step-count">2</span></div>
        </div>
      </section>

      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>Pelamar Terbaru</h2>
            <p>Klik untuk membuka profil akun</p>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <?php foreach (array_slice($workerProfiles, 0, 3) as $recent): ?>
          <a class="recent-applicant-row" href="worker-profile.php?id=<?php echo urlencode($recent['id']); ?>">
            <div class="recent-applicant-left">
              <div class="recent-avatar" style="background:<?php echo htmlspecialchars($recent['color'], ENT_QUOTES, 'UTF-8'); ?>">
                <img src="<?php echo htmlspecialchars($recent['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="" />
              </div>
              <div>
                <div style="font-size:0.88rem;font-weight:800;"><?php echo htmlspecialchars($recent['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div style="font-size:0.74rem;color:var(--text-muted);"><?php echo htmlspecialchars($recent['title'], ENT_QUOTES, 'UTF-8'); ?> · ★ <?php echo number_format((float)$recent['rating'], 1); ?></div>
              </div>
            </div>
            <span class="btn-action-sm">Lihat Profil</span>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <section class="white-card" style="margin-top:20px;">
      <div class="card-header-flex">
        <div class="card-title-group">
          <h2>Lowongan Terkini</h2>
          <p>Cuplikan proyek yang sedang Anda kelola</p>
        </div>
        <a class="btn-action-sm" href="employer-lowongan.php">Kelola Semua Lowongan</a>
      </div>
      <div class="project-grid">
        <?php foreach (array_slice($vacancies, 0, 3) as $proj): ?>
        <article class="project-card">
          <div class="project-top-meta">
            <span class="project-category-tag"><?php echo htmlspecialchars($proj['category'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="badge-status <?php echo $proj['status'] === 'active' ? 'active' : 'review'; ?>"><?php echo htmlspecialchars($proj['statusLabel'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <h3 class="project-card-title"><?php echo htmlspecialchars($proj['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p class="project-card-desc"><?php echo htmlspecialchars($proj['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div class="project-card-footer">
            <a class="applicants-count-badge" href="employer-pelamar.php"><?php echo (int)$proj['applicantsCount']; ?> Pelamar</a>
            <a class="btn-action-sm" href="employer-lowongan.php">Kelola</a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
