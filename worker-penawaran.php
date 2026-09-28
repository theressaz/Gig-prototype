<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/project-offers.php';
require_once __DIR__ . '/includes/project-applications.php';
require_once __DIR__ . '/includes/project-history.php';

$flashMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_action'])) {
    $appId  = trim((string)($_POST['app_id'] ?? ''));
    $action = trim((string)($_POST['confirm_action'] ?? ''));
    if ($appId !== '' && in_array($action, ['confirm', 'decline'], true)) {
        $res = gig_worker_confirm_application($appId, $action, $username);
        if (!empty($res['ok'])) {
            $flashMsg = ($action === 'confirm')
                ? 'Selamat! Anda resmi direkrut untuk proyek ini. Pemberi kerja telah diberitahukan.'
                : 'Penawaran proyek telah ditolak. Pemberi kerja telah diberitahukan.';
        }
    }
}

gig_seed_demo_offers_if_needed($username);
$offers = gig_offers_for_worker($username);
$workerApps = gig_get_applications_for_worker($username);
$projectHistory = gig_get_history_for_worker($username);

// Banner, category label & avatar helpers are loaded from includes/project-vacancies.php


$pageTitle = 'Penawaran Proyek';
$pageKey = 'penawaran';
$breadcrumbCurrent = 'Penawaran Proyek';
require __DIR__ . '/includes/worker-layout-start.php';
?>

<style>
.penawaran-page {
  max-width: 1400px;
  margin: 0 auto;
}

.penawaran-notice {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1e40af;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 0.86rem;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 22px;
}

.penawaran-count-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  font-size: 0.88rem;
  color: #64748b;
}

.penawaran-list-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.penawaran-card-item {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}

.penawaran-card-item:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
  border-color: #cbd5e1;
}

.penawaran-card-header {
  margin-bottom: 14px;
}

.penawaran-title-group {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 6px;
}

.penawaran-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  line-height: 1.35;
}

.penawaran-title a {
  color: inherit;
  text-decoration: none;
}

.penawaran-title a:hover {
  color: #1d4ed8;
}

.penawaran-badge-pill {
  display: inline-block;
  padding: 3px 12px;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
}

.penawaran-badge-pill.blue {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.penawaran-badge-pill.amber {
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
}

.penawaran-badge-pill.green {
  background: #d1fae5;
  color: #047857;
  border: 1px solid #a7f3d0;
}

.penawaran-badge-pill.gray {
  background: #f8fafc;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.penawaran-meta-sub {
  font-size: 0.82rem;
  color: #64748b;
}

.penawaran-meta-sub strong {
  color: #334155;
}

.penawaran-inner-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.employer-info-group {
  display: flex;
  align-items: center;
  gap: 14px;
}

.employer-avatar-circle {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: #1d4ed8;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 800;
  flex-shrink: 0;
}

.employer-name-row {
  font-size: 0.94rem;
  font-weight: 800;
  color: #0f172a;
  display: flex;
  align-items: center;
  gap: 8px;
}

.employer-rating-tag {
  font-size: 0.74rem;
  font-weight: 700;
  color: #d97706;
  background: #fef3c7;
  padding: 2px 6px;
  border-radius: 6px;
}

.employer-sub-info {
  font-size: 0.78rem;
  color: #64748b;
  margin-top: 2px;
}

.employer-verified-check {
  font-size: 0.74rem;
  color: #059669;
  font-weight: 700;
  margin-top: 3px;
}

.offer-skills-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.skill-pill-tag {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #334155;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 8px;
}

.skill-pill-tag.more {
  background: #e2e8f0;
  color: #475569;
  font-weight: 700;
}

.penawaran-card-footer {
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.footer-notice-label {
  font-size: 0.82rem;
  color: #64748b;
}

.footer-actions-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-act-green {
  background: #2563eb;
  color: #ffffff;
  font-weight: 700;
  font-size: 0.86rem;
  padding: 9px 18px;
  border-radius: 10px;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-act-green:hover {
  background: #1d4ed8;
  color: #ffffff;
}

.btn-act-outline {
  background: #ffffff;
  color: #334155;
  border: 1px solid #cbd5e1;
  font-weight: 700;
  font-size: 0.86rem;
  padding: 8px 16px;
  border-radius: 10px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-act-outline:hover {
  background: #f8fafc;
  color: #1d4ed8;
  border-color: #93c5fd;
}

.btn-act-danger {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecdd3;
  font-weight: 700;
  font-size: 0.82rem;
  padding: 8px 14px;
  border-radius: 10px;
  cursor: pointer;
}

.btn-act-danger:hover {
  background: #fee2e2;
}

.offer-empty-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 48px 20px;
  text-align: center;
  color: #64748b;
}

.offer-empty-card h3 {
  font-size: 1.1rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 6px;
}

.offer-empty-card p {
  font-size: 0.9rem;
  margin-bottom: 16px;
}

.flash-ok {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 0.86rem;
  margin-bottom: 18px;
  font-weight: 700;
}

@media (max-width: 768px) {
  .penawaran-inner-box {
    flex-direction: column;
    align-items: flex-start;
  }
  .penawaran-card-footer {
    flex-direction: column;
    align-items: flex-start;
  }
  .footer-actions-group {
    width: 100%;
    flex-direction: column;
  }
  .footer-actions-group .btn-act-green,
  .footer-actions-group .btn-act-outline,
  .footer-actions-group .btn-act-danger {
    width: 100%;
    justify-content: center;
  }
}

/* ─── PROJECT HISTORY SECTION ─── */
.ph-section {
  margin-top: 40px;
}

.ph-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 10px;
}

.ph-section-title {
  font-size: 1.1rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.ph-section-count {
  font-size: 0.82rem;
  color: #64748b;
}

.ph-view-all-link {
  font-size: 0.82rem;
  font-weight: 700;
  color: #1d4ed8;
  text-decoration: none;
}

.ph-view-all-link:hover {
  text-decoration: underline;
}

.ph-card-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.ph-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px 22px;
  transition: box-shadow 0.15s ease, border-color 0.15s ease;
  position: relative;
  overflow: hidden;
}

.ph-card:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
  border-color: #cbd5e1;
}

.ph-card.ongoing {
  border-color: #93c5fd;
  background: linear-gradient(135deg, #eff6ff 0%, #ffffff 60%);
}

.ph-card.ongoing::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, #3b82f6, #6366f1);
  border-radius: 14px 0 0 14px;
}

.ph-card.completed {
  border-color: #a7f3d0;
}

.ph-card.completed::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: #10b981;
  border-radius: 14px 0 0 14px;
}

