<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/project-vacancies.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$jobId = trim((string)($_GET['id'] ?? 'GIG-2026-09-001'));
$job = gig_find_vacancy($jobId);

if ($job === null) {
    header('Location: employer-lowongan.php');
    exit;
}

$workerProfiles = gig_worker_profiles();
// Filter applicants matching this job title/category
$applicants = array_filter($workerProfiles, function($w) use ($job) {
    return strtolower($w['applied_project']) === strtolower($job['title']) 
        || strtolower($w['category']) === strtolower($job['category']);
});

$pageTitle = 'Detail Lowongan · ' . $job['title'];
$pageKey = 'lowongan';
$breadcrumbCurrent = 'Detail Lowongan';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <div>
        <div style="font-size:0.8rem;color:var(--text-muted);font-weight:700;margin-bottom:2px;">ID LOWONGAN: <?php echo htmlspecialchars($job['id'], ENT_QUOTES, 'UTF-8'); ?></div>
        <h1><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
      </div>
      <a class="btn-action-sm" href="employer-lowongan.php">← Kembali ke Lowongan</a>
    </div>

    <?php if ($job['status'] === 'revision'): ?>
      <div style="background:#fff7ed;border:1px solid #ffedd5;border-left:4px solid #ea580c;padding:14px 18px;border-radius:10px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:8px;font-weight:800;color:#c2410c;font-size:0.95rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Status: Perlu Revisi oleh Pemberi Kerja
        </div>
        <p style="margin:6px 0 10px 0;font-size:0.85rem;color:#7c2d12;line-height:1.5;">
          <strong>Catatan Admin Verification:</strong> <?php echo htmlspecialchars($job['adminNote'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <button class="btn-action-sm" type="button" onclick="showToast('Modul edit rincian lowongan dibuka. Silakan sesuaikan deskripsi.')" style="background:#ea580c;color:#fff;border:none;">Edit &amp; Ajukan Ulang Verifikasi</button>
      </div>
    <?php elseif ($job['status'] === 'rejected'): ?>
      <div style="background:#fef2f2;border:1px solid #fee2e2;border-left:4px solid #dc2626;padding:14px 18px;border-radius:10px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:8px;font-weight:800;color:#991b1b;font-size:0.95rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          Status: Lowongan Ditolak Admin
        </div>
        <p style="margin:6px 0 0 0;font-size:0.85rem;color:#7f1d1d;line-height:1.5;">
          <strong>Alasan Penolakan:</strong> <?php echo htmlspecialchars($job['adminNote'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
        </p>
      </div>
    <?php elseif ($job['status'] === 'review'): ?>
      <div style="background:#eff6ff;border:1px solid #dbeafe;border-left:4px solid #2563eb;padding:14px 18px;border-radius:10px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:8px;font-weight:800;color:#1e40af;font-size:0.95rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Status: Menunggu Verifikasi Admin
        </div>
        <p style="margin:6px 0 0 0;font-size:0.85rem;color:#1e3a8a;line-height:1.5;">
          Lowongan ini sedang diperiksa oleh tim Admin KarirHub. Setelah disetujui, lowongan akan otomatis berstatus <strong>Tayang Aktif</strong> dan dipublikasikan ke pencari kerja.
        </p>
      </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
      <div>
        <section class="section-card">
          <h2>Rincian Deskripsi Lowongan</h2>
          <p style="font-size:0.92rem;line-height:1.6;color:var(--text-dark);"><?php echo htmlspecialchars($job['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
          
          <h3 style="font-size:0.9rem;font-weight:700;margin-top:16px;">Keahlian / Skill yang Dibutuhkan</h3>
          <div class="skill-row" style="margin-top:6px;">
            <?php foreach ($job['skills'] as $skill): ?>
              <span class="skill-tag"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="section-card" style="margin-top:20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2>Daftar Pelamar Masuk (<?php echo count($applicants); ?>)</h2>
            <a class="btn-action-sm" href="employer-pelamar.php">Kelola Semua Pelamar</a>
          </div>

          <?php if (count($applicants) > 0): ?>
            <div style="display:flex;flex-direction:column;gap:12px;margin-top:14px;">
              <?php foreach ($applicants as $app): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                  <div style="display:flex;align-items:center;gap:12px;">
                    <img src="<?php echo htmlspecialchars($app['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:40px;height:40px;border-radius:50%;background:#e2e8f0;" />
                    <div>
                      <div style="font-size:0.9rem;font-weight:700;">
                        <a href="worker-profile.php?id=<?php echo urlencode($app['id']); ?>" style="color:inherit;text-decoration:none;"><?php echo htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <span style="font-size:0.75rem;color:#f59e0b;font-weight:800;margin-left:6px;">★ <?php echo (int)$app['rating']; ?></span>
                      </div>
                      <div style="font-size:0.76rem;color:var(--text-muted);"><?php echo htmlspecialchars($app['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                  </div>
                  <div style="text-align:right;">
                    <div style="font-size:0.85rem;font-weight:800;color:var(--primary-blue);"><?php echo htmlspecialchars($app['bid'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <a class="btn-outline-blue" href="worker-profile.php?id=<?php echo urlencode($app['id']); ?>" style="padding:4px 10px;font-size:0.76rem;margin-top:4px;display:inline-block;">Lihat Profil</a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:0.85rem;">Belum ada pelamar untuk lowongan ini.</div>
          <?php endif; ?>
        </section>
      </div>

      <div>
        <section class="section-card">
          <h2>Ringkasan Lowongan</h2>
          <div style="display:flex;flex-direction:column;gap:12px;font-size:0.84rem;margin-top:10px;">
            <div>
              <span style="color:var(--text-muted);display:block;">Status Publikasi</span>
              <strong style="font-size:0.9rem;"><?php echo htmlspecialchars($job['statusLabel'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;">Anggaran / Fee Proyek</span>
              <strong style="font-size:1.05rem;color:var(--primary-blue);"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;">Estimasi Durasi</span>
              <strong><?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;">Batas Waktu Lamaran</span>
              <strong><?php echo htmlspecialchars($job['deadline'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;">Lokasi Penempatan</span>
              <strong><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;">Kuota Pekerja</span>
              <strong><?php echo (int)$job['acceptedCount']; ?> / <?php echo (int)$job['quota']; ?> Terisi</strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;">Tanggal Dipasang</span>
              <strong><?php echo htmlspecialchars($job['posted'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>
        </section>
      </div>
    </div>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
