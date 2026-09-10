<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$workerProfiles = gig_worker_profiles();
$searchQ = trim((string)($_GET['q'] ?? ''));
$pageTitle = 'Pelamar Proyek';
$pageKey = 'pelamar';
$breadcrumbCurrent = 'Pelamar';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <div>
        <h1>Pelamar Proyek</h1>
        <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Kontak tidak ditampilkan sampai kedua belah pihak menyetujui kerja sama.</p>
      </div>
      <div style="font-size:0.82rem;color:var(--text-muted);">
        Menampilkan <strong id="applicants-visible-count"><?php echo count($workerProfiles); ?></strong> pelamar
      </div>
    </div>

    <div class="privacy-lock-note">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Klik nama atau <strong>Lihat Profil</strong> untuk keterampilan, pengalaman, portofolio, dan ulasan.
    </div>

    <div class="toolbar-filter">
      <button class="filter-btn-pill active" type="button" onclick="filterApplicants('all', this)">Semua Pelamar (<?php echo count($workerProfiles); ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterApplicants('ui-ux', this)">UI/UX (2)</button>
      <button class="filter-btn-pill" type="button" onclick="filterApplicants('backend', this)">Backend &amp; API (2)</button>
      <button class="filter-btn-pill" type="button" onclick="filterApplicants('marketing', this)">Pemasaran (2)</button>
      <div class="search-input-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="search-applicant-input" placeholder="Cari nama atau keterampilan..." value="<?php echo htmlspecialchars($searchQ, ENT_QUOTES, 'UTF-8'); ?>" onkeyup="searchApplicants(this.value)" />
      </div>
    </div>

    <div class="applicants-grid">
      <?php foreach ($workerProfiles as $applicant): ?>
      <article class="applicant-card" data-category="<?php echo htmlspecialchars($applicant['category'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="applicant-left-info">
          <a class="applicant-avatar" href="worker-profile.php?id=<?php echo urlencode($applicant['id']); ?>" style="background:<?php echo htmlspecialchars($applicant['color'], ENT_QUOTES, 'UTF-8'); ?>;">
            <img src="<?php echo htmlspecialchars($applicant['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($applicant['name'], ENT_QUOTES, 'UTF-8'); ?>" />
            <?php if (!empty($applicant['verified'])): ?><span class="verified-icon-badge">✓</span><?php endif; ?>
          </a>
          <div class="applicant-details">
            <div class="applicant-name-row">
              <a class="applicant-name" href="worker-profile.php?id=<?php echo urlencode($applicant['id']); ?>"><?php echo htmlspecialchars($applicant['name'], ENT_QUOTES, 'UTF-8'); ?></a>
              <span class="fl-rating-badge">★ <?php echo number_format((float)$applicant['rating'], 1); ?> (<?php echo (int)$applicant['reviews_count']; ?> ulasan)</span>
            </div>
            <div class="applicant-applied-role">Melamar: <strong><?php echo htmlspecialchars($applicant['applied_project'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
            <div class="project-skill-tags">
              <?php foreach (array_slice($applicant['skills'], 0, 3) as $skillTag): ?>
                <span class="skill-tag-item"><?php echo htmlspecialchars((string)$skillTag, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
            </div>
            <div class="applicant-lock-hint">Kontak dikunci hingga kesepakatan kerja sama</div>
          </div>
        </div>
        <div class="applicant-center-meta">
          <span style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Penawaran</span>
          <span class="bid-amount"><?php echo htmlspecialchars($applicant['bid'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="bid-time"><?php echo htmlspecialchars($applicant['eta'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="applicant-right-actions">
          <a class="btn-outline-blue" href="worker-profile.php?id=<?php echo urlencode($applicant['id']); ?>">Lihat Profil</a>
          <button type="button" class="btn-hire" onclick="hireApplicant('<?php echo htmlspecialchars($applicant['name'], ENT_QUOTES, 'UTF-8'); ?>')">Undang Kerja Sama</button>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($searchQ !== ''): ?>
    <script>document.addEventListener('DOMContentLoaded', function () { searchApplicants(<?php echo json_encode($searchQ); ?>); });</script>
    <?php endif; ?>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
