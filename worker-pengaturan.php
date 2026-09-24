<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$pageTitle = 'Pengaturan';
$pageKey = 'pengaturan';
$breadcrumbCurrent = 'Pengaturan';
require __DIR__ . '/includes/worker-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Pengaturan</h1>
    </div>

    <section class="white-card settings-card">
      <div class="card-title-group" style="margin-bottom:16px;">
        <h2>Akun Gig Worker</h2>
        <p>Kelola identitas Anda di KarirHub</p>
      </div>
      <?php 
        $workerObj = gig_find_worker($username) ?? gig_find_worker('tessa');
        $displayWorkerName = $workerObj['name'] ?? 'Theressa Zaratrusha';
        $displayWorkerEmail = $workerObj['contact']['email'] ?? 'theressaz@pasker.id';
      ?>
      <div class="settings-row">
        <div>
          <strong>Informasi Profil</strong>
          <div style="font-size:0.84rem;color:var(--text-muted);"><?php echo htmlspecialchars($displayWorkerName, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($displayWorkerEmail, ENT_QUOTES, 'UTF-8'); ?>)</div>
        </div>
        <a class="btn-action-sm" href="worker-edit-profile.php" style="text-decoration:none;">✏️ Edit Profil</a>
      </div>
      <div class="settings-row">
        <div>
          <strong>Privasi kontak</strong>
          <div style="font-size:0.84rem;color:var(--text-muted);">WhatsApp dan email hanya terbuka setelah kesepakatan kerja sama.</div>
        </div>
        <span style="color:#059669;font-weight:700;font-size:0.8rem;">Aktif</span>
      </div>
      <div class="settings-row">
        <div>
          <strong>Sesi</strong>
          <div style="font-size:0.84rem;color:var(--text-muted);">Keluar dari dashboard Gig Worker</div>
        </div>
        <form method="post" action="">
          <button class="btn-action-sm" type="submit" name="logout" value="1">Keluar</button>
        </form>
      </div>
    </section>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
