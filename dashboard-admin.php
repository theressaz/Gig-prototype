<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/admin-store.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/vacancy-store.php';

$flash = '';
$flashType = 'success';
$tab = (string)($_GET['tab'] ?? 'verification');
if ($tab === 'overview') {
    $tab = 'verification';
}
$allowedTabs = ['verification', 'workers', 'employers', 'projects'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'verification';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $note = trim((string)($_POST['admin_note'] ?? ''));
    $redirectTab = (string)($_POST['tab'] ?? 'verification');
    if ($redirectTab === 'overview') {
        $redirectTab = 'verification';
    }

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
    } elseif ($action === 'worker_edit_approve') {
        $ok = gig_admin_set_worker_profile_edit_status((int)($_POST['edit_id'] ?? 0), 'approved', $note);
        $flash = $ok ? 'Pengajuan edit profil worker disetujui.' : 'Gagal menyetujui pengajuan edit profil.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'workers';
    } elseif ($action === 'worker_edit_reject') {
        $ok = gig_admin_set_worker_profile_edit_status((int)($_POST['edit_id'] ?? 0), 'rejected', $note);
        $flash = $ok ? 'Pengajuan edit profil worker ditolak.' : 'Gagal menolak pengajuan edit profil.';
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
        $ok = gig_vacancy_set_status($id, 'active', $note !== '' ? $note : 'Disetujui Admin KarirHub.');
        $flash = $ok ? 'Lowongan proyek disetujui dan dapat ditayangkan.' : 'Gagal menyetujui lowongan.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'projects';
    } elseif ($action === 'vacancy_revision') {
        $id = (string)($_POST['vacancy_id'] ?? '');
        if ($note === '') {
            $note = 'Harap perbaiki detail lowongan sesuai catatan Admin.';
        }
        $ok = gig_vacancy_set_status($id, 'revision', $note);
        $flash = $ok ? 'Lowongan dikembalikan untuk revisi.' : 'Gagal mengirim permintaan revisi.';
        $flashType = $ok ? 'success' : 'error';
        $redirectTab = 'projects';
    } elseif ($action === 'vacancy_reject') {
        $id = (string)($_POST['vacancy_id'] ?? '');
        if ($note === '') {
            $note = 'Lowongan tidak memenuhi Syarat & Ketentuan KarirHub.';
        }
        $ok = gig_vacancy_set_status($id, 'rejected', $note);
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
$vacancies = gig_admin_list_project_vacancies();
$workerProfileEdits = gig_admin_list_worker_profile_edits();
$metrics = gig_admin_dashboard_metrics($workers, $employers, $vacancies);
$clusters = gig_admin_cluster_by_industry($employers, $vacancies);

$searchQ = trim((string)($_GET['q'] ?? ''));
$matchesSearch = static function (array $row, array $fields) use ($searchQ): bool {
    if ($searchQ === '') {
        return true;
    }
    $needle = strtolower($searchQ);
    foreach ($fields as $field) {
        if (str_contains(strtolower((string)($row[$field] ?? '')), $needle)) {
            return true;
        }
    }
    return false;
};

if ($searchQ !== '') {
    $workers = array_values(array_filter($workers, fn($r) => $matchesSearch($r, ['username', 'bidang_keahlian', 'contact_email', 'skills'])));
    $employers = array_values(array_filter($employers, fn($r) => $matchesSearch($r, ['nama_pic', 'company_name', 'siapkerja_email', 'industry'])));
    $vacancies = array_values(array_filter($vacancies, fn($r) => $matchesSearch($r, ['title', 'id', 'employer', 'category', 'location'])));
    $workerProfileEdits = array_values(array_filter($workerProfileEdits, fn($r) => $matchesSearch($r, ['worker_username', 'worker_email', 'edited_field', 'reason_code', 'reason_detail', 'change_summary'])));
}

$pendingWorkers = array_values(array_filter($workers, fn($r) => ($r['status'] ?? 'pending') === 'pending'));
$pendingEmployers = array_values(array_filter($employers, fn($r) => ($r['status'] ?? 'pending') === 'pending'));
$pendingProjects = array_values(array_filter($vacancies, fn($v) => ($v['status'] ?? '') === 'review'));
$pendingProfileEdits = array_values(array_filter($workerProfileEdits, fn($r) => ($r['status'] ?? 'pending') === 'pending'));
$nextPendingTab = $metrics['workers_pending'] > 0
    ? 'workers'
    : ($metrics['employers_pending'] > 0 ? 'employers' : ($metrics['vacancies_review'] > 0 ? 'projects' : 'verification'));
if ($nextPendingTab === 'verification' && (int)$metrics['worker_profile_edits_pending'] > 0) {
    $nextPendingTab = 'workers';
}

function admin_status_badge(string $status): string
{
    $map = [
        'pending' => ['Menunggu Revisi', '#fef3c7', '#92400e'],
        'approved' => ['Disetujui', '#d1fae5', '#065f46'],
        'active' => ['Tayang', '#d1fae5', '#065f46'],
        'rejected' => ['Ditolak', '#fee2e2', '#991b1b'],
        'revision' => ['Revisi', '#ffedd5', '#9a3412'],
        'review' => ['Verifikasi', '#dbeafe', '#1e40af'],
    ];
    $item = $map[$status] ?? [$status, '#f1f5f9', '#334155'];
    return '<span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;background:' . $item[1] . ';color:' . $item[2] . ';">' . htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') . '</span>';
}

function admin_bar_height(int $value, int $max, int $cap = 130): int
{
    if ($value <= 0) {
        return 8;
    }
    $max = max($max, 1);
    return max(10, (int)round($value / $max * $cap));
}

function admin_render_chart(string $title, array $segments): void
{
    $max = max(1, ...array_map(static fn($s) => (int)$s['value'], $segments));
    echo '<article class="admin-chart-card"><h3>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3><div class="admin-bars">';
    foreach ($segments as $seg) {
        $val = (int)$seg['value'];
        $h = admin_bar_height($val, $max);
        $color = htmlspecialchars((string)$seg['color'], ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string)$seg['label'], ENT_QUOTES, 'UTF-8');
        echo '<div class="admin-bar-col">';
        echo '<div class="admin-bar-val">' . $val . '</div>';
        echo '<div class="admin-bar" style="height:' . $h . 'px;background:' . $color . ';"></div>';
        echo '<div class="admin-bar-label">' . $label . '</div>';
        echo '</div>';
    }
    echo '</div></article>';
}

