<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/db.php';
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
        $res = gig_request_project_extension($contractId, 'employer', $username, $amount, $unit, $reason);
        $flashMsg = !empty($res['ok'])
            ? 'Pengajuan perpanjangan dikirim. Menunggu konfirmasi gig worker.'
            : (string)($res['error'] ?? 'Gagal mengajukan perpanjangan.');
        $flashErr = empty($res['ok']);
    } elseif ($action === 'approve_extension' || $action === 'reject_extension') {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $res = gig_decide_project_extension($requestId, 'employer', $action === 'approve_extension');
        $flashMsg = !empty($res['ok'])
            ? (($action === 'approve_extension') ? 'Perpanjangan disetujui. Deadline proyek diperbarui.' : 'Perpanjangan ditolak. Deadline tetap sesuai kesepakatan.')
            : (string)($res['error'] ?? 'Gagal memproses konfirmasi perpanjangan.');
        $flashErr = empty($res['ok']);
    }
}

$p1 = gig_demo_active_project_by_id('GIG-2026-09-001');
$p2 = gig_demo_active_project_by_id('GIG-2026-09-002');

$pageTitle = 'Proyek Aktif';
$pageKey = 'aktif';
$breadcrumbCurrent = 'Proyek Aktif';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Proyek Aktif</h1>
      <div style="font-size:0.82rem;color:var(--text-muted);background:#f1f5f9;padding:6px 12px;border-radius:9999px;">
        Koordinasi langsung perusahaan &amp; mitra
      </div>
    </div>
    <?php if ($flashMsg !== ''): ?>
      <div style="margin-bottom:14px;padding:11px 14px;border-radius:10px;font-size:0.84rem;font-weight:700;<?php echo $flashErr ? 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;' : 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;'; ?>">
        <?php echo htmlspecialchars($flashMsg, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <div class="active-projects-list">
      <?php
        // Load completion status from DB first, fall back to session
        $pdo = gig_db();
        $dbCompletions = [];
        if ($pdo !== null) {
            try {
                $stmt = $pdo->prepare(
                    "SELECT `contract_id`, `rating_given`, `review_given`
                     FROM `project_completions`
                     WHERE `employer_username` = :emp"
                );
                $stmt->execute([':emp' => $username]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $dbCompletions[$row['contract_id']] = $row;
                }
            } catch (Throwable $ignored) {}
        }

        $c1Id = 'CTR-GIG-2026-0811';
        $c2Id = 'CTR-GIG-2026-0819';
        $completedP1 = isset($dbCompletions[$c1Id]) || isset($_SESSION['completed_projects'][$c1Id]);
        $completedP2 = isset($dbCompletions[$c2Id]) || isset($_SESSION['completed_projects'][$c2Id]);
        $ratingP1 = $dbCompletions[$c1Id]['rating_given']
                    ?? ($_SESSION['completed_projects'][$c1Id]['ratingGiven'] ?? 5);
        $ratingP2 = $dbCompletions[$c2Id]['rating_given']
                    ?? ($_SESSION['completed_projects'][$c2Id]['ratingGiven'] ?? 5);
        $isExpiredP1 = !empty($p1['is_expired']);
        $isExpiredP2 = !empty($p2['is_expired']);
        $pendingExtP1 = is_array($p1['pending_extension'] ?? null) ? $p1['pending_extension'] : null;
        $pendingExtP2 = is_array($p2['pending_extension'] ?? null) ? $p2['pending_extension'] : null;
        $hasActive = !$completedP1 || !$completedP2;
      ?>

      <?php if (!$hasActive): ?>
        <div class="white-card" style="text-align:center;padding:48px 24px;">
          <h3 style="font-size:1.05rem;font-weight:800;margin-bottom:6px;">Tidak ada proyek aktif</h3>
          <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;">Semua proyek yang sudah selesai ada di Riwayat Proyek.</p>
          <a class="btn-create-post" href="employer-riwayat-proyek.php" style="text-decoration:none;display:inline-flex;">Buka Riwayat Proyek</a>
        </div>
      <?php endif; ?>

      <?php if (!$completedP1): ?>
      <!-- Project 1 -->
      <div class="active-project-card" id="project-GIG-2026-09-001" style="<?php echo $completedP1 ? 'border-color:#10b981;background:#f0fdf4;' : ''; ?>">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title" style="display:flex;align-items:center;gap:10px;">
              <span>Redesign UI/UX Dashboard Prototype KarirHub</span>
              <?php if ($isExpiredP1 && !$completedP1): ?>
                <span class="badge-status cancelled">Tidak Selesai</span>
              <?php else: ?>
                <span class="badge-status in-progress"><?php echo htmlspecialchars((string)($p1['status_label'] ?? 'Berjalan'), ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
              <?php if ($completedP1): ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#d1fae5;color:#047857;">✓ Selesai &amp; Dinilai</span>
              <?php endif; ?>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
              No. Kontrak: <strong>CTR-GIG-2026-0811</strong> · Mulai: <strong><?php echo htmlspecialchars($p1['hired_label'], ENT_QUOTES, 'UTF-8'); ?></strong> · Durasi Disepakati: <strong><?php echo htmlspecialchars($p1['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>
        </div>

        <div class="active-proj-body">
          <div class="freelancer-profile-box" style="flex:1;min-width:200px;">
            <div class="fl-avatar" style="background:#2563eb;">T</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?active=1&id=tessa" style="color:inherit;text-decoration:none;">Theressa Zaratrusha</a>
                <span class="fl-rating-badge">★ 5</span>
              </div>
              <div class="fl-info-sub">Lead UI/UX Designer</div>
            </div>
          </div>

          <!-- Countdown Widget (Replaces Milestones) -->
          <div class="countdown-widget-box" style="background:#ffffff;border:1px solid #cbd5e1;padding:10px 14px;border-radius:10px;flex:1;max-width:420px;width:100%;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
            <div style="display:flex;flex-direction:column;gap:1px;margin-bottom:6px;">
              <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
              <span style="font-size:0.7rem;color:#475569;font-weight:600;">Tenggat: <?php echo htmlspecialchars($p1['deadline'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div style="display:flex;gap:6px;text-align:center;" class="js-project-countdown" data-deadline="<?php echo htmlspecialchars($p1['deadline_iso'], ENT_QUOTES, 'UTF-8'); ?>" id="countdown-proj-1">
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-days" style="font-size:1.05rem;font-weight:800;color:#1e293b;display:block;line-height:1.2;"><?php echo (int)$p1['days_left']; ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Hari</span>
              </div>
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-hours" style="font-size:1.05rem;font-weight:800;color:#1e293b;display:block;line-height:1.2;"><?php echo sprintf('%02d', (int)$p1['hours_left']); ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Jam</span>
              </div>
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-mins" style="font-size:1.05rem;font-weight:800;color:#1e293b;display:block;line-height:1.2;"><?php echo sprintf('%02d', (int)$p1['mins_left']); ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Menit</span>
              </div>
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-secs" style="font-size:1.05rem;font-weight:800;color:#2563eb;display:block;line-height:1.2;"><?php echo sprintf('%02d', (int)$p1['secs_left']); ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Detik</span>
              </div>
            </div>
          </div>

          <div style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
            <div style="font-size:0.82rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Perpanjangan Durasi</div>
            <?php if (!empty($p1['approved_extension_days'])): ?>
              <div style="font-size:0.78rem;color:#065f46;background:#ecfdf5;border:1px solid #a7f3d0;padding:8px 10px;border-radius:8px;margin-bottom:8px;">
                Disetujui: <?php echo htmlspecialchars(gig_format_extension_label((int)$p1['approved_extension_days']), ENT_QUOTES, 'UTF-8'); ?>. Deadline sudah diperbarui.
              </div>
            <?php endif; ?>

            <?php if ($isExpiredP1): ?>
              <div style="font-size:0.78rem;color:#991b1b;background:#fef2f2;border:1px solid #fecaca;padding:8px 10px;border-radius:8px;">
                Deadline terlewati dan proyek belum selesai. Status otomatis menjadi <strong>Tidak Selesai</strong>.
              </div>
            <?php elseif ($pendingExtP1): ?>
              <div style="font-size:0.78rem;color:#1e293b;background:#eff6ff;border:1px solid #bfdbfe;padding:8px 10px;border-radius:8px;">
                Pengajuan pending dari <strong><?php echo $pendingExtP1['requested_by_role'] === 'worker' ? 'Gig Worker' : 'Pemberi Kerja'; ?></strong>:
                <strong><?php echo htmlspecialchars(gig_format_extension_label((int)$pendingExtP1['amount_days']), ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php if (!empty($pendingExtP1['reason'])): ?>
                  <div style="margin-top:4px;color:#334155;">Alasan: <?php echo htmlspecialchars((string)$pendingExtP1['reason'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
              </div>
              <?php if (($pendingExtP1['requested_by_role'] ?? '') !== 'employer'): ?>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                  <form method="POST" action="" style="margin:0;">
                    <input type="hidden" name="ext_action" value="approve_extension">
                    <input type="hidden" name="request_id" value="<?php echo (int)$pendingExtP1['id']; ?>">
                    <button type="submit" class="btn-create-post" style="padding:6px 12px;font-size:0.78rem;background:#059669;border-color:#047857;">Setujui Perpanjangan</button>
                  </form>
                  <form method="POST" action="" style="margin:0;">
                    <input type="hidden" name="ext_action" value="reject_extension">
                    <input type="hidden" name="request_id" value="<?php echo (int)$pendingExtP1['id']; ?>">
                    <button type="submit" class="btn-outline-blue" style="padding:6px 12px;font-size:0.78rem;">Tolak</button>
                  </form>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <button type="button" class="btn-action-sm" onclick="openExtensionModal('ext-modal-employer-p1')">Ajukan Perpanjangan</button>
              <div id="ext-modal-employer-p1" style="display:none;position:fixed;inset:0;z-index:1200;background:rgba(15,23,42,0.45);padding:16px;">
                <div style="max-width:560px;margin:7vh auto 0;background:#fff;border-radius:14px;box-shadow:0 20px 50px rgba(15,23,42,0.24);overflow:hidden;">
                  <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid #e2e8f0;">
                    <strong style="font-size:0.95rem;color:#0f172a;">Ajukan Perpanjangan Durasi</strong>
                    <button type="button" onclick="closeExtensionModal('ext-modal-employer-p1')" style="border:none;background:#f1f5f9;color:#334155;border-radius:8px;padding:4px 8px;cursor:pointer;">Tutup</button>
                  </div>
                  <form method="POST" action="" style="padding:14px;display:flex;flex-direction:column;gap:10px;">
                    <input type="hidden" name="ext_action" value="request_extension">
                    <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars((string)$p1['contract_id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <label style="font-size:0.8rem;font-weight:700;color:#334155;">Jumlah Perpanjangan</label>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                      <input type="number" min="1" name="ext_amount" value="1" required style="width:92px;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;">
                      <select name="ext_unit" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;">
                        <option value="day">Hari</option>
                        <option value="week">Minggu</option>
                        <option value="month">Bulan</option>
                      </select>
                    </div>
                    <label style="font-size:0.8rem;font-weight:700;color:#334155;">Alasan Perpanjangan</label>
                    <textarea name="ext_reason" required rows="3" placeholder="Jelaskan alasan kenapa butuh tambahan durasi" style="resize:vertical;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;"></textarea>
                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:4px;">
                      <button type="button" class="btn-outline-blue" onclick="closeExtensionModal('ext-modal-employer-p1')">Batal</button>
                      <button type="submit" class="btn-action-sm">Kirim Pengajuan</button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>

          </div>

        <div class="active-proj-actions" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
          <div>
            <?php if ($completedP1): ?>
              <span style="font-size:0.82rem;color:#047857;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
               <span>⭐</span> Rating Diberikan: <?php echo (int)$ratingP1; ?>/5
              </span>
            <?php else: ?>
              <span style="font-size:0.8rem;color:var(--text-muted);">Deliverable siap? Selesaikan proyek dan tinggalkan penilaian.</span>
            <?php endif; ?>
          </div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button class="btn-action-sm" type="button"
              data-contact-role="Gig Worker"
              data-contact-name="Theressa Zaratrusha"
              data-contact-phone="0812-3456-7890"
              data-contact-email="theressaz@pasker.id"
              onclick="openContactModal(this)">Kontak Freelancer</button>
            <a class="btn-outline-blue" href="worker-profile.php?active=1&id=tessa">Lihat Profil</a>
            
            <?php if ($completedP1): ?>
              <a class="btn-create-post" href="employer-riwayat-proyek.php" style="text-decoration:none;padding:6px 14px;font-size:0.82rem;background:#059669;border-color:#047857;">
                Buka di Riwayat →
              </a>
            <?php else: ?>
              <a class="btn-create-post" href="employer-rating-worker.php?contract=CTR-GIG-2026-0811&worker=tessa" style="text-decoration:none;padding:6px 14px;font-size:0.82rem;background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);box-shadow:0 4px 10px rgba(217,119,6,0.35);">
                ★ Selesaikan &amp; Beri Rating
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!$completedP2): ?>
      <!-- Project 2 -->
      <div class="active-project-card" id="project-GIG-2026-09-002" style="<?php echo $completedP2 ? 'border-color:#10b981;background:#f0fdf4;' : ''; ?>">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title" style="display:flex;align-items:center;gap:10px;">
              <span>Integrasi REST API Modul Notifikasi SMS &amp; WhatsApp</span>
              <?php if ($isExpiredP2 && !$completedP2): ?>
                <span class="badge-status cancelled">Tidak Selesai</span>
              <?php else: ?>
                <span class="badge-status in-progress"><?php echo htmlspecialchars((string)($p2['status_label'] ?? 'Berjalan'), ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
              <?php if ($completedP2): ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#d1fae5;color:#047857;">✓ Selesai &amp; Dinilai</span>
              <?php endif; ?>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
              No. Kontrak: <strong>CTR-GIG-2026-0819</strong> · Mulai: <strong><?php echo htmlspecialchars($p2['hired_label'], ENT_QUOTES, 'UTF-8'); ?></strong> · Durasi Disepakati: <strong><?php echo htmlspecialchars($p2['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>
        </div>

        <div class="active-proj-body">
          <div class="freelancer-profile-box" style="flex:1;min-width:200px;">
            <div class="fl-avatar" style="background:#0891b2;">R</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?active=1&id=rian" style="color:inherit;text-decoration:none;">Rian Ardiansyah</a>
                <span class="fl-rating-badge">★ 5</span>
              </div>
              <div class="fl-info-sub">Backend API Developer</div>
            </div>
          </div>

          <!-- Countdown Widget (Replaces Milestones) -->
          <div class="countdown-widget-box" style="background:#ffffff;border:1px solid #cbd5e1;padding:10px 14px;border-radius:10px;flex:1;max-width:420px;width:100%;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
            <div style="display:flex;flex-direction:column;gap:1px;margin-bottom:6px;">
              <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
              <span style="font-size:0.7rem;color:#475569;font-weight:600;">Tenggat: <?php echo htmlspecialchars($p2['deadline'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div style="display:flex;gap:6px;text-align:center;" class="js-project-countdown" data-deadline="<?php echo htmlspecialchars($p2['deadline_iso'], ENT_QUOTES, 'UTF-8'); ?>" id="countdown-proj-2">
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-days" style="font-size:1.05rem;font-weight:800;color:#1e293b;display:block;line-height:1.2;"><?php echo (int)$p2['days_left']; ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Hari</span>
              </div>
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-hours" style="font-size:1.05rem;font-weight:800;color:#1e293b;display:block;line-height:1.2;"><?php echo sprintf('%02d', (int)$p2['hours_left']); ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Jam</span>
              </div>
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-mins" style="font-size:1.05rem;font-weight:800;color:#1e293b;display:block;line-height:1.2;"><?php echo sprintf('%02d', (int)$p2['mins_left']); ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Menit</span>
              </div>
              <div style="background:#f8fafc;border:1px solid #cbd5e1;padding:4px 4px;border-radius:6px;flex:1;">
                <span class="c-secs" style="font-size:1.05rem;font-weight:800;color:#0891b2;display:block;line-height:1.2;"><?php echo sprintf('%02d', (int)$p2['secs_left']); ?></span>
                <span style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Detik</span>
              </div>
            </div>
          </div>

          <div style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
            <div style="font-size:0.82rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Perpanjangan Durasi</div>
            <?php if (!empty($p2['approved_extension_days'])): ?>
              <div style="font-size:0.78rem;color:#065f46;background:#ecfdf5;border:1px solid #a7f3d0;padding:8px 10px;border-radius:8px;margin-bottom:8px;">
                Disetujui: <?php echo htmlspecialchars(gig_format_extension_label((int)$p2['approved_extension_days']), ENT_QUOTES, 'UTF-8'); ?>. Deadline sudah diperbarui.
              </div>
            <?php endif; ?>

            <?php if ($isExpiredP2): ?>
              <div style="font-size:0.78rem;color:#991b1b;background:#fef2f2;border:1px solid #fecaca;padding:8px 10px;border-radius:8px;">
                Deadline terlewati dan proyek belum selesai. Status otomatis menjadi <strong>Tidak Selesai</strong>.
              </div>
            <?php elseif ($pendingExtP2): ?>
              <div style="font-size:0.78rem;color:#1e293b;background:#eff6ff;border:1px solid #bfdbfe;padding:8px 10px;border-radius:8px;">
                Pengajuan pending dari <strong><?php echo $pendingExtP2['requested_by_role'] === 'worker' ? 'Gig Worker' : 'Pemberi Kerja'; ?></strong>:
                <strong><?php echo htmlspecialchars(gig_format_extension_label((int)$pendingExtP2['amount_days']), ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php if (!empty($pendingExtP2['reason'])): ?>
                  <div style="margin-top:4px;color:#334155;">Alasan: <?php echo htmlspecialchars((string)$pendingExtP2['reason'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
              </div>
              <?php if (($pendingExtP2['requested_by_role'] ?? '') !== 'employer'): ?>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                  <form method="POST" action="" style="margin:0;">
                    <input type="hidden" name="ext_action" value="approve_extension">
                    <input type="hidden" name="request_id" value="<?php echo (int)$pendingExtP2['id']; ?>">
                    <button type="submit" class="btn-create-post" style="padding:6px 12px;font-size:0.78rem;background:#059669;border-color:#047857;">Setujui Perpanjangan</button>
                  </form>
                  <form method="POST" action="" style="margin:0;">
                    <input type="hidden" name="ext_action" value="reject_extension">
                    <input type="hidden" name="request_id" value="<?php echo (int)$pendingExtP2['id']; ?>">
                    <button type="submit" class="btn-outline-blue" style="padding:6px 12px;font-size:0.78rem;">Tolak</button>
                  </form>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <button type="button" class="btn-action-sm" onclick="openExtensionModal('ext-modal-employer-p2')">Ajukan Perpanjangan</button>
              <div id="ext-modal-employer-p2" style="display:none;position:fixed;inset:0;z-index:1200;background:rgba(15,23,42,0.45);padding:16px;">
                <div style="max-width:560px;margin:7vh auto 0;background:#fff;border-radius:14px;box-shadow:0 20px 50px rgba(15,23,42,0.24);overflow:hidden;">
                  <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid #e2e8f0;">
                    <strong style="font-size:0.95rem;color:#0f172a;">Ajukan Perpanjangan Durasi</strong>
                    <button type="button" onclick="closeExtensionModal('ext-modal-employer-p2')" style="border:none;background:#f1f5f9;color:#334155;border-radius:8px;padding:4px 8px;cursor:pointer;">Tutup</button>
                  </div>
                  <form method="POST" action="" style="padding:14px;display:flex;flex-direction:column;gap:10px;">
                    <input type="hidden" name="ext_action" value="request_extension">
                    <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars((string)$p2['contract_id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <label style="font-size:0.8rem;font-weight:700;color:#334155;">Jumlah Perpanjangan</label>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                      <input type="number" min="1" name="ext_amount" value="1" required style="width:92px;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;">
                      <select name="ext_unit" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;">
                        <option value="day">Hari</option>
                        <option value="week">Minggu</option>
                        <option value="month">Bulan</option>
                      </select>
                    </div>
                    <label style="font-size:0.8rem;font-weight:700;color:#334155;">Alasan Perpanjangan</label>
                    <textarea name="ext_reason" required rows="3" placeholder="Jelaskan alasan kenapa butuh tambahan durasi" style="resize:vertical;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;"></textarea>
                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:4px;">
                      <button type="button" class="btn-outline-blue" onclick="closeExtensionModal('ext-modal-employer-p2')">Batal</button>
                      <button type="submit" class="btn-action-sm">Kirim Pengajuan</button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>

          </div>

        <div class="active-proj-actions" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
          <div>
            <?php if ($completedP2): ?>
              <span style="font-size:0.82rem;color:#047857;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
               <span>⭐</span> Rating Diberikan: <?php echo (int)$ratingP2; ?>/5
              </span>
            <?php else: ?>
              <span style="font-size:0.8rem;color:var(--text-muted);">Deliverable siap? Selesaikan proyek dan tinggalkan penilaian.</span>
            <?php endif; ?>
          </div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button class="btn-action-sm" type="button"
              data-contact-role="Gig Worker"
              data-contact-name="Rian Ardiansyah"
              data-contact-phone="0813-8899-7711"
              data-contact-email="rian.dev@email.com"
              onclick="openContactModal(this)">Kontak Freelancer</button>
            <a class="btn-outline-blue" href="worker-profile.php?active=1&id=rian">Lihat Profil</a>
            
            <?php if ($completedP2): ?>
              <a class="btn-create-post" href="employer-riwayat-proyek.php" style="text-decoration:none;padding:6px 14px;font-size:0.82rem;background:#059669;border-color:#047857;">
                Buka di Riwayat →
              </a>
            <?php else: ?>
              <a class="btn-create-post" href="employer-rating-worker.php?contract=CTR-GIG-2026-0819&worker=rian" style="text-decoration:none;padding:6px 14px;font-size:0.82rem;background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);box-shadow:0 4px 10px rgba(217,119,6,0.35);">
                ★ Selesaikan &amp; Beri Rating
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

<div id="contact-info-modal" style="display:none;position:fixed;inset:0;z-index:1250;background:rgba(15,23,42,0.45);padding:16px;" onclick="if(event.target===this){closeContactModal();}">
  <div style="max-width:500px;margin:10vh auto 0;background:#fff;border-radius:14px;box-shadow:0 20px 50px rgba(15,23,42,0.24);overflow:hidden;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid #e2e8f0;">
      <strong data-contact-title style="font-size:0.95rem;color:#0f172a;">Info Kontak</strong>
      <button type="button" onclick="closeContactModal()" style="border:none;background:#f1f5f9;color:#334155;border-radius:8px;padding:4px 8px;cursor:pointer;">Tutup</button>
    </div>
    <div style="padding:14px;display:grid;gap:8px;font-size:0.86rem;color:#1e293b;">
      <div><strong>Nama:</strong> <span data-contact-name>-</span></div>
      <div><strong>Telepon/WhatsApp:</strong> <span data-contact-phone>-</span></div>
      <div><strong>Email:</strong> <span data-contact-email>-</span></div>
    </div>
  </div>
</div>

    <!-- Countdown Timer Script -->
    <script>
      function openContactModal(button) {
        if (!button) return;
        const role = button.getAttribute('data-contact-role') || 'Kontak';
        const name = button.getAttribute('data-contact-name') || '-';
        const phone = button.getAttribute('data-contact-phone') || '-';
        const email = button.getAttribute('data-contact-email') || '-';
        const modal = document.getElementById('contact-info-modal');
        if (!modal) return;
        const title = modal.querySelector('[data-contact-title]');
        const nameEl = modal.querySelector('[data-contact-name]');
        const phoneEl = modal.querySelector('[data-contact-phone]');
        const emailEl = modal.querySelector('[data-contact-email]');
        if (title) title.textContent = 'Info Kontak ' + role;
        if (nameEl) nameEl.textContent = name;
        if (phoneEl) phoneEl.textContent = phone;
        if (emailEl) emailEl.textContent = email;
        modal.style.display = 'block';
      }

      function closeContactModal() {
        const modal = document.getElementById('contact-info-modal');
        if (!modal) return;
        modal.style.display = 'none';
      }

      function openExtensionModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.style.display = 'block';
      }

      function closeExtensionModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.style.display = 'none';
      }

      document.addEventListener('click', function(e) {
        if (e.target && e.target.id && e.target.id.indexOf('ext-modal-employer-') === 0) {
          e.target.style.display = 'none';
        }
      });

      (function startCountdowns() {
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

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
