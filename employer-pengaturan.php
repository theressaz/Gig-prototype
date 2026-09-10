<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';

$pageTitle = 'Pengaturan';
$pageKey = 'pengaturan';
$breadcrumbCurrent = 'Pengaturan';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Pengaturan</h1>
    </div>

    <section class="white-card settings-card">
      <div class="card-title-group" style="margin-bottom:16px;">
        <h2>Akun Perusahaan</h2>
        <p>Kelola identitas pemberi kerja di KarirHub Gig Workers</p>
      </div>
      <div class="settings-row">
        <div>
          <strong>Nama perusahaan</strong>
          <div style="font-size:0.84rem;color:var(--text-muted);"><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <span class="chip">Perusahaan</span>
      </div>
      <div class="settings-row">
        <div>
          <strong>ID Perusahaan</strong>
          <div style="font-size:0.84rem;color:var(--text-muted);">EMP-99201</div>
        </div>
      </div>
      <div class="settings-row">
        <div>
          <strong>Privasi kontak pelamar</strong>
          <div style="font-size:0.84rem;color:var(--text-muted);">Kontak hanya terbuka setelah kesepakatan kerja sama.</div>
        </div>
        <span style="color:#059669;font-weight:700;font-size:0.8rem;">Aktif</span>
      </div>
      <div class="settings-row">
        <div>
          <strong>Sesi</strong>
          <div style="font-size:0.84rem;color:var(--text-muted);">Keluar dari dashboard pemberi kerja</div>
        </div>
        <form method="post" action="">
          <button class="btn-action-sm" type="submit" name="logout" value="1">Keluar</button>
        </form>
      </div>
    </section>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
