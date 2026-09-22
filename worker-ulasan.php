<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-history.php';

$profileId = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode(' ', $username)[0] ?? $username));
$worker = gig_find_worker($profileId);
$reviews = gig_get_reviews_for_worker($username);
if ($reviews === []) {
    $reviews = $worker['reviews'] ?? [];
}

$pageTitle = 'Ulasan Mitra';
$pageKey = 'ulasan';
$breadcrumbCurrent = 'Ulasan';
require __DIR__ . '/includes/worker-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Ulasan Mitra</h1>
    </div>

    <?php if ($reviews === []): ?>
      <section class="white-card">
        <p style="color:var(--text-muted);">Belum ada ulasan. Ulasan muncul setelah proyek selesai.</p>
      </section>
    <?php else: ?>
      <div class="review-list">
        <?php foreach ($reviews as $review): ?>
          <article class="review-card">
            <div class="review-top">
              <div>
                <strong><?php echo htmlspecialchars($review['employer'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <div class="muted"><?php echo htmlspecialchars($review['project'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($review['date'], ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div>
                <span class="stars"><?php echo gig_stars((float)$review['rating']); ?></span>
                <strong><?php echo number_format((float)$review['rating'], 1); ?></strong>
              </div>
            </div>
            <p><?php echo htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
