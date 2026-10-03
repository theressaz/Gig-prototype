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
$state = trim((string)($_GET['state'] ?? 'all'));
$allowedStates = ['all', 'pending', 'revision', 'approved', 'rejected'];
if (!in_array($state, $allowedStates, true)) {
    $state = 'all';
}
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

$filterByState = static function (array $rows, string $currentState): array {
    if ($currentState === 'all') {
        return $rows;
    }
    if ($currentState === 'revision') {
        return array_values(array_filter($rows, static fn($r) => ($r['status'] ?? '') === 'revision'));
    }
    return array_values(array_filter($rows, static fn($r) => ($r['status'] ?? 'pending') === $currentState));
};

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

function admin_state_tab_url(string $tab, string $state, string $q): string
{
    $params = ['tab' => $tab, 'state' => $state];
    if ($q !== '') {
        $params['q'] = $q;
    }
    return 'dashboard-admin.php?' . http_build_query($params);
}

$adminTab = $tab;
$pageTitle = 'Dashboard Admin';
$breadcrumbCurrent = 'Dashboard';
if ($tab === 'workers') {
    $breadcrumbCurrent = 'Verifikasi Gig Worker';
} elseif ($tab === 'employers') {
    $breadcrumbCurrent = 'Verifikasi Pemberi Kerja';
} elseif ($tab === 'projects') {
    $breadcrumbCurrent = 'Verifikasi Lowongan Proyek';
}
require __DIR__ . '/includes/admin-layout-start.php';
?>

    <?php
      $mainHeading = 'Dashboard';
      if ($tab === 'workers') {
          $mainHeading = 'Verifikasi Gig Worker';
      } elseif ($tab === 'employers') {
          $mainHeading = 'Verifikasi Pemberi Kerja';
      } elseif ($tab === 'projects') {
          $mainHeading = 'Verifikasi Lowongan Proyek';
      }
    ?>
    <div class="admin-page-head">
      <h1><?php echo htmlspecialchars($mainHeading, ENT_QUOTES, 'UTF-8'); ?></h1>
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
      <?php
        $workerStateCounts = [
            'all' => count($workers),
            'pending' => count(array_filter($workers, static fn($r) => ($r['status'] ?? 'pending') === 'pending')),
            'revision' => count($pendingProfileEdits),
            'approved' => count(array_filter($workers, static fn($r) => ($r['status'] ?? '') === 'approved')),
            'rejected' => count(array_filter($workers, static fn($r) => ($r['status'] ?? '') === 'rejected')),
        ];
        $workerRows = $state === 'revision' ? [] : $filterByState($workers, $state);
      ?>
      <section class="verify-board">
        <div class="verify-board-head">
          <h2>Verifikasi Gig Worker</h2>
          <div class="verify-status-tabs">
            <a class="<?php echo $state === 'all' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('workers', 'all', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Semua <span><?php echo (int)$workerStateCounts['all']; ?></span></a>
            <a class="<?php echo $state === 'pending' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('workers', 'pending', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Menunggu Verifikasi <span><?php echo (int)$workerStateCounts['pending']; ?></span></a>
            <a class="<?php echo $state === 'revision' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('workers', 'revision', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Revisi <span><?php echo (int)$workerStateCounts['revision']; ?></span></a>
            <a class="<?php echo $state === 'approved' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('workers', 'approved', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Terverifikasi <span><?php echo (int)$workerStateCounts['approved']; ?></span></a>
            <a class="<?php echo $state === 'rejected' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('workers', 'rejected', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Ditolak <span><?php echo (int)$workerStateCounts['rejected']; ?></span></a>
          </div>
        </div>

        <div class="verify-table-wrap">
          <table class="verify-table">
            <?php if ($state === 'revision'): ?>
              <thead>
                <tr>
                  <th>Gig Worker</th>
                  <th>Bidang Diubah</th>
                  <th>Alasan</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($pendingProfileEdits === []): ?>
                  <tr><td colspan="5"><div class="empty-state">Tidak ada pengajuan revisi profil worker.</div></td></tr>
                <?php endif; ?>
                <?php foreach ($pendingProfileEdits as $edit): ?>
                  <?php $payload = is_array($edit['proposed_payload'] ?? null) ? $edit['proposed_payload'] : []; ?>
                  <tr>
                    <td>
                      <div class="verify-main-text"><?php echo htmlspecialchars((string)$edit['worker_username'], ENT_QUOTES, 'UTF-8'); ?></div>
                      <div class="verify-sub-text"><?php echo htmlspecialchars((string)$edit['worker_email'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars((string)$edit['edited_field'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$edit['reason_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo admin_status_badge((string)($edit['status'] ?? 'pending')); ?></td>
                    <td>
                      <a class="verify-open-link" href="admin-worker-detail.php?u=<?php echo urlencode((string)$edit['worker_username']); ?>&email=<?php echo urlencode((string)$edit['worker_email']); ?>">Lihat Detail</a>
                      <form method="post" class="review-actions" style="margin-top:8px;">
                        <input type="hidden" name="tab" value="workers" />
                        <input type="hidden" name="edit_id" value="<?php echo (int)$edit['id']; ?>" />
                        <textarea name="admin_note" placeholder="Catatan verifikasi edit profil (opsional)"></textarea>
                        <button class="btn-approve" name="action" value="worker_edit_approve" type="submit">Setujui Edit Profil</button>
                        <button class="btn-reject" name="action" value="worker_edit_reject" type="submit">Tolak Edit Profil</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            <?php else: ?>
              <thead>
                <tr>
                  <th>Gig Worker</th>
                  <th>Bidang Keahlian</th>
                  <th>Kontak</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($workerRows === []): ?>
                  <tr><td colspan="5"><div class="empty-state">Belum ada data Gig Worker<?php echo $searchQ !== '' ? ' yang cocok dengan pencarian.' : '.'; ?></div></td></tr>
                <?php endif; ?>
                <?php foreach ($workerRows as $row): ?>
                  <tr>
                    <td><div class="verify-main-text"><?php echo htmlspecialchars((string)$row['username'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                    <td><?php echo htmlspecialchars((string)$row['bidang_keahlian'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                      <div class="verify-sub-text"><?php echo htmlspecialchars((string)$row['contact_email'], ENT_QUOTES, 'UTF-8'); ?></div>
                      <div class="verify-sub-text"><?php echo htmlspecialchars((string)$row['contact_wa'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td><?php echo admin_status_badge((string)($row['status'] ?? 'pending')); ?></td>
                    <td>
                      <a class="verify-open-link" href="admin-worker-detail.php?u=<?php echo urlencode((string)$row['username']); ?>&email=<?php echo urlencode((string)$row['contact_email']); ?>">Lihat Detail</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            <?php endif; ?>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($tab === 'employers'): ?>
      <?php
        $employerStateCounts = [
            'all' => count($employers),
            'pending' => count(array_filter($employers, static fn($r) => ($r['status'] ?? 'pending') === 'pending')),
            'revision' => count(array_filter($employers, static fn($r) => ($r['status'] ?? '') === 'revision')),
            'approved' => count(array_filter($employers, static fn($r) => ($r['status'] ?? '') === 'approved')),
            'rejected' => count(array_filter($employers, static fn($r) => ($r['status'] ?? '') === 'rejected')),
        ];
        $employerRows = $filterByState($employers, $state);
      ?>
      <section class="verify-board">
        <div class="verify-board-head">
          <h2>Verifikasi Pemberi Kerja</h2>
          <div class="verify-status-tabs">
            <a class="<?php echo $state === 'all' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('employers', 'all', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Semua <span><?php echo (int)$employerStateCounts['all']; ?></span></a>
            <a class="<?php echo $state === 'pending' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('employers', 'pending', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Menunggu Verifikasi <span><?php echo (int)$employerStateCounts['pending']; ?></span></a>
            <a class="<?php echo $state === 'revision' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('employers', 'revision', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Revisi <span><?php echo (int)$employerStateCounts['revision']; ?></span></a>
            <a class="<?php echo $state === 'approved' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('employers', 'approved', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Terverifikasi <span><?php echo (int)$employerStateCounts['approved']; ?></span></a>
            <a class="<?php echo $state === 'rejected' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(admin_state_tab_url('employers', 'rejected', $searchQ), ENT_QUOTES, 'UTF-8'); ?>">Ditolak <span><?php echo (int)$employerStateCounts['rejected']; ?></span></a>
          </div>
        </div>
        <div class="verify-table-wrap">
          <table class="verify-table">
            <thead>
              <tr>
                <th>Nama Pemberi Kerja</th>
                <th>Jenis Entitas</th>
                <th>Kontak PIC</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($employerRows === []): ?>
                <tr><td colspan="5"><div class="empty-state">Belum ada pendaftaran pemberi kerja Gig Worker.</div></td></tr>
              <?php endif; ?>
              <?php foreach ($employerRows as $row): ?>
                <tr>
                  <td>
                    <div class="verify-main-text"><?php echo htmlspecialchars((string)($row['company_name'] ?: $row['nama_pic']), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="verify-sub-text"><?php echo htmlspecialchars((string)$row['siapkerja_email'], ENT_QUOTES, 'UTF-8'); ?></div>
                  </td>
                  <td><?php echo htmlspecialchars((string)$row['industry'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td>
                    <div class="verify-sub-text"><?php echo htmlspecialchars((string)$row['nama_pic'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="verify-sub-text"><?php echo htmlspecialchars((string)$row['phone_pic'], ENT_QUOTES, 'UTF-8'); ?></div>
                  </td>
                  <td><?php echo admin_status_badge((string)($row['status'] ?? 'pending')); ?></td>
                  <td>
                    <details class="verify-detail-drawer">
                      <summary>Lihat Detail</summary>
                      <div class="verify-drawer-body">
                        <p><strong>Perusahaan:</strong> <?php echo htmlspecialchars((string)$row['company_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Email PIC:</strong> <?php echo htmlspecialchars((string)$row['email_pic'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if (($row['status'] ?? '') === 'pending'): ?>
                          <form method="post" class="review-actions">
                            <input type="hidden" name="tab" value="employers" />
                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>" />
                            <textarea name="admin_note" placeholder="Catatan verifikasi (opsional)"></textarea>
                            <button class="btn-approve" name="action" value="employer_approve" type="submit">Setujui</button>
                            <button class="btn-reject" name="action" value="employer_reject" type="submit">Tolak</button>
                          </form>
                        <?php elseif (!empty($row['admin_note'])): ?>
                          <p><strong>Catatan Admin:</strong> <?php echo htmlspecialchars((string)$row['admin_note'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                      </div>
                    </details>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
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