$adminTab = $tab;
$pageTitle = 'Dashboard Admin';
$breadcrumbCurrent = 'Dashboard';
require __DIR__ . '/includes/admin-layout-start.php';
?>

    <div class="admin-page-head">
      <h1>Dashboard</h1>
    </div>

    <?php if ($flash !== ''): ?>
      <div class="flash <?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <nav class="admin-subtabs" aria-label="Tab dashboard admin">
      <a href="?tab=verification" class="<?php echo $tab === 'verification' ? 'active' : ''; ?>">Verifikasi</a>
      <a href="?tab=workers" class="<?php echo $tab === 'workers' ? 'active' : ''; ?>">Gig Worker<?php $wQueue = (int)$metrics['workers_pending'] + (int)$metrics['worker_profile_edits_pending']; echo $wQueue > 0 ? ' (' . $wQueue . ')' : ''; ?></a>
      <a href="?tab=employers" class="<?php echo $tab === 'employers' ? 'active' : ''; ?>">Pemberi Kerja<?php echo $metrics['employers_pending'] > 0 ? ' (' . (int)$metrics['employers_pending'] . ')' : ''; ?></a>
      <a href="?tab=projects" class="<?php echo $tab === 'projects' ? 'active' : ''; ?>">Lowongan Proyek<?php echo $metrics['vacancies_review'] > 0 ? ' (' . (int)$metrics['vacancies_review'] . ')' : ''; ?></a>
    </nav>

    <?php if ($tab === 'verification'): ?>
      <div class="admin-toolbar-row">
        <a href="?tab=<?php echo urlencode($nextPendingTab); ?>" class="admin-btn-ghost">Menunggu verifikasi: <strong><?php echo (int)$metrics['pending_all']; ?></strong></a>
      </div>

      <section class="admin-kpi-grid">
        <a href="?tab=workers" class="admin-kpi-link">
          <article class="admin-kpi-card">
            <div class="kpi-label">Pengajuan Gig Worker</div>
            <div class="kpi-value"><?php echo number_format($metrics['workers_total'], 0, ',', '.'); ?></div>
            <div class="kpi-sub"><?php echo (int)$metrics['workers_pending']; ?> pendaftaran · <?php echo (int)$metrics['worker_profile_edits_pending']; ?> edit profil</div>
          </article>
        </a>
        <a href="?tab=employers" class="admin-kpi-link">
          <article class="admin-kpi-card accent-navy">
            <div class="kpi-label">Pengajuan Pemberi Kerja</div>
            <div class="kpi-value"><?php echo number_format($metrics['employers_total'], 0, ',', '.'); ?></div>
            <div class="kpi-sub"><?php echo (int)$metrics['employers_pending']; ?> menunggu verifikasi</div>
          </article>
        </a>
        <a href="?tab=projects" class="admin-kpi-link">
          <article class="admin-kpi-card accent-teal">
            <div class="kpi-label">Pengajuan Lowongan Proyek</div>
            <div class="kpi-value"><?php echo number_format($metrics['vacancies_total'], 0, ',', '.'); ?></div>
            <div class="kpi-sub"><?php echo (int)$metrics['vacancies_review']; ?> menunggu verifikasi</div>
          </article>
        </a>
        <a href="?tab=projects" class="admin-kpi-link">
          <article class="admin-kpi-card accent-green">
            <div class="kpi-label">Disetujui / Tayang</div>
            <div class="kpi-value"><?php echo number_format($metrics['workers_approved'] + $metrics['employers_approved'] + $metrics['vacancies_active'], 0, ',', '.'); ?></div>
            <div class="kpi-sub"><?php echo (int)$metrics['workers_approved']; ?> worker · <?php echo (int)$metrics['employers_approved']; ?> employer · <?php echo (int)$metrics['vacancies_active']; ?> lowongan aktif</div>
          </article>
        </a>
      </section>

      <div class="admin-chart-grid">
        <?php
        admin_render_chart('Status Verifikasi Gig Worker', [
            ['label' => 'Menunggu Revisi', 'value' => $metrics['workers_pending'], 'color' => '#f59e0b'],
            ['label' => 'Disetujui', 'value' => $metrics['workers_approved'], 'color' => '#1e3a8a'],
            ['label' => 'Ditolak', 'value' => $metrics['workers_rejected'], 'color' => '#ef4444'],
        ]);
        admin_render_chart('Status Verifikasi Pemberi Kerja', [
            ['label' => 'Menunggu Revisi', 'value' => $metrics['employers_pending'], 'color' => '#f59e0b'],
            ['label' => 'Terverifikasi', 'value' => $metrics['employers_approved'], 'color' => '#1e3a8a'],
            ['label' => 'Ditolak', 'value' => $metrics['employers_rejected'], 'color' => '#ef4444'],
        ]);
        ?>
      </div>
      <div class="admin-chart-grid">
        <?php
        admin_render_chart('Status Verifikasi Lowongan', [
            ['label' => 'Menunggu Revisi', 'value' => $metrics['vacancies_review'], 'color' => '#f59e0b'],
            ['label' => 'Revisi', 'value' => $metrics['vacancies_revision'], 'color' => '#14b8a6'],
            ['label' => 'Disetujui', 'value' => $metrics['vacancies_active'], 'color' => '#1e3a8a'],
            ['label' => 'Ditolak', 'value' => $metrics['vacancies_rejected'], 'color' => '#ef4444'],
        ]);
        ?>
        <article class="admin-chart-card">
          <h3>Antrian Verifikasi Terbaru</h3>
          <?php if ($metrics['pending_all'] === 0): ?>
            <p style="font-size:0.86rem;color:#64748b;">Tidak ada pengajuan yang menunggu verifikasi.</p>
          <?php else: ?>
            <?php foreach (array_slice($pendingWorkers, 0, 2) as $row): ?>
              <div class="admin-queue-item">
                <span><strong>Gig Worker</strong> · <?php echo htmlspecialchars((string)$row['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="?tab=workers">Proses →</a>
              </div>
            <?php endforeach; ?>
            <?php foreach (array_slice($pendingProfileEdits, 0, 2) as $edit): ?>
              <div class="admin-queue-item">
                <span><strong>Edit Profil</strong> · <?php echo htmlspecialchars((string)$edit['worker_username'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="?tab=workers">Proses →</a>
              </div>
            <?php endforeach; ?>
            <?php foreach (array_slice($pendingEmployers, 0, 2) as $row): ?>
              <div class="admin-queue-item">
                <span><strong>Pemberi Kerja</strong> · <?php echo htmlspecialchars((string)($row['company_name'] ?: $row['nama_pic']), ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="?tab=employers">Proses →</a>
              </div>
            <?php endforeach; ?>
            <?php foreach (array_slice($pendingProjects, 0, 2) as $job): ?>
              <div class="admin-queue-item">
                <span><strong>Lowongan</strong> · <?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="?tab=projects">Proses →</a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </article>
      </div>

      <section class="admin-region-card">
        <div class="admin-region-head">
          <div>
            <h3>Rekap Pengajuan per Klaster</h3>
            <p>Ringkasan pendaftaran pemberi kerja (per industri) dan lowongan proyek (per lokasi) dari database KarirHub Gig.</p>
          </div>
          <div class="admin-toggle" aria-hidden="true">
            <span class="on">Pemberi Kerja</span>
            <span>Lowongan</span>
          </div>
        </div>
        <?php if ($clusters === []): ?>
          <p style="font-size:0.86rem;color:#64748b;">Belum ada data klaster untuk ditampilkan.</p>
        <?php else: ?>
          <table class="admin-region-table">
            <thead>
              <tr>
                <th>Klaster</th>
                <th>Pemberi Kerja</th>
                <th>Lowongan Proyek</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clusters as $cluster): ?>
                <tr>
                  <td><?php echo htmlspecialchars((string)$cluster['label'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo (int)$cluster['employers']; ?></td>
                  <td><?php echo (int)$cluster['vacancies']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php if ($tab === 'workers'): ?>
      <h1 class="admin-dark-header">Verifikasi Gig Worker</h1>

      <?php
      $stFilter = strtolower((string)($_GET['status'] ?? 'pending'));
      $pendingWorkerCount = count($pendingWorkers) + count($pendingProfileEdits);
      ?>

      <div class="admin-dark-subtabs">
        <a href="?tab=workers&status=all" class="<?php echo $stFilter === 'all' ? 'active' : ''; ?>">Semua</a>
        <a href="?tab=workers&status=pending" class="<?php echo $stFilter === 'pending' ? 'active' : ''; ?>">
          Menunggu Verifikasi <?php if ($pendingWorkerCount > 0): ?><span class="badge-count-red"><?php echo $pendingWorkerCount; ?></span><?php endif; ?>
        </a>
        <a href="?tab=workers&status=revision" class="<?php echo $stFilter === 'revision' ? 'active' : ''; ?>">Revisi</a>
        <a href="?tab=workers&status=approved" class="<?php echo $stFilter === 'approved' ? 'active' : ''; ?>">Terverifikasi</a>
        <a href="?tab=workers&status=rejected" class="<?php echo $stFilter === 'rejected' ? 'active' : ''; ?>">Ditolak</a>
      </div>

      <div class="admin-dark-toolbar">
        <div class="admin-dark-search-box">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" placeholder="Cari gig worker..." onkeyup="searchAdminDarkTable(this.value)" />
        </div>
        <button class="btn-dark-filter" type="button" onclick="alert('Filter kriteria aktif.')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          Filter
        </button>
      </div>

      <div class="admin-dark-table-card">
        <table class="admin-dark-table" id="admin-dark-main-table">
          <thead>
            <tr>
              <th>Nama Gig Worker <span class="sort-icon">↑↓</span></th>
              <th>Keahlian / Profesi</th>
              <th>Lokasi</th>
              <th>Telepon / WA</th>
              <th>Status</th>
              <th>Deadline Verifikasi</th>
              <th>Pemeriksa</th>
              <th>Tanggal</th>
              <th style="text-align:right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $filteredWorkers = $workers;
            if ($stFilter !== 'all') {
                $filteredWorkers = array_values(array_filter($workers, fn($w) => ($w['status'] ?? 'pending') === $stFilter));
            }
            if (empty($filteredWorkers)): 
            ?>
              <tr>
                <td colspan="9" style="text-align:center;padding:36px;color:#64748b;">
                  Tidak ada data verifikasi gig worker untuk status ini.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($filteredWorkers as $row): 
                $wName = (string)($row['display_name'] ?: $row['username']);
                $wEmail = (string)($row['contact_email'] ?? ($row['username'] . '@pasker.id'));
                $wInit = strtoupper(substr($wName, 0, 2));
                $st = (string)($row['status'] ?? 'pending');
                $stClass = $st === 'approved' ? 'pill-status-green' : ($st === 'rejected' ? 'pill-status-red' : ($st === 'revision' ? 'pill-status-amber' : 'pill-status-blue'));
                $stLabel = $st === 'approved' ? '• Terverifikasi' : ($st === 'rejected' ? '• Ditolak' : ($st === 'revision' ? '• Revisi' : '• Dikirim'));
                $dlLabel = $st === 'approved' ? 'Terverifikasi' : '4 hari lagi';
                $skill = (string)($row['bidang_keahlian'] ?? 'Gig Worker Professional');
                $loc = (string)($row['domicile'] ?? 'Jakarta Selatan, DKI Jakarta');
                $phone = (string)($row['contact_wa'] ?? '081298765432');
                $dateStr = date('d M Y', strtotime($row['created_at'] ?? '2026-09-02'));
              ?>
                <tr>
                  <td>
                    <div class="dark-entity-info">
                      <div class="dark-avatar-badge"><?php echo htmlspecialchars($wInit, ENT_QUOTES, 'UTF-8'); ?></div>
                      <div class="dark-entity-details">
                        <span class="dark-entity-name"><?php echo htmlspecialchars($wName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="dark-entity-email"><?php echo htmlspecialchars($wEmail, ENT_QUOTES, 'UTF-8'); ?></span>
                      </div>
                    </div>
                  </td>
                  <td style="color:#cbd5e1;font-weight:600;"><?php echo htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="max-width:220px;color:#94a3b8;"><?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="color:#cbd5e1;"><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><span class="<?php echo $stClass; ?>"><?php echo $stLabel; ?></span></td>
                  <td><span class="pill-deadline-info">ℹ <?php echo htmlspecialchars($dlLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                  <td style="color:#64748b;">-</td>
                  <td style="color:#94a3b8;white-space:nowrap;"><?php echo htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="text-align:right;">
                    <button type="button" class="btn-dark-outline-detail" onclick="openAdminDarkModal('worker', '<?php echo htmlspecialchars(addslashes($row['username']), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($wName), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($wEmail), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($phone), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($loc), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($row['admin_note'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>')">
                      Lihat Detail
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($tab === 'employers'): ?>
      <h1 class="admin-dark-header">Verifikasi Pemberi Kerja</h1>

      <?php
      $stFilter = strtolower((string)($_GET['status'] ?? 'pending'));
      $pendingEmpCount = count($pendingEmployers);
      ?>

      <div class="admin-dark-subtabs">
        <a href="?tab=employers&status=all" class="<?php echo $stFilter === 'all' ? 'active' : ''; ?>">Semua</a>
        <a href="?tab=employers&status=pending" class="<?php echo $stFilter === 'pending' ? 'active' : ''; ?>">
          Menunggu Verifikasi <?php if ($pendingEmpCount > 0): ?><span class="badge-count-red"><?php echo $pendingEmpCount; ?></span><?php endif; ?>
        </a>
        <a href="?tab=employers&status=revision" class="<?php echo $stFilter === 'revision' ? 'active' : ''; ?>">Revisi</a>
        <a href="?tab=employers&status=approved" class="<?php echo $stFilter === 'approved' ? 'active' : ''; ?>">Terverifikasi</a>
        <a href="?tab=employers&status=rejected" class="<?php echo $stFilter === 'rejected' ? 'active' : ''; ?>">Ditolak</a>
      </div>

      <div class="admin-dark-toolbar">
        <div class="admin-dark-search-box">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" placeholder="Cari pemberi kerja..." onkeyup="searchAdminDarkTable(this.value)" />
        </div>
        <button class="btn-dark-filter" type="button" onclick="alert('Filter kriteria aktif.')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          Filter
        </button>
      </div>

      <div class="admin-dark-table-card">
        <table class="admin-dark-table" id="admin-dark-main-table">
          <thead>
            <tr>
              <th>Nama Pemberi Kerja <span class="sort-icon">↑↓</span></th>
              <th>Jenis Entitas</th>
              <th>Lokasi</th>
              <th>Telepon</th>
              <th>Status</th>
              <th>Deadline Verifikasi</th>
              <th>Pemeriksa</th>
              <th>Tanggal</th>
              <th style="text-align:right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $filteredEmployers = $employers;
            if ($stFilter !== 'all') {
                $filteredEmployers = array_values(array_filter($employers, fn($e) => ($e['status'] ?? 'pending') === $stFilter));
            }
            if (empty($filteredEmployers)): 
            ?>
              <tr>
                <td colspan="9" style="text-align:center;padding:36px;color:#64748b;">
                  Tidak ada data verifikasi pemberi kerja untuk status ini.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($filteredEmployers as $row): 
                $cName = (string)($row['company_name'] ?: $row['nama_pic']);
                $cEmail = (string)($row['email_pic'] ?: $row['siapkerja_email']);
                $cInit = strtoupper(substr($cName, 0, 2));
                $st = (string)($row['status'] ?? 'pending');
                $stClass = $st === 'approved' ? 'pill-status-green' : ($st === 'rejected' ? 'pill-status-red' : ($st === 'revision' ? 'pill-status-amber' : 'pill-status-blue'));
                $stLabel = $st === 'approved' ? '• Terverifikasi' : ($st === 'rejected' ? '• Ditolak' : ($st === 'revision' ? '• Revisi' : '• Dikirim'));
                $dlLabel = $st === 'approved' ? 'Terverifikasi' : ($row['deadline'] ?? '4 hari lagi');
                $loc = $row['location'] ?? ($row['industry'] . ', Indonesia');
                $phone = $row['phone_pic'] ?? '082552399300';
                $dateStr = date('d M Y', strtotime($row['created_at'] ?? '2026-09-02'));
              ?>
                <tr>
                  <td>
                    <div class="dark-entity-info">
                      <div class="dark-avatar-badge"><?php echo htmlspecialchars($cInit, ENT_QUOTES, 'UTF-8'); ?></div>
                      <div class="dark-entity-details">
                        <span class="dark-entity-name"><?php echo htmlspecialchars($cName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="dark-entity-email"><?php echo htmlspecialchars($cEmail, ENT_QUOTES, 'UTF-8'); ?></span>
                      </div>
                    </div>
                  </td>
                  <td style="color:#cbd5e1;font-weight:600;"><?php echo htmlspecialchars($row['jenis_entitas'] ?? 'Perusahaan', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="max-width:220px;color:#94a3b8;"><?php echo htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="color:#cbd5e1;"><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><span class="<?php echo $stClass; ?>"><?php echo $stLabel; ?></span></td>
                  <td><span class="pill-deadline-info">ℹ <?php echo htmlspecialchars($dlLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                  <td style="color:#64748b;">-</td>
                  <td style="color:#94a3b8;white-space:nowrap;"><?php echo htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td style="text-align:right;">
                    <button type="button" class="btn-dark-outline-detail" onclick="openAdminDarkModal('emp', '<?php echo (int)$row['id']; ?>', '<?php echo htmlspecialchars(addslashes($cName), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($cEmail), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($phone), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($loc), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($row['admin_note'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>')">
                      Lihat Detail
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($tab === 'projects'): ?>
      <?php if ($pendingProjects === []): ?>
        <div class="empty-state">Tidak ada lowongan yang menunggu verifikasi saat ini.</div>
      <?php endif; ?>
      <?php foreach ($vacancies as $job): ?>
        <?php if (($job['status'] ?? '') !== 'review') { continue; } ?>
        <article class="review-card">
          <h3><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?> <?php echo admin_status_badge('review'); ?></h3>
          <div class="review-meta">ID: <?php echo htmlspecialchars((string)$job['id'], ENT_QUOTES, 'UTF-8'); ?> · Pemberi Kerja: <?php echo htmlspecialchars((string)($job['employer'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?> · Kategori: <?php echo htmlspecialchars((string)$job['category'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="review-detail"><?php echo htmlspecialchars((string)$job['desc'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="review-detail">Budget: <?php echo htmlspecialchars((string)$job['budget'], ENT_QUOTES, 'UTF-8'); ?> · Durasi: <?php echo htmlspecialchars((string)$job['duration'], ENT_QUOTES, 'UTF-8'); ?> · Lokasi: <?php echo htmlspecialchars((string)$job['location'], ENT_QUOTES, 'UTF-8'); ?></div>
          <form method="post" class="review-actions">
            <input type="hidden" name="tab" value="projects" />
            <input type="hidden" name="vacancy_id" value="<?php echo htmlspecialchars((string)$job['id'], ENT_QUOTES, 'UTF-8'); ?>" />
            <textarea name="admin_note" placeholder="Catatan untuk employer (wajib untuk revisi/penolakan)"></textarea>
            <button class="btn-approve" name="action" value="vacancy_approve" type="submit">Setujui & Tayang</button>
            <button class="btn-revision" name="action" value="vacancy_revision" type="submit">Minta Revisi</button>
            <button class="btn-reject" name="action" value="vacancy_reject" type="submit">Tolak</button>
          </form>
        </article>
      <?php endforeach; ?>

      <h2 style="margin: 24px 0 12px; font-size: 1rem;">Riwayat / Status Lain</h2>
      <?php foreach ($vacancies as $job): ?>
        <?php if (($job['status'] ?? '') === 'review' || ($job['status'] ?? '') === 'draft') { continue; } ?>
        <article class="review-card" style="opacity:0.92;">
          <h3><?php echo htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8'); ?> <?php echo admin_status_badge((string)($job['status'] ?? '')); ?></h3>
          <div class="review-meta"><?php echo htmlspecialchars((string)$job['id'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars((string)($job['employer'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if (!empty($job['adminNote'])): ?>
            <div class="review-detail"><?php echo htmlspecialchars((string)$job['adminNote'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