.ph-card.cancelled::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: #f87171;
  border-radius: 14px 0 0 14px;
}

.ph-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.ph-card-title-group {
  flex: 1;
  min-width: 0;
}

.ph-card-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 4px;
}

.ph-title {
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  line-height: 1.3;
}

.ph-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px;
  border-radius: 9999px;
  font-size: 0.72rem;
  font-weight: 700;
  white-space: nowrap;
}

.ph-status-pill.ongoing {
  background: #dbeafe;
  color: #1d4ed8;
  border: 1px solid #93c5fd;
  animation: ph-pulse 2s infinite;
}

@keyframes ph-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.ph-status-pill.completed {
  background: #d1fae5;
  color: #047857;
  border: 1px solid #6ee7b7;
}

.ph-status-pill.cancelled {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

.ph-card-meta {
  font-size: 0.78rem;
  color: #64748b;
}

.ph-card-budget {
  text-align: right;
  flex-shrink: 0;
}

.ph-budget-label {
  font-size: 0.72rem;
  color: #94a3b8;
  margin-bottom: 2px;
}

.ph-budget-value {
  font-size: 1.05rem;
  font-weight: 800;
  color: #1d4ed8;
}

.ph-card-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px 20px;
  padding-top: 12px;
  border-top: 1px solid #f1f5f9;
}

.ph-field-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 3px;
}

.ph-field-value {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.4;
}

.ph-summary-full {
  grid-column: 1 / -1;
}

.ph-rating-box {
  grid-column: 1 / -1;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  padding: 10px 14px;
}

.ph-rating-stars {
  font-size: 0.95rem;
  color: #f59e0b;
  font-weight: 800;
}

.ph-rating-comment {
  font-size: 0.82rem;
  color: #1e293b;
  font-style: italic;
  margin-top: 4px;
}

.ph-ongoing-badge {
  grid-column: 1 / -1;
  background: #eff6ff;
  border: 1px dashed #93c5fd;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.82rem;
  color: #1d4ed8;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.ph-cancelled-box {
  grid-column: 1 / -1;
  background: #fff1f2;
  border: 1px solid #fecdd3;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.8rem;
  color: #9f1239;
}

@media (max-width: 600px) {
  .ph-card-body {
    grid-template-columns: 1fr;
  }
  .ph-summary-full, .ph-rating-box, .ph-ongoing-badge, .ph-cancelled-box {
    grid-column: 1;
  }
}
</style>

