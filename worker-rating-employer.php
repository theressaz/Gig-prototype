<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION["username"])) {
    header("Location: welcome-screen.php");
    exit;
}
$username = $_SESSION["username"];

require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/project-history.php';
require_once __DIR__ . '/includes/project-schedule.php';

function gig_ensure_employer_reviews_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `employer_reviews` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `contract_id` VARCHAR(50) NOT NULL,
            `worker_id` VARCHAR(50) NOT NULL,
            `employer_username` VARCHAR(100) NOT NULL,
            `project_title` VARCHAR(255) NOT NULL,
            `overall_rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
            `comment` TEXT NOT NULL,
            `badges` TEXT NOT NULL DEFAULT '',
            `recommend_employer` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_contract_worker` (`contract_id`, `worker_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

// ── Active contracts ─────────────────────────────────────────────────────────
$activeContracts = [
    'CTR-GIG-2026-0811' => [
        'id'          => 'CTR-GIG-2026-0811',
        'title'       => 'Redesign UI/UX Dashboard Prototype KarirHub',
        'employer'    => 'PT ABC',
        'employer_category' => 'IT & Software Partner',
        'workerId'    => 'theressaz@pasker.id',
        'workerName'  => 'Theressa Zaratrusha',
        'duration'    => '3 Minggu',
        'budget'      => 'Rp 8.500.000',
        'deliverables'=> '12 Layar Prototype Interaktif Figma, UI Kit Design System, User Testing Report',
    ],
    'CTR-GIG-2026-0905' => [
        'id'          => 'CTR-GIG-2026-0905',
        'title'       => 'Desain UI/UX Mobile App E-Commerce UMKM',
        'employer'    => 'CV Visual Studio Creative',
        'employer_category' => 'Design & Creative Agency',
        'workerId'    => 'theressaz@pasker.id',
        'workerName'  => 'Theressa Zaratrusha',
        'duration'    => '2 Minggu',
        'budget'      => 'Rp 6.500.000',
        'deliverables'=> 'Wireframe & High Fidelity Screens Mobile App E-Commerce',
    ],
];

$contractId  = trim((string)($_GET['contract'] ?? 'CTR-GIG-2026-0811'));
$projectData = gig_demo_active_project_by_id($contractId) ?? ($activeContracts[$contractId] ?? reset($activeContracts));
$contractKey = (string)($projectData['contract_id'] ?? $projectData['id']);
$completionState = gig_project_completion_status($contractKey);
$canSubmitReview = !empty($completionState['both_confirmed']);

$workerId = trim((string)($_SESSION['username'] ?? 'theressaz@pasker.id'));
$worker   = gig_find_worker($workerId) ?? ['id' => $workerId, 'name' => 'Theressa Zaratrusha', 'title' => 'Lead UI/UX Designer'];

$pdo          = gig_db();
$submitted    = false;
$errorMessage = '';

// ── Handle UNDO (delete rating) ──────────────────────────────────────────────
if (isset($_GET['undo']) && $_GET['undo'] === '1') {
    $undoContract = trim((string)($_GET['contract'] ?? ''));
    if ($undoContract !== '' && $pdo !== null) {
        gig_ensure_employer_reviews_table($pdo);
        $stmt = $pdo->prepare("DELETE FROM `employer_reviews` WHERE `contract_id` = :cid AND `worker_id` = :wid");
        $stmt->execute([':cid' => $undoContract, ':wid' => (string)($worker['id'] ?? $username)]);
    }
    if (isset($_SESSION['project_review_status'][$undoContract]) && is_array($_SESSION['project_review_status'][$undoContract])) {
        $_SESSION['project_review_status'][$undoContract]['worker_reviewed'] = false;
    }
    if (isset($_SESSION['worker_employer_reviews'][$undoContract])) {
        unset($_SESSION['worker_employer_reviews'][$undoContract]);
    }
    header('Location: worker-tugas.php');
    exit;
}

// ── Handle SUBMIT ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $overallRating  = min(5, max(1, (int)($_POST['overall_rating'] ?? 5)));
    $comment        = trim((string)($_POST['comment'] ?? ''));
    $selectedBadges = is_array($_POST['badges'] ?? null) ? $_POST['badges'] : [];
    $recommend      = !empty($_POST['recommend_employer']);
    $confirmDone    = !empty($_POST['confirm_deliverables']);
    $confirmGiven   = !empty($_POST['confirm_given_deliverables']);

    if (!$canSubmitReview) {
        $errorMessage = 'Ulasan belum bisa dikirim. Kedua pihak harus mengonfirmasi bahwa proyek selesai terlebih dahulu.';
    } elseif (!$confirmDone || !$confirmGiven) {
        $errorMessage = 'Centang seluruh konfirmasi proyek yang wajib untuk mengirim ulasan.';
    } elseif ($comment === '') {
        $errorMessage = 'Mohon tuliskan ulasan atau testimoni singkat untuk pemberi kerja.';
    } else {
        $badgesJson = implode('||', $selectedBadges);
        $todayDate  = date('Y-m-d');

        if ($pdo !== null) {
            gig_ensure_employer_reviews_table($pdo);
            $stmt = $pdo->prepare("
                INSERT INTO `employer_reviews`
                    (`contract_id`, `worker_id`, `employer_username`, `project_title`,
                     `overall_rating`, `comment`, `badges`, `recommend_employer`)
                VALUES
                    (:cid, :wid, :emp, :title,
                     :overall, :comment, :badges, :rec)
                ON DUPLICATE KEY UPDATE
                    `overall_rating`      = VALUES(`overall_rating`),
                    `comment`             = VALUES(`comment`),
                    `badges`              = VALUES(`badges`),
                    `recommend_employer`  = VALUES(`recommend_employer`)
            ");
            $stmt->execute([
                ':cid'     => $contractKey,
                ':wid'     => (string)($worker['id'] ?? $username),
                ':emp'     => (string)($projectData['employer'] ?? 'PT Perusahaan'),
                ':title'   => (string)($projectData['title'] ?? 'Proyek'),
                ':overall' => $overallRating,
                ':comment' => $comment,
                ':badges'  => $badgesJson,
                ':rec'     => $recommend ? 1 : 0,
            ]);
        }

        if (!isset($_SESSION['project_review_status']) || !is_array($_SESSION['project_review_status'])) {
            $_SESSION['project_review_status'] = [];
        }
        if (!isset($_SESSION['project_review_status'][$contractKey]) || !is_array($_SESSION['project_review_status'][$contractKey])) {
            $_SESSION['project_review_status'][$contractKey] = [
                'employer_reviewed' => false,
                'worker_reviewed' => false,
            ];
        }
        $_SESSION['project_review_status'][$contractKey]['worker_reviewed'] = true;

        if (!isset($_SESSION['worker_employer_reviews'])) {
            $_SESSION['worker_employer_reviews'] = [];
        }
        $_SESSION['worker_employer_reviews'][$contractKey] = [
            'overall_rating' => $overallRating,
            'comment' => $comment,
            'badges' => $selectedBadges,
        ];

        $submitted = true;
    }
}

// ── Check if already rated ───────────────────────────────────────────────────
$alreadyRated = false;
$existingRating = null;
if ($pdo !== null) {
    gig_ensure_employer_reviews_table($pdo);
    $stmt = $pdo->prepare("SELECT * FROM `employer_reviews` WHERE `contract_id` = :cid AND `worker_id` = :wid LIMIT 1");
    $stmt->execute([
        ':cid' => $contractKey,
        ':wid' => (string)($worker['id'] ?? $username),
    ]);
    $existingRating = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $alreadyRated = ($existingRating !== null);
} elseif (isset($_SESSION['worker_employer_reviews'][$contractKey])) {
    $alreadyRated = true;
}

$employerName = (string)($projectData['employer'] ?? 'Pemberi Kerja');
$employerCat  = (string)($projectData['employer_category'] ?? 'Pemberi Kerja');
$deliverablesText = trim((string)($projectData['deliverables'] ?? 'Deliverable proyek telah diselesaikan dan dikonfirmasi.'));
$undoLink = 'worker-rating-employer.php?contract=' . urlencode($contractKey) . '&undo=1';

$pageTitle         = 'Beri Ulasan Pemberi Kerja';
$pageKey           = 'tugas';
$breadcrumbCurrent = 'Ulasan Pemberi Kerja';

require __DIR__ . '/includes/worker-layout-start.php';
?>

<div class="page-toolbar" style="margin-bottom: 20px;">
  <div>
    <h1>Penilaian &amp; Ulasan Pemberi Kerja</h1>
    <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">
      Berikan penilaian dan masukan profesional terhadap pengalaman bekerja sama dengan Pemberi Kerja.
    </p>
  </div>
  <a href="worker-tugas.php" class="btn-create-post" style="text-decoration:none;background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:8px 14px;border-radius:8px;font-weight:700;">
    ← Kembali ke Proyek Aktif
  </a>
</div>

<?php if ($submitted): ?>
  <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;padding:24px;margin-bottom:24px;text-align:center;">
    <div style="font-size:2.5rem;margin-bottom:8px;">🎉</div>
    <h2 style="font-size:1.2rem;font-weight:800;color:#065f46;margin-bottom:6px;">Terima Kasih! Ulasan Anda Berhasil Dikirim</h2>
    <p style="font-size:0.88rem;color:#047857;max-width:560px;margin:0 auto 16px auto;line-height:1.5;">
      Penilaian Anda untuk <strong><?php echo htmlspecialchars($employerName, ENT_QUOTES, 'UTF-8'); ?></strong> telah tersimpan dalam sistem KarirHub Gig.
    </p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="worker-tugas.php" class="btn-create-post" style="text-decoration:none;background:#059669;color:#fff;padding:9px 18px;border-radius:8px;font-weight:700;">
        Lihat Proyek Aktif
      </a>
      <a href="<?php echo htmlspecialchars($undoLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn-create-post" style="text-decoration:none;background:#ffffff;color:#dc2626;border:1px solid #fca5a5;padding:9px 18px;border-radius:8px;font-weight:700;" onclick="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">
        Hapus / Ubah Ulasan
      </a>
    </div>
  </div>
<?php else: ?>

  <?php if ($errorMessage !== ''): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:0.88rem;font-weight:600;">
      ⚠️ <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <?php if (!$canSubmitReview): ?>
    <div style="background:#fffbebf5;border:1px solid #fde68a;color:#92400e;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:0.88rem;line-height:1.5;">
      ℹ️ <strong>Informasi Verifikasi:</strong> Tombol pengiriman ulasan akan aktif setelah kedua pihak (Gig Worker dan Pemberi Kerja) mengonfirmasi bahwa pengerjaan proyek telah selesai.
    </div>
  <?php endif; ?>

  <?php if ($alreadyRated): ?>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:14px 18px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <div style="font-size:0.88rem;">
        ✓ <strong>Anda sudah memberikan ulasan untuk pemberi kerja ini.</strong> Mengirim ulang form di bawah akan memperbarui ulasan sebelumnya.
      </div>
      <a href="<?php echo htmlspecialchars($undoLink, ENT_QUOTES, 'UTF-8'); ?>" style="font-size:0.82rem;font-weight:700;color:#dc2626;text-decoration:none;" onclick="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">
        [Hapus Ulasan Saya]
      </a>
    </div>
  <?php endif; ?>

  <form method="POST" style="display:grid;grid-template-columns:1fr 340px;gap:20px;">
    <!-- LEFT COLUMN: Form Fields -->
    <div style="display:flex;flex-direction:column;gap:20px;">
      
      <!-- Card 1: Target Employer Info -->
      <section style="background:#fff;border:1px solid var(--border-subtle);border-radius:14px;padding:20px;box-shadow:var(--shadow-xs);">
        <div style="font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:12px;">
          Pemberi Kerja yang Dinilai
        </div>
        <div style="display:flex;align-items:center;gap:14px;">
          <div style="width:52px;height:52px;border-radius:12px;background:#e0e7ff;color:#3730a3;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:800;flex-shrink:0;">
            🏢
          </div>
          <div>
            <h2 style="font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:2px;">
              <?php echo htmlspecialchars($employerName, ENT_QUOTES, 'UTF-8'); ?>
            </h2>
            <div style="font-size:0.82rem;color:var(--text-muted);">
              <?php echo htmlspecialchars($employerCat, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          </div>
        </div>
      </section>

      <!-- Card 2: Rating Star -->
      <section style="background:#fff;border:1px solid var(--border-subtle);border-radius:14px;padding:20px;box-shadow:var(--shadow-xs);">
        <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin-bottom:6px;">
          Skor Penilaian Keseluruhan
        </h3>
        <p style="font-size:0.83rem;color:var(--text-muted);margin-bottom:16px;">
          Berikan rating bintang untuk pengalaman kerja sama, kejelasan instruksi, dan ketepatan pembayaran.
        </p>

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
          <div style="display:flex;gap:6px;" id="star-rating-box">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <button type="button" class="star-btn" data-val="<?php echo $s; ?>" style="background:none;border:none;font-size:1.8rem;cursor:pointer;color:#f59e0b;padding:0;">★</button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="overall_rating" id="overall_rating_input" value="5" />
          <span id="rating_label_text" style="font-size:0.95rem;font-weight:800;color:#d97706;">5.0 — SANGAT PUAS</span>
        </div>
      </section>

      <!-- Card 3: Badges -->
      <section style="background:#fff;border:1px solid var(--border-subtle);border-radius:14px;padding:20px;box-shadow:var(--shadow-xs);">
        <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin-bottom:6px;">
          Lencana Apresiasi Pemberi Kerja (Opsional)
        </h3>
        <p style="font-size:0.83rem;color:var(--text-muted);margin-bottom:14px;">
          Pilih hal positif utama selama bekerja sama dengan Pemberi Kerja ini:
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:8px;">
          <?php
            $badgesList = [
                'Pembayaran Tepat Waktu',
                'Instruksi Jelas & Detail',
                'Komunikasi Profesional & Respektif',
                'Lingkungan Kerja Positif',
                'Umpan Balik Membantu',
                'Rekomendasi Utama',
            ];
            foreach ($badgesList as $bIdx => $bName):
          ?>
            <label style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #cbd5e1;border-radius:999px;font-size:0.8rem;font-weight:700;color:#334155;cursor:pointer;background:#f8fafc;user-select:none;">
              <input type="checkbox" name="badges[]" value="<?php echo htmlspecialchars($bName, ENT_QUOTES, 'UTF-8'); ?>" style="accent-color:#2563eb;" />
              <span><?php echo htmlspecialchars($bName, ENT_QUOTES, 'UTF-8'); ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Card 4: Comment -->
      <section style="background:#fff;border:1px solid var(--border-subtle);border-radius:14px;padding:20px;box-shadow:var(--shadow-xs);">
        <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin-bottom:6px;">
          Testimoni &amp; Catatan Pengalaman Kerja <span style="color:#ef4444;">*</span>
        </h3>
        <p style="font-size:0.83rem;color:var(--text-muted);margin-bottom:12px;">
          Tuliskan ulasan jujur mengenai profesionalitas, kejelasan brief, dan proses kerja sama.
        </p>
        <textarea name="comment" rows="4" required placeholder="Contoh: Pemberi kerja sangat kooperatif, memberikan brief yang jelas, dan pembayaran diproses tepat waktu setelah deliverables disetujui." style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:10px;font-size:0.88rem;line-height:1.5;outline:none;font-family:inherit;resize:vertical;"></textarea>

        <div style="margin-top:14px;">
          <label style="display:inline-flex;align-items:center;gap:8px;font-size:0.85rem;font-weight:700;color:#0f172a;cursor:pointer;">
            <input type="checkbox" name="recommend_employer" value="1" checked style="accent-color:#059669;width:16px;height:16px;" />
            <span>Rekomendasikan Pemberi Kerja ini kepada Gig Worker lain</span>
          </label>
        </div>
      </section>

      <!-- Card 5: Mandatory Confirmations & Submit -->
      <section style="background:#fff;border:1px solid var(--border-subtle);border-radius:14px;padding:20px;box-shadow:var(--shadow-xs);">
        <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin-bottom:12px;">
          Konfirmasi Persyaratan Penyelesaian
        </h3>

        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
          <label style="display:flex;align-items:flex-start;gap:10px;font-size:0.84rem;color:#334155;cursor:pointer;line-height:1.45;">
            <input type="checkbox" name="confirm_deliverables" value="1" required style="accent-color:#2563eb;margin-top:2px;" />
            <span>Saya mengonfirmasi bahwa seluruh hasil kerja (deliverables) proyek telah diserahkan sesuai kesepakatan.</span>
          </label>
          <label style="display:flex;align-items:flex-start;gap:10px;font-size:0.84rem;color:#334155;cursor:pointer;line-height:1.45;">
            <input type="checkbox" name="confirm_given_deliverables" value="1" required style="accent-color:#2563eb;margin-top:2px;" />
            <span>Saya menyatakan ulasan ini dibuat secara jujur dan profesional.</span>
          </label>
        </div>

        <button type="submit" name="submit_review" value="1" class="btn-create-post" style="width:100%;padding:12px;font-size:0.95rem;font-weight:800;background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#fff;border:none;border-radius:10px;cursor:pointer;" <?php echo !$canSubmitReview ? 'disabled style="opacity:0.55;cursor:not-allowed;"' : ''; ?>>
          Kirim Ulasan Pemberi Kerja
        </button>
      </section>

    </div>

    <!-- RIGHT COLUMN: Project Info Sidebar -->
    <div>
      <aside style="background:#fff;border:1px solid var(--border-subtle);border-radius:14px;padding:18px;box-shadow:var(--shadow-xs);position:sticky;top:20px;">
        <div style="font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:10px;">
          Ringkasan Proyek
        </div>
        <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin-bottom:8px;line-height:1.35;">
          <?php echo htmlspecialchars((string)($projectData['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
        </h3>
        <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:14px;">
          ID Kontrak: <code><?php echo htmlspecialchars($contractKey, ENT_QUOTES, 'UTF-8'); ?></code>
        </div>

        <div style="border-top:1px solid #f1f5f9;padding-top:12px;display:flex;flex-direction:column;gap:8px;font-size:0.83rem;">
          <div style="display:flex;justify-space-between;">
            <span style="color:var(--text-muted);">Status:</span>
            <span style="font-weight:700;color:#059669;">Selesai Dikerjakan</span>
          </div>
          <div style="display:flex;justify-space-between;">
            <span style="color:var(--text-muted);">Durasi Kerja:</span>
            <span style="font-weight:700;color:#0f172a;"><?php echo htmlspecialchars((string)($projectData['duration'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div style="display:flex;justify-space-between;">
            <span style="color:var(--text-muted);">Nilai Proyek:</span>
            <span style="font-weight:700;color:#0f172a;"><?php echo htmlspecialchars((string)($projectData['budget'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>

        <div style="margin-top:14px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:0.78rem;color:#475569;line-height:1.4;">
          <strong>Deliverable Diserahkan:</strong><br />
          <?php echo htmlspecialchars($deliverablesText, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      </aside>
    </div>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const starBtns = document.querySelectorAll('.star-btn');
      const input = document.getElementById('overall_rating_input');
      const labelText = document.getElementById('rating_label_text');

      const labels = {
        1: '1.0 — SANGAT KURANG',
        2: '2.0 — KURANG',
        3: '3.0 — CUKUP',
        4: '4.0 — BAGUS & PUAS',
        5: '5.0 — SANGAT PUAS'
      };

      function updateStars(val) {
        input.value = val;
        starBtns.forEach((btn, idx) => {
          btn.style.color = (idx < val) ? '#f59e0b' : '#cbd5e1';
        });
        if (labelText) {
          labelText.textContent = labels[val] || (val + '.0');
        }
      }

      starBtns.forEach((btn) => {
        btn.addEventListener('click', function() {
          const val = parseInt(this.getAttribute('data-val') || '5', 10);
          updateStars(val);
        });
      });

      updateStars(5);
    });
  </script>

<?php endif; ?>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
