<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/project-applications.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/vacancy-store.php';

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
    'applied' => ['label' => 'Lamaran Masuk', 'group' => 'incoming', 'chipBg' => '#fff7ed', 'chipColor' => '#c2410c', 'border' => '#ffedd5'],
    'reviewing' => ['label' => 'Sedang Dipelajari', 'group' => 'reviewing', 'chipBg' => '#eff6ff', 'chipColor' => '#1d4ed8', 'border' => '#dbeafe'],
    'interview' => ['label' => 'Wawancara', 'group' => 'interview', 'chipBg' => '#ecfeff', 'chipColor' => '#0e7490', 'border' => '#cff4fc'],
    'confirmed_by_worker' => ['label' => 'Diterima', 'group' => 'accepted', 'chipBg' => '#ecfdf5', 'chipColor' => '#047857', 'border' => '#d1fae5'],
    'accepted_by_employer' => ['label' => 'Diterima', 'group' => 'accepted', 'chipBg' => '#ecfdf5', 'chipColor' => '#047857', 'border' => '#d1fae5'],
    'rejected_by_employer' => ['label' => 'Ditolak', 'group' => 'rejected', 'chipBg' => '#fef2f2', 'chipColor' => '#b91c1c', 'border' => '#fee2e2'],
    'declined_by_worker' => ['label' => 'Ditolak', 'group' => 'rejected', 'chipBg' => '#fef2f2', 'chipColor' => '#b91c1c', 'border' => '#fee2e2'],
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
.lamaran-wrap {
  max-width: 1400px;
  margin: 0 auto;
}

.lamaran-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 22px;
  flex-wrap: wrap;
}

.lamaran-title {
  margin: 0;
  font-size: 1.85rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
}

.lamaran-sub {
  margin: 6px 0 0 0;
  color: #64748b;
  font-size: 0.92rem;
}

.lamaran-kpis {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
  margin-bottom: 22px;
}

.lamaran-kpi {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 18px 20px;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
  transition: all 0.15s ease;
}

.lamaran-kpi:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.lamaran-kpi .lbl {
  font-size: 0.74rem;
  color: #64748b;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-bottom: 6px;
}

.lamaran-kpi .num {
  font-size: 1.65rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.1;
}

.lamaran-filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 22px;
}

.lamaran-filter-btn {
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  border-radius: 9999px;
  padding: 8px 16px;
  font-size: 0.84rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.lamaran-filter-btn:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
  color: #1e293b;
}

.lamaran-filter-btn.active {
  border-color: #0284c7;
  background: #0284c7;
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(2, 132, 199, 0.25);
}

.lamaran-card-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.lamaran-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
  padding: 22px 26px;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.lamaran-card:hover {
  transform: translateY(-2px);
  border-color: #93c5fd;
  box-shadow: 0 10px 24px -4px rgba(37, 99, 235, 0.09);
}

.lamaran-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  flex-wrap: wrap;
}

.lamaran-project {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.35;
}

.lamaran-company {
  margin: 4px 0 0 0;
  font-size: 0.86rem;
  color: #64748b;
  font-weight: 600;
}

.lamaran-chip {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 9999px;
  font-size: 0.78rem;
  font-weight: 800;
}

.lamaran-meta-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-top: 18px;
  padding-top: 18px;
  border-top: 1px dashed #e2e8f0;
}

.lamaran-meta-label {
  font-size: 0.72rem;
  color: #64748b;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  display: block;
  margin-bottom: 4px;
}

.lamaran-meta-value {
  font-size: 0.92rem;
  color: #0f172a;
  font-weight: 700;
}

.btn-detail-sm {
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 7px 16px;
  font-weight: 700;
  font-size: 0.82rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s ease;
}

.btn-detail-sm:hover {
  background: #0284c7;
  color: #ffffff;
  border-color: #0284c7;
}

.lamaran-empty {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  text-align: center;
  padding: 56px 24px;
  color: #64748b;
}

@media (max-width: 1024px) {
  .lamaran-kpis {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .lamaran-meta-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .lamaran-kpis {
    grid-template-columns: repeat(2, 1fr);
  }
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
          $rawBid = (string)($app['bid_amount'] ?? ($vacancy['budget'] ?? ''));
          $cleanBid = gig_vacancy_budget_range($rawBid);
          $detailUrl = 'worker-project-detail.php?id=' . urlencode((string)($app['vacancy_id'] ?? ''));
        ?>
        <article class="lamaran-card" data-group="<?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="lamaran-top">
            <div style="min-width:220px;flex:1;">
              <h2 class="lamaran-project"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="lamaran-company">🏢 <?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
              <span class="lamaran-chip" style="background:<?php echo htmlspecialchars((string)$meta['chipBg'], ENT_QUOTES, 'UTF-8'); ?>;color:<?php echo htmlspecialchars((string)$meta['chipColor'], ENT_QUOTES, 'UTF-8'); ?>;border:1px solid <?php echo htmlspecialchars((string)$meta['border'], ENT_QUOTES, 'UTF-8'); ?>;">
                <?php echo htmlspecialchars((string)$meta['label'], ENT_QUOTES, 'UTF-8'); ?>
              </span>
              <a class="btn-detail-sm" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">Lihat Detail</a>
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
              <span class="lamaran-meta-value"><?php echo htmlspecialchars($cleanBid, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div>
              <span class="lamaran-meta-label">Lamar Sebelum</span>
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
