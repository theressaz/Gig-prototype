<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/project-applications.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/worker-profiles.php';

/**
 * Collect worker applications using all known identity keys
 * (username, profile id, SIAPKerja email, profile email, display name),
 * so historical records remain visible even if IDs differ.
 */
$workerProfile = gig_find_worker($username) ?? gig_find_worker('tessa');
$identityKeys = array_values(array_unique(array_filter(array_map(
    static fn($v) => strtolower(trim((string)$v)),
    [
        $username,
        $_SESSION['username'] ?? '',
        $_SESSION['siapkerja_email'] ?? '',
        $_SESSION['siapkerja_name'] ?? '',
        $workerProfile['id'] ?? '',
        $workerProfile['name'] ?? '',
        $workerProfile['contact']['email'] ?? '',
    ]
), static fn($v) => $v !== '')));

$applications = [];
foreach (gig_get_all_applications() as $app) {
    $wid = strtolower(trim((string)($app['worker_id'] ?? '')));
    $wname = strtolower(trim((string)($app['worker_name'] ?? '')));
    if (in_array($wid, $identityKeys, true) || in_array($wname, $identityKeys, true)) {
        $applications[] = $app;
    }
}
usort($applications, static function (array $a, array $b): int {
    return strtotime((string)($b['updated_at'] ?? '')) <=> strtotime((string)($a['updated_at'] ?? ''));
});

$statusMeta = [
    'applied' => ['label' => 'Lamaran Masuk', 'group' => 'incoming', 'chipBg' => '#fff7ed', 'chipColor' => '#c2410c'],
    'reviewing' => ['label' => 'Sedang Dipelajari', 'group' => 'reviewing', 'chipBg' => '#eff6ff', 'chipColor' => '#1d4ed8'],
    'interview' => ['label' => 'Wawancara', 'group' => 'interview', 'chipBg' => '#ecfeff', 'chipColor' => '#0e7490'],
    'confirmed_by_worker' => ['label' => 'Diterima', 'group' => 'accepted', 'chipBg' => '#ecfdf5', 'chipColor' => '#047857'],
    'accepted_by_employer' => ['label' => 'Diterima', 'group' => 'accepted', 'chipBg' => '#ecfdf5', 'chipColor' => '#047857'],
    'rejected_by_employer' => ['label' => 'Ditolak', 'group' => 'rejected', 'chipBg' => '#fef2f2', 'chipColor' => '#b91c1c'],
    'declined_by_worker' => ['label' => 'Ditolak', 'group' => 'rejected', 'chipBg' => '#fef2f2', 'chipColor' => '#b91c1c'],
];

$counts = ['all' => count($applications), 'incoming' => 0, 'reviewing' => 0, 'interview' => 0, 'accepted' => 0, 'rejected' => 0];
foreach ($applications as $row) {
    $st = (string)($row['status'] ?? 'applied');
    $meta = $statusMeta[$st] ?? $statusMeta['applied'];
    $grp = (string)$meta['group'];
    if (isset($counts[$grp])) {
        $counts[$grp]++;
    }
}

$pageTitle = 'Lamaran Saya';
$pageKey = 'lamaran';
$breadcrumbCurrent = 'Lamaran Saya';
require __DIR__ . '/includes/worker-layout-start.php';
?>

