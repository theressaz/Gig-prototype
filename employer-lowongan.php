<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/project-vacancies.php';

$vacancies = gig_project_vacancies();
$draftCount = 0;
$reviewCount = 0;
$activeCount = 0;
foreach ($vacancies as $vacancy) {
    if ($vacancy['status'] === 'draft') {
        $draftCount++;
    } elseif ($vacancy['status'] === 'review') {
        $reviewCount++;
    } elseif ($vacancy['status'] === 'active') {
        $activeCount++;
    }
}

$pageTitle = 'Lowongan Proyek';
$pageKey = 'lowongan';
$breadcrumbCurrent = 'Lowongan';
require __DIR__ . '/includes/employer-layout-start.php';

$colors = ['#2563eb', '#0891b2', '#7c3aed', '#059669'];
?>

    <div class="page-toolbar">
      <h1>Lowongan</h1>
      <button class="btn-primary-add" type="button" onclick="openPostProjectModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah
      </button>
    </div>

    <section class="mini-stats">
      <article class="mini-stat">
        <div>
          <div class="lbl">Draft</div>
          <div class="num"><?php echo $draftCount; ?></div>
          <div class="sub">Belum diajukan</div>
        </div>
        <div class="ico">📄</div>
      </article>
      <article class="mini-stat">
        <div>
          <div class="lbl">Dikirim</div>
          <div class="num"><?php echo $reviewCount; ?></div>
          <div class="sub">Menunggu verifikasi</div>
        </div>
        <div class="ico">⏱️</div>
      </article>
      <article class="mini-stat">
        <div>
          <div class="lbl">Perlu Direvisi</div>
          <div class="num">0</div>
          <div class="sub">Perlu perbaikan</div>
        </div>
        <div class="ico">⚠️</div>
      </article>
      <article class="mini-stat">
        <div>
          <div class="lbl">Lowongan Aktif</div>
          <div class="num"><?php echo $activeCount; ?></div>
          <div class="sub">Sedang tayang</div>
        </div>
        <div class="ico">✓</div>
      </article>
    </section>

    <div class="toolbar-filter">
      <button class="filter-btn-pill active" type="button" onclick="filterVacancyRows('all', this)">Semua Status</button>
      <button class="filter-btn-pill" type="button" onclick="filterVacancyRows('draft', this)">Draft</button>
      <button class="filter-btn-pill" type="button" onclick="filterVacancyRows('review', this)">Menunggu Verifikasi</button>
      <button class="filter-btn-pill" type="button" onclick="filterVacancyRows('active', this)">Aktif</button>
      <div class="search-input-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Cari berdasarkan judul" onkeyup="searchVacancies(this.value)" />
      </div>
    </div>

    <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:10px;">Menampilkan <?php echo count($vacancies); ?> lowongan proyek</p>

    <div class="vacancy-table-wrap">
      <table class="vacancy-table">
        <thead>
          <tr>
            <th>Lowongan</th>
            <th>Penempatan</th>
            <th>Kuota Tersedia</th>
            <th>Pelamar</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="vacancy-table-body">
          <?php foreach ($vacancies as $i => $job): ?>
          <tr data-status="<?php echo htmlspecialchars($job['status'], ENT_QUOTES, 'UTF-8'); ?>">
            <td>
              <div class="job-cell">
                <div class="job-avatar" style="background:<?php echo $colors[$i % count($colors)]; ?>">
                  <?php echo strtoupper(substr($job['title'], 0, 2)); ?>
                </div>
                <div>
                  <div class="job-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="job-sub">Dibuat <?php echo htmlspecialchars($job['posted'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
            </td>
            <td><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo (int)$job['acceptedCount']; ?>/<?php echo (int)$job['quota']; ?> tersedia</td>
            <td>
              <a href="employer-pelamar.php" style="color:var(--primary-blue);font-weight:700;text-decoration:none;">
                <?php echo (int)$job['applicantsCount']; ?> pelamar
              </a>
            </td>
            <td>
              <span class="status-text <?php echo htmlspecialchars($job['status'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($job['statusLabel'], ENT_QUOTES, 'UTF-8'); ?>
              </span>
            </td>
            <td>
              <a class="icon-btn" href="employer-pelamar.php" title="Lihat pelamar" style="display:inline-flex;align-items:center;justify-content:center;">⋯</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
