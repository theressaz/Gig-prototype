<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/project-schedule.php';

$flashMsg = '';
$flashErr = false;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string)($_POST['ext_action'] ?? '');
    if ($action === 'request_extension') {
        $contractId = trim((string)($_POST['contract_id'] ?? ''));
        $amount = (int)($_POST['ext_amount'] ?? 0);
        $unit = trim((string)($_POST['ext_unit'] ?? 'day'));
        $reason = trim((string)($_POST['ext_reason'] ?? ''));
        $res = gig_request_project_extension($contractId, 'worker', $username, $amount, $unit, $reason);
        $flashMsg = !empty($res['ok'])
            ? 'Pengajuan perpanjangan dikirim. Menunggu konfirmasi pemberi kerja.'
            : (string)($res['error'] ?? 'Gagal mengajukan perpanjangan.');
        $flashErr = empty($res['ok']);
    } elseif ($action === 'approve_extension' || $action === 'reject_extension') {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $res = gig_decide_project_extension($requestId, 'worker', $action === 'approve_extension');
        $flashMsg = !empty($res['ok'])
            ? (($action === 'approve_extension') ? 'Perpanjangan disetujui. Deadline proyek diperbarui.' : 'Perpanjangan ditolak. Deadline tetap sesuai kesepakatan.')
            : (string)($res['error'] ?? 'Gagal memproses konfirmasi perpanjangan.');
        $flashErr = empty($res['ok']);
    }
}

$pageTitle = 'Proyek Aktif';
$pageKey = 'tugas';
$breadcrumbCurrent = 'Proyek Aktif';
require __DIR__ . '/includes/worker-layout-start.php';

$workerEmail = (string)($_SESSION['siapkerja_email'] ?? '');
$activeProjects = gig_worker_ongoing_active_projects($username, $workerEmail);
?>

<div class="page-toolbar">
  <h1>Proyek Aktif</h1>
  <div style="font-size:0.82rem;color:var(--text-muted);background:#f1f5f9;padding:6px 14px;border-radius:9999px;font-weight:600;">
    Koordinasi langsung mitra freelancer &amp; pemberi kerja
  </div>
</div>

