<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/project-offers.php';
require_once __DIR__ . '/includes/project-applications.php';

$flashMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_action'])) {
    $appId  = trim((string)($_POST['app_id'] ?? ''));
    $action = trim((string)($_POST['confirm_action'] ?? ''));
    if ($appId !== '' && in_array($action, ['confirm', 'decline'], true)) {
        $res = gig_worker_confirm_application($appId, $action, $username);
        if ($res['ok']) {
            $flashMsg = ($action === 'confirm')
                ? '🎉 Selamat! Anda RESMI DIREKRUT untuk proyek ini. Pemberi kerja telah diberitahukan dan kesepakatan kini aktif!'
                : 'Penawaran proyek telah ditolak. Pemberi kerja telah diberitahukan.';
        }
    }
}

gig_seed_demo_offers_if_needed($username);
$offers = gig_offers_for_worker($username);
$workerApps = gig_get_applications_for_worker($username);
?>

function gig_offer_banner_class(string $cat): string
{
    $c = strtolower($cat);
    if (str_contains($c, 'desain') || str_contains($c, 'ui/ux')) {
        return 'banner-blue';
    }
    if (str_contains($c, 'it') || str_contains($c, 'backend') || str_contains($c, 'pemrograman')) {
        return 'banner-cyan';
    }
    if (str_contains($c, 'pemasaran') || str_contains($c, 'konten') || str_contains($c, 'marketing')) {
        return 'banner-purple';
    }
    if (str_contains($c, 'data')) {
        return 'banner-green';
    }
    return 'banner-indigo';
}

function gig_offer_category_label(string $cat): string
{
    $c = strtolower($cat);
    if (str_contains($c, 'desain') || str_contains($c, 'ui/ux')) {
        return 'UI/UX & Desain';
    }
    if (str_contains($c, 'it') || str_contains($c, 'backend') || str_contains($c, 'pemrograman')) {
        return 'Backend & API';
    }
    if (str_contains($c, 'pemasaran') || str_contains($c, 'konten') || str_contains($c, 'marketing')) {
        return 'Digital Marketing';
    }
    if (str_contains($c, 'data')) {
        return 'Data & Analitik';
    }
    return $cat !== '' ? $cat : 'Proyek';
}

function gig_offer_avatar_svg(int $idx): string
{
    $avatars = [
        '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#e0f2fe"/><circle cx="50" cy="38" r="18" fill="#f87171"/><path d="M50 20c-10 0-18 6-18 15 0 2 1 4 3 5 2-8 7-12 15-12s13 4 15 12c2-1 3-3 3-5 0-9-8-15-18-15z" fill="#1e293b"/><circle cx="43" cy="38" r="2.5" fill="#1e293b"/><circle cx="57" cy="38" r="2.5" fill="#1e293b"/><path d="M46 45q4 3 8 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M22 82c3-14 15-22 28-22s25 8 28 22" fill="#3b82f6"/></svg>',
        '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#ccfbf1"/><circle cx="50" cy="40" r="18" fill="#fcd34d"/><path d="M30 36c0-12 9-20 20-20s20 8 20 20v4H30v-4z" fill="#0f766e"/><circle cx="42" cy="40" r="2.5" fill="#1e293b"/><circle cx="58" cy="40" r="2.5" fill="#1e293b"/><path d="M46 47q4 2 8 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M20 85c4-16 16-23 30-23s26 7 30 23" fill="#0d9488"/></svg>',
        '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#f3e8ff"/><circle cx="50" cy="38" r="18" fill="#fed7aa"/><path d="M30 30c0-8 8-16 20-16s20 8 20 16v18c0 0-6 4-20 4s-20-4-20-4V30z" fill="#6b21a8"/><circle cx="43" cy="38" r="2.5" fill="#1e293b"/><circle cx="57" cy="38" r="2.5" fill="#1e293b"/><path d="M45 45q5 4 10 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M22 84c3-15 15-22 28-22s25 7 28 22" fill="#9333ea"/></svg>',
        '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#dcfce7"/><circle cx="50" cy="38" r="18" fill="#fca5a5"/><path d="M32 24c4-6 11-8 18-8s14 2 18 8v10H32V24z" fill="#14532d"/><circle cx="43" cy="36" r="2.5" fill="#1e293b"/><circle cx="57" cy="36" r="2.5" fill="#1e293b"/><path d="M46 44q4 3 8 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M20 84c4-15 16-22 30-22s26 7 30 22" fill="#15803d"/></svg>',
    ];
    return $avatars[$idx % count($avatars)];
}

