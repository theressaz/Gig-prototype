<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$pageTitle = 'Riwayat Proyek';
$pageKey = 'riwayat';
$breadcrumbCurrent = 'Riwayat Proyek';
require __DIR__ . '/includes/employer-layout-start.php';

$historyProjects = [
    [
        'id' => 'CTR-GIG-2026-0811',
        'title' => 'Redesign UI/UX Dashboard Prototype KarirHub',
        'status' => 'Aktif',
        'statusCode' => 'active',
        'worker' => 'Tessa',
        'workerRole' => 'Lead UI/UX Designer',
        'workerId' => 'tessa',
        'workerAvatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Tessa&backgroundColor=dbeafe',
        'duration' => '3 Minggu',
        'startDate' => '01 Sep 2026',
        'endDate' => '22 Sep 2026',
        'budget' => 'Rp 8.000.000',
        'ratingGiven' => null,
        'reviewGiven' => null,
        'summary' => 'Pengerjaan prototype interaktif 12 layar dan pengujian pengguna.',
    ],
    [
        'id' => 'CTR-GIG-2026-0819',
        'title' => 'Integrasi REST API Modul Notifikasi SMS & WhatsApp',
        'status' => 'Aktif',
        'statusCode' => 'active',
        'worker' => 'Rian Ardiansyah',
        'workerRole' => 'Backend API Developer',
        'workerId' => 'rian',
        'workerAvatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Rian&backgroundColor=cffafe',
        'duration' => '2 Minggu',
        'startDate' => '03 Sep 2026',
        'endDate' => '17 Sep 2026',
        'budget' => 'Rp 6.000.000',
        'ratingGiven' => null,
        'reviewGiven' => null,
        'summary' => 'Pengembangan endpoint webhook dan stress testing 5000 req/min.',
    ],
    [
        'id' => 'CTR-GIG-2026-0640',
        'title' => 'Pembuatan Landing Page Kampanye Edukasi Karir',
        'status' => 'Selesai',
        'statusCode' => 'completed',
        'worker' => 'Siti Nurhaliza',
        'workerRole' => 'Social Media Specialist & Copywriter',
        'workerId' => 'siti',
        'workerAvatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Siti&backgroundColor=ede9fe',
        'duration' => '1 Bulan',
        'startDate' => '01 Jul 2026',
        'endDate' => '01 Agu 2026',
        'budget' => 'Rp 4.500.000',
        'ratingGiven' => 5,
        'reviewGiven' => 'Hasil pekerjaan luar biasa! Copywriting komunikatif dan desain landing page meningkatkan konversi pendaftaran.',
        'summary' => 'Penulisan naskah landing page dan pembuatan 20 aset visual media sosial.',
    ],
    [
        'id' => 'CTR-GIG-2026-0512',
        'title' => 'Audit Keamanan & PenTesting Microservice Gateway',
        'status' => 'Selesai',
        'statusCode' => 'completed',
        'worker' => 'Dimas Prasetyo',
        'workerRole' => 'Cloud API Engineer',
        'workerId' => 'dimas',
        'workerAvatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Dimas&backgroundColor=ffedd5',
        'duration' => '2 Minggu',
        'startDate' => '10 Mei 2026',
        'endDate' => '24 Mei 2026',
        'budget' => 'Rp 7.000.000',
        'ratingGiven' => 5,
        'reviewGiven' => 'Penetrasi testing komprehensif, laporan celah keamanan sangat lengkap dan solutif.',
        'summary' => 'Laporan uji keamanan vulnerability assessment dan patching celah API.',
    ],
    [
        'id' => 'CTR-GIG-2026-0305',
        'title' => 'Migrasi Database Legacy ke PostgreSQL',
        'status' => 'Tidak Selesai',
        'statusCode' => 'cancelled',
        'worker' => 'Budi Wicaksono',
        'workerRole' => 'UI Designer / Technical Analyst',
        'workerId' => 'budi',
        'workerAvatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Budi&backgroundColor=d1fae5',
        'duration' => '2 Minggu',
        'startDate' => '01 Mar 2026',
        'endDate' => '08 Mar 2026',
        'budget' => 'Rp 5.000.000',
        'ratingGiven' => null,
        'reviewGiven' => null,
        'summary' => 'Proyek dibatalkan secara bersama karena perubahan spesifikasi arsitektur internal.',
    ],
];

// Merge any dynamically completed projects from session
if (isset($_SESSION['completed_projects']) && is_array($_SESSION['completed_projects'])) {
    foreach ($historyProjects as &$proj) {
        $pId = $proj['id'];
        if (isset($_SESSION['completed_projects'][$pId])) {
            $sessInfo = $_SESSION['completed_projects'][$pId];
            $proj['status'] = $sessInfo['status'] ?? 'Selesai';
            $proj['statusCode'] = $sessInfo['statusCode'] ?? 'completed';
            $proj['ratingGiven'] = $sessInfo['ratingGiven'] ?? 5;
            $proj['reviewGiven'] = $sessInfo['reviewGiven'] ?? '';
            $proj['summary'] = 'Proyek telah selesai dikerjakan, seluruh deliverable diterima dan pembayaran berhasil dituntaskan.';
        }
    }
    unset($proj);
}

