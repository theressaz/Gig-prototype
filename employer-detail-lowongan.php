<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/project-applications.php';

$jobId = trim((string)($_GET['id'] ?? 'GIG-2026-09-001'));
$job = gig_find_vacancy($jobId);
if ($job === null) {
    header('Location: employer-lowongan.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_action'], $_POST['app_id'])) {
    $appId = trim((string)$_POST['app_id']);
    $action = trim((string)$_POST['app_action']);
    if ($appId !== '' && in_array($action, ['accept', 'reject'], true)) {
        $decision = $action === 'accept' ? 'accept' : 'reject';
        gig_employer_respond_application($appId, $decision, (string)($_SESSION['username'] ?? ''));
        header('Location: employer-detail-lowongan.php?id=' . urlencode($jobId));
        exit;
    }
}

$workers = gig_worker_profiles();
$workerById = [];
foreach ($workers as $w) {
    $workerById[strtolower(trim((string)($w['id'] ?? '')))] = $w;
}

$jobApplications = [];
foreach (gig_get_all_applications() as $app) {
    if (strcasecmp((string)($app['vacancy_id'] ?? ''), (string)$job['id']) === 0) {
        $jobApplications[] = $app;
    }
}

usort($jobApplications, static function(array $a, array $b): int {
    return strtotime((string)($b['updated_at'] ?? '')) <=> strtotime((string)($a['updated_at'] ?? ''));
});

$statusToLane = [
    'applied' => 'incoming',
    'reviewing' => 'reviewed',
    'interview' => 'interview',
    'confirmed_by_worker' => 'accepted',
    'accepted_by_employer' => 'accepted',
    'rejected_by_employer' => 'rejected',
    'declined_by_worker' => 'rejected',
];

$laneMeta = [
    'incoming' => ['label' => 'Lamaran Masuk', 'dot' => '#f59e0b'],
    'reviewed' => ['label' => 'Sedang Dipelajari', 'dot' => '#fb923c'],
    'interview' => ['label' => 'Wawancara', 'dot' => '#3b82f6'],
    'accepted' => ['label' => 'Diterima', 'dot' => '#22c55e'],
    'rejected' => ['label' => 'Ditolak', 'dot' => '#ef4444'],
];

$lanes = [
    'incoming' => [],
    'reviewed' => [],
    'interview' => [],
    'accepted' => [],
    'rejected' => [],
];

foreach ($jobApplications as $app) {
    $status = (string)($app['status'] ?? 'applied');
    $lane = $statusToLane[$status] ?? 'incoming';
    $workerId = strtolower(trim((string)($app['worker_id'] ?? '')));
    $profile = $workerById[$workerId] ?? null;

    $lanes[$lane][] = [
        'id' => (string)($app['id'] ?? ''),
        'worker_id' => (string)($app['worker_id'] ?? ''),
        'name' => (string)($profile['name'] ?? $app['worker_name'] ?? 'Gig Worker'),
        'title' => (string)($profile['title'] ?? 'Gig Worker'),
        'location' => (string)($profile['location'] ?? 'Lokasi belum diisi'),
        'rating' => (float)($profile['rating'] ?? 0),
        'bid' => (string)($app['bid_amount'] ?? $job['budget'] ?? '-'),
        'status' => $status,
    ];
}

$statusLabel = (string)($job['statusLabel'] ?? 'Draft');
$pageTitle = 'Detail Lowongan · ' . $job['title'];
$pageKey = 'lowongan';
$breadcrumbCurrent = 'Detail Lowongan';
require __DIR__ . '/includes/employer-layout-start.php';
?>

