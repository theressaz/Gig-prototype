<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$pageTitle = 'Riwayat Proyek';
$pageKey = 'riwayat';
$breadcrumbCurrent = 'Riwayat Proyek';
require __DIR__ . '/includes/worker-layout-start.php';

$historyProjects = [
    [
        'id' => 'CTR-GIG-2026-0640',
        'title' => 'Portal Rekrutmen BUMN',
        'status' => 'Selesai',
        'statusCode' => 'completed',
        'employer' => 'PT Talenta Nusantara',
        'duration' => '6 Bulan',
        'startDate' => '01 Jan 2026',
        'endDate' => '24 Jun 2026',
        'budget' => 'Rp 12.000.000',
        'ratingGiven' => 5,
        'reviewGiven' => 'Hasil desain rapi, komunikatif, dan tepat waktu. Prototype mudah diuji tim internal.',
        'summary' => 'High-fidelity mockup desktop/mobile dan panduan interaksi portal rekrutmen.',
    ],
    [
        'id' => 'CTR-GIG-2026-0422',
        'title' => 'Redesign Aplikasi Lowongan',
        'status' => 'Selesai',
        'statusCode' => 'completed',
        'employer' => 'CV Kreasi Digital',
        'duration' => '1 Bulan',
        'startDate' => '01 Mei 2026',
        'endDate' => '31 Mei 2026',
        'budget' => 'Rp 7.500.000',
        'ratingGiven' => 5,
        'reviewGiven' => 'Sangat memahami kebutuhan pengguna awam. Iterasi cepat setelah umpan balik.',
        'summary' => 'Perbaikan alur pencarian lowongan dan uji keterbacaan untuk pengguna baru.',
    ],
    [
        'id' => 'CTR-GIG-2026-0118',
        'title' => 'Landing Page Program Pelatihan',
        'status' => 'Selesai',
        'statusCode' => 'completed',
        'employer' => 'Yayasan Kerja Adil',
        'duration' => '3 Minggu',
        'startDate' => '06 Jan 2026',
        'endDate' => '28 Jan 2026',
        'budget' => 'Rp 4.500.000',
        'ratingGiven' => 5,
        'reviewGiven' => 'Visual konsisten dan aksesibel. Direkomendasikan untuk proyek pemerintahan.',
        'summary' => 'Landing page kampanye pelatihan dan aset visual pendukung.',
    ],
    [
        'id' => 'CTR-GIG-2025-1102',
        'title' => 'Aplikasi Pelaporan Pekerja Lepas',
        'status' => 'Tidak Selesai',
        'statusCode' => 'cancelled',
        'employer' => 'Startup Ketenagakerjaan',
        'duration' => '2 Bulan',
        'startDate' => '01 Nov 2025',
        'endDate' => '20 Nov 2025',
        'budget' => 'Rp 9.000.000',
        'ratingGiven' => null,
        'reviewGiven' => null,
        'summary' => 'Kontrak dihentikan bersama karena perubahan ruang lingkup produk.',
    ],
];

$recentContracts = [
    [
        'id' => 'CTR-GIG-2026-0811',
        'title' => 'Redesign UI/UX Dashboard Prototype KarirHub',
        'employer' => 'PT ABC',
        'duration' => '3 Minggu',
        'startDate' => '01 Sep 2026',
        'endDate' => '22 Sep 2026',
        'budget' => 'Rp 8.500.000',
        'summary' => 'Prototype interaktif dashboard pemberi kerja.',
    ],
    [
        'id' => 'CTR-GIG-2026-0819',
        'title' => 'Integrasi REST API Modul Notifikasi SMS & WhatsApp',
        'employer' => 'PT ABC',
        'duration' => '2 Minggu',
        'startDate' => '03 Sep 2026',
        'endDate' => '17 Sep 2026',
        'budget' => 'Rp 6.000.000',
        'summary' => 'Integrasi webhook SMS/WA ke sistem inti.',
    ],
];

$pdo = gig_db();
$dbCompletions = [];
if ($pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT `contract_id`, `rating_given`, `review_given` FROM `project_completions`");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dbCompletions[$row['contract_id']] = $row;
        }
    } catch (Throwable $ignored) {
    }
}

$moved = [];
foreach ($recentContracts as $recent) {
    $pId = $recent['id'];
    $done = isset($dbCompletions[$pId]) || isset($_SESSION['completed_projects'][$pId]);
    if (!$done) {
        continue;
    }
    $rating = $dbCompletions[$pId]['rating_given'] ?? ($_SESSION['completed_projects'][$pId]['ratingGiven'] ?? 5);
    $review = $dbCompletions[$pId]['review_given'] ?? ($_SESSION['completed_projects'][$pId]['reviewGiven'] ?? '');
    $moved[] = [
        'id' => $pId,
        'title' => $recent['title'],
        'status' => 'Selesai',
        'statusCode' => 'completed',
        'employer' => $recent['employer'],
        'duration' => $recent['duration'],
        'startDate' => $recent['startDate'],
        'endDate' => $recent['endDate'],
        'budget' => $recent['budget'],
        'ratingGiven' => (int)$rating,
        'reviewGiven' => (string)$review,
        'summary' => 'Proyek telah selesai dikerjakan dan penilaian sudah diberikan.',
    ];
}

