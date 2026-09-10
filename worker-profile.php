<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$workerId = trim((string)($_GET['id'] ?? ''));
$worker = $workerId !== '' ? gig_find_worker($workerId) : null;
if ($worker === null) {
    header('Location: employer-pelamar.php');
    exit;
}

$contactUnlocked = !empty($worker['agreed']);
$pageTitle = $worker['name'] . ' · Profil Gig Worker';
$pageKey = 'pelamar';
$breadcrumbCurrent = $worker['name'];
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <h1><?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
      <a class="btn-action-sm" href="employer-pelamar.php">← Kembali ke Pelamar</a>
    </div>

    <?php if ($contactUnlocked): ?>
      <div class="privacy-banner unlocked">Kedua belah pihak telah menyetujui kerja sama. Informasi kontak dapat dilihat di bawah.</div>
    <?php else: ?>
      <div class="privacy-banner">Kontak disembunyikan sampai Pemberi Kerja dan Gig Worker sama-sama menyetujui kerja sama.</div>
    <?php endif; ?>

    <section class="profile-hero">
      <div class="profile-photo-wrap">
        <img class="profile-photo" src="<?php echo htmlspecialchars($worker['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto profil <?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?>" />
        <?php if (!empty($worker['verified'])): ?><span class="verified-dot">✓</span><?php endif; ?>
      </div>
      <div class="profile-id">
        <div class="worker-display-name"><?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="profile-title"><?php echo htmlspecialchars($worker['title'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($worker['location'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="profile-meta">
          <span class="stars"><?php echo gig_stars((float)$worker['rating']); ?></span>
          <strong><?php echo number_format((float)$worker['rating'], 1); ?></strong>
          <span class="chip gold"><?php echo (int)$worker['reviews_count']; ?> ulasan pemberi kerja</span>
          <span class="chip"><?php echo (int)$worker['completed_projects']; ?> proyek selesai</span>
          <?php if (!empty($worker['top_rated'])): ?><span class="chip gold">Top Rated</span><?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section-card">
      <h2>Keterampilan</h2>
      <div class="skill-row">
        <?php foreach ($worker['skills'] as $skill): ?>
          <span class="skill-tag"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Pengalaman</h2>
      <div class="timeline">
        <?php foreach ($worker['experience'] as $item): ?>
          <article class="timeline-item">
            <strong><?php echo htmlspecialchars($item['role'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span class="muted"><?php echo htmlspecialchars($item['project'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($item['period'], ENT_QUOTES, 'UTF-8'); ?></span>
            <p><?php echo htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Portofolio</h2>
      <div class="portfolio-grid">
        <?php foreach ($worker['portfolio'] as $item): ?>
          <article class="portfolio-card">
            <div class="portfolio-cover" style="background:linear-gradient(135deg, <?php echo htmlspecialchars($worker['color'], ENT_QUOTES, 'UTF-8'); ?>, #1e293b);">
              <?php echo htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="portfolio-body">
              <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <div class="muted"><?php echo htmlspecialchars($item['client'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($item['year'], ENT_QUOTES, 'UTF-8'); ?></div>
              <p><?php echo htmlspecialchars($item['deliverable'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Rating &amp; Ulasan Pemberi Kerja</h2>
      <div class="review-list">
        <?php foreach ($worker['reviews'] as $review): ?>
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
    </section>

    <section class="section-card">
      <h2>Informasi Kontak</h2>
      <?php if ($contactUnlocked): ?>
        <div class="contact-box">
          <span class="contact-tag">WhatsApp: <?php echo htmlspecialchars($worker['contact']['wa'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="contact-tag">Email: <?php echo htmlspecialchars($worker['contact']['email'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php else: ?>
        <div class="locked-box">
          Kontak belum dapat dibuka. Setelah Anda dan <?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?> menyetujui kerja sama, WhatsApp dan email akan ditampilkan di sini.
        </div>
      <?php endif; ?>
    </section>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
