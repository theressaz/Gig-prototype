<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/admin-store.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/vacancy-store.php';

$flash = '';
$flashType = 'success';
$tab = (string)($_GET['tab'] ?? 'overview');
$allowedTabs = ['overview', 'workers', 'employers', 'projects'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'overview';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $note = trim((string)($_POST['admin_note'] ?? ''));
    $redirectTab = (string)($_POST['tab'] ?? 'overview');

    if ($action === 'worker_approve') {
        $ok = gig_admin_set_worker_status((string)($_POST['username'] ?? ''), 'approved', $note);
        $flash = $ok ? 'Pendaftaran Gig Worker disetujui.' : 'Gagal memproses pendaftaran worker.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'workers';
    } elseif ($action === 'worker_reject') {
        $ok = gig_admin_set_worker_status((string)($_POST['username'] ?? ''), 'rejected', $note);
        $flash = $ok ? 'Pendaftaran Gig Worker ditolak.' : 'Gagal menolak pendaftaran worker.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'workers';
    } elseif ($action === 'employer_approve') {
        $ok = gig_admin_set_employer_status((int)($_POST['id'] ?? 0), 'approved', $note);
        $flash = $ok ? 'Pendaftaran pemberi kerja disetujui.' : 'Gagal memproses pendaftaran employer.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'employers';
    } elseif ($action === 'employer_reject') {
        $ok = gig_admin_set_employer_status((int)($_POST['id'] ?? 0), 'rejected', $note);
        $flash = $ok ? 'Pendaftaran pemberi kerja ditolak.' : 'Gagal menolak pendaftaran employer.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'employers';
    } elseif ($action === 'vacancy_approve') {
        $id = (string)($_POST['vacancy_id'] ?? '');
        $kind = (string)($_POST['vacancy_kind'] ?? 'catalog');
        $ok = $kind === 'submission'
            ? gig_admin_set_submission_status($id, 'active', $note !== '' ? $note : 'Disetujui Admin KarirHub.')
            : gig_admin_set_vacancy_override($id, 'active', $note !== '' ? $note : 'Disetujui Admin KarirHub.');
        $flash = $ok ? 'Lowongan proyek disetujui dan dapat ditayangkan.' : 'Gagal menyetujui lowongan.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'projects';
    } elseif ($action === 'vacancy_revision') {
        $id = (string)($_POST['vacancy_id'] ?? '');
        $kind = (string)($_POST['vacancy_kind'] ?? 'catalog');
        if ($note === '') {
            $note = 'Harap perbaiki detail lowongan sesuai catatan Admin.';
        }
        $ok = $kind === 'submission'
            ? gig_admin_set_submission_status($id, 'revision', $note)
            : gig_admin_set_vacancy_override($id, 'revision', $note);
        $flash = $ok ? 'Lowongan dikembalikan untuk revisi.' : 'Gagal mengirim permintaan revisi.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'projects';
    } elseif ($action === 'vacancy_reject') {
        $id = (string)($_POST['vacancy_id'] ?? '');
        $kind = (string)($_POST['vacancy_kind'] ?? 'catalog');
        if ($note === '') {
            $note = 'Lowongan tidak memenuhi Syarat & Ketentuan KarirHub.';
        }
        $ok = $kind === 'submission'
            ? gig_admin_set_submission_status($id, 'rejected', $note)
            : gig_admin_set_vacancy_override($id, 'rejected', $note);
        $flash = $ok ? 'Lowongan proyek ditolak.' : 'Gagal menolak lowongan.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'projects';
    }

    header('Location: dashboard-admin.php?tab=' . urlencode($redirectTab) . '&msg=' . urlencode($flash) . '&type=' . urlencode($flashType));
    exit;
}

if (isset($_GET['msg'])) {
    $flash = (string)$_GET['msg'];
    $flashType = (string)($_GET['type'] ?? 'success');
}

$workers = gig_admin_list_worker_registrations();
$employers = gig_admin_list_employer_registrations();
$vacancies = gig_project_vacancies();

