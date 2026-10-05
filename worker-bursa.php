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

// Filter by Keyword / Skill / Title / Client
if ($q !== '') {
    $vacancies = array_values(array_filter($vacancies, static function ($job) use ($q) {
        $hay = strtolower($job['title'] . ' ' . $job['category'] . ' ' . implode(' ', $job['skills']) . ' ' . $job['desc'] . ' ' . $job['client']);
        return str_contains($hay, strtolower($q));
    }));
}

// Filter by Category
if ($selectedCat !== '' && $selectedCat !== 'all') {
    $vacancies = array_values(array_filter($vacancies, static function ($job) use ($selectedCat) {
        $jobCat = strtolower($job['category']);
        $selCat = strtolower($selectedCat);
        if ($selCat === 'ui/ux & desain' || $selCat === 'desain & kreatif') {
            return str_contains($jobCat, 'desain') || str_contains($jobCat, 'ui/ux');
        }
        if ($selCat === 'backend & api' || $selCat === 'it & pemrograman') {
            return str_contains($jobCat, 'it') || str_contains($jobCat, 'backend') || str_contains($jobCat, 'pemrograman');
        }
        if ($selCat === 'pemasaran' || $selCat === 'pemasaran & konten') {
            return str_contains($jobCat, 'pemasaran') || str_contains($jobCat, 'konten') || str_contains($jobCat, 'marketing');
        }
        if ($selCat === 'data & analitik' || $selCat === 'data analytics & entry') {
            return str_contains($jobCat, 'data');
        }
        return $jobCat === $selCat;
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

// Banner & category label helpers are loaded from includes/project-vacancies.php


$pageTitle = 'Cari Proyek';
$pageKey = 'bursa';
$breadcrumbCurrent = 'Cari Proyek';
require __DIR__ . '/includes/worker-layout-start.php';
?>

<style>
.cari-proyek-container {
  max-width: 1460px;
  margin: 0 auto;
}

.bursa-layout {
  display: grid;
  grid-template-columns: 290px 1fr;
  gap: 20px;
  align-items: start;
}

.filter-panel {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05);
  padding: 16px 14px;
  position: sticky;
  top: 86px;
}

.filter-panel h2 {
  margin: 0 0 12px 0;
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
}

.filter-search-form { margin-bottom: 12px; }

.filter-search-box {
  display: flex;
  align-items: center;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  padding: 8px 10px;
}

.filter-search-box:focus-within {
  border-color: #60a5fa;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
  background: #fff;
}

.filter-search-box input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  font-size: 0.82rem;
  color: #0f172a;
  margin-left: 8px;
}

.filter-group {
  border-top: 1px solid #f1f5f9;
  padding-top: 11px;
  margin-top: 11px;
}

.filter-group-title {
  font-size: 0.78rem;
  font-weight: 800;
  color: #334155;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.filter-link-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 4px;
}

.filter-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 7px 9px;
  border-radius: 8px;
  border: 1px solid transparent;
  color: #475569;
  font-size: 0.82rem;
  font-weight: 600;
  text-decoration: none;
}

.filter-link:hover {
  background: #f8fafc;
  color: #1e293b;
}

.filter-link.active {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #1d4ed8;
  font-weight: 700;
}

.filter-pill-count {
  font-size: 0.72rem;
  background: #e2e8f0;
  color: #334155;
  border-radius: 999px;
  padding: 1px 7px;
}

.filter-link.active .filter-pill-count {
  background: #dbeafe;
  color: #1e40af;
}

.filter-reset {
  display: inline-flex;
  margin-top: 12px;
  font-size: 0.78rem;
  font-weight: 700;
  color: #2563eb;
}

.results-pane {
  min-width: 0;
}

.results-topbar {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 13px 16px;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.results-title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 800;
  color: #0f172a;
}

.results-sub {
  margin: 3px 0 0 0;
  color: #64748b;
  font-size: 0.82rem;
}

.results-chips {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.results-chip {
  display: inline-flex;
  padding: 5px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  font-size: 0.74rem;
  color: #334155;
  background: #f8fafc;
  font-weight: 700;
}

/* Grid layout: 3 columns per row for clean spacious layout */
.proyek-cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

@media (max-width: 992px) {
  .bursa-layout {
    grid-template-columns: 1fr;
  }
  .filter-panel {
    position: static;
  }
  .proyek-cards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 640px) {
  .proyek-cards-grid {
    grid-template-columns: 1fr;
  }
}

/* Clean Professional Card Design for Cari Proyek */
.proyek-card-item {
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 22px;
  transition: all 0.2s ease;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}

a.proyek-card-item:hover {
  transform: translateY(-3px);
  border-color: #93c5fd;
  box-shadow: 0 12px 28px rgba(37, 99, 235, 0.1);
  color: inherit;
}

.card-employer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
  gap: 12px;
}

.card-employer-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Same profile picture avatar as Penawaran Proyek */
.employer-avatar-circle-sm {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #2563eb;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
  box-shadow: 0 3px 8px rgba(37, 99, 235, 0.22);
}

.card-employer-name {
  font-size: 0.88rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.25;
}

.card-employer-sub {
  font-size: 0.78rem;
  color: #64748b;
  margin-top: 2px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.card-cat-badge {
  background: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 9999px;
  white-space: nowrap;
  border: 1px solid #e2e8f0;
}

.card-project-title {
  font-size: 1.08rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 14px 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 2.8em;
}

/* Metadata box with Salary and Duration */
.card-meta-detail {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  padding: 12px 16px;
  border-radius: 12px;
  margin-bottom: 16px;
  border: 1px solid #f1f5f9;
}

.card-salary {
  font-size: 1.02rem;
  font-weight: 800;
  color: #2563eb;
}

.card-meta-pills {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.8rem;
  color: #475569;
  font-weight: 600;
}

.meta-pill-info {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.card-skills-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  min-height: 32px;
  align-content: flex-start;
  margin-bottom: 16px;
}

.skill-pill-sm {
  background: #ffffff;
  color: #475569;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.skill-pill-more {
  background: #f1f5f9;
  color: #334155;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 5px 10px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
}

.card-action-footer {
  padding-top: 14px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.84rem;
  font-weight: 700;
  color: #2563eb;
}

</style>

<div class="cari-proyek-container">
  <?php
    $catCounts = [
      'all' => count($allActiveVacancies),
      'UI/UX & Desain' => count(array_filter($allActiveVacancies, static fn($j) => str_contains(strtolower((string)$j['category']), 'desain') || str_contains(strtolower((string)$j['category']), 'ui/ux'))),
      'Backend & API' => count(array_filter($allActiveVacancies, static fn($j) => str_contains(strtolower((string)$j['category']), 'it') || str_contains(strtolower((string)$j['category']), 'backend') || str_contains(strtolower((string)$j['category']), 'pemrograman'))),
      'Pemasaran' => count(array_filter($allActiveVacancies, static fn($j) => str_contains(strtolower((string)$j['category']), 'pemasaran') || str_contains(strtolower((string)$j['category']), 'konten') || str_contains(strtolower((string)$j['category']), 'marketing'))),
      'Data & Analitik' => count(array_filter($allActiveVacancies, static fn($j) => str_contains(strtolower((string)$j['category']), 'data'))),
    ];
    $catMap = [
      'all' => 'Semua Bidang',
      'UI/UX & Desain' => 'UI/UX & Desain',
      'Backend & API' => 'Backend & API',
      'Pemasaran' => 'Pemasaran',
      'Data & Analitik' => 'Data & Analitik',
    ];
    $budgetMap = [
      'all' => 'Semua Rentang Gaji',
      'under_5m' => 'Di bawah 5 Juta',
      '5m_10m' => '5 Juta - 10 Juta',
      'over_10m' => 'Di atas 10 Juta',
    ];
  ?>

  <div class="bursa-layout">
    <aside class="filter-panel">
      <h2>Filter</h2>

      <form method="get" action="" class="filter-search-form">
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCat, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="budget" value="<?php echo htmlspecialchars($selectedBudget, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="filter-search-box">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="q" placeholder="Cari lowongan..." value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
      </form>

      <div class="filter-group">
        <div class="filter-group-title">Kategori</div>
        <ul class="filter-link-list">
          <?php foreach ($catMap as $catKey => $catLabel): ?>
            <?php $isActive = (strtolower($selectedCat) === strtolower($catKey) || ($catKey === 'all' && $selectedCat === 'all')); ?>
            <li>
              <a class="filter-link <?php echo $isActive ? 'active' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query(['category' => $catKey, 'budget' => $selectedBudget, 'q' => $q]), ENT_QUOTES, 'UTF-8'); ?>">
                <span><?php echo htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="filter-pill-count"><?php echo (int)($catCounts[$catKey] ?? 0); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="filter-group">
        <div class="filter-group-title">Rentang Gaji</div>
        <ul class="filter-link-list">
          <?php foreach ($budgetMap as $budgetKey => $budgetLabel): ?>
            <?php $isBudgetActive = $selectedBudget === $budgetKey || ($selectedBudget === '' && $budgetKey === 'all'); ?>
            <li>
              <a class="filter-link <?php echo $isBudgetActive ? 'active' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query(['category' => $selectedCat, 'budget' => $budgetKey, 'q' => $q]), ENT_QUOTES, 'UTF-8'); ?>">
                <span><?php echo htmlspecialchars($budgetLabel, ENT_QUOTES, 'UTF-8'); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <a class="filter-reset" href="worker-bursa.php">Reset semua filter</a>
    </aside>

    <section class="results-pane">
      <div class="results-topbar">
        <div>
          <h2 class="results-title">Lowongan Dalam Negeri</h2>
          <p class="results-sub">Menampilkan <strong><?php echo count($vacancies); ?></strong> proyek siap dilamar.</p>
        </div>
        <div class="results-chips">
          <span class="results-chip">Kategori: <?php echo htmlspecialchars($catMap[$selectedCat] ?? 'Semua Bidang', ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="results-chip">Gaji: <?php echo htmlspecialchars($budgetMap[$selectedBudget] ?? 'Semua Rentang Gaji', ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      </div>

      <?php if (empty($vacancies)): ?>
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 48px 20px; text-align: center; color: #64748b;">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: #94a3b8;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Tidak ada proyek yang cocok</h3>
          <p style="font-size: 0.9rem;">Coba sesuaikan kata kunci pencarian atau pilih kategori lain.</p>
          <a href="worker-bursa.php" class="results-chip" style="display: inline-flex; margin-top: 16px; text-decoration: none;">Tampilkan Semua Proyek</a>
        </div>
      <?php else: ?>
        <div class="proyek-cards-grid">
          <?php foreach ($vacancies as $idx => $job): ?>
            <?php
              $badgeLabel = getCategoryShortLabel($job['category']);
              $employerDisplayName = (string)($job['employer'] ?? ($job['client'] ?? 'PT SIAPKerja Partner'));
              $skillsToShow = array_slice($job['skills'], 0, 3);
              $remainingCount = count($job['skills']) - count($skillsToShow);
            ?>
            <a class="proyek-card-item" href="worker-project-detail.php?id=<?php echo urlencode($job['id']); ?>">
              <div>
                <div class="card-employer-header">
                  <div class="card-employer-left">
                    <div class="employer-avatar-circle-sm">🏢</div>
                    <div>
                      <div class="card-employer-name"><?php echo htmlspecialchars($employerDisplayName, ENT_QUOTES, 'UTF-8'); ?></div>
                      <div class="card-employer-sub">📍 <?php echo htmlspecialchars((string)($job['location'] ?? 'Lokasi belum diisi'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                  </div>
                  <span class="card-cat-badge"><?php echo htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <h3 class="card-project-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>

                <div class="card-meta-detail">
                  <div class="card-salary"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="card-meta-pills">
                    <span class="meta-pill-info">⏱️ <?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>
                </div>

                <div class="card-skills-row">
                  <?php foreach ($skillsToShow as $sk): ?>
                    <span class="skill-pill-sm"><?php echo htmlspecialchars((string)$sk, ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endforeach; ?>
                  <?php if ($remainingCount > 0): ?>
                    <span class="skill-pill-more">+<?php echo $remainingCount; ?></span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-action-footer">
                <span>Lihat Detail Proyek</span>
                <span>&rarr;</span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>

</div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>

