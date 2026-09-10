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
          <div class="hero-stat-pill"><span class="hero-stat-val"><?php echo count($vacancies); ?></span><span class="hero-stat-lbl">Lowongan Terdaftar</span></div>
          <div class="hero-stat-pill"><span class="hero-stat-val"><?php echo count($workerProfiles); ?></span><span class="hero-stat-lbl">Pelamar Masuk</span></div>
          <div class="hero-stat-pill"><span class="hero-stat-val">2</span><span class="hero-stat-lbl">Proyek Berjalan</span></div>
        </div>
      </div>
    </section>

    <section class="stats-grid">
      <a class="stat-card" href="employer-lowongan.php">
        <div class="stat-card-header"><span class="stat-label">LOWONGAN PROYEK</span></div>
        <div class="stat-number"><?php echo count($vacancies); ?></div>
        <div class="stat-caption"><span class="stat-trend-positive">2 tayang aktif</span> · 1 verifikasi · 1 revisi</div>
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
      <a class="stat-card" href="employer-riwayat-proyek.php">
        <div class="stat-card-header"><span class="stat-label">RIWAYAT PROYEK</span></div>
        <div class="stat-number">5</div>
        <div class="stat-caption">Semua status kontrak kerja</div>
      </a>
    </section>

    <div class="overview-dual-grid">
      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>Countdown Proyek Aktif</h2>
            <p>Proyek dengan sisa waktu pengerjaan terdekat</p>
          </div>
          <a class="btn-action-sm" href="employer-proyek-aktif.php">Lihat Semua Proyek</a>
        </div>

        <!-- Featured Project with Shortest Time Left -->
        <div style="margin-top:14px;padding:14px;background:linear-gradient(135deg, #eff6ff, #f0fdf4);border:1px solid #bfdbfe;border-radius:12px;position:relative;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <span style="font-size:0.7rem;font-weight:800;background:#ef4444;color:#fff;padding:3px 8px;border-radius:9999px;letter-spacing:0.5px;">🔥 TENGGAT TERDEKAT</span>
            <span style="font-size:0.75rem;color:#1e40af;font-weight:700;">Tenggat: 17 Sep 2026</span>
          </div>

          <h3 style="font-size:0.95rem;font-weight:800;color:#1e293b;margin:0 0 4px 0;">Integrasi REST API Modul Notifikasi SMS &amp; WhatsApp</h3>
          <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:12px;">Mitra Gig: <strong>Rian Ardiansyah</strong> · Backend API Developer</div>

          <div style="display:flex;gap:8px;text-align:center;" id="dash-shortest-countdown">
            <div style="background:#fff;border:1px solid #93c5fd;padding:6px 10px;border-radius:8px;flex:1;">
              <span class="c-days" style="font-size:1.2rem;font-weight:800;color:#1e40af;display:block;">07</span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Hari</span>
            </div>
            <div style="background:#fff;border:1px solid #93c5fd;padding:6px 10px;border-radius:8px;flex:1;">
              <span class="c-hours" style="font-size:1.2rem;font-weight:800;color:#1e40af;display:block;">08</span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Jam</span>
            </div>
            <div style="background:#fff;border:1px solid #93c5fd;padding:6px 10px;border-radius:8px;flex:1;">
              <span class="c-mins" style="font-size:1.2rem;font-weight:800;color:#1e40af;display:block;">15</span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Menit</span>
            </div>
            <div style="background:#fff;border:1px solid #93c5fd;padding:6px 10px;border-radius:8px;flex:1;">
              <span class="c-secs" style="font-size:1.2rem;font-weight:800;color:#ef4444;display:block;">20</span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Detik</span>
            </div>
          </div>
        </div>

        <!-- Other Active Projects -->
        <div style="margin-top:12px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div style="font-size:0.84rem;font-weight:700;color:var(--text-dark);">Redesign UI/UX Dashboard Prototype KarirHub</div>
            <div style="font-size:0.74rem;color:var(--text-muted);">Mitra: Tessa · Sisa 12 Hari 14 Jam (Tenggat: 22 Sep 2026)</div>
          </div>
          <a class="btn-action-sm" href="employer-proyek-aktif.php" style="text-decoration:none;">Pantau →</a>
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
                <div style="font-size:0.74rem;color:var(--text-muted);"><?php echo htmlspecialchars($recent['title'], ENT_QUOTES, 'UTF-8'); ?> · ★ <?php echo (int)$recent['rating']; ?></div>
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
          <p>Cuplikan proyek yang sedang Anda kelola (Klik untuk rincian detail)</p>
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
          <h3 class="project-card-title">
            <a href="employer-detail-lowongan.php?id=<?php echo urlencode($proj['id']); ?>" style="color:inherit;text-decoration:none;">
              <?php echo htmlspecialchars($proj['title'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
          </h3>
          <p class="project-card-desc"><?php echo htmlspecialchars($proj['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div class="project-card-footer">
            <a class="applicants-count-badge" href="employer-pelamar.php"><?php echo (int)$proj['applicantsCount']; ?> Pelamar</a>
            <a class="btn-action-sm" href="employer-detail-lowongan.php?id=<?php echo urlencode($proj['id']); ?>">Detail →</a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <script>
      (function startDashboardCountdown() {
        setInterval(function() {
          const container = document.getElementById('dash-shortest-countdown');
          if (!container) return;
          const secEl = container.querySelector('.c-secs');
          if (secEl) {
            let s = parseInt(secEl.innerText, 10);
            if (s > 0) {
              s--;
            } else {
              s = 59;
              const minEl = container.querySelector('.c-mins');
              if (minEl) {
                let m = parseInt(minEl.innerText, 10);
                if (m > 0) {
                  m--;
                  minEl.innerText = m < 10 ? '0' + m : String(m);
                }
              }
            }
            secEl.innerText = s < 10 ? '0' + s : String(s);
          }
        }, 1000);
      })();
    </script>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
