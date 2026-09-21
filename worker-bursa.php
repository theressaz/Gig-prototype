<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';

// Only open projects with "active" (Tayang Aktif) status are visible to Gig Workers
$allActiveVacancies = array_values(array_filter(gig_project_vacancies(), static function ($job) {
    return $job['status'] === 'active';
}));

$q = trim((string)($_GET['q'] ?? ''));
$selectedCat = trim((string)($_GET['category'] ?? 'all'));
$selectedBudget = trim((string)($_GET['budget'] ?? 'all'));

$vacancies = $allActiveVacancies;

// Filter by Keyword / Skill
if ($q !== '') {
    $vacancies = array_values(array_filter($vacancies, static function ($job) use ($q) {
        $hay = strtolower($job['title'] . ' ' . $job['category'] . ' ' . implode(' ', $job['skills']) . ' ' . $job['desc']);
        return str_contains($hay, strtolower($q));
    }));
}

// Filter by Category
if ($selectedCat !== '' && $selectedCat !== 'all') {
    $vacancies = array_values(array_filter($vacancies, static function ($job) use ($selectedCat) {
        return strtolower($job['category']) === strtolower($selectedCat);
    }));
}

// Filter by Budget Range
if ($selectedBudget !== '' && $selectedBudget !== 'all') {
    $vacancies = array_values(array_filter($vacancies, static function ($job) use ($selectedBudget) {
        $bVal = (int) preg_replace('/[^0-9]/', '', $job['budget']);
        if ($selectedBudget === 'under_5m') return $bVal < 5000000;
        if ($selectedBudget === '5m_10m') return $bVal >= 5000000 && $bVal <= 10000000;
        if ($selectedBudget === 'over_10m') return $bVal > 10000000;
        return true;
    }));
}

