<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/admin-store.php';
require_once __DIR__ . '/includes/vacancy-store.php';

$vacancyId = trim((string)($_GET['id'] ?? ''));
$flash = (string)($_GET['msg'] ?? '');
$flashType = (string)($_GET['type'] ?? 'success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $note = trim((string)($_POST['admin_note'] ?? ''));
    if (!empty($_POST['compliance_reasons']) && is_array($_POST['compliance_reasons'])) {
        $reasons = array_filter(array_map(static fn($r) => trim((string)$r), $_POST['compliance_reasons']));
        if ($reasons !== []) {
            $reasonsText = 'Ketidakpatuhan: ' . implode(', ', $reasons);
            $note = $note !== '' ? $reasonsText . '. ' . $note : $reasonsText;
        }
    }
    if ($action === 'vacancy_decision') {
        $decision = (string)($_POST['decision'] ?? '');
        $targetId = trim((string)($_POST['vacancy_id'] ?? $vacancyId));
        if ($decision === 'approve') {
            $ok = gig_vacancy_set_status($targetId, 'active', $note !== '' ? $note : 'Disetujui Admin KarirHub.');
            $flash = $ok ? 'Lowongan disetujui dan ditayangkan.' : 'Gagal menyetujui lowongan.';
        } elseif ($decision === 'reject') {
            if ($note === '') {
                $note = 'Lowongan tidak memenuhi Syarat & Ketentuan KarirHub.';
            }
            $ok = gig_vacancy_set_status($targetId, 'rejected', $note);
            $flash = $ok ? 'Lowongan ditolak.' : 'Gagal menolak lowongan.';
        } else {
            if ($note === '') {
                $note = 'Harap perbaiki detail lowongan sesuai catatan Admin.';
            }
            $ok = gig_vacancy_set_status($targetId, 'revision', $note);
            $flash = $ok ? 'Lowongan dikembalikan untuk revisi.' : 'Gagal mengirim catatan revisi.';
        }
        $flashType = !empty($ok) ? 'success' : 'error';
        $params = ['id' => $targetId, 'msg' => $flash, 'type' => $flashType];
        header('Location: admin-project-detail.php?' . http_build_query($params));
        exit;
    }
}

$vacancy = $vacancyId !== '' ? gig_find_vacancy($vacancyId) : null;

$dbMeta = [];
$submittedAt = '';
$reviewedAt = '';
if ($vacancy !== null && function_exists('gig_db')) {
    $db = gig_db();
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT `created_at`, `reviewed_at`, `status`, `admin_note` FROM `project_vacancies` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (string)$vacancy['id']]);
            $dbMeta = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $submittedAt = trim((string)($dbMeta['created_at'] ?? ''));
            $reviewedAt = trim((string)($dbMeta['reviewed_at'] ?? ''));
        } catch (Throwable $e) {
        }
    }
}

if ($vacancy === null) {
    $adminTab = 'projects';
    $pageTitle = 'Detail Verifikasi Lowongan';
    $breadcrumbCurrent = 'Detail Verifikasi Lowongan';
    require __DIR__ . '/includes/admin-layout-start.php';
    echo '<div class="empty-state">Data lowongan tidak ditemukan. <a href="dashboard-admin.php?tab=projects">Kembali ke verifikasi lowongan</a>.</div>';
    require __DIR__ . '/includes/admin-layout-end.php';
    exit;
}

$status = (string)($vacancy['status'] ?? 'review');
$statusLabel = (string)($vacancy['statusLabel'] ?? 'Menunggu Verifikasi');
$statusBg = '#dbeafe';
$statusColor = '#1d4ed8';
if ($status === 'active') {
    $statusLabel = 'Disetujui';
    $statusBg = '#d1fae5';
    $statusColor = '#065f46';
} elseif ($status === 'revision') {
    $statusLabel = 'Revisi';
    $statusBg = '#fef3c7';
    $statusColor = '#92400e';
} elseif ($status === 'rejected') {
    $statusLabel = 'Ditolak';
    $statusBg = '#fee2e2';
    $statusColor = '#991b1b';
}

$postedStamp = $submittedAt !== '' ? strtotime($submittedAt) : strtotime((string)($vacancy['posted'] ?? ''));
if (!$postedStamp) {
    $postedStamp = time();
}
$verifyDeadlineTs = strtotime('+3 days', $postedStamp);
$daysLeft = (int)ceil(($verifyDeadlineTs - time()) / 86400);
$slaLabel = $daysLeft > 0 ? $daysLeft . ' hari lagi' : ($daysLeft === 0 ? 'Hari ini' : 'Lewat tenggat');
$blacklistStatus = 'Tidak terdeteksi';