<style>
.lamaran-wrap { max-width: 1220px; margin: 0 auto; }
.lamaran-toolbar { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px; flex-wrap:wrap; }
.lamaran-title { margin:0; font-size:2.1rem; line-height:1.1; color:#0f172a; font-weight:800; letter-spacing:-0.02em; }
.lamaran-sub { margin:7px 0 0 0; color:#64748b; font-size:0.9rem; }
.lamaran-filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.lamaran-filter-btn { border:1px solid #dbe2ef; background:#fff; color:#475569; border-radius:9999px; padding:7px 12px; font-size:0.8rem; font-weight:700; cursor:pointer; }
.lamaran-filter-btn.active { border-color:#93c5fd; background:#eff6ff; color:#1d4ed8; }
.lamaran-card-list { display:flex; flex-direction:column; gap:12px; }
.lamaran-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; box-shadow:0 2px 7px rgba(15,23,42,0.04); padding:16px 18px; }
.lamaran-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; }
.lamaran-project { margin:0; font-size:1.05rem; font-weight:800; color:#0f172a; line-height:1.35; }
.lamaran-company { margin:4px 0 0 0; font-size:0.82rem; color:#64748b; font-weight:600; }
.lamaran-meta-grid { display:grid; grid-template-columns:repeat(4,minmax(120px,1fr)); gap:10px; margin-top:12px; border-top:1px dashed #e2e8f0; padding-top:12px; }
.lamaran-meta-label { font-size:0.72rem; color:#64748b; display:block; margin-bottom:2px; }
.lamaran-meta-value { font-size:0.84rem; color:#0f172a; font-weight:700; }
.lamaran-empty { background:#fff; border:1px solid #e2e8f0; border-radius:14px; text-align:center; padding:44px 18px; color:#64748b; }
.lamaran-kpis { display:grid; grid-template-columns:repeat(5,minmax(130px,1fr)); gap:10px; margin-bottom:14px; }
.lamaran-kpi { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:12px; box-shadow:0 2px 6px rgba(15,23,42,0.03); }
.lamaran-kpi .lbl { font-size:0.74rem; color:#64748b; font-weight:700; margin-bottom:4px; }
.lamaran-kpi .num { font-size:1.2rem; font-weight:800; color:#0f172a; line-height:1; }
@media (max-width: 900px) {
  .lamaran-meta-grid { grid-template-columns:repeat(2,minmax(120px,1fr)); }
  .lamaran-kpis { grid-template-columns:repeat(2,minmax(130px,1fr)); }
}
</style>

<div class="lamaran-wrap">
  <section class="lamaran-toolbar">
    <div>
      <h1 class="lamaran-title">Lamaran Saya</h1>
      <p class="lamaran-sub">Pantau status seluruh lamaran proyek Anda secara real-time.</p>
    </div>
    <a class="btn-primary-add" href="worker-bursa.php" style="text-decoration:none;display:inline-flex;">+ Lamar Proyek Baru</a>
  </section>

  <div class="lamaran-filters">
    <button class="lamaran-filter-btn active" type="button" onclick="filterLamaran('all', this)">Semua (<?php echo (int)$counts['all']; ?>)</button>
    <button class="lamaran-filter-btn" type="button" onclick="filterLamaran('incoming', this)">Lamaran Masuk (<?php echo (int)$counts['incoming']; ?>)</button>
    <button class="lamaran-filter-btn" type="button" onclick="filterLamaran('reviewing', this)">Sedang Dipelajari (<?php echo (int)$counts['reviewing']; ?>)</button>
    <button class="lamaran-filter-btn" type="button" onclick="filterLamaran('interview', this)">Wawancara (<?php echo (int)$counts['interview']; ?>)</button>
    <button class="lamaran-filter-btn" type="button" onclick="filterLamaran('accepted', this)">Diterima (<?php echo (int)$counts['accepted']; ?>)</button>
    <button class="lamaran-filter-btn" type="button" onclick="filterLamaran('rejected', this)">Ditolak (<?php echo (int)$counts['rejected']; ?>)</button>
  </div>

  <section class="lamaran-kpis">
    <article class="lamaran-kpi"><div class="lbl">Total Lamaran</div><div class="num"><?php echo (int)$counts['all']; ?></div></article>
    <article class="lamaran-kpi"><div class="lbl">Diproses</div><div class="num"><?php echo (int)$counts['incoming'] + (int)$counts['reviewing'] + (int)$counts['interview']; ?></div></article>
    <article class="lamaran-kpi"><div class="lbl">Diterima</div><div class="num" style="color:#047857;"><?php echo (int)$counts['accepted']; ?></div></article>
    <article class="lamaran-kpi"><div class="lbl">Ditolak</div><div class="num" style="color:#b91c1c;"><?php echo (int)$counts['rejected']; ?></div></article>
    <article class="lamaran-kpi"><div class="lbl">Wawancara</div><div class="num" style="color:#0e7490;"><?php echo (int)$counts['interview']; ?></div></article>
  </section>

  <?php if ($applications === []): ?>
    <div class="lamaran-empty">
      <h3 style="margin:0 0 6px 0;color:#0f172a;font-size:1.05rem;">Belum ada lamaran</h3>
      <p style="margin:0 0 14px 0;font-size:0.88rem;">Mulai lamar proyek dari halaman Cari Proyek.</p>
      <a class="btn-primary-add" href="worker-bursa.php" style="text-decoration:none;display:inline-flex;">Cari Proyek</a>
    </div>
  <?php else: ?>
    <div class="lamaran-card-list" id="lamaranList">
      <?php foreach ($applications as $app): ?>
        <?php
          $status = (string)($app['status'] ?? 'applied');
          $meta = $statusMeta[$status] ?? $statusMeta['applied'];
          $group = (string)$meta['group'];
          $vacancy = gig_find_vacancy((string)($app['vacancy_id'] ?? ''));
          $title = (string)($vacancy['title'] ?? ('Proyek #' . (string)($app['vacancy_id'] ?? '-')));
          $company = (string)($vacancy['employer'] ?? $app['employer_username'] ?? 'Pemberi Kerja');
          $deadline = (string)($vacancy['deadline'] ?? '-');
          $detailUrl = 'worker-project-detail.php?id=' . urlencode((string)($app['vacancy_id'] ?? ''));
        ?>
        <article class="lamaran-card" data-group="<?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="lamaran-top">
            <div style="min-width:220px;flex:1;">
              <h2 class="lamaran-project"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="lamaran-company"><?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
              <span style="display:inline-block;padding:6px 10px;border-radius:9999px;font-size:0.76rem;font-weight:800;background:<?php echo htmlspecialchars((string)$meta['chipBg'], ENT_QUOTES, 'UTF-8'); ?>;color:<?php echo htmlspecialchars((string)$meta['chipColor'], ENT_QUOTES, 'UTF-8'); ?>;">
                <?php echo htmlspecialchars((string)$meta['label'], ENT_QUOTES, 'UTF-8'); ?>
              </span>
              <a class="btn-action-sm" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration:none;">Lihat Detail</a>
            </div>
          </div>

          <div class="lamaran-meta-grid">
            <div>
              <span class="lamaran-meta-label">Tanggal Lamar</span>
              <span class="lamaran-meta-value"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string)($app['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div>
              <span class="lamaran-meta-label">Terakhir Update</span>
              <span class="lamaran-meta-value"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string)($app['updated_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div>
              <span class="lamaran-meta-label">Gaji Proyek</span>
              <span class="lamaran-meta-value"><?php echo htmlspecialchars((string)($app['bid_amount'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div>
              <span class="lamaran-meta-label">Batas Lamaran</span>
              <span class="lamaran-meta-value"><?php echo htmlspecialchars($deadline, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
function filterLamaran(group, btn) {
  document.querySelectorAll('.lamaran-filter-btn').forEach(function(el) { el.classList.remove('active'); });
  if (btn) btn.classList.add('active');
  document.querySelectorAll('#lamaranList .lamaran-card').forEach(function(card) {
    const g = card.getAttribute('data-group') || '';
    card.style.display = (group === 'all' || g === group) ? '' : 'none';
  });
}
</script>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