<div class="penawaran-page">
  <div class="page-toolbar">
    <h1>Penawaran Proyek</h1>
  </div>

  <?php if ($flashMsg !== ''): ?>
    <div class="flash-ok"><?php echo htmlspecialchars($flashMsg, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <div class="penawaran-notice">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Pemberi kerja dapat menawarkan lowongan yang sudah tayang. Klik judul atau <strong>Detail Proyek</strong> untuk membuka informasi lengkapnya.</span>
  </div>

  <?php if (count($offers) === 0): ?>
    <div class="offer-empty-card">
      <h3>Belum ada penawaran</h3>
      <p>Ketika pemberi kerja menawarkan proyek yang sudah diposting, tawaran itu akan muncul di halaman ini.</p>
      <a class="btn-act-outline" href="worker-bursa.php" style="margin:0 auto;">Cari Proyek Sendiri</a>
    </div>
  <?php else: ?>
    <div class="penawaran-count-row">
      <div>Menampilkan <strong><?php echo count($offers); ?></strong> penawaran proyek</div>
    </div>
    
    <div class="penawaran-list-container">
      <?php foreach ($offers as $idx => $offer):
          $detailUrl = 'worker-project-detail.php?id=' . urlencode((string)$offer['detail_id']) . '&from=penawaran';
          $created = strtotime((string)($offer['created_at'] ?? ''));
          $createdLabel = $created ? date('d M Y', $created) : 'Baru saja';
          $skills = is_array($offer['skills'] ?? null) ? $offer['skills'] : [];
          $skillsToShow = array_slice($skills, 0, 4);
          $remaining = count($skills) - count($skillsToShow);
          $badgeLabel = getCategoryShortLabel((string)$offer['category']);

          $appMatch = null;
          foreach ($workerApps as $wa) {
              if (strtolower((string)($wa['vacancy_id'] ?? '')) === strtolower((string)$offer['detail_id'])) {
                  $appMatch = $wa;
                  break;
              }
          }
          $appId = (string)($appMatch['id'] ?? ('APP-' . $offer['detail_id']));
          $appStatus = (string)($appMatch['status'] ?? 'pending');
      ?>
        <article class="penawaran-card-item">
          <!-- CARD HEADER -->
          <div class="penawaran-card-header">
            <div class="penawaran-title-group">
              <h3 class="penawaran-title">
                <a href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars((string)$offer['project_title'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </h3>

              <?php if (gig_is_hired_status($appStatus)): ?>
                <span class="penawaran-badge-pill green">✓ Resmi Direkrut · Proyek Aktif</span>
              <?php elseif ($appStatus === 'declined_by_worker'): ?>
                <span class="penawaran-badge-pill gray">✕ Penawaran Ditolak</span>
              <?php else: ?>
                <span class="penawaran-badge-pill blue">📩 Menunggu Tanggapan</span>
              <?php endif; ?>
            </div>

            <div class="penawaran-meta-sub">
              Kategori: <strong><?php echo htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></strong> &bull;
              Durasi: <strong><?php echo htmlspecialchars((string)$offer['duration'], ENT_QUOTES, 'UTF-8'); ?></strong> &bull;
              Gaji Proyek: <strong style="color:#1d4ed8;font-weight:800;"><?php echo htmlspecialchars((string)$offer['budget'], ENT_QUOTES, 'UTF-8'); ?></strong> &bull;
              Ditawarkan: <strong><?php echo htmlspecialchars($createdLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>

          <!-- MIDDLE INNER BOX -->
          <div class="penawaran-inner-box">
            <div class="employer-info-group">
              <div class="employer-avatar-circle">🏢</div>
              <div>
                <div class="employer-name-row">
                  <span><?php echo htmlspecialchars((string)$offer['employer_display'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="employer-rating-tag">★ 4.9</span>
                </div>
                <div class="employer-sub-info">Pemberi Kerja Verifikasi &bull; KarirHub Partner</div>
                <div class="employer-verified-check">&check; Penawaran resmi dikirimkan ke profil Anda</div>
              </div>
            </div>

            <div class="offer-skills-list">
              <?php foreach ($skillsToShow as $skill): ?>
                <span class="skill-pill-tag"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
              <?php if ($remaining > 0): ?>
                <span class="skill-pill-tag more">+<?php echo $remaining; ?></span>
              <?php endif; ?>
            </div>
          </div>

          <!-- CARD FOOTER ACTIONS -->
          <div class="penawaran-card-footer">
            <div class="footer-notice-label">
              <?php if (gig_is_hired_status($appStatus)): ?>
                Proyek ini sudah resmi aktif dan dapat dipantau di Tugas Aktif.
              <?php elseif ($appStatus === 'declined_by_worker'): ?>
                Anda telah menolak penawaran proyek ini.
              <?php else: ?>
                Penawaran dikirimkan pemberi kerja. Buka detail proyek untuk meninjau kualifikasi.
              <?php endif; ?>
            </div>

            <div class="footer-actions-group">
              <?php if (gig_is_hired_status($appStatus)): ?>
                <a class="btn-act-green" href="worker-tugas.php">Buka di Proyek Aktif &rarr;</a>
              <?php elseif ($appStatus === 'declined_by_worker'): ?>
                <a class="btn-act-outline" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">Lihat Detail Proyek</a>
              <?php else: ?>
                <form method="post" action="" style="display:inline-flex;gap:8px;align-items:center;margin:0;">
                  <input type="hidden" name="app_id" value="<?php echo htmlspecialchars($appId, ENT_QUOTES, 'UTF-8'); ?>" />
                  <button type="submit" name="confirm_action" value="confirm" class="btn-act-green">Terima Penawaran</button>
                  <a class="btn-act-outline" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">Profil &amp; Detail Proyek</a>
                  <button type="submit" name="confirm_action" value="decline" class="btn-act-danger" onclick="return confirm('Tolak penawaran proyek ini?')">Tolak Penawaran</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($projectHistory)): ?>
  <!-- ═══════ PROJECT HISTORY SECTION ═══════ -->
  <section class="ph-section" aria-label="Riwayat Proyek">
    <div class="ph-section-header">
      <h2 class="ph-section-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        Riwayat Proyek
        <span class="ph-section-count"><?php echo count($projectHistory); ?> proyek</span>
      </h2>
      <a class="ph-view-all-link" href="worker-riwayat.php">Lihat semua &rarr;</a>
    </div>

    <div class="ph-card-list">
      <?php foreach ($projectHistory as $ph): ?>
      <?php
        $phStatus = $ph['statusCode'] ?? 'completed';
        $stars = '';
        if (!empty($ph['ratingGiven'])) {
            $stars = str_repeat('★', (int)$ph['ratingGiven']) . str_repeat('☆', 5 - (int)$ph['ratingGiven']);
        }
      ?>
      <div class="ph-card <?php echo htmlspecialchars($phStatus, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="ph-card-top">
          <div class="ph-card-title-group">
            <div class="ph-card-title-row">
              <h3 class="ph-title"><?php echo htmlspecialchars($ph['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <?php if ($phStatus === 'ongoing'): ?>
                <span class="ph-status-pill ongoing">⚡ Sedang Berjalan</span>
              <?php elseif ($phStatus === 'completed'): ?>
                <span class="ph-status-pill completed">✓ Selesai</span>
              <?php else: ?>
                <span class="ph-status-pill cancelled">✕ Tidak Selesai</span>
              <?php endif; ?>
            </div>
            <div class="ph-card-meta">
              No. Kontrak: <strong><?php echo htmlspecialchars($ph['id'], ENT_QUOTES, 'UTF-8'); ?></strong>
              &bull; <?php echo htmlspecialchars($ph['startDate'], ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($ph['endDate'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          </div>
          <div class="ph-card-budget">
            <div class="ph-budget-label">Nilai Kontrak</div>
            <div class="ph-budget-value"><?php echo htmlspecialchars($ph['budget'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
        </div>

        <div class="ph-card-body">
          <div>
            <div class="ph-field-label">Pemberi Kerja</div>
            <div class="ph-field-value"><?php echo htmlspecialchars($ph['employer'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
          <div>
            <div class="ph-field-label">Durasi</div>
            <div class="ph-field-value"><?php echo htmlspecialchars($ph['duration'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
          <div class="ph-summary-full">
            <div class="ph-field-label">Ringkasan Pekerjaan</div>
            <div class="ph-field-value" style="font-weight:400;color:#475569;"><?php echo htmlspecialchars($ph['summary'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
          <?php if ($phStatus === 'ongoing'): ?>
            <div class="ph-ongoing-badge">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Proyek ini masih aktif berjalan. Ulasan akan tersedia setelah proyek selesai.
            </div>
          <?php elseif ($phStatus === 'completed' && !empty($ph['ratingGiven'])): ?>
            <div class="ph-rating-box">
              <div class="ph-rating-stars"><?php echo $stars; ?> <strong style="color:#065f46;font-size:0.84rem;"><?php echo (int)$ph['ratingGiven']; ?> / 5</strong></div>
              <?php if (!empty($ph['reviewGiven'])): ?>
                <div class="ph-rating-comment">&ldquo;<?php echo htmlspecialchars((string)$ph['reviewGiven'], ENT_QUOTES, 'UTF-8'); ?>&rdquo;</div>
              <?php endif; ?>
            </div>
          <?php elseif ($phStatus === 'cancelled'): ?>
            <div class="ph-cancelled-box">Kontrak dihentikan. Proyek ini tidak dilanjutkan.</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>