$pageTitle = 'Lowongan Proyek';
$pageKey = 'bursa';
$breadcrumbCurrent = 'Lowongan Proyek';
require __DIR__ . '/includes/worker-layout-start.php';
?>

    <div class="page-toolbar" style="margin-bottom: 16px;">
      <div>
        <h1>Lowongan Proyek</h1>
        <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Temukan dan lamar lowongan proyek yang sesuai dengan keahlian Anda</p>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div style="background: #ffffff; border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 18px 20px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
      <form method="get" action="" id="filterForm" style="display: flex; flex-direction: column; gap: 14px;">
        
        <!-- TOP ROW: SEARCH INPUT + BUDGET SELECT -->
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 12px; align-items: center;">
          <div class="search-input-box" style="width: 100%;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" placeholder="Cari judul proyek, kata kunci, atau skill (e.g. Figma, React, Copywriting)..." value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" />
          </div>

          <div>
            <select name="budget" class="form-select" onchange="document.getElementById('filterForm').submit()" style="font-size: 0.85rem; padding: 8px 12px;">
              <option value="all" <?php echo $selectedBudget === 'all' ? 'selected' : ''; ?>>-- Semua Nilai Anggaran --</option>
              <option value="under_5m" <?php echo $selectedBudget === 'under_5m' ? 'selected' : ''; ?>>&lt; Rp 5.000.000</option>
              <option value="5m_10m" <?php echo $selectedBudget === '5m_10m' ? 'selected' : ''; ?>>Rp 5.000.000 - Rp 10.000.000</option>
              <option value="over_10m" <?php echo $selectedBudget === 'over_10m' ? 'selected' : ''; ?>>&gt; Rp 10.000.000</option>
            </select>
          </div>

          <button type="submit" class="btn-primary-add" style="padding: 8px 18px; font-size: 0.85rem;">Cari</button>
        </div>

        <!-- BOTTOM ROW: CATEGORY PILLS -->
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; padding-top: 10px; border-top: 1px dashed var(--border-subtle);">
          <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-right: 4px;">Kategori:</span>
          
          <?php 
            $categories = [
              'all' => 'Semua Kategori',
              'Desain & Kreatif' => 'Desain & Kreatif',
              'IT & Pemrograman' => 'IT & Pemrograman',
              'Pemasaran & Konten' => 'Pemasaran & Konten',
              'Data Analytics & Entry' => 'Data Analytics & Entry',
            ];
          ?>
          <?php foreach ($categories as $catKey => $catLabel): ?>
            <a href="?category=<?php echo urlencode($catKey); ?>&budget=<?php echo urlencode($selectedBudget); ?>&q=<?php echo urlencode($q); ?>" 
               class="filter-btn-pill <?php echo strtolower($selectedCat) === strtolower($catKey) ? 'active' : ''; ?>" 
               style="font-size: 0.8rem; text-decoration: none;">
              <?php echo htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8'); ?>
            </a>
          <?php endforeach; ?>

          <?php if ($q !== '' || $selectedCat !== 'all' || $selectedBudget !== 'all'): ?>
            <a href="worker-bursa.php" style="font-size: 0.78rem; color: #ef4444; text-decoration: underline; font-weight: 700; margin-left: 8px;">Reset Filter</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- RESULT COUNT -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <p style="font-size:0.85rem;color:var(--text-muted);">
        Menampilkan <strong><?php echo count($vacancies); ?></strong> lowongan proyek terbuka
      </p>
    </div>

    <!-- PROJECT GRID -->
    <?php if (empty($vacancies)): ?>
      <div style="background: #ffffff; border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 40px 20px; text-align: center; color: var(--text-muted);">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: #94a3b8;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">Tidak ada lowongan proyek yang cocok</h3>
        <p style="font-size: 0.88rem;">Coba sesuaikan kata kunci pencarian atau reset filter kategori &amp; anggaran.</p>
        <a href="worker-bursa.php" class="btn-action-sm" style="display: inline-block; margin-top: 14px; text-decoration: none;">Tampilkan Semua Lowongan</a>
      </div>
    <?php else: ?>
      <div class="project-grid">
        <?php foreach ($vacancies as $job): ?>
        <article class="project-card" style="display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div class="project-top-meta" style="flex-wrap: wrap; gap: 8px;">
              <span class="project-category-tag"><?php echo htmlspecialchars($job['category'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span style="font-size: 0.76rem; background: #eff6ff; color: #1d4ed8; font-weight: 700; padding: 3px 10px; border-radius: 9999px; border: 1px solid #bfdbfe; display: inline-flex; align-items: center; gap: 4px;">
                👤 Kuota: <?php echo (int)($job['quota'] ?? 1); ?> Freelancer
              </span>
              <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-left: auto;">📅 <?php echo htmlspecialchars($job['posted'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            
            <a href="worker-project-detail.php?id=<?php echo urlencode($job['id']); ?>" style="text-decoration: none;">
              <h3 class="project-card-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            </a>
            
            <p class="project-card-desc"><?php echo htmlspecialchars($job['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
            
            <div class="project-skill-tags" style="margin-bottom: 16px;">
              <?php foreach (array_slice($job['skills'], 0, 4) as $skill): ?>
                <span class="skill-tag-item"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="project-card-footer" style="padding-top: 12px; border-top: 1px dashed var(--border-subtle);">
            <div>
              <span class="job-sub" style="font-weight: 800; color: var(--primary-blue); font-size: 0.95rem;"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span style="font-size: 0.76rem; color: var(--text-muted); display: block;">Estimasi: <?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?> &bull; <strong>Kuota: <?php echo (int)($job['quota'] ?? 1); ?> Orang</strong></span>
            </div>

            <a href="worker-project-detail.php?id=<?php echo urlencode($job['id']); ?>" class="btn-primary-add" style="font-size: 0.8rem; padding: 7px 14px; text-decoration: none;">
              Lihat Detail &amp; Lamar &rarr;
            </a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