<?php if ($flashMsg !== ''): ?>
  <div style="margin-bottom:14px;padding:11px 14px;border-radius:10px;font-size:0.84rem;font-weight:700;<?php echo $flashErr ? 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;' : 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;'; ?>">
    <?php echo htmlspecialchars($flashMsg, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<div class="active-projects-list">
  <?php if (count($activeProjects) === 0): ?>
    <div class="white-card" style="text-align:center;padding:48px 24px;">
      <h3 style="font-size:1.05rem;font-weight:800;margin-bottom:6px;">Tidak ada proyek aktif</h3>
      <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;">Proyek yang sudah selesai ada di Riwayat Proyek.</p>
      <a class="btn-primary-add" href="worker-riwayat.php" style="display:inline-flex;text-decoration:none;">Buka Riwayat Proyek</a>
    </div>
  <?php endif; ?>

  <?php foreach ($activeProjects as $idx => $proj): ?>
    <?php
      $pendingExt = is_array($proj['pending_extension'] ?? null) ? $proj['pending_extension'] : null;
      $pendingBy = (string)($pendingExt['requester_role'] ?? '');
      $isExpired = !empty($proj['is_expired']);
    ?>
    <div class="active-project-card" style="margin-bottom:20px;">
      <div class="active-proj-header">
        <div>
          <div class="active-proj-title" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="worker-project-detail.php?id=<?php echo urlencode((string)$proj['id']); ?>" style="color:inherit;text-decoration:none;">
              <?php echo htmlspecialchars((string)$proj['title'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <?php if ($isExpired): ?>
              <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#fee2e2;color:#b91c1c;">Tidak Selesai</span>
            <?php else: ?>
              <span class="<?php echo htmlspecialchars((string)$proj['status_badge_class'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string)$proj['status_label'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
          </div>
          <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">
            No. Kontrak: <strong><?php echo htmlspecialchars((string)$proj['contract_id'], ENT_QUOTES, 'UTF-8'); ?></strong> &bull;
            Mulai: <strong><?php echo htmlspecialchars((string)$proj['hired_label'], ENT_QUOTES, 'UTF-8'); ?></strong> &bull;
            Durasi Disepakati: <strong><?php echo htmlspecialchars((string)$proj['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
        </div>
      </div>

      <div class="active-proj-body">
        <div class="freelancer-profile-box">
          <div class="fl-avatar" style="background:#1d4ed8;color:#fff;font-weight:800;font-size:1.1rem;">🏢</div>
          <div>
            <div class="fl-info-name"><?php echo htmlspecialchars((string)$proj['employer'], ENT_QUOTES, 'UTF-8'); ?> <span class="fl-rating-badge">★ 4.9</span></div>
            <div class="fl-info-sub"><?php echo htmlspecialchars((string)$proj['employer_category'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">&check; Kesepakatan disetujui &bull; kontak terbuka</div>
          </div>
        </div>

        <div class="countdown-widget-box" style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:10px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
            <span style="font-size:0.72rem;color:#2563eb;font-weight:700;">Tenggat: <?php echo htmlspecialchars((string)$proj['deadline'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div style="display:flex;gap:8px;text-align:center;" class="js-project-countdown" data-deadline="<?php echo htmlspecialchars((string)$proj['deadline_iso'], ENT_QUOTES, 'UTF-8'); ?>" id="countdown-worker-<?php echo (int)$idx; ?>">
            <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
              <span class="c-days" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo (int)$proj['days_left']; ?></span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Hari</span>
            </div>
            <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
              <span class="c-hours" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', (int)$proj['hours_left']); ?></span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Jam</span>
            </div>
            <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
              <span class="c-mins" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', (int)$proj['mins_left']); ?></span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Menit</span>
            </div>
            <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
              <span class="c-secs" style="font-size:1.1rem;font-weight:800;color:#2563eb;display:block;"><?php echo sprintf('%02d', (int)$proj['secs_left']); ?></span>
              <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Detik</span>
            </div>
          </div>
        </div>
      </div>

      <div class="active-proj-actions" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <span style="font-size:0.8rem;color:var(--text-muted);"><?php echo htmlspecialchars((string)$proj['deliverable_note'], ENT_QUOTES, 'UTF-8'); ?></span>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <button class="btn-action-sm" type="button" onclick="copyEmployerContact('<?php echo htmlspecialchars(addslashes((string)$proj['employer']), ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlspecialchars((string)$proj['employer_phone'], ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlspecialchars((string)$proj['employer_email'], ENT_QUOTES, 'UTF-8'); ?>')">
            Kontak Pemberi Kerja
          </button>
          <a class="btn-outline-blue" href="employer-profile.php?name=<?php echo urlencode((string)$proj['employer']); ?>">Profil Pemberi Kerja</a>
          <a class="btn-create-post" href="employer-rating-worker.php?contract=<?php echo urlencode((string)$proj['contract_id']); ?>&from=worker" style="text-decoration:none;padding:6px 14px;font-size:0.82rem;background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);box-shadow:0 4px 10px rgba(217,119,6,0.35);">
            ★ Selesaikan &amp; Beri Rating
          </a>
        </div>
      </div>

      <div style="margin-top:10px;padding-top:10px;border-top:1px dashed #e2e8f0;">
        <?php if (!empty($proj['approved_extension_days'])): ?>
          <div style="font-size:0.78rem;color:#065f46;font-weight:700;margin-bottom:6px;">
            Durasi diperpanjang total <?php echo (int)$proj['approved_extension_days']; ?> hari. Deadline baru: <?php echo htmlspecialchars((string)$proj['deadline'], ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <?php if ($pendingExt && $pendingBy !== 'worker'): ?>
          <div style="font-size:0.78rem;color:#1e3a8a;font-weight:700;margin-bottom:6px;">
            Pemberi kerja mengajukan perpanjangan <?php echo htmlspecialchars(gig_format_extension_label((int)$pendingExt['amount'], (string)$pendingExt['unit']), ENT_QUOTES, 'UTF-8'); ?>.
          </div>
          <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;">
            <input type="hidden" name="ext_action" value="approve_extension">
            <input type="hidden" name="request_id" value="<?php echo (int)$pendingExt['id']; ?>">
            <button class="btn-action-sm" type="submit" style="background:#059669;color:#fff;border:none;">Setujui Perpanjangan</button>
          </form>
          <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;">
            <input type="hidden" name="ext_action" value="reject_extension">
            <input type="hidden" name="request_id" value="<?php echo (int)$pendingExt['id']; ?>">
            <button class="btn-action-sm" type="submit" style="background:#dc2626;color:#fff;border:none;">Tolak Perpanjangan</button>
          </form>
        <?php elseif ($pendingExt): ?>
          <div style="font-size:0.78rem;color:#92400e;background:#fffbeb;border:1px solid #fde68a;padding:7px 9px;border-radius:8px;display:inline-block;">
            Pengajuan perpanjangan Anda (<?php echo htmlspecialchars(gig_format_extension_label((int)$pendingExt['amount'], (string)$pendingExt['unit']), ENT_QUOTES, 'UTF-8'); ?>) menunggu konfirmasi pemberi kerja.
          </div>
        <?php elseif (!$isExpired): ?>
          <form method="post" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <input type="hidden" name="ext_action" value="request_extension">
            <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars((string)$proj['contract_id'], ENT_QUOTES, 'UTF-8'); ?>">
            <span style="font-size:0.78rem;color:#475569;font-weight:700;">Ajukan perpanjangan:</span>
            <input type="number" name="ext_amount" min="1" max="12" value="1" style="width:70px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:8px;">
            <select name="ext_unit" style="padding:6px 8px;border:1px solid #cbd5e1;border-radius:8px;">
              <option value="day">Hari</option>
              <option value="week">Minggu</option>
              <option value="month">Bulan</option>
            </select>
            <input type="text" name="ext_reason" placeholder="Alasan singkat (opsional)" style="min-width:220px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:8px;">
            <button class="btn-action-sm" type="submit" style="background:#2563eb;color:#fff;border:none;">Ajukan</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
function copyEmployerContact(name, phone, email) {
  alert("Kontak Resmi Pemberi Kerja (" + name + "):\n\nWhatsApp / Telepon: " + phone + "\nEmail: " + email + "\n\nKontak terbuka karena kesepakatan proyek telah aktif.");
}

(function startWorkerCountdowns() {
  function pad(n) { return n < 10 ? '0' + n : String(n); }
  function tick() {
    document.querySelectorAll('.js-project-countdown').forEach(function(container) {
      const iso = container.getAttribute('data-deadline');
      if (!iso) return;
      const end = new Date(iso).getTime();
      let ms = end - Date.now();
      if (ms < 0) ms = 0;
      const days = Math.floor(ms / 86400000);
      ms -= days * 86400000;
      const hours = Math.floor(ms / 3600000);
      ms -= hours * 3600000;
      const mins = Math.floor(ms / 60000);
      const secs = Math.floor((ms - mins * 60000) / 1000);
      const d = container.querySelector('.c-days');
      const h = container.querySelector('.c-hours');
      const m = container.querySelector('.c-mins');
      const s = container.querySelector('.c-secs');
      if (d) d.textContent = String(days);
      if (h) h.textContent = pad(hours);
      if (m) m.textContent = pad(mins);
      if (s) s.textContent = pad(secs);
    });
  }
  tick();
  setInterval(tick, 1000);
})();
</script>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
