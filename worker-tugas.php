<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/project-schedule.php';

$pageTitle = 'Proyek Aktif';
$pageKey = 'tugas';
$breadcrumbCurrent = 'Proyek Aktif';
require __DIR__ . '/includes/worker-layout-start.php';

// Load completion status from DB / session
$pdo = gig_db();
$dbCompletions = [];
if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare(
            "SELECT `contract_id`, `rating_given`, `review_given` FROM `project_completions`"
        );
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dbCompletions[$row['contract_id']] = $row;
        }
    } catch (Throwable $ignored) {}
}

$activeProjects = gig_demo_active_projects();
?>

    <div class="page-toolbar">
      <h1>Proyek Aktif</h1>
      <div style="font-size:0.82rem;color:var(--text-muted);background:#f1f5f9;padding:6px 14px;border-radius:9999px;font-weight:600;">
        Koordinasi langsung mitra freelancer &amp; pemberi kerja
      </div>
    </div>

    <div class="active-projects-list">
      <?php
        $ongoingProjects = [];
        foreach ($activeProjects as $idx => $proj) {
            $cId = $proj['contract_id'];
            $completed = isset($dbCompletions[$cId]) || isset($_SESSION['completed_projects'][$cId]);
            if (!$completed) {
                $proj['_idx'] = $idx;
                $ongoingProjects[] = $proj;
            }
        }
      ?>
      <?php if (count($ongoingProjects) === 0): ?>
        <div class="white-card" style="text-align:center;padding:48px 24px;">
          <h3 style="font-size:1.05rem;font-weight:800;margin-bottom:6px;">Tidak ada proyek aktif</h3>
          <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;">Proyek yang sudah selesai ada di Riwayat Proyek.</p>
          <a class="btn-primary-add" href="worker-riwayat.php" style="display:inline-flex;text-decoration:none;">Buka Riwayat Proyek</a>
        </div>
      <?php endif; ?>
      <?php foreach ($ongoingProjects as $proj):
        $idx = (int)$proj['_idx'];
        $cId = $proj['contract_id'];
        $completed = false;
        $ratingVal = 5;
      ?>
        <div class="active-project-card" id="project-<?php echo htmlspecialchars((string)$proj['id'], ENT_QUOTES, 'UTF-8'); ?>" style="margin-bottom:20px;<?php echo $completed ? 'border-color:#10b981;background:#f0fdf4;' : ''; ?>">
          <!-- CARD HEADER -->
          <div class="active-proj-header">
            <div>
              <div class="active-proj-title" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <a href="worker-project-detail.php?id=<?php echo urlencode($proj['id']); ?>" style="color:inherit;text-decoration:none;">
                  <?php echo htmlspecialchars($proj['title'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php if ($completed): ?>
                  <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#d1fae5;color:#047857;">✓ Selesai &amp; Dinilai</span>
                <?php else: ?>
                  <span class="<?php echo $proj['status_badge_class']; ?>"><?php echo htmlspecialchars($proj['status_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
              </div>
              <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">
                No. Kontrak: <strong><?php echo htmlspecialchars($proj['contract_id'], ENT_QUOTES, 'UTF-8'); ?></strong> &bull; Mulai: <strong><?php echo htmlspecialchars($proj['hired_label'], ENT_QUOTES, 'UTF-8'); ?></strong> &bull; Durasi Disepakati: <strong><?php echo htmlspecialchars($proj['duration'], ENT_QUOTES, 'UTF-8'); ?></strong> &bull; Gaji Proyek: <strong style="color:var(--primary-blue);"><?php echo htmlspecialchars($proj['budget'], ENT_QUOTES, 'UTF-8'); ?></strong>
              </div>
            </div>
          </div>

          <!-- CARD BODY -->
          <div class="active-proj-body">
            <!-- EMPLOYER INFO BOX -->
            <div class="freelancer-profile-box">
              <div class="fl-avatar" style="background:#1d4ed8; color:#fff; font-weight:800; font-size:1.1rem;">
                🏢
              </div>
              <div>
                <div class="fl-info-name">
                  <a href="employer-profile.php?name=<?php echo urlencode($proj['employer']); ?>" style="color:inherit;text-decoration:none;font-weight:800;">
                    <?php echo htmlspecialchars($proj['employer'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                  <span class="fl-rating-badge">★ 4.9</span>
                </div>
                <div class="fl-info-sub"><?php echo htmlspecialchars($proj['employer_category'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">&check; Kesepakatan disetujui &bull; kontak terbuka</div>
              </div>
            </div>

            <!-- COUNTDOWN TIMER WIDGET -->
            <div class="countdown-widget-box" style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:10px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
                <span style="font-size:0.72rem;color:#2563eb;font-weight:700;">Tenggat: <?php echo htmlspecialchars($proj['deadline'], ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <div style="display:flex;gap:8px;text-align:center;" class="js-project-countdown" data-deadline="<?php echo htmlspecialchars($proj['deadline_iso'], ENT_QUOTES, 'UTF-8'); ?>" id="countdown-worker-<?php echo $idx; ?>">
                <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                  <span class="c-days" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo $proj['days_left']; ?></span>
                  <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Hari</span>
                </div>
                <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                  <span class="c-hours" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', $proj['hours_left']); ?></span>
                  <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Jam</span>
                </div>
                <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                  <span class="c-mins" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;"><?php echo sprintf('%02d', $proj['mins_left']); ?></span>
                  <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Menit</span>
                </div>
                <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                  <span class="c-secs" style="font-size:1.1rem;font-weight:800;color:#2563eb;display:block;"><?php echo sprintf('%02d', $proj['secs_left']); ?></span>
                  <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Detik</span>
                </div>
              </div>
            </div>

          </div>

          <!-- CARD ACTIONS FOOTER -->
          <div class="active-proj-actions" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
              <?php if ($completed): ?>
                <span style="font-size:0.82rem;color:#047857;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                  <span>⭐</span> Rating Diberikan: <?php echo (int)$ratingVal; ?>/5
                </span>
              <?php else: ?>
                <span style="font-size:0.8rem;color:var(--text-muted);">Pengerjaan selesai? Selesaikan proyek dan tinggalkan penilaian.</span>
              <?php endif; ?>
            </div>

            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
              <button class="btn-action-sm" type="button" onclick="copyEmployerContact('<?php echo htmlspecialchars(addslashes($proj['employer']), ENT_QUOTES, 'UTF-8'); ?>','<?php echo $proj['employer_phone']; ?>','<?php echo $proj['employer_email']; ?>')">
                Kontak Pemberi Kerja
              </button>
              
              <a class="btn-outline-blue" href="employer-profile.php?name=<?php echo urlencode($proj['employer']); ?>">
                Profil Pemberi Kerja
              </a>
              <a class="btn-create-post" href="employer-rating-worker.php?contract=<?php echo urlencode($proj['contract_id']); ?>&from=worker" style="text-decoration:none;padding:6px 14px;font-size:0.82rem;background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);box-shadow:0 4px 10px rgba(217,119,6,0.35);">
                ★ Selesaikan &amp; Beri Rating
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Countdown & Actions Script -->
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
