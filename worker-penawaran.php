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
        if (!empty($res['ok'])) {
            $flashMsg = ($action === 'confirm')
                ? 'Selamat! Anda resmi direkrut untuk proyek ini. Pemberi kerja telah diberitahukan.'
                : 'Penawaran proyek telah ditolak. Pemberi kerja telah diberitahukan.';
        }
    }
}

gig_seed_demo_offers_if_needed($username);
$offers = gig_offers_for_worker($username);
$workerApps = gig_get_applications_for_worker($username);

if (!function_exists('getCategoryBannerClass')) {
    function getCategoryBannerClass(string $cat): string
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
}

if (!function_exists('getCategoryShortLabel')) {
    function getCategoryShortLabel(string $cat): string
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
}

if (!function_exists('getProjectAvatarSvg')) {
    function getProjectAvatarSvg(int $idx): string
    {
        $avatars = [
            '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#e0f2fe"/><circle cx="50" cy="38" r="18" fill="#f87171"/><path d="M50 20c-10 0-18 6-18 15 0 2 1 4 3 5 2-8 7-12 15-12s13 4 15 12c2-1 3-3 3-5 0-9-8-15-18-15z" fill="#1e293b"/><circle cx="43" cy="38" r="2.5" fill="#1e293b"/><circle cx="57" cy="38" r="2.5" fill="#1e293b"/><path d="M46 45q4 3 8 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M22 82c3-14 15-22 28-22s25 8 28 22" fill="#3b82f6"/></svg>',
            '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#ccfbf1"/><circle cx="50" cy="40" r="18" fill="#fcd34d"/><path d="M30 36c0-12 9-20 20-20s20 8 20 20v4H30v-4z" fill="#0f766e"/><circle cx="42" cy="40" r="2.5" fill="#1e293b"/><circle cx="58" cy="40" r="2.5" fill="#1e293b"/><path d="M46 47q4 2 8 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M20 85c4-16 16-23 30-23s26 7 30 23" fill="#0d9488"/></svg>',
            '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#f3e8ff"/><circle cx="50" cy="38" r="18" fill="#fed7aa"/><path d="M30 30c0-8 8-16 20-16s20 8 20 16v18c0 0-6 4-20 4s-20-4-20-4V30z" fill="#6b21a8"/><circle cx="43" cy="38" r="2.5" fill="#1e293b"/><circle cx="57" cy="38" r="2.5" fill="#1e293b"/><path d="M45 45q5 4 10 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M22 84c3-15 15-22 28-22s25 7 28 22" fill="#9333ea"/></svg>',
            '<svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#dcfce7"/><circle cx="50" cy="38" r="18" fill="#fca5a5"/><path d="M32 24c4-6 11-8 18-8s14 2 18 8v10H32V24z" fill="#14532d"/><circle cx="43" cy="36" r="2.5" fill="#1e293b"/><circle cx="57" cy="36" r="2.5" fill="#1e293b"/><path d="M46 44q4 3 8 0" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/><path d="M20 84c4-15 16-22 30-22s26 7 30 22" fill="#15803d"/></svg>',
        ];
        return $avatars[$idx % count($avatars)];
    }
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

.subheader-info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  font-size: 0.88rem;
}
.subheader-count { color: #64748b; font-weight: 500; }

.proyek-cards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
@media (max-width: 1200px) {
  .proyek-cards-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px) {
  .proyek-cards-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .proyek-cards-grid { grid-template-columns: 1fr; }
}

.proyek-card-item {
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.proyek-card-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.card-header-banner {
  height: 85px;
  position: relative;
  padding: 12px 16px;
  display: flex;
  justify-content: flex-end;
  align-items: flex-start;
}
.banner-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
.banner-cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.banner-purple { background: linear-gradient(135deg, #a855f7, #7e22ce); }
.banner-green { background: linear-gradient(135deg, #10b981, #047857); }
.banner-indigo { background: linear-gradient(135deg, #6366f1, #4338ca); }

.banner-badge {
  background: rgba(255, 255, 255, 0.95);
  color: #0f172a;
  font-weight: 700;
  font-size: 0.75rem;
  padding: 4px 12px;
  border-radius: 9999px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.avatar-overlap-wrapper {
  position: relative;
  padding: 0 20px;
  margin-top: -30px;
  margin-bottom: 12px;
}
.avatar-circle {
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
.avatar-circle svg { width: 100%; height: 100%; border-radius: 50%; }
.verified-check-badge {
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

.proyek-card-body {
  padding: 0 20px 20px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.card-project-title {
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
.card-project-title a {
  color: inherit;
  text-decoration: none;
}
.card-project-title a:hover { color: #1d4ed8; }

.card-project-client {
  font-size: 0.84rem;
  color: #64748b;
  margin-bottom: 10px;
}

.card-meta-detail {
  font-size: 0.82rem;
  color: #475569;
  font-weight: 600;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.card-skills-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 16px;
  min-height: 52px;
  align-content: flex-start;
}
.skill-pill-sm {
  background: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 8px;
}
.skill-pill-more {
  background: #e2e8f0;
  color: #334155;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 8px;
}

.status-pill-box {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1d4ed8;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 8px 12px;
  border-radius: 12px;
  text-align: center;
  margin-bottom: 12px;
}
.status-pill-box.wait {
  background: #fef3c7;
  border-color: #fde68a;
  color: #b45309;
}
.status-pill-box.ok {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #047857;
}
.status-pill-box.off {
  background: #f8fafc;
  border-color: #e2e8f0;
  color: #64748b;
}

.btn-tawarkan {
  background: #1d4ed8;
  color: #ffffff;
  font-weight: 700;
  font-size: 0.88rem;
  padding: 10px 16px;
  border-radius: 12px;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  border: none;
  box-shadow: 0 2px 6px rgba(29, 78, 216, 0.2);
  cursor: pointer;
}
.btn-tawarkan:hover { background: #1e40af; color: #ffffff; }
.btn-tawarkan.green { background: #059669; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25); }
.btn-tawarkan.green:hover { background: #047857; }

.offer-actions { display: flex; flex-direction: column; gap: 6px; }
.offer-decline {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecdd3;
  padding: 8px 12px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.8rem;
  cursor: pointer;
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

.flash-ok {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 0.86rem;
  margin-bottom: 18px;
  font-weight: 700;
}
</style>

<div class="penawaran-page">
  <div class="page-toolbar">
    <h1>Penawaran Proyek</h1>
  </div>

  <?php if ($flashMsg !== ''): ?>
    <div class="flash-ok"><?php echo htmlspecialchars($flashMsg, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <div class="penawaran-notice">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Pemberi kerja dapat menawarkan lowongan yang sudah tayang. Klik judul atau <strong>Lihat Detail Proyek</strong> untuk membuka detailnya.</span>
  </div>

  <?php if (count($offers) === 0): ?>
    <div class="offer-empty-card">
      <h3>Belum ada penawaran</h3>
      <p>Ketika pemberi kerja menawarkan proyek yang sudah diposting, tawaran itu akan muncul di halaman ini.</p>
      <a class="btn-tawarkan" href="worker-bursa.php" style="max-width:240px;margin:0 auto;">Cari Proyek Sendiri</a>
    </div>
  <?php else: ?>
    <div class="subheader-info-row">
      <div class="subheader-count">Menampilkan <strong><?php echo count($offers); ?></strong> penawaran proyek</div>
    </div>
    <div class="proyek-cards-grid">
      <?php foreach ($offers as $idx => $offer):
          $detailUrl = 'worker-project-detail.php?id=' . urlencode((string)$offer['detail_id']) . '&from=penawaran';
          $created = strtotime((string)($offer['created_at'] ?? ''));
          $createdLabel = $created ? date('d M Y', $created) : 'Baru saja';
          $skills = is_array($offer['skills'] ?? null) ? $offer['skills'] : [];
          $skillsToShow = array_slice($skills, 0, 3);
          $remaining = count($skills) - count($skillsToShow);
          $bannerClass = getCategoryBannerClass((string)$offer['category']);
          $badgeLabel = getCategoryShortLabel((string)$offer['category']);

          $appMatch = null;
          foreach ($workerApps as $wa) {
              if (strtolower((string)($wa['vacancy_id'] ?? '')) === strtolower((string)$offer['detail_id'])) {
                  $appMatch = $wa;
                  break;
              }
          }
          $appId = (string)($appMatch['id'] ?? ('APP-' . $offer['detail_id']));
          $appStatus = (string)($appMatch['status'] ?? 'pending');
      ?>
        <article class="proyek-card-item">
          <div>
            <div class="card-header-banner <?php echo htmlspecialchars($bannerClass, ENT_QUOTES, 'UTF-8'); ?>">
              <span class="banner-badge"><?php echo htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="avatar-overlap-wrapper">
              <div class="avatar-circle">
                <?php echo getProjectAvatarSvg((int)$idx); ?>
                <div class="verified-check-badge">✓</div>
              </div>
            </div>
            <div class="proyek-card-body">
              <h3 class="card-project-title">
                <a href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars((string)$offer['project_title'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </h3>
              <div class="card-project-client">
                Ditawarkan oleh <strong><?php echo htmlspecialchars((string)$offer['employer_display'], ENT_QUOTES, 'UTF-8'); ?></strong>
              </div>
              <div class="card-meta-detail">
                <span style="color:#1d4ed8;font-weight:800;font-size:0.92rem;"><?php echo htmlspecialchars((string)$offer['budget'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span style="color:#64748b;font-size:0.78rem;"><?php echo htmlspecialchars((string)$offer['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <div class="card-skills-row">
                <?php foreach ($skillsToShow as $skill): ?>
                  <span class="skill-pill-sm"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
                <?php if ($remaining > 0): ?>
                  <span class="skill-pill-more">+<?php echo $remaining; ?></span>
                <?php endif; ?>
              </div>

              <?php if ($appStatus === 'confirmed_by_worker'): ?>
                <div class="status-pill-box ok">Resmi Direkrut · Proyek Aktif</div>
                <a class="btn-tawarkan green" href="worker-tugas.php">Buka di Tugas Aktif</a>
              <?php elseif ($appStatus === 'declined_by_worker'): ?>
                <div class="status-pill-box off">Penawaran ditolak</div>
                <a class="btn-tawarkan" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">Lihat Detail Proyek</a>
              <?php elseif ($appStatus === 'accepted_by_employer'): ?>
                <div class="status-pill-box wait">Menunggu konfirmasi Anda · <?php echo htmlspecialchars($createdLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                <a class="btn-tawarkan" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">Lihat Detail Proyek</a>
                <form method="post" action="" class="offer-actions" style="margin-top:8px;">
                  <input type="hidden" name="app_id" value="<?php echo htmlspecialchars($appId, ENT_QUOTES, 'UTF-8'); ?>" />
                  <button type="submit" name="confirm_action" value="confirm" class="btn-tawarkan green">Konfirmasi &amp; Terima Proyek</button>
                  <button type="submit" name="confirm_action" value="decline" class="offer-decline" onclick="return confirm('Tolak penawaran proyek ini?')">Tolak Penawaran</button>
                </form>
              <?php else: ?>
                <div class="status-pill-box">Menunggu tanggapan · <?php echo htmlspecialchars($createdLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                <a class="btn-tawarkan" href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">Lihat Detail Proyek</a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
