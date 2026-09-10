<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';

$workerProfiles = gig_worker_profiles();
$vacancies = gig_project_vacancies();

// Only posted/active vacancies can be offered
$activeVacancies = array_values(array_filter($vacancies, fn($v) => $v['status'] === 'active'));

$pageTitle = 'Cari Mitra Gig Worker';
$pageKey = 'cari-mitra';
$breadcrumbCurrent = 'Cari Mitra Gig';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <div>
        <h1>Cari Mitra Gig Worker</h1>
        <p style="font-size:0.86rem;color:var(--text-muted);margin-top:4px;">Temukan Gig Worker yang sesuai dan tawarkan proyek aktif Anda langsung. Maks. 3 penawaran per lowongan.</p>
      </div>
    </div>

    <div class="toolbar-filter">
      <button class="filter-btn-pill active" type="button" onclick="filterWorkers('all', this)">Semua Bidang (<?php echo count($workerProfiles); ?>)</button>
      <button class="filter-btn-pill" type="button" onclick="filterWorkers('ui-ux', this)">UI/UX & Desain (2)</button>
      <button class="filter-btn-pill" type="button" onclick="filterWorkers('backend', this)">Backend & API (2)</button>
      <button class="filter-btn-pill" type="button" onclick="filterWorkers('marketing', this)">Pemasaran (2)</button>
      <div class="search-input-box">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Cari nama, keahlian, atau bidang..." onkeyup="searchWorkers(this.value)" />
      </div>
    </div>

    <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:12px;">Menampilkan <span id="worker-count"><?php echo count($workerProfiles); ?></span> Gig Worker tersedia</p>

    <div class="applicants-grid" id="workers-grid">
      <?php foreach ($workerProfiles as $w): ?>
      <article class="applicant-card" data-category="<?php echo htmlspecialchars($w['category'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="applicant-left-info">
          <a class="applicant-avatar" href="worker-profile.php?id=<?php echo urlencode($w['id']); ?>" style="background:<?php echo htmlspecialchars($w['color'], ENT_QUOTES, 'UTF-8'); ?>;">
            <img src="<?php echo htmlspecialchars($w['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8'); ?>" />
            <?php if (!empty($w['verified'])): ?><span class="verified-icon-badge">✓</span><?php endif; ?>
          </a>
          <div class="applicant-details">
            <div class="applicant-name-row">
              <a class="applicant-name" href="worker-profile.php?id=<?php echo urlencode($w['id']); ?>"><?php echo htmlspecialchars($w['name'], ENT_QUOTES, 'UTF-8'); ?></a>
              <span class="fl-rating-badge">★ <?php echo number_format((float)$w['rating'], 1); ?> (<?php echo (int)$w['reviews_count']; ?> ulasan)</span>
            </div>
            <div class="applicant-applied-role"><?php echo htmlspecialchars($w['title'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($w['location'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="project-skill-tags">
              <?php foreach (array_slice($w['skills'], 0, 3) as $skillTag): ?>
                <span class="skill-tag-item"><?php echo htmlspecialchars((string)$skillTag, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
            </div>
            <div style="font-size:0.75rem;color:#059669;font-weight:700;margin-top:4px;">
              <?php echo (int)$w['completed_projects']; ?> dari <?php echo (int)($w['total_projects'] ?? $w['completed_projects']); ?> proyek selesai
            </div>
          </div>
        </div>

        <div class="applicant-right-actions">
          <a class="btn-outline-blue" href="worker-profile.php?id=<?php echo urlencode($w['id']); ?>">Lihat Profil</a>
          <button type="button" class="btn-hire"
            onclick="openOfferModal('<?php echo htmlspecialchars($w['id'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($w['name']), ENT_QUOTES, 'UTF-8'); ?>')">
            Tawarkan Proyek
          </button>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Offer Project Modal -->
    <div class="modal-backdrop" id="offerProjectModal" onclick="if(event.target.id==='offerProjectModal') closeOfferModal();">
      <div class="modal-window" style="max-width:560px;">
        <div class="modal-header">
          <h3>Tawarkan Proyek ke <span id="offer-worker-name"></span></h3>
          <button class="modal-close-btn" type="button" onclick="closeOfferModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:20px;">
          <?php if (count($activeVacancies) === 0): ?>
            <div style="text-align:center;padding:24px 0;">
              <div style="font-size:2rem;margin-bottom:12px;">📋</div>
              <div style="font-weight:700;font-size:0.95rem;color:#1e293b;margin-bottom:6px;">Belum Ada Lowongan Aktif</div>
              <p style="font-size:0.85rem;color:var(--text-muted);line-height:1.5;margin-bottom:16px;">Anda belum memiliki lowongan yang sudah tayang. Ajukan lowongan terlebih dahulu dan tunggu persetujuan Admin sebelum dapat menawarkan proyek ke Gig Worker.</p>
              <a href="employer-lowongan.php" class="btn-primary" style="display:inline-block;text-decoration:none;padding:8px 20px;border-radius:8px;font-size:0.86rem;">Kelola Lowongan →</a>
            </div>
          <?php else: ?>
            <p style="font-size:0.86rem;color:var(--text-muted);margin-bottom:14px;">Pilih proyek aktif yang ingin Anda tawarkan. Maksimal <strong>3 penawaran</strong> per lowongan.</p>
            <div style="display:flex;flex-direction:column;gap:10px;" id="offer-vacancy-list">
              <?php foreach ($activeVacancies as $v): ?>
              <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;"
                   data-vacancy-id="<?php echo htmlspecialchars($v['id'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-offer-count="0"
                   data-quota="<?php echo (int)$v['quota']; ?>">
                <div>
                  <div style="font-size:0.88rem;font-weight:700;color:#1e293b;"><?php echo htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div style="font-size:0.74rem;color:var(--text-muted);margin-top:2px;">
                    <?php echo htmlspecialchars($v['budget'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($v['duration'], ENT_QUOTES, 'UTF-8'); ?>
                    · <span style="color:#059669;font-weight:700;">Kuota: <?php echo (int)$v['quota']; ?></span>
                  </div>
                </div>
                <button type="button" class="btn-hire offer-send-btn"
                  onclick="sendOffer('<?php echo htmlspecialchars($v['id'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars(addslashes($v['title']), ENT_QUOTES, 'UTF-8'); ?>')">
                  Kirim Tawaran
                </button>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeOfferModal()">Tutup</button>
        </div>
      </div>
    </div>

    <script>
      let currentOfferWorkerName = '';
      let offerCounts = {}; // vacancy_id => count of pending offers

      function filterWorkers(cat, btn) {
        document.querySelectorAll('.toolbar-filter .filter-btn-pill').forEach(function(b) { b.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        let count = 0;
        document.querySelectorAll('#workers-grid .applicant-card').forEach(function(card) {
          const show = cat === 'all' || card.getAttribute('data-category') === cat;
          card.style.display = show ? 'grid' : 'none';
          if (show) count++;
        });
        const el = document.getElementById('worker-count');
        if (el) el.innerText = String(count);
      }

      function searchWorkers(query) {
        const q = (query || '').toLowerCase();
        let count = 0;
        document.querySelectorAll('#workers-grid .applicant-card').forEach(function(card) {
          const match = card.innerText.toLowerCase().includes(q);
          card.style.display = match ? 'grid' : 'none';
          if (match) count++;
        });
        const el = document.getElementById('worker-count');
        if (el) el.innerText = String(count);
      }

      function openOfferModal(workerId, workerName) {
        currentOfferWorkerName = workerName;
        document.getElementById('offer-worker-name').innerText = workerName;
        document.getElementById('offerProjectModal').classList.add('open');
      }

      function closeOfferModal() {
        document.getElementById('offerProjectModal').classList.remove('open');
      }

      function sendOffer(vacancyId, vacancyTitle) {
        if (!offerCounts[vacancyId]) offerCounts[vacancyId] = 0;

        const container = document.querySelector('[data-vacancy-id="' + vacancyId + '"]');
        const maxOffers = 3;

        if (offerCounts[vacancyId] >= maxOffers) {
          showToast('Lowongan ini sudah mencapai batas 3 penawaran aktif.');
          return;
        }

        offerCounts[vacancyId]++;
        showToast('Penawaran "' + vacancyTitle + '" dikirim ke ' + currentOfferWorkerName + '. Menunggu konfirmasi Gig Worker.');

        if (offerCounts[vacancyId] >= maxOffers && container) {
          const btn = container.querySelector('.offer-send-btn');
          if (btn) {
            btn.disabled = true;
            btn.style.background = '#94a3b8';
            btn.style.cursor = 'not-allowed';
            btn.innerText = 'Penawaran Penuh (3/3)';
          }
        }

        closeOfferModal();
      }
    </script>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
