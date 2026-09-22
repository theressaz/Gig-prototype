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
  max-width: 1400px;
  margin: 0 auto;
}

/* Filter Header Bar matching user screenshot */
.filter-header-bar {
  background: #ffffff;
  border-radius: 20px;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.03);
  border: 1px solid #f1f5f9;
}

.category-pills-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.cat-pill-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  border-radius: 9999px;
  font-size: 0.88rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  color: #475569;
  background: #f1f5f9;
}

.cat-pill-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.cat-pill-btn.active {
  background: #1d4ed8;
  color: #ffffff;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(29, 78, 216, 0.3);
}

.search-pill-form {
  display: flex;
  align-items: center;
}

.search-pill-box {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 9999px;
  padding: 8px 18px;
  width: 340px;
  transition: all 0.2s ease;
}

.search-pill-box:focus-within {
  border-color: #3b82f6;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.search-pill-box input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.86rem;
  width: 100%;
  color: #1e293b;
  margin-left: 8px;
}

/* Sub-header row */
.subheader-info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  font-size: 0.88rem;
}

.subheader-count {
  color: #64748b;
  font-weight: 500;
}

.subheader-badge {
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
}

/* Grid layout: 3 columns per row for clean spacious layout */
.proyek-cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

@media (max-width: 992px) {
  .proyek-cards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .filter-header-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .search-pill-box {
    width: 100%;
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

/* Metadata box with Salary, Duration, and Kuota */
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

  <!-- TOP FILTER HEADER BAR -->
  <div class="filter-header-bar">
    <!-- Category Pills Left -->
    <div class="category-pills-wrapper">
      <?php 
        $catMap = [
          'all' => 'Semua Bidang (' . count($allActiveVacancies) . ')',
          'UI/UX & Desain' => 'UI/UX & Desain',
          'Backend & API' => 'Backend & API',
          'Pemasaran' => 'Pemasaran',
          'Data & Analitik' => 'Data & Analitik',
        ];
      ?>
      <?php foreach ($catMap as $catKey => $catLabel): ?>
        <?php $isActive = (strtolower($selectedCat) === strtolower($catKey) || ($catKey === 'all' && $selectedCat === 'all')); ?>
        <a href="?category=<?php echo urlencode($catKey); ?>&budget=<?php echo urlencode($selectedBudget); ?>&q=<?php echo urlencode($q); ?>" 
           class="cat-pill-btn <?php echo $isActive ? 'active' : ''; ?>">
          <?php echo htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8'); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Search Input Right -->
    <form method="get" action="" class="search-pill-form">
      <?php if ($selectedCat !== 'all'): ?>
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCat, ENT_QUOTES, 'UTF-8'); ?>">
      <?php endif; ?>
      <div class="search-pill-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="q" placeholder="Cari nama, keahlian, atau bidang..." value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" />
      </div>
    </form>
  </div>

  <!-- SUB-HEADER INFO ROW -->
  <div class="subheader-info-row">
    <div class="subheader-count">
      Menampilkan <strong><?php echo count($vacancies); ?></strong> Proyek siap dilamar
    </div>
    <div class="subheader-badge">
      <span>💡</span> Maks. 3 penawaran per lowongan aktif
    </div>
  </div>

  <!-- PROJECT GRID -->
  <?php if (empty($vacancies)): ?>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 48px 20px; text-align: center; color: #64748b;">
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: #94a3b8;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Tidak ada proyek yang cocok</h3>
      <p style="font-size: 0.9rem;">Coba sesuaikan kata kunci pencarian atau pilih kategori lain.</p>
      <a href="worker-bursa.php" class="cat-pill-btn active" style="display: inline-flex; margin-top: 16px; text-decoration: none;">Tampilkan Semua Proyek</a>
    </div>
  <?php else: ?>
    <div class="proyek-cards-grid">
      <?php foreach ($vacancies as $idx => $job): ?>
        <?php 
          $badgeLabel = getCategoryShortLabel($job['category']);
          $quotaNum = (int)($job['quota'] ?? 1);
          $employerDisplayName = (string)($job['employer'] ?? ($job['client'] ?? 'PT SIAPKerja Partner'));
          $skillsToShow = array_slice($job['skills'], 0, 3);
          $remainingCount = count($job['skills']) - count($skillsToShow);
        ?>
        <a class="proyek-card-item" href="worker-project-detail.php?id=<?php echo urlencode($job['id']); ?>">
          <div>
            <!-- Employer Avatar + Name + Category Badge -->
            <div class="card-employer-header">
              <div class="card-employer-left">
                <div class="employer-avatar-circle-sm">🏢</div>
                <div>
                  <div class="card-employer-name"><?php echo htmlspecialchars($employerDisplayName, ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="card-employer-sub">📍 <?php echo htmlspecialchars($job['location'] ?? 'Remote', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
              <span class="card-cat-badge"><?php echo htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <!-- Project Title -->
            <h3 class="card-project-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>

            <!-- Salary, Duration, & Kuota -->
            <div class="card-meta-detail">
              <div class="card-salary"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="card-meta-pills">
                <span class="meta-pill-info">⏱️ <?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="meta-pill-info">👥 Kuota: <?php echo $quotaNum; ?></span>
              </div>
            </div>

            <!-- Skills List -->
            <div class="card-skills-row">
              <?php foreach ($skillsToShow as $sk): ?>
                <span class="skill-pill-sm"><?php echo htmlspecialchars((string)$sk, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
              <?php if ($remainingCount > 0): ?>
                <span class="skill-pill-more">+<?php echo $remainingCount; ?></span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Card Action Footer -->
          <div class="card-action-footer">
            <span>Lihat Detail Proyek</span>
            <span>&rarr;</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>