$pendingWorkers = array_values(array_filter($workers, fn($r) => ($r['status'] ?? 'pending') === 'pending'));
$pendingEmployers = array_values(array_filter($employers, fn($r) => ($r['status'] ?? 'pending') === 'pending'));
$pendingProjects = array_values(array_filter($vacancies, fn($v) => ($v['status'] ?? '') === 'review'));

function admin_status_badge(string $status): string
{
    $map = [
        'pending' => ['Menunggu', '#fef3c7', '#92400e'],
        'approved' => ['Disetujui', '#d1fae5', '#065f46'],
        'active' => ['Tayang', '#d1fae5', '#065f46'],
        'rejected' => ['Ditolak', '#fee2e2', '#991b1b'],
        'revision' => ['Revisi', '#ffedd5', '#9a3412'],
        'review' => ['Verifikasi', '#dbeafe', '#1e40af'],
    ];
    $item = $map[$status] ?? [$status, '#f1f5f9', '#334155'];
    return '<span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;background:' . $item[1] . ';color:' . $item[2] . ';">' . htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') . '</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin · Karirhub</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/employer.css" />
  <style>
    .admin-shell { max-width: 1200px; margin: 0 auto; padding: 24px 20px 48px; }
    .admin-top { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
    .admin-top h1 { font-size: 1.5rem; }
    .admin-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
    .admin-tabs a { text-decoration: none; padding: 8px 14px; border-radius: 999px; border: 1px solid var(--border-subtle); color: var(--text-soft); font-size: 0.85rem; font-weight: 700; background: #fff; }
    .admin-tabs a.active { background: var(--primary-blue); border-color: var(--primary-blue); color: #fff; }
    .flash { padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-size: 0.88rem; font-weight: 600; }
    .flash.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .flash.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .review-card { background: #fff; border: 1px solid var(--border-subtle); border-radius: 14px; padding: 18px; margin-bottom: 14px; box-shadow: var(--shadow-xs); }
    .review-card h3 { font-size: 1rem; margin-bottom: 6px; }
    .review-meta { color: var(--text-muted); font-size: 0.82rem; margin-bottom: 10px; }
    .review-detail { font-size: 0.86rem; color: var(--text-soft); margin-bottom: 12px; line-height: 1.55; }
    .review-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-start; }
    .review-actions textarea { width: 100%; min-height: 70px; border: 1px solid var(--border-light); border-radius: 10px; padding: 10px; font: inherit; margin-bottom: 8px; }
    .btn-approve { background: #059669; color: #fff; border: none; border-radius: 8px; padding: 8px 14px; font-weight: 700; cursor: pointer; }
    .btn-reject { background: #dc2626; color: #fff; border: none; border-radius: 8px; padding: 8px 14px; font-weight: 700; cursor: pointer; }
    .btn-revision { background: #d97706; color: #fff; border: none; border-radius: 8px; padding: 8px 14px; font-weight: 700; cursor: pointer; }
    .btn-logout { background: #fff; border: 1px solid var(--border-light); border-radius: 8px; padding: 8px 12px; font-weight: 700; cursor: pointer; }
    .empty-state { background: #fff; border: 1px dashed var(--border-light); border-radius: 12px; padding: 28px; text-align: center; color: var(--text-muted); }
  </style>
</head>
<body style="background: var(--bg-page);">
  <div class="admin-shell">
    <div class="admin-top">
      <div>
        <h1>Panel Verifikasi Admin</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Kelola pendaftaran Gig Worker, pemberi kerja, dan pengajuan lowongan proyek.</p>
      </div>
      <form method="post">
        <input type="hidden" name="logout" value="1" />
        <button type="submit" class="btn-logout">Keluar</button>
      </form>
    </div>

    <?php if ($flash !== ''): ?>
      <div class="flash <?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <nav class="admin-tabs">
      <a href="?tab=overview" class="<?php echo $tab === 'overview' ? 'active' : ''; ?>">Ringkasan</a>
      <a href="?tab=workers" class="<?php echo $tab === 'workers' ? 'active' : ''; ?>">Gig Worker (<?php echo count($pendingWorkers); ?>)</a>
      <a href="?tab=employers" class="<?php echo $tab === 'employers' ? 'active' : ''; ?>">Pemberi Kerja (<?php echo count($pendingEmployers); ?>)</a>
      <a href="?tab=projects" class="<?php echo $tab === 'projects' ? 'active' : ''; ?>">Lowongan Proyek (<?php echo count($pendingProjects); ?>)</a>
    </nav>

    <?php if ($tab === 'overview'): ?>
      <section class="mini-stats" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
        <article class="mini-stat"><div><div class="lbl">Worker Menunggu</div><div class="num"><?php echo count($pendingWorkers); ?></div></div><div class="ico">👷</div></article>
        <article class="mini-stat"><div><div class="lbl">Employer Menunggu</div><div class="num"><?php echo count($pendingEmployers); ?></div></div><div class="ico">🏢</div></article>
        <article class="mini-stat"><div><div class="lbl">Lowongan Menunggu</div><div class="num"><?php echo count($pendingProjects); ?></div></div><div class="ico">📋</div></article>
      </section>
      <p style="margin-top:18px;color:var(--text-muted);font-size:0.88rem;">Gunakan tab di atas untuk menyetujui, menolak, atau meminta revisi pada setiap pengajuan.</p>
    <?php endif; ?>

    <?php if ($tab === 'workers'): ?>
      <?php if ($workers === []): ?>
        <div class="empty-state">Belum ada pendaftaran Gig Worker di database.</div>
      <?php endif; ?>
      <?php foreach ($workers as $row): ?>
        <article class="review-card">
          <h3><?php echo htmlspecialchars((string)$row['username'], ENT_QUOTES, 'UTF-8'); ?> <?php echo admin_status_badge((string)($row['status'] ?? 'pending')); ?></h3>
          <div class="review-meta">Bidang: <?php echo htmlspecialchars((string)$row['bidang_keahlian'], ENT_QUOTES, 'UTF-8'); ?> · Email: <?php echo htmlspecialchars((string)$row['contact_email'], ENT_QUOTES, 'UTF-8'); ?> · WA: <?php echo htmlspecialchars((string)$row['contact_wa'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="review-detail">Skills: <?php echo htmlspecialchars(is_array($row['skills']) ? implode(', ', $row['skills']) : (string)$row['skills'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if (($row['status'] ?? '') === 'pending'): ?>
            <form method="post" class="review-actions">
              <input type="hidden" name="tab" value="workers" />
              <input type="hidden" name="username" value="<?php echo htmlspecialchars((string)$row['username'], ENT_QUOTES, 'UTF-8'); ?>" />
              <textarea name="admin_note" placeholder="Catatan verifikasi (opsional)"></textarea>
              <button class="btn-approve" name="action" value="worker_approve" type="submit">Setujui</button>
              <button class="btn-reject" name="action" value="worker_reject" type="submit">Tolak</button>
            </form>
          <?php elseif (!empty($row['admin_note'])): ?>
            <div class="review-detail"><strong>Catatan Admin:</strong> <?php echo htmlspecialchars((string)$row['admin_note'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($tab === 'employers'): ?>
      <?php if ($employers === []): ?>
        <div class="empty-state">Belum ada pendaftaran pemberi kerja Gig Worker.</div>
      <?php endif; ?>
      <?php foreach ($employers as $row): ?>
        <article class="review-card">
          <h3><?php echo htmlspecialchars((string)$row['nama_pic'], ENT_QUOTES, 'UTF-8'); ?> <?php echo admin_status_badge((string)($row['status'] ?? 'pending')); ?></h3>
          <div class="review-meta">SIAPkerja: <?php echo htmlspecialchars((string)$row['siapkerja_email'], ENT_QUOTES, 'UTF-8'); ?> · Industri: <?php echo htmlspecialchars((string)$row['industry'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="review-detail">PIC: <?php echo htmlspecialchars((string)$row['nama_pic'], ENT_QUOTES, 'UTF-8'); ?> · NIK: <?php echo htmlspecialchars((string)$row['nik_pic'], ENT_QUOTES, 'UTF-8'); ?> · Email: <?php echo htmlspecialchars((string)$row['email_pic'], ENT_QUOTES, 'UTF-8'); ?> · Telp: <?php echo htmlspecialchars((string)$row['phone_pic'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if (($row['status'] ?? '') === 'pending'): ?>
            <form method="post" class="review-actions">
              <input type="hidden" name="tab" value="employers" />
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>" />
              <textarea name="admin_note" placeholder="Catatan verifikasi (opsional)"></textarea>
              <button class="btn-approve" name="action" value="employer_approve" type="submit">Setujui</button>
              <button class="btn-reject" name="action" value="employer_reject" type="submit">Tolak</button>
            </form>
          <?php elseif (!empty($row['admin_note'])): ?>
            <div class="review-detail"><strong>Catatan Admin:</strong> <?php echo htmlspecialchars((string)$row['admin_note'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($tab === 'projects'): ?>
      <?php if ($pendingProjects === []): ?>
        <div class="empty-state">Tidak ada lowongan yang menunggu verifikasi saat ini.</div>
      <?php endif; ?>
      <?php foreach ($vacancies as $job): ?>
        <?php if (($job['status'] ?? '') !== 'review') { continue; } ?>
        <?php $kind = !empty($job['is_submission']) ? 'submission' : 'catalog'; ?>
        <article class="review-card">
          <h3><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?> <?php echo admin_status_badge('review'); ?></h3>
          <div class="review-meta">ID: <?php echo htmlspecialchars((string)$job['id'], ENT_QUOTES, 'UTF-8'); ?> · Pemberi Kerja: <?php echo htmlspecialchars((string)($job['employer'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?> · Kategori: <?php echo htmlspecialchars((string)$job['category'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="review-detail"><?php echo htmlspecialchars((string)$job['desc'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="review-detail">Budget: <?php echo htmlspecialchars((string)$job['budget'], ENT_QUOTES, 'UTF-8'); ?> · Durasi: <?php echo htmlspecialchars((string)$job['duration'], ENT_QUOTES, 'UTF-8'); ?> · Lokasi: <?php echo htmlspecialchars((string)$job['location'], ENT_QUOTES, 'UTF-8'); ?></div>
          <form method="post" class="review-actions">
            <input type="hidden" name="tab" value="projects" />
            <input type="hidden" name="vacancy_id" value="<?php echo htmlspecialchars((string)$job['id'], ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" name="vacancy_kind" value="<?php echo htmlspecialchars($kind, ENT_QUOTES, 'UTF-8'); ?>" />
            <textarea name="admin_note" placeholder="Catatan untuk employer (wajib untuk revisi/penolakan)"></textarea>
            <button class="btn-approve" name="action" value="vacancy_approve" type="submit">Setujui & Tayang</button>
            <button class="btn-revision" name="action" value="vacancy_revision" type="submit">Minta Revisi</button>
            <button class="btn-reject" name="action" value="vacancy_reject" type="submit">Tolak</button>
          </form>
        </article>
      <?php endforeach; ?>

      <h2 style="margin: 24px 0 12px; font-size: 1rem;">Riwayat / Status Lain</h2>
      <?php foreach ($vacancies as $job): ?>
        <?php if (($job['status'] ?? '') === 'review') { continue; } ?>
        <article class="review-card" style="opacity:0.92;">
          <h3><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?> <?php echo admin_status_badge((string)($job['status'] ?? '')); ?></h3>
          <div class="review-meta"><?php echo htmlspecialchars((string)$job['id'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars((string)($job['employer'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if (!empty($job['adminNote'])): ?>
            <div class="review-detail"><?php echo htmlspecialchars((string)$job['adminNote'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
