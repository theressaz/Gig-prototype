<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$siapkerja = gig_get_siapkerja_profile($username);
$isRegistered = gig_is_worker_registered($username);
$workerRegData = $isRegistered ? gig_get_worker_registration($username) : null;
$profileId = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode(' ', $username)[0] ?? $username));

$pageTitle = 'Ringkasan';
$pageKey = 'overview';
$breadcrumbCurrent = 'Ringkasan';
require __DIR__ . '/includes/worker-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Ringkasan</h1>
      <a class="btn-primary-add" href="worker-bursa.php">Cari Tugas</a>
    </div>

    <section class="hero-banner">
      <div class="hero-header">
        <div>
          <div class="hero-badge">PORTAL GIG WORKER</div>
          <h2 class="hero-title">Halo, <?php echo htmlspecialchars($siapkerja['nama'] ?? $username, ENT_QUOTES, 'UTF-8'); ?></h2>
          <p class="hero-desc">Pantau tugas, profil, dan aktivitas proyek dari satu tempat.</p>
        </div>
        <div class="hero-quick-stats">
          <div class="hero-stat-pill"><span class="hero-stat-val">14</span><span class="hero-stat-lbl">Tugas Selesai</span></div>
          <div class="hero-stat-pill"><span class="hero-stat-val">2</span><span class="hero-stat-lbl">Tugas Aktif</span></div>
          <div class="hero-stat-pill"><span class="hero-stat-val">3</span><span class="hero-stat-lbl">Mitra Aktif</span></div>
        </div>
      </div>
    </section>

    <?php if (!$isRegistered): ?>
      <div class="reg-banner warn">
        <div class="reg-banner-left">
          <div class="reg-icon" style="background:var(--primary-blue);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="17" y1="11" x2="23" y2="11"/></svg>
          </div>
          <div>
            <div class="chip" style="margin-bottom:6px;">AKUN SIAPKERJA TERKONEKSI</div>
            <h3 style="font-size:1.05rem;font-weight:800;">Anda belum terdaftar sebagai Gig Worker</h3>
            <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Lengkapi profil keahlian, portofolio, dan kontak agar pemberi kerja bisa meninjau akun Anda.</p>
          </div>
        </div>
        <a class="btn-primary-add" href="worker-register.php">Daftar Sebagai Gig Worker</a>
      </div>
    <?php else: ?>
      <div class="reg-banner ok">
        <div class="reg-banner-left">
          <div class="reg-icon" style="background:#10b981;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div>
            <h3 style="font-size:1.05rem;font-weight:800;color:#065f46;">Profil Gig Worker aktif</h3>
            <p style="font-size:0.84rem;color:#047857;margin-top:4px;">
              <strong>Bidang:</strong> <?php echo htmlspecialchars($workerRegData['bidang_keahlian'] ?? 'Gig Professional', ENT_QUOTES, 'UTF-8'); ?>
            </p>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <a class="btn-action-sm" href="worker-register.php">Edit Pendaftaran</a>
          <a class="btn-primary-add" href="worker-profile.php?id=<?php echo urlencode($profileId); ?>">Lihat Profil</a>
        </div>
      </div>
    <?php endif; ?>

    <div class="notice-bar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Sesi login: <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong>. Kontak Anda tidak dibagikan ke pemberi kerja sebelum kesepakatan kerja sama.</span>
    </div>

    <section class="stats-grid">
      <a class="stat-card" href="worker-tugas.php">
        <div class="stat-card-header"><span class="stat-label">TUGAS SELESAI</span></div>
        <div class="stat-number">14</div>
        <div class="stat-caption">Total tugas diselesaikan</div>
      </a>
      <a class="stat-card" href="worker-tugas.php">
        <div class="stat-card-header"><span class="stat-label">TUGAS AKTIF</span></div>
        <div class="stat-number">2</div>
        <div class="stat-caption">Sedang dikerjakan</div>
      </a>
      <a class="stat-card" href="worker-bursa.php">
        <div class="stat-card-header"><span class="stat-label">LOWONGAN TERBUKA</span></div>
        <div class="stat-number">3</div>
        <div class="stat-caption">Siap dilamar di bursa tugas</div>
      </a>
      <a class="stat-card" href="worker-ulasan.php">
        <div class="stat-card-header"><span class="stat-label">RATING</span></div>
        <div class="stat-number">4.9</div>
        <div class="stat-caption">Dari ulasan pemberi kerja</div>
      </a>
    </section>

    <div class="middle-grid">
      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>Distribusi Status Tugas</h2>
            <p>Perbandingan terhadap total tugas</p>
          </div>
        </div>
        <div class="dist-item">
          <div class="dist-header"><span>Tugas Aktif</span><span>2 · 14%</span></div>
          <div class="progress-bar-bg"><div class="progress-bar-fill blue" style="width:14%"></div></div>
        </div>
        <div class="dist-item">
          <div class="dist-header"><span>Selesai</span><span style="color:#16a34a;">12 · 86%</span></div>
          <div class="progress-bar-bg"><div class="progress-bar-fill green" style="width:86%"></div></div>
        </div>
        <div class="dist-item">
          <div class="dist-header"><span>Dibatalkan</span><span>0 · 0%</span></div>
          <div class="progress-bar-bg"><div class="progress-bar-fill amber" style="width:0%"></div></div>
        </div>
      </section>

      <section class="white-card">
        <div class="card-header-flex">
          <div class="card-title-group">
            <h2>Akses Cepat</h2>
            <p>Fitur yang paling sering digunakan</p>
          </div>
        </div>
        <div class="quick-access-grid">
          <a href="worker-bursa.php" class="quick-access-tile">
            <div class="tile-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/></svg></div>
            <div>
              <div style="font-size:0.85rem;font-weight:700;">Bursa Tugas</div>
              <div style="font-size:0.72rem;color:var(--text-muted);">Cari lowongan proyek</div>
            </div>
          </a>
          <a href="worker-tugas.php" class="quick-access-tile">
            <div class="tile-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg></div>
            <div>
              <div style="font-size:0.85rem;font-weight:700;">Tugas Aktif</div>
              <div style="font-size:0.72rem;color:var(--text-muted);">Pantau pengerjaan</div>
            </div>
          </a>
          <a href="worker-register.php" class="quick-access-tile">
            <div class="tile-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <div>
              <div style="font-size:0.85rem;font-weight:700;">Profil</div>
              <div style="font-size:0.72rem;color:var(--text-muted);">Lengkapi akun Anda</div>
            </div>
          </a>
          <a href="worker-ulasan.php" class="quick-access-tile">
            <div class="tile-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
            <div>
              <div style="font-size:0.85rem;font-weight:700;">Ulasan Mitra</div>
              <div style="font-size:0.72rem;color:var(--text-muted);">Lihat rating Anda</div>
            </div>
          </a>
        </div>
      </section>
    </div>

    <section class="white-card" style="margin-top:20px;">
      <div class="card-header-flex">
        <div class="card-title-group">
          <h2>Aktivitas Terbaru</h2>
          <p>Riwayat aktivitas akun</p>
        </div>
        <a class="btn-action-sm" href="worker-tugas.php">Lihat Semua</a>
      </div>
      <div class="activity-list">
        <div class="activity-row">
          <div class="activity-left">
            <div class="activity-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg></div>
            <div>
              <div class="activity-title">Proyek aktif — Redesign UI/UX Dashboard KarirHub</div>
              <div class="activity-date">9 September 2026, 11:20 WIB</div>
            </div>
          </div>
          <div class="activity-right">
            <span class="activity-code">CTR-GIG-2026-0811</span>
            <span class="status-badge blue">Berjalan</span>
          </div>
        </div>
        <div class="activity-row">
          <div class="activity-left">
            <div class="activity-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg></div>
            <div>
              <div class="activity-title">Selesai — Portal Rekrutmen BUMN</div>
              <div class="activity-date">24 Juni 2026, 14:10 WIB</div>
            </div>
          </div>
          <div class="activity-right">
            <span class="activity-code">GIG-2026-06-00087</span>
            <span class="status-badge green">Selesai</span>
          </div>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
