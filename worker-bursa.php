<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';

// Only open projects with "active" (Tayang Aktif) status are visible to Gig Workers
$vacancies = array_values(array_filter(gig_project_vacancies(), static function ($job) {
    return $job['status'] === 'active';
}));

$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $vacancies = array_values(array_filter($vacancies, static function ($job) use ($q) {
        $hay = strtolower($job['title'] . ' ' . $job['category'] . ' ' . implode(' ', $job['skills']));
        return str_contains($hay, strtolower($q));
    }));
}

$pageTitle = 'Lowongan Proyek';
$pageKey = 'bursa';
$breadcrumbCurrent = 'Lowongan Proyek';
require __DIR__ . '/includes/worker-layout-start.php';
?>

    <div class="page-toolbar">
      <div>
        <h1>Lowongan Proyek</h1>
        <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Lowongan proyek yang bisa Anda lamar</p>
      </div>
    </div>

    <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:12px;">Menampilkan <?php echo count($vacancies); ?> lowongan proyek</p>

    <div class="project-grid">
      <?php foreach ($vacancies as $job): ?>
      <article class="project-card">
        <div class="project-top-meta">
          <span class="project-category-tag"><?php echo htmlspecialchars($job['category'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <h3 class="project-card-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
        <p class="project-card-desc"><?php echo htmlspecialchars($job['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="project-skill-tags">
          <?php foreach (array_slice($job['skills'], 0, 4) as $skill): ?>
            <span class="skill-tag-item"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
          <?php endforeach; ?>
        </div>
        <div class="project-card-footer">
          <span class="job-sub"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
          <button class="btn-action-sm" type="button" onclick="showToast('Lamaran terkirim. Pemberi kerja hanya melihat profil akun Anda, bukan kontak.')">Lamar</button>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