$activeCount = 0;
$completedCount = 0;
$cancelledCount = 0;
foreach ($historyProjects as $p) {
    if ($p['statusCode'] === 'active') $activeCount++;
    elseif ($p['statusCode'] === 'completed') $completedCount++;
    elseif ($p['statusCode'] === 'cancelled') $cancelledCount++;
}
?>

    <div class="page-toolbar">
      <div>
        <h1>Riwayat Proyek</h1>
        <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Daftar seluruh rekam jejak proyek pekerjaan, baik yang sedang Aktif, Selesai, maupun Tidak Selesai.</p>
      </div>
    </div>

    <div class="toolbar-filter">
      <button class="filter-btn-pill active" type="button" onclick="filterHistory('all', this)">Semua Status (<?php echo count($historyProjects); ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterHistory('active', this)">Proyek Aktif (<?php echo $activeCount; ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterHistory('completed', this)">Selesai (<?php echo $completedCount; ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterHistory('cancelled', this)">Tidak Selesai (<?php echo $cancelledCount; ?>)</button>
      <div class="search-input-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Cari nomor kontrak, judul, atau freelancer..." onkeyup="searchHistory(this.value)" />
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;" id="history-list">
      <?php foreach ($historyProjects as $item): ?>
      <div class="white-card history-card" data-status="<?php echo $item['statusCode']; ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--border-light);">
          <div>
            <div style="display:flex;align-items:center;gap:10px;">
              <h3 style="font-size:1.05rem;font-weight:700;margin:0;"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <?php if ($item['statusCode'] === 'active'): ?>
                <span class="badge-status active">Aktif</span>
              <?php elseif ($item['statusCode'] === 'completed'): ?>
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
          <div style="display:flex;align-items:center;gap:12px;">
            <img src="<?php echo htmlspecialchars($item['workerAvatar'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:42px;height:42px;border-radius:50%;background:#f1f5f9;" />
            <div>
              <div style="font-size:0.9rem;font-weight:700;">
                <a href="worker-profile.php?id=<?php echo urlencode($item['workerId']); ?>" style="color:inherit;text-decoration:none;"><?php echo htmlspecialchars($item['worker'], ENT_QUOTES, 'UTF-8'); ?></a>
              </div>
              <div style="font-size:0.76rem;color:var(--text-muted);"><?php echo htmlspecialchars($item['workerRole'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          </div>

          <div>
            <div style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">Ringkasan Pekerjaan</div>
            <div style="font-size:0.84rem;color:var(--text-dark);margin-top:2px;"><?php echo htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
        </div>

        <?php if ($item['statusCode'] === 'completed' && $item['ratingGiven'] !== null): ?>
          <div style="margin-top:12px;padding:12px 16px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;font-size:0.84rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="color:#f59e0b;font-weight:800;font-size:1rem;"><?php echo gig_stars($item['ratingGiven']); ?></span>
                <strong style="color:#065f46;">Penilaian Diberikan: <?php echo (int)$item['ratingGiven']; ?> / 5</strong>
              </div>
              <a href="worker-profile.php?id=<?php echo urlencode($item['workerId']); ?>" style="font-size:0.78rem;color:var(--primary-blue);text-decoration:none;font-weight:700;">
                Lihat di Profil <?php echo htmlspecialchars($item['worker'], ENT_QUOTES, 'UTF-8'); ?> →
              </a>
            </div>
            <?php if (!empty($item['reviewGiven'])): ?>
              <p style="margin:6px 0 0 0;color:#1e293b;font-style:italic;">"<?php echo htmlspecialchars($item['reviewGiven'], ENT_QUOTES, 'UTF-8'); ?>"</p>
            <?php endif; ?>
          </div>
        <?php elseif ($item['statusCode'] === 'active'): ?>
          <div style="margin-top:12px;padding:10px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe;font-size:0.82rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <span style="color:#1e40af;">Proyek ini sedang berjalan aktif. Anda dapat menyelesaikan kontrak dan memberikan rating kapan saja.</span>
            <a href="employer-rating-worker.php?contract=<?php echo urlencode($item['id']); ?>&worker=<?php echo urlencode($item['workerId']); ?>" class="btn-create-post" style="padding:6px 14px;font-size:0.8rem;text-decoration:none;background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
              ★ Beri Ulasan &amp; Selesaikan
            </a>
          </div>
        <?php elseif ($item['statusCode'] === 'cancelled'): ?>
          <div style="margin-top:12px;padding:10px 14px;background:#fff1f2;border-radius:8px;border:1px solid #fecdd3;font-size:0.8rem;color:#9f1239;">
            Status Pembatalan: Kontrak dihentikan dengan pengembalian dana penuh / kesepakatan pembatalan mutual.
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

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