$historyProjects = array_merge($moved, $historyProjects);

$completedCount = 0;
$cancelledCount = 0;
foreach ($historyProjects as $p) {
    if ($p['statusCode'] === 'completed') {
        $completedCount++;
    } elseif ($p['statusCode'] === 'cancelled') {
        $cancelledCount++;
    }
}
?>

    <div class="page-toolbar">
      <div>
        <h1>Riwayat Proyek</h1>
        <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Proyek yang sudah selesai atau tidak dilanjutkan. Yang masih berjalan ada di Proyek Aktif.</p>
      </div>
    </div>

    <div class="toolbar-filter">
      <button class="filter-btn-pill active" type="button" onclick="filterHistory('all', this)">Semua (<?php echo count($historyProjects); ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterHistory('completed', this)">Selesai (<?php echo $completedCount; ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterHistory('cancelled', this)">Tidak Selesai (<?php echo $cancelledCount; ?>)</button>
      <div class="search-input-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Cari nomor kontrak, judul, atau pemberi kerja..." onkeyup="searchHistory(this.value)" />
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;" id="history-list">
      <?php if (count($historyProjects) === 0): ?>
        <div class="white-card" style="text-align:center;padding:48px 24px;">
          <h3 style="font-size:1.05rem;font-weight:800;margin-bottom:6px;">Belum ada riwayat proyek</h3>
          <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;">Setelah proyek selesai, catatan kerjanya akan tampil di sini.</p>
          <a class="btn-primary-add" href="worker-tugas.php" style="display:inline-flex;text-decoration:none;">Lihat Proyek Aktif</a>
        </div>
      <?php endif; ?>

      <?php foreach ($historyProjects as $item): ?>
      <div class="white-card history-card" data-status="<?php echo htmlspecialchars($item['statusCode'], ENT_QUOTES, 'UTF-8'); ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--border-light);">
          <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
              <h3 style="font-size:1.05rem;font-weight:700;margin:0;"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <?php if ($item['statusCode'] === 'completed'): ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#d1fae5;color:#047857;">Selesai</span>
              <?php else: ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#fee2e2;color:#b91c1c;">Tidak Selesai</span>
              <?php endif; ?>
            </div>
            <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;">
              No. Kontrak: <strong><?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?></strong> · Periode: <strong><?php echo htmlspecialchars($item['startDate'], ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($item['endDate'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:0.74rem;color:var(--text-muted);">Nilai Kontrak</div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--primary-blue);"><?php echo htmlspecialchars($item['budget'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:14px;align-items:center;">
          <div>
            <div style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">Pemberi Kerja</div>
            <div style="font-size:0.9rem;font-weight:700;margin-top:2px;"><?php echo htmlspecialchars($item['employer'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div style="font-size:0.76rem;color:var(--text-muted);">Durasi <?php echo htmlspecialchars($item['duration'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
          <div>
            <div style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">Ringkasan Pekerjaan</div>
            <div style="font-size:0.84rem;color:var(--text-dark);margin-top:2px;"><?php echo htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
        </div>

        <?php if ($item['statusCode'] === 'completed' && $item['ratingGiven'] !== null): ?>
          <div style="margin-top:12px;padding:12px 16px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;font-size:0.84rem;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="color:#f59e0b;font-weight:800;font-size:1rem;"><?php echo gig_stars((int)$item['ratingGiven']); ?></span>
              <strong style="color:#065f46;">Penilaian: <?php echo (int)$item['ratingGiven']; ?> / 5</strong>
            </div>
            <?php if (!empty($item['reviewGiven'])): ?>
              <p style="margin:6px 0 0 0;color:#1e293b;font-style:italic;">"<?php echo htmlspecialchars((string)$item['reviewGiven'], ENT_QUOTES, 'UTF-8'); ?>"</p>
            <?php endif; ?>
          </div>
        <?php elseif ($item['statusCode'] === 'cancelled'): ?>
          <div style="margin-top:12px;padding:10px 14px;background:#fff1f2;border-radius:8px;border:1px solid #fecdd3;font-size:0.8rem;color:#9f1239;">
            Kontrak dihentikan. Proyek ini tidak dilanjutkan.
          </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <script>
      function filterHistory(status, btn) {
        document.querySelectorAll('.toolbar-filter .filter-btn-pill').forEach(function(b) { b.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        document.querySelectorAll('#history-list .history-card').forEach(function(card) {
          const s = card.getAttribute('data-status');
          card.style.display = (status === 'all' || s === status) ? 'block' : 'none';
        });
      }
      function searchHistory(query) {
        const q = (query || '').toLowerCase();
        document.querySelectorAll('#history-list .history-card').forEach(function(card) {
          card.style.display = card.innerText.toLowerCase().includes(q) ? 'block' : 'none';
        });
      }
    </script>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