$pageTitle = 'Penawaran Proyek';
$pageKey = 'penawaran';
$breadcrumbCurrent = 'Penawaran Proyek';
require __DIR__ . '/includes/worker-layout-start.php';
?>

<style>
.penawaran-page { max-width: 1400px; margin: 0 auto; }

.penawaran-notice {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1e40af;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 0.84rem;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 22px;
}

.penawaran-count {
  font-size: 0.88rem;
  color: #64748b;
  margin-bottom: 18px;
}

.offer-cards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

@media (max-width: 1200px) {
  .offer-cards-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px) {
  .offer-cards-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .offer-cards-grid { grid-template-columns: 1fr; }
}

.offer-card-item {
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  text-decoration: none !important;
  color: inherit !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.offer-card-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  color: inherit !important;
}

.offer-card-item * { text-decoration: none !important; }

.offer-banner {
  height: 85px;
  position: relative;
  padding: 12px 16px;
  display: flex;
  justify-content: flex-end;
  align-items: flex-start;
}
.offer-banner.banner-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
.offer-banner.banner-cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.offer-banner.banner-purple { background: linear-gradient(135deg, #a855f7, #7e22ce); }
.offer-banner.banner-green { background: linear-gradient(135deg, #10b981, #047857); }
.offer-banner.banner-indigo { background: linear-gradient(135deg, #6366f1, #4338ca); }

.offer-banner-badge {
  background: rgba(255, 255, 255, 0.95);
  color: #0f172a;
  font-weight: 700;
  font-size: 0.75rem;
  padding: 4px 12px;
  border-radius: 9999px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.offer-avatar-wrap {
  position: relative;
  padding: 0 20px;
  margin-top: -30px;
  margin-bottom: 12px;
}

.offer-avatar {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: #ffffff;
  border: 3px solid #ffffff;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.offer-avatar svg { width: 100%; height: 100%; border-radius: 50%; }

.offer-check {
  position: absolute;
  bottom: 0;
  right: -2px;
  background: #0284c7;
  color: #ffffff;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: bold;
}

.offer-body {
  padding: 0 20px 20px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.offer-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 6px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 2.6em;
}

.offer-client {
  font-size: 0.84rem;
  color: #64748b;
  margin-bottom: 10px;
}

.offer-meta {
  font-size: 0.82rem;
  color: #475569;
  font-weight: 600;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.offer-skills {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 16px;
  min-height: 52px;
  align-content: flex-start;
}

.offer-skill {
  background: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 8px;
}

.offer-status-box {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1d4ed8;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 8px 12px;
  border-radius: 12px;
  text-align: center;
  margin-bottom: 16px;
}

.offer-cta {
  background: #1d4ed8;
  color: #ffffff !important;
  font-weight: 700;
  font-size: 0.88rem;
  padding: 10px 16px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  box-shadow: 0 2px 6px rgba(29, 78, 216, 0.2);
}

.offer-card-item:hover .offer-cta {
  background: #1e40af;
}

.offer-empty-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 48px 20px;
  text-align: center;
  color: #64748b;
}
.offer-empty-card h3 {
  font-size: 1.1rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 6px;
}
.offer-empty-card p { font-size: 0.9rem; margin-bottom: 16px; }
</style>

<div class="penawaran-page">
  <div class="page-toolbar">
    <h1>Penawaran Proyek</h1>
  </div>

  <?php if ($flashMsg !== ''): ?>
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:12px;font-size:0.86rem;margin-bottom:18px;font-weight:700;display:flex;align-items:center;gap:8px;">
      <span>✓</span> <?php echo htmlspecialchars($flashMsg, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <div class="penawaran-notice">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Ketika pemberi kerja menyetujui lamaran Anda, konfirmasi ketersediaan Anda di bawah ini untuk menjadi <strong>Resmi Direkrut</strong>.</span>
  </div>

  <?php if (count($offers) === 0): ?>
    <div class="offer-empty-card">
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;color:#94a3b8;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      <h3>Belum ada penawaran</h3>
      <p>Ketika pemberi kerja menawarkan proyek atau menyetujui lamaran Anda, undangan konfirmasi akan muncul di halaman ini.</p>
      <a class="btn-primary-add" href="worker-bursa.php" style="display:inline-flex;text-decoration:none;">Cari Proyek Sendiri</a>
    </div>
  <?php else: ?>
    <div class="penawaran-count">Menampilkan <strong><?php echo count($offers); ?></strong> penawaran &amp; konfirmasi proyek</div>
    <div class="offer-cards-grid">
      <?php foreach ($offers as $idx => $offer):
          $detailUrl = 'worker-project-detail.php?id=' . urlencode((string)$offer['detail_id']) . '&from=penawaran';
          $created = strtotime((string)($offer['created_at'] ?? ''));
          $createdLabel = $created ? date('d M Y', $created) : 'Baru saja';
          $skills = is_array($offer['skills'] ?? null) ? $offer['skills'] : [];
          $skillsToShow = array_slice($skills, 0, 3);
          $remaining = count($skills) - count($skillsToShow);
          
          $appMatch = null;
          foreach ($workerApps as $wa) {
              if (strtolower((string)$wa['vacancy_id']) === strtolower((string)$offer['detail_id'])) {
                  $appMatch = $wa;
                  break;
              }
          }
          $appId = $appMatch['id'] ?? ('APP-' . $offer['detail_id']);
          $appStatus = $appMatch['status'] ?? 'accepted_by_employer';
      ?>
        <div class="offer-card-item">
          <div class="offer-banner <?php echo htmlspecialchars(gig_offer_banner_class((string)$offer['category']), ENT_QUOTES, 'UTF-8'); ?>">
            <span class="offer-banner-badge"><?php echo htmlspecialchars(gig_offer_category_label((string)$offer['category']), ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="offer-avatar-wrap">
            <div class="offer-avatar">
              <?php echo gig_offer_avatar_svg($idx); ?>
              <div class="offer-check">✓</div>
            </div>
          </div>
          <div class="offer-body">
            <h3 class="offer-title">
              <a href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>" style="color:inherit;text-decoration:none;">
                <?php echo htmlspecialchars((string)$offer['project_title'], ENT_QUOTES, 'UTF-8'); ?>
              </a>
            </h3>
            <div class="offer-client">Ditawarkan oleh <strong><?php echo htmlspecialchars((string)$offer['employer_display'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
            <div class="offer-meta">
              <span style="color:#1d4ed8;font-weight:800;font-size:0.92rem;"><?php echo htmlspecialchars((string)$offer['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span style="color:#64748b;font-size:0.78rem;"><?php echo htmlspecialchars((string)$offer['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="offer-skills">
              <?php foreach ($skillsToShow as $skill): ?>
                <span class="offer-skill"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
              <?php if ($remaining > 0): ?>
                <span class="offer-skill">+<?php echo $remaining; ?></span>
              <?php endif; ?>
            </div>

            <?php if ($appStatus === 'confirmed_by_worker'): ?>
              <div style="background:#d1fae5;color:#047857;padding:10px;border-radius:10px;font-weight:800;font-size:0.82rem;text-align:center;margin-bottom:10px;">
                🎉 Resmi Direkrut &amp; Proyek Aktif
              </div>
              <a class="offer-cta" href="worker-tugas.php" style="background:#059669;">
                Buka di Proyek Aktif &rarr;
              </a>
            <?php elseif ($appStatus === 'declined_by_worker'): ?>
              <div style="background:#f1f5f9;color:#64748b;padding:8px;border-radius:10px;font-weight:700;font-size:0.8rem;text-align:center;">
                ✕ Penawaran Ditolak
              </div>
            <?php else: ?>
              <div class="offer-status-box" style="margin-bottom:10px;background:#fef3c7;color:#b45309;border-color:#fde68a;">
                ⏳ Menunggu Konfirmasi Anda · <?php echo htmlspecialchars($createdLabel, ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <form method="post" action="" style="display:flex;flex-direction:column;gap:6px;">
                <input type="hidden" name="app_id" value="<?php echo htmlspecialchars($appId, ENT_QUOTES, 'UTF-8'); ?>" />
                <button type="submit" name="confirm_action" value="confirm" style="background:#16a34a;color:#ffffff;border:none;padding:10px 14px;border-radius:10px;font-weight:800;font-size:0.84rem;cursor:pointer;box-shadow:0 2px 6px rgba(22,163,74,0.3);">
                  ✓ Konfirmasi &amp; Terima Proyek
                </button>
                <button type="submit" name="confirm_action" value="decline" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecdd3;padding:8px 12px;border-radius:10px;font-weight:700;font-size:0.8rem;cursor:pointer;" onclick="return confirm('Tolak penawaran proyek ini?')">
                  ✕ Tolak Penawaran
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
