<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/project-offers.php';

gig_seed_demo_offers_if_needed($username);
$offers = gig_offers_for_worker($username);

$pageTitle = 'Penawaran Proyek';
$pageKey = 'penawaran';
$breadcrumbCurrent = 'Penawaran Proyek';
require __DIR__ . '/includes/worker-layout-start.php';

function gig_offer_banner_class(string $cat): string
{
    $c = strtolower($cat);
    if (str_contains($c, 'desain') || str_contains($c, 'ui/ux')) {
        return 'banner-blue';
    }
    if (str_contains($c, 'it') || str_contains($c, 'backend') || str_contains($c, 'pemrograman')) {
        return 'banner-cyan';
    }
    if (str_contains($c, 'pemasaran') || str_contains($c, 'konten') || str_contains($c, 'marketing')) {
        return 'banner-purple';
    }
    if (str_contains($c, 'data')) {
        return 'banner-green';
    }
    return 'banner-indigo';
}
?>

    <div class="page-toolbar">
      <h1>Penawaran Proyek</h1>
    </div>

    <div class="notice-bar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Pemberi kerja dapat menawarkan lowongan yang sudah tayang. Klik penawaran untuk membuka detail proyek.</span>
    </div>

    <?php if (count($offers) === 0): ?>
      <section class="white-card offer-empty">
        <div class="offer-empty-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <h2>Belum ada penawaran</h2>
        <p>Ketika pemberi kerja menawarkan proyek yang sudah diposting, tawaran itu akan muncul di halaman ini.</p>
        <a class="btn-primary-add" href="worker-bursa.php">Cari Proyek Sendiri</a>
      </section>
    <?php else: ?>
      <div class="offer-list">
        <?php foreach ($offers as $offer):
            $detailUrl = 'worker-project-detail.php?id=' . urlencode((string)$offer['detail_id']) . '&from=penawaran';
            $created = strtotime((string)($offer['created_at'] ?? ''));
            $createdLabel = $created ? date('d M Y, H:i', $created) . ' WIB' : 'Baru saja';
            $status = (string)($offer['status'] ?? 'pending');
            $statusLabel = $status === 'pending' ? 'Menunggu tanggapan' : ucfirst($status);
        ?>
          <a class="offer-card" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="offer-card-accent offer-accent-<?php echo htmlspecialchars(gig_offer_banner_class((string)$offer['category']), ENT_QUOTES, 'UTF-8'); ?>"></div>
            <div class="offer-card-body">
              <div class="offer-card-top">
                <span class="offer-status"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="offer-date"><?php echo htmlspecialchars($createdLabel, ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <h2 class="offer-title"><?php echo htmlspecialchars((string)$offer['project_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="offer-employer">Ditawarkan oleh <strong><?php echo htmlspecialchars((string)$offer['employer_display'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
              <div class="offer-meta">
                <span><?php echo htmlspecialchars((string)$offer['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><?php echo htmlspecialchars((string)$offer['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><?php echo htmlspecialchars((string)$offer['location'], ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <?php if (!empty($offer['skills']) && is_array($offer['skills'])): ?>
                <div class="offer-skills">
                  <?php foreach (array_slice($offer['skills'], 0, 4) as $skill): ?>
                    <span class="offer-skill"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="offer-card-cta">
              <span>Lihat Detail Proyek</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