<style>
  .jobd-shell { display:flex; flex-direction:column; gap:16px; }
  .jobd-head { background:#fff; border:1px solid var(--border-subtle); border-radius:14px; padding:18px 20px; box-shadow:var(--shadow-sm); }
  .jobd-head-top { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; flex-wrap:wrap; }
  .jobd-title { font-size:2rem; font-weight:800; color:#0f172a; line-height:1.1; margin:0 0 8px 0; }
  .jobd-meta { display:flex; align-items:center; gap:10px; flex-wrap:wrap; font-size:0.82rem; color:#64748b; }
  .jobd-pill { background:#f1f5f9; border:1px solid #e2e8f0; border-radius:9999px; padding:4px 10px; font-weight:700; color:#334155; }
  .jobd-kpi { display:grid; grid-template-columns:repeat(3,minmax(140px,1fr)); gap:14px; margin-top:14px; border-top:1px solid #eef2ff; padding-top:14px; }
  .jobd-kpi .lbl { font-size:0.76rem; color:#64748b; margin-bottom:2px; display:block; }
  .jobd-kpi .val { font-size:0.9rem; font-weight:800; color:#0f172a; }
  .jobd-tabs { display:flex; gap:16px; border-bottom:1px solid #e2e8f0; margin-top:8px; }
  .jobd-tab { border:none; background:none; padding:10px 2px; font-size:0.9rem; color:#64748b; font-weight:700; border-bottom:2px solid transparent; cursor:pointer; }
  .jobd-tab.active { color:#0284c7; border-bottom-color:#0ea5e9; }
  .jobd-tools { display:flex; justify-content:space-between; gap:10px; margin-top:14px; flex-wrap:wrap; }
  .jobd-search { display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:8px 10px; min-width:260px; }
  .jobd-search input { border:none; outline:none; width:100%; font-size:0.86rem; }
  .jobd-board { display:grid; grid-template-columns:repeat(5,minmax(220px,1fr)); gap:12px; overflow-x:auto; padding-bottom:4px; }
  .jobd-col { min-width:220px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; display:flex; flex-direction:column; max-height:540px; }
  .jobd-col-head { padding:10px 12px; border-bottom:1px solid #eef2ff; display:flex; justify-content:space-between; align-items:center; font-size:0.84rem; font-weight:800; color:#1e293b; }
  .jobd-dot { width:7px; height:7px; border-radius:9999px; display:inline-block; margin-right:7px; vertical-align:middle; }
  .jobd-col-body { padding:10px; overflow:auto; display:flex; flex-direction:column; gap:8px; }
  .jobd-card { border:1px solid #e2e8f0; border-radius:10px; padding:10px; background:#f8fafc; }
  .jobd-card-name { font-size:0.84rem; font-weight:800; color:#0f172a; margin-bottom:2px; }
  .jobd-card-sub { font-size:0.74rem; color:#64748b; margin-bottom:6px; }
  .jobd-card-meta { font-size:0.72rem; color:#475569; display:flex; gap:8px; flex-wrap:wrap; margin-bottom:6px; }
  .jobd-card-actions { display:flex; gap:6px; margin-top:8px; }
  .jobd-empty { font-size:0.8rem; color:#94a3b8; text-align:center; padding:24px 10px; }
  .jobd-info { display:none; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; }
  .jobd-info.active { display:block; }
</style>

<div class="jobd-shell">
  <div class="jobd-head">
    <div class="jobd-head-top">
      <div>
        <a class="btn-action-sm" href="employer-lowongan.php" style="margin-bottom:10px;display:inline-flex;">← Kembali ke Lowongan</a>
        <h1 class="jobd-title"><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="jobd-meta">
          <span class="jobd-pill"><?php echo htmlspecialchars((string)($job['workType'] ?? 'Full time'), ENT_QUOTES, 'UTF-8'); ?></span>
          <span>•</span>
          <span>Dibuat <?php echo htmlspecialchars((string)$job['posted'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span>•</span>
          <span>Status: <strong><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></strong></span>
        </div>
      </div>
      <button type="button" class="btn-primary-add" onclick="showToast('Fitur re-open lowongan siap digunakan.');">Buka Kembali Lowongan</button>
    </div>
    <div class="jobd-kpi">
      <div><span class="lbl">Tayang</span><span class="val"><?php echo htmlspecialchars((string)$job['posted'], ENT_QUOTES, 'UTF-8'); ?></span></div>
      <div><span class="lbl">Kadaluarsa</span><span class="val"><?php echo htmlspecialchars((string)$job['deadline'], ENT_QUOTES, 'UTF-8'); ?></span></div>
      <div><span class="lbl">Lokasi</span><span class="val"><?php echo htmlspecialchars((string)$job['location'], ENT_QUOTES, 'UTF-8'); ?></span></div>
    </div>
  </div>

  <div class="jobd-tabs">
    <button class="jobd-tab active" type="button" data-tab="lamaran">Lamaran</button>
    <button class="jobd-tab" type="button" data-tab="detail">Detail Lowongan</button>
  </div>

  <section id="tab-lamaran" class="jobd-pane">
    <div class="jobd-tools">
      <div class="jobd-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input id="candidateSearchInput" type="text" placeholder="Cari pelamar..." />
      </div>
      <div style="display:flex;gap:8px;">
        <button class="filter-btn-pill" type="button">Filter</button>
      </div>
    </div>

    <div class="jobd-board" id="kanbanBoard" style="margin-top:10px;">
      <?php foreach ($lanes as $key => $cards): ?>
        <div class="jobd-col" data-lane="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="jobd-col-head">
            <span><span class="jobd-dot" style="background:<?php echo htmlspecialchars($laneMeta[$key]['dot'], ENT_QUOTES, 'UTF-8'); ?>"></span><?php echo htmlspecialchars($laneMeta[$key]['label'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span><?php echo count($cards); ?></span>
          </div>
          <div class="jobd-col-body">
            <?php if (count($cards) === 0): ?>
              <div class="jobd-empty">Tidak ada data.</div>
            <?php endif; ?>
            <?php foreach ($cards as $c): ?>
              <div class="jobd-card candidate-card" data-search="<?php echo htmlspecialchars(strtolower($c['name'] . ' ' . $c['title'] . ' ' . $c['location']), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="jobd-card-name"><?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="jobd-card-sub"><?php echo htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="jobd-card-meta">
                  <span>📍 <?php echo htmlspecialchars($c['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <span>★ <?php echo number_format((float)$c['rating'], 1); ?></span>
                </div>
                <div style="font-size:0.76rem;font-weight:800;color:#2563eb;"><?php echo htmlspecialchars($c['bid'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="jobd-card-actions">
                  <a class="btn-outline-blue" style="padding:4px 8px;font-size:0.72rem;" href="worker-profile.php?id=<?php echo urlencode($c['worker_id']); ?>&from=kandidat">Profil</a>
                  <?php if ($key === 'incoming' || $key === 'reviewed' || $key === 'interview'): ?>
                    <form method="post" action="" style="display:inline-flex;gap:4px;">
                      <input type="hidden" name="app_id" value="<?php echo htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8'); ?>">
                      <button class="btn-action-sm" type="submit" name="app_action" value="accept" style="padding:4px 8px;font-size:0.72rem;background:#dcfce7;color:#166534;border-color:#bbf7d0;">Terima</button>
                      <button class="btn-action-sm" type="submit" name="app_action" value="reject" style="padding:4px 8px;font-size:0.72rem;background:#fef2f2;color:#b91c1c;border-color:#fecaca;" onclick="return confirm('Tolak kandidat ini?');">Tolak</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="tab-detail" class="jobd-info">
    <h2 style="font-size:1rem;font-weight:800;color:#0f172a;margin:0 0 10px 0;">Detail Lowongan</h2>
    <p style="font-size:0.9rem;line-height:1.6;color:#334155;"><?php echo htmlspecialchars((string)$job['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
    <div class="skill-row" style="margin-top:12px;">
      <?php foreach (($job['skills'] ?? []) as $skill): ?>
        <span class="skill-tag"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
      <?php endforeach; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(160px,1fr));gap:12px;margin-top:16px;">
      <div><span style="font-size:0.74rem;color:#64748b;display:block;">Gaji</span><strong><?php echo htmlspecialchars((string)$job['budget'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
      <div><span style="font-size:0.74rem;color:#64748b;display:block;">Durasi</span><strong><?php echo htmlspecialchars((string)$job['duration'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
      <div><span style="font-size:0.74rem;color:#64748b;display:block;">Lokasi</span><strong><?php echo htmlspecialchars((string)$job['location'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
      <div><span style="font-size:0.74rem;color:#64748b;display:block;">Status Verifikasi</span><strong><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></strong></div>
    </div>
  </section>
</div>

<script>
  (function() {
    const tabs = document.querySelectorAll('.jobd-tab');
    const lamaranPane = document.getElementById('tab-lamaran');
    const detailPane = document.getElementById('tab-detail');
    tabs.forEach(function(btn) {
      btn.addEventListener('click', function() {
        tabs.forEach(function(x) { x.classList.remove('active'); });
        btn.classList.add('active');
        const isLamaran = btn.getAttribute('data-tab') === 'lamaran';
        lamaranPane.style.display = isLamaran ? 'block' : 'none';
        detailPane.classList.toggle('active', !isLamaran);
      });
    });

    const searchInput = document.getElementById('candidateSearchInput');
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const q = (searchInput.value || '').toLowerCase().trim();
        document.querySelectorAll('.candidate-card').forEach(function(card) {
          const hay = card.getAttribute('data-search') || '';
          card.style.display = (!q || hay.indexOf(q) !== -1) ? 'block' : 'none';
        });
      });
    }
  })();
</script>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