$auditLogs = [];
$auditLogs[] = [
    'time' => date('d M Y, H:i', $postedStamp),
    'title' => 'Pengajuan lowongan dibuat',
    'detail' => 'Lowongan masuk antrean verifikasi admin.',
];
if ($reviewedAt !== '') {
    $reviewStatus = $status === 'active' ? 'disetujui' : ($status === 'rejected' ? 'ditolak' : 'dikembalikan untuk revisi');
    $auditLogs[] = [
        'time' => date('d M Y, H:i', strtotime($reviewedAt)),
        'title' => 'Lowongan ' . $reviewStatus,
        'detail' => trim((string)($vacancy['adminNote'] ?? '')) !== '' ? (string)$vacancy['adminNote'] : 'Keputusan admin tercatat.',
    ];
}

$employerName = (string)($vacancy['employer'] ?? 'Perusahaan');
$skills = is_array($vacancy['skills'] ?? null) ? $vacancy['skills'] : [];
$adminTab = 'projects';
$pageTitle = 'Detail Verifikasi Lowongan';
$breadcrumbCurrent = 'Detail Verifikasi Lowongan';
require __DIR__ . '/includes/admin-layout-start.php';
?>

<style>
  .proj-detail-wrap { background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 4px 14px rgba(15,23,42,.05);overflow:hidden; }
  .proj-detail-top { padding:18px;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start; }
  .proj-crumb { font-size:.72rem;color:#64748b;font-weight:700;letter-spacing:.03em;text-transform:uppercase; }
  .proj-title { font-size:1.7rem;font-weight:800;color:#0f172a;line-height:1.2;margin:4px 0 8px; }
  .proj-meta { display:flex;gap:12px;flex-wrap:wrap;font-size:.82rem;color:#64748b; }
  .proj-grid-top { padding:16px;display:grid;grid-template-columns:2fr 1fr;gap:12px; }
  .proj-card { background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px; }
  .proj-card h3 { margin:0 0 10px 0;font-size:.95rem;font-weight:800;color:#0f172a; }
  .proj-mini-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px 14px; }
  .proj-mini-label { font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.03em; }
  .proj-mini-value { font-size:.82rem;color:#0f172a;font-weight:600;line-height:1.4; }
  .proj-audit-item { border-left:2px solid #bfdbfe;padding-left:9px;margin-bottom:10px; }
  .proj-audit-item:last-child { margin-bottom:0; }
  .proj-audit-time { font-size:.72rem;color:#64748b;font-weight:700; }
  .proj-audit-title { font-size:.82rem;color:#0f172a;font-weight:700; margin-top:2px; }
  .proj-audit-detail { font-size:.78rem;color:#475569; line-height:1.45; margin-top:2px; }
  .proj-main { padding:0 16px 16px; display:grid; gap:12px; }
  .skill-chip { display:inline-flex;padding:4px 10px;border-radius:999px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;font-size:.74rem;font-weight:700;margin:2px 6px 2px 0; }
  .proj-flash { padding:11px 13px;border-radius:10px;margin:0 0 12px 0;font-size:.84rem;font-weight:700; }
  .proj-flash.success { background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0; }
  .proj-flash.error { background:#fef2f2;color:#991b1b;border:1px solid #fecaca; }
  .proj-row { display:grid;grid-template-columns:170px 1fr;gap:10px;padding:6px 0;border-bottom:1px dashed #eef2f7; }
  .proj-row:last-child { border-bottom:none; }
  .proj-row .k { font-size:.78rem;color:#64748b;font-weight:700; }
  .proj-row .v { font-size:.82rem;color:#0f172a;font-weight:600;line-height:1.45; }
  @media (max-width: 980px) { .proj-grid-top { grid-template-columns:1fr; } .proj-mini-grid { grid-template-columns:1fr; } .proj-row { grid-template-columns:1fr; gap:4px; } }
</style>

<div style="margin-bottom:10px;">
  <a href="dashboard-admin.php?tab=projects" style="font-size:0.84rem;font-weight:700;color:#334155;">&larr; Kembali</a>
</div>

<?php if ($flash !== ''): ?>
  <div class="proj-flash <?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<section class="proj-detail-wrap">
  <div class="proj-detail-top">
    <div>
      <div class="proj-crumb">Detail Pengajuan Verifikasi Lowongan</div>
      <div class="proj-title"><?php echo htmlspecialchars((string)$vacancy['title'], ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="proj-meta">
        <span><?php echo htmlspecialchars($employerName, ENT_QUOTES, 'UTF-8'); ?></span>
        <span>Perusahaan</span>
        <span>Diajukan <?php echo htmlspecialchars(date('d M Y, H:i', $postedStamp), ENT_QUOTES, 'UTF-8'); ?></span>
        <span style="display:inline-flex;padding:3px 10px;border-radius:999px;background:<?php echo htmlspecialchars($statusBg, ENT_QUOTES, 'UTF-8'); ?>;color:<?php echo htmlspecialchars($statusColor, ENT_QUOTES, 'UTF-8'); ?>;font-weight:800;"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    </div>
    <button type="button" class="detail-btn primary" style="background:#0ea5e9;color:#fff;border-color:#0ea5e9;" onclick="openAdminDecisionModal({entityType:'vacancy', entityName:'lowongan proyek', vacancyId:<?php echo json_encode((string)$vacancy['id']); ?>, action:'vacancy_decision'})">Ambil Keputusan</button>
  </div>

  <div class="proj-grid-top">
    <article class="proj-card">
      <h3>Ringkasan Pengajuan</h3>
      <div class="proj-mini-grid">
        <div><div class="proj-mini-label">Jenis Lowongan</div><div class="proj-mini-value">Lowongan Kerja Proyek</div></div>
        <div><div class="proj-mini-label">Tanggal Pengajuan</div><div class="proj-mini-value"><?php echo htmlspecialchars(date('d M Y, H:i', $postedStamp), ENT_QUOTES, 'UTF-8'); ?></div></div>
        <div><div class="proj-mini-label">Wilayah</div><div class="proj-mini-value"><?php echo htmlspecialchars((string)($vacancy['location'] ?? 'Remote'), ENT_QUOTES, 'UTF-8'); ?></div></div>
        <div><div class="proj-mini-label">Blacklist</div><div class="proj-mini-value" style="color:#065f46;"><?php echo htmlspecialchars($blacklistStatus, ENT_QUOTES, 'UTF-8'); ?></div></div>
        <div><div class="proj-mini-label">SLA Verifikasi</div><div class="proj-mini-value"><?php echo htmlspecialchars($slaLabel, ENT_QUOTES, 'UTF-8'); ?></div></div>
      </div>
    </article>
    <article class="proj-card">
      <h3>Aktivitas &amp; Audit Log</h3>
      <?php foreach ($auditLogs as $log): ?>
        <div class="proj-audit-item">
          <div class="proj-audit-time"><?php echo htmlspecialchars((string)$log['time'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="proj-audit-title"><?php echo htmlspecialchars((string)$log['title'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="proj-audit-detail"><?php echo htmlspecialchars((string)$log['detail'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
      <?php endforeach; ?>
    </article>
  </div>

  <div class="proj-main">
    <article class="proj-card">
      <h3>Informasi Keputusan dan Verifikasi</h3>
      <div class="proj-mini-grid">
        <div><div class="proj-mini-label">Deadline Verifikasi</div><div class="proj-mini-value"><?php echo htmlspecialchars(date('d M Y, H:i', $verifyDeadlineTs), ENT_QUOTES, 'UTF-8'); ?></div></div>
        <div><div class="proj-mini-label">Pemeriksaan pada</div><div class="proj-mini-value"><?php echo htmlspecialchars(date('d M Y, H:i', $postedStamp), ENT_QUOTES, 'UTF-8'); ?></div></div>
        <div><div class="proj-mini-label">Nama petugas</div><div class="proj-mini-value"><?php echo htmlspecialchars((string)($adminName ?? 'Admin KarirHub'), ENT_QUOTES, 'UTF-8'); ?></div></div>
        <div><div class="proj-mini-label">Email petugas</div><div class="proj-mini-value"><?php echo htmlspecialchars((string)($_SESSION['admin_email'] ?? 'admin@kemnaker.go.id'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      </div>
    </article>

    <article class="proj-card">
      <h3>Informasi Lowongan</h3>
      <div class="proj-row"><div class="k">Judul Lowongan</div><div class="v"><?php echo htmlspecialchars((string)$vacancy['title'], ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Pemberi Kerja</div><div class="v"><?php echo htmlspecialchars($employerName, ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Bidang Pekerjaan</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['category'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Jenis Pekerjaan</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['work_type'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Industri / Sektor</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['industry'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Durasi Proyek</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['duration'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Rentang Gaji</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['budget'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Lokasi Pekerjaan</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['location'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Status Lowongan</div><div class="v"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Batas Lamaran</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['deadline'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Tingkat Pengalaman</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['experience_level'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Visibilitas</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['visibility'] ?? 'public'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Deskripsi</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['desc'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Persyaratan Khusus</div><div class="v"><?php echo htmlspecialchars((string)($vacancy['qualifications'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
      <div class="proj-row"><div class="k">Keahlian yang Dibutuhkan</div><div class="v"><?php if ($skills === []) { echo '-'; } else { foreach ($skills as $skill) { echo '<span class="skill-chip">' . htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8') . '</span>'; } } ?></div></div>
      <div class="proj-row"><div class="k">Kontak Lowongan</div><div class="v"><?php echo htmlspecialchars($employerName, ENT_QUOTES, 'UTF-8'); ?></div></div>
    </article>
  </div>
</section>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
