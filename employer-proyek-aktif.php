<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/project-schedule.php';

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
          <div class="freelancer-profile-box">
            <div class="fl-avatar" style="background:#2563eb;">T</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?active=1&id=tessa" style="color:inherit;text-decoration:none;">Tessa</a>
                <span class="fl-rating-badge">★ 5</span>
              </div>
              <div class="fl-info-sub">Lead UI/UX Designer</div>
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">Kesepakatan disetujui · kontak terbuka</div>
            </div>
          </div>

          <!-- Countdown Widget (Replaces Milestones) -->
          <div class="countdown-widget-box" style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
              <span style="font-size:0.72rem;color:#2563eb;font-weight:700;">Tenggat: <?php echo htmlspecialchars($p1['deadline'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div style="display:flex;gap:8px;text-align:center;" class="js-project-countdown" data-deadline="<?php echo htmlspecialchars($p1['deadline_iso'], ENT_QUOTES, 'UTF-8'); ?>" id="countdown-proj-1">
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-days" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo (int)$p1['days_left']; ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Hari</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-hours" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', (int)$p1['hours_left']); ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Jam</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-mins" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', (int)$p1['mins_left']); ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Menit</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-secs" style="font-size:1.1rem;font-weight:800;color:#2563eb;display:block;"><?php echo sprintf('%02d', (int)$p1['secs_left']); ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Detik</span>
              </div>
            </div>
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
            <button class="btn-action-sm" type="button" onclick="copyContact('Tessa','0812-3456-7890','tessa.design@email.com')">Kontak Freelancer</button>
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
          <div class="freelancer-profile-box">
            <div class="fl-avatar" style="background:#0891b2;">R</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?active=1&id=rian" style="color:inherit;text-decoration:none;">Rian Ardiansyah</a>
                <span class="fl-rating-badge">★ 5</span>
              </div>
              <div class="fl-info-sub">Backend API Developer</div>
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">Kesepakatan disetujui · kontak terbuka</div>
            </div>
          </div>

          <!-- Countdown Widget (Replaces Milestones) -->
          <div class="countdown-widget-box" style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
              <span style="font-size:0.72rem;color:#0891b2;font-weight:700;">Tenggat: <?php echo htmlspecialchars($p2['deadline'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div style="display:flex;gap:8px;text-align:center;" class="js-project-countdown" data-deadline="<?php echo htmlspecialchars($p2['deadline_iso'], ENT_QUOTES, 'UTF-8'); ?>" id="countdown-proj-2">
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-days" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo (int)$p2['days_left']; ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Hari</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-hours" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', (int)$p2['hours_left']); ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Jam</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-mins" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', (int)$p2['mins_left']); ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Menit</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-secs" style="font-size:1.1rem;font-weight:800;color:#0891b2;display:block;"><?php echo sprintf('%02d', (int)$p2['secs_left']); ?></span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Detik</span>
              </div>
            </div>
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
            <button class="btn-action-sm" type="button" onclick="copyContact('Rian Ardiansyah','0813-8899-7711','rian.dev@email.com')">Kontak Freelancer</button>
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

    <!-- Countdown Timer Script -->
    <script>
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
