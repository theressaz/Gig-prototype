<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$workerId = trim((string)($_GET['id'] ?? ''));
$worker = $workerId !== '' ? gig_find_worker($workerId) : null;
if ($worker === null) {
    header('Location: employer-pelamar.php');
    exit;
}

$isActive = !empty($_GET['active']);
$contactUnlocked = !empty($worker['agreed']) || $isActive;
$pageTitle = $worker['name'] . ' · Profil Gig Worker';
$pageKey = 'pelamar';
$breadcrumbCurrent = $worker['name'];
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <h1><?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
      <a class="btn-action-sm" href="employer-pelamar.php">← Kembali ke Pelamar</a>
    </div>

    <?php if ($contactUnlocked): ?>
      <div class="privacy-banner unlocked">Kedua belah pihak telah menyetujui kerja sama. Informasi kontak dapat dilihat di bawah.</div>
    <?php else: ?>
      <div class="privacy-banner">Kontak disembunyikan sampai Pemberi Kerja dan Gig Worker sama-sama menyetujui kerja sama.</div>
    <?php endif; ?>

    <section class="profile-hero">
      <div class="profile-photo-wrap">
        <img class="profile-photo" src="<?php echo htmlspecialchars($worker['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto profil <?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?>" />
        <?php if (!empty($worker['verified'])): ?><span class="verified-dot">✓</span><?php endif; ?>
      </div>
      <div class="profile-id">
        <div class="worker-display-name"><?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="profile-title"><?php echo htmlspecialchars($worker['title'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($worker['location'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="profile-meta">
          <span class="stars"><?php echo gig_stars($worker['rating']); ?></span>
          <strong><?php echo (int)$worker['rating']; ?></strong>
          <span class="chip gold"><?php echo (int)$worker['reviews_count']; ?> ulasan pemberi kerja</span>
          <span class="chip"><?php echo (int)$worker['completed_projects']; ?> proyek selesai</span>
        </div>
      </div>
    </section>

    <section class="section-card">
      <h2>Keterampilan</h2>
      <div class="skill-row">
        <?php foreach ($worker['skills'] as $skill): ?>
          <span class="skill-tag"><?php echo htmlspecialchars((string)$skill, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Pengalaman</h2>
      <div class="timeline">
        <?php foreach ($worker['experience'] as $item): ?>
          <article class="timeline-item">
            <strong><?php echo htmlspecialchars($item['role'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span class="muted"><?php echo htmlspecialchars($item['project'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($item['period'], ENT_QUOTES, 'UTF-8'); ?></span>
            <p><?php echo htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Portofolio</h2>
        <span style="font-size:0.8rem;color:var(--primary-blue);font-weight:600;">Klik item untuk melihat berkas deliverable</span>
      </div>
      <div class="portfolio-grid" style="margin-top:14px;">
        <?php foreach ($worker['portfolio'] as $item): ?>
          <article class="portfolio-card" onclick="openDeliverableModal('<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>')" style="cursor:pointer;transition:transform 0.2s, box-shadow 0.2s;">
            <div class="portfolio-cover" style="background:linear-gradient(135deg, <?php echo htmlspecialchars($worker['color'], ENT_QUOTES, 'UTF-8'); ?>, #1e293b);display:flex;flex-direction:column;justify-content:center;align-items:center;padding:20px;text-align:center;">
              <span style="font-size:1.1rem;font-weight:700;color:#fff;"><?php echo htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span style="font-size:0.75rem;color:rgba(255,255,255,0.8);margin-top:4px;">📂 Lihat <?php echo count($item['files'] ?? []); ?> Berkas Deliverable</span>
            </div>
            <div class="portfolio-body">
              <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <div class="muted"><?php echo htmlspecialchars($item['client'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($item['year'], ENT_QUOTES, 'UTF-8'); ?></div>
              <p style="margin-top:6px;font-size:0.84rem;line-height:1.4;"><?php echo htmlspecialchars($item['deliverable'], ENT_QUOTES, 'UTF-8'); ?></p>
              <div style="margin-top:10px;font-size:0.78rem;color:var(--primary-blue);font-weight:700;">🔍 Buka Berkas Hasil Pekerjaan →</div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Rating &amp; Ulasan Pemberi Kerja</h2>
      <div class="review-list">
        <?php foreach ($worker['reviews'] as $review): ?>
          <article class="review-card">
            <div class="review-top">
              <div>
                <strong><?php echo htmlspecialchars($review['employer'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <div class="muted"><?php echo htmlspecialchars($review['project'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($review['date'], ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div>
                <span class="stars"><?php echo gig_stars($review['rating']); ?></span>
                <strong><?php echo (int)$review['rating']; ?></strong>
              </div>
            </div>
            <p><?php echo htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-card">
      <h2>Informasi Kontak</h2>
      <?php if ($contactUnlocked): ?>
        <div class="contact-box">
          <span class="contact-tag">WhatsApp: <?php echo htmlspecialchars($worker['contact']['wa'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="contact-tag">Email: <?php echo htmlspecialchars($worker['contact']['email'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php else: ?>
        <div class="locked-box">
          Kontak belum dapat dibuka. Setelah Anda dan <?php echo htmlspecialchars($worker['name'], ENT_QUOTES, 'UTF-8'); ?> menyetujui kerja sama, WhatsApp dan email akan ditampilkan di sini.
        </div>
      <?php endif; ?>
    </section>

    <!-- Deliverable Modal -->
    <div class="modal-backdrop" id="deliverableModal" onclick="if(event.target.id==='deliverableModal') closeDeliverableModal();">
      <div class="modal-window" style="max-width:620px;">
        <div class="modal-header">
          <h3 id="deliv-modal-title">Deliverable Portofolio</h3>
          <button class="modal-close-btn" type="button" onclick="closeDeliverableModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:20px;">
          <div style="font-size:0.8rem;color:var(--text-muted);" id="deliv-modal-meta">Client · Year</div>
          <p style="margin:10px 0 16px 0;font-size:0.9rem;line-height:1.5;color:var(--text-dark);" id="deliv-modal-desc"></p>
          
          <h4 style="font-size:0.88rem;font-weight:700;margin-bottom:10px;">Berkas Upload Deliverable:</h4>
          <div id="deliv-modal-files" style="display:flex;flex-direction:column;gap:10px;">
            <!-- Rendered by JS -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeDeliverableModal()">Tutup</button>
        </div>
      </div>
    </div>

    <script>
      const workerPortfolioData = <?php echo json_encode($worker['portfolio']); ?>;

      function openDeliverableModal(id) {
        const item = workerPortfolioData.find(p => p.id === id);
        if (!item) return;
        
        document.getElementById('deliv-modal-title').innerText = item.title + ' (' + item.type + ')';
        document.getElementById('deliv-modal-meta').innerText = 'Pemberi Kerja: ' + item.client + ' · Tahun: ' + item.year;
        document.getElementById('deliv-modal-desc').innerText = item.deliverable;
        
        const filesContainer = document.getElementById('deliv-modal-files');
        filesContainer.innerHTML = '';
        
        if (item.files && item.files.length > 0) {
          item.files.forEach(f => {
            const fileRow = document.createElement('div');
            fileRow.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;';
            fileRow.innerHTML = `
              <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:1.2rem;">📄</span>
                <div>
                  <div style="font-size:0.85rem;font-weight:700;color:var(--text-dark);">${f.name}</div>
                  <div style="font-size:0.74rem;color:var(--text-muted);">${f.type} · ${f.size}</div>
                </div>
              </div>
              <button type="button" class="btn-action-sm" onclick="showToast('Mengunduh berkas ${f.name}...')">Unduh Berkas</button>
            `;
            filesContainer.appendChild(fileRow);
          });
        } else {
          filesContainer.innerHTML = '<div style="font-size:0.82rem;color:var(--text-muted);">Tidak ada lampiran file fisik.</div>';
        }
        
        document.getElementById('deliverableModal').classList.add('open');
      }

      function closeDeliverableModal() {
        document.getElementById('deliverableModal').classList.remove('open');
      }
    </script>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
