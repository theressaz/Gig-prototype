<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-applications.php';

$flashMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_action'])) {
    $appId = trim((string)($_POST['app_id'] ?? ''));
    $act   = trim((string)($_POST['app_action'] ?? ''));
    if ($appId !== '' && in_array($act, ['accept', 'reject'], true)) {
        $res = gig_employer_respond_application($appId, $act, $username);
        if ($res['ok']) {
            $flashMsg = ($act === 'accept')
                ? 'Persetujuan lamaran berhasil dikirimkan ke Gig Worker. Menunggu konfirmasi dari Gig Worker.'
                : 'Lamaran kandidat telah ditolak.';
        }
    }
}

$allApps = gig_get_all_applications();
$appMapByWorker = [];
foreach ($allApps as $ap) {
    $wKey = strtolower(trim((string)$ap['worker_id']));
    $appMapByWorker[$wKey] = $ap;
}

$workerProfiles = gig_worker_profiles();
$searchQ = trim((string)($_GET['q'] ?? ''));
$pageTitle = 'Kandidat Proyek';
$pageKey = 'pelamar';
$breadcrumbCurrent = 'Kandidat';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <div>
        <h1>Kandidat Proyek</h1>
        <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Kelola kandidat yang melamar proyek Anda. Saat Anda menyetujui pelamar, konfirmasi ketersediaan akan dikirimkan ke Gig Worker untuk perekrutan resmi.</p>
      </div>
      <div style="font-size:0.82rem;color:var(--text-muted);">
        Menampilkan <strong id="applicants-visible-count"><?php echo count($workerProfiles); ?></strong> kandidat
      </div>
    </div>

    <?php if ($flashMsg !== ''): ?>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:10px;font-size:0.86rem;margin-bottom:18px;font-weight:700;display:flex;align-items:center;gap:8px;">
        <span>✓</span> <?php echo htmlspecialchars($flashMsg, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <div class="privacy-lock-note">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Klik <strong>Terima &amp; Setujui Pelamar</strong> untuk mengirim undangan konfirmasi ke Gig Worker. Setelah Gig Worker mengonfirmasi, kontak resmi terbuka &amp; proyek menjadi aktif.
    </div>

    <div class="toolbar-filter">
      <button class="filter-btn-pill active" type="button" onclick="filterApplicants('all', this)">Semua Kandidat (<?php echo count($workerProfiles); ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterApplicants('ui-ux', this)">UI/UX (2)</button>
      <button class="filter-btn-pill" type="button" onclick="filterApplicants('backend', this)">Backend &amp; API (2)</button>
      <button class="filter-btn-pill" type="button" onclick="filterApplicants('marketing', this)">Pemasaran (2)</button>
      <div class="search-input-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="search-applicant-input" placeholder="Cari nama atau keterampilan..." value="<?php echo htmlspecialchars($searchQ, ENT_QUOTES, 'UTF-8'); ?>" onkeyup="searchApplicants(this.value)" />
      </div>
    </div>

    <div class="applicants-grid">
      <?php foreach ($workerProfiles as $applicant): 
        $wKey = strtolower(trim((string)$applicant['id']));
        $appData = $appMapByWorker[$wKey] ?? null;
        $status = $appData['status'] ?? 'applied';
        $appId = $appData['id'] ?? ('APP-' . $applicant['id']);
      ?>
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
            <div class="applicant-applied-role">Proyek yang dilamar: <strong><?php echo htmlspecialchars($applicant['applied_project'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
            <div class="project-skill-tags">
              <?php foreach (array_slice($applicant['skills'], 0, 3) as $skillTag): ?>
                <span class="skill-tag-item"><?php echo htmlspecialchars((string)$skillTag, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
            </div>
            <div class="applicant-lock-hint">
              <?php if ($status === 'confirmed_by_worker'): ?>
                <span style="color:#047857;font-weight:700;">✓ Kontak resmi terbuka (Kerja sama aktif)</span>
              <?php else: ?>
                <span>Kontak dikunci hingga kerja sama dikonfirmasi worker</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="applicant-center-meta">
          <span style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Penawaran / Gaji</span>
          <span class="bid-amount"><?php echo htmlspecialchars($applicant['bid'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="bid-time"><?php echo htmlspecialchars($applicant['eta'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="applicant-right-actions" style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
          <a class="btn-outline-blue" href="worker-profile.php?id=<?php echo urlencode($applicant['id']); ?>" style="padding:4px 12px;font-size:0.8rem;">Lihat Profil</a>
          
          <?php if ($status === 'confirmed_by_worker'): ?>
            <span style="padding:6px 14px;border-radius:9999px;background:#d1fae5;color:#047857;font-weight:800;font-size:0.8rem;">🎉 Resmi Direkrut</span>
            <a class="btn-action-sm" href="employer-proyek-aktif.php" style="background:#059669;color:#fff;text-decoration:none;font-size:0.78rem;">Buka Proyek Aktif &rarr;</a>
          <?php elseif ($status === 'accepted_by_employer'): ?>
            <span style="padding:6px 14px;border-radius:9999px;background:#fef3c7;color:#b45309;font-weight:700;font-size:0.8rem;">⏳ Menunggu Konfirmasi Worker</span>
          <?php elseif ($status === 'declined_by_worker'): ?>
            <span style="padding:6px 14px;border-radius:9999px;background:#f1f5f9;color:#64748b;font-weight:700;font-size:0.8rem;">✕ Dibatalkan oleh Worker</span>
          <?php elseif ($status === 'rejected_by_employer'): ?>
            <span style="padding:6px 14px;border-radius:9999px;background:#fef2f2;color:#b91c1c;font-weight:700;font-size:0.8rem;">✕ Lamaran Ditolak</span>
          <?php else: ?>
            <form method="post" action="" style="display:flex;gap:6px;flex-wrap:wrap;">
              <input type="hidden" name="app_id" value="<?php echo htmlspecialchars($appId, ENT_QUOTES, 'UTF-8'); ?>" />
              <button type="submit" name="app_action" value="accept" class="btn-hire" style="background:#16a34a;border-color:#15803d;padding:6px 12px;font-size:0.8rem;">
                ✓ Terima &amp; Setujui
              </button>
              <button type="submit" name="app_action" value="reject" class="btn-action-sm" style="background:#fef2f2;color:#b91c1c;border-color:#fecdd3;padding:6px 10px;font-size:0.8rem;" onclick="return confirm('Tolak lamaran kandidat ini?')">
                ✕ Tolak
              </button>
            </form>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($searchQ !== ''): ?>
    <script>document.addEventListener('DOMContentLoaded', function () { searchApplicants(<?php echo json_encode($searchQ); ?>); });</script>
    <?php endif; ?>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
