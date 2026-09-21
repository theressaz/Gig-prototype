<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/project-vacancies.php';

$projectId = trim((string)($_GET['id'] ?? ''));
$job = gig_find_vacancy($projectId);

if (!$job) {
    // Fallback to first active project if id not specified
    $activeJobs = array_values(array_filter(gig_project_vacancies(), fn($j) => $j['status'] === 'active'));
    $job = $activeJobs[0] ?? null;
}

$pageTitle = $job ? $job['title'] : 'Detail Lowongan Proyek';
$pageKey = 'bursa';
$breadcrumbCurrent = 'Detail Lowongan Proyek';
require __DIR__ . '/includes/worker-layout-start.php';
?>

<style>
  .detail-header-card {
    background: #ffffff;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 24px 28px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
  }

  .detail-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--primary-blue);
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    margin-bottom: 14px;
    transition: transform 0.2s;
  }

  .detail-back-link:hover {
    transform: translateX(-3px);
  }

  .detail-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1.3;
    margin-bottom: 14px;
  }

  .detail-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
    font-size: 0.86rem;
    color: var(--text-muted);
    padding-top: 14px;
    border-top: 1px dashed var(--border-subtle);
  }

  .detail-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    padding: 6px 12px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--border-subtle);
    font-weight: 600;
    color: var(--text-main);
  }

  .detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
  }

  @media (max-width: 900px) {
    .detail-grid {
      grid-template-columns: 1fr;
    }
  }

  .detail-section-card {
    background: #ffffff;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
  }

  .detail-section-card h2 {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--text-main);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .detail-section-card p {
    font-size: 0.92rem;
    line-height: 1.6;
    color: #334155;
  }

  .sidebar-summary-card {
    background: #ffffff;
    border: 1.5px solid #bfdbfe;
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 90px;
  }

  .budget-highlight {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--primary-blue);
    margin: 8px 0;
  }

  .btn-apply-hero {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, var(--primary-blue), #1d4ed8);
    color: #ffffff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 0.95rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
  }

  .btn-apply-hero:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
  }

  .btn-applied-done {
    background: #ecfdf5 !important;
    color: #047857 !important;
    border: 1px solid #6ee7b7 !important;
    box-shadow: none !important;
    cursor: default !important;
  }

  /* MODAL APPLY */
  .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 100;
    padding: 20px;
  }

  .modal-backdrop.open {
    display: flex;
  }

  .modal-card {
    background: #ffffff;
    border-radius: var(--radius-lg);
    max-width: 540px;
    width: 100%;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    animation: modalSlide 0.25s ease-out;
  }

  @keyframes modalSlide {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .modal-header {
    background: var(--kemnaker-navy);
    color: #ffffff;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .modal-body {
    padding: 24px;
  }
</style>

<?php if (!$job): ?>
  <div class="detail-section-card" style="text-align: center; padding: 40px 20px;">
    <h2>Proyek Tidak Ditemukan</h2>
    <p>Lowongan proyek yang Anda cari tidak tersedia atau belum dipublikasikan.</p>
    <a href="worker-bursa.php" class="btn-primary-add" style="display: inline-flex; margin-top: 16px;">Kembali ke Lowongan Proyek</a>
  </div>
<?php else: ?>

  <!-- TOP HEADER CARD -->
  <section class="detail-header-card">
    <a href="worker-bursa.php" class="detail-back-link">&larr; Kembali ke Daftar Lowongan Proyek</a>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
      <div>
        <span class="project-category-tag" style="margin-bottom: 8px; display: inline-block;">
          <?php echo htmlspecialchars($job['category'], ENT_QUOTES, 'UTF-8'); ?>
        </span>
        <h1 class="detail-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
      </div>
    </div>

    <div class="detail-meta-row">
      <div class="detail-meta-item">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        Anggaran: <strong><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <div class="detail-meta-item">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Estimasi: <strong><?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <div class="detail-meta-item">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Lokasi: <strong><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <div class="detail-meta-item">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Dipublikasikan: <?php echo htmlspecialchars($job['posted'], ENT_QUOTES, 'UTF-8'); ?>
      </div>
    </div>
  </section>

  <!-- MAIN GRID -->
  <div class="detail-grid">
    <!-- LEFT CONTENT -->
    <div>
      <section class="detail-section-card">
        <h2>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Deskripsi &amp; Ruang Lingkup Pekerjaan (Scope of Work)
        </h2>
        <p><?php echo nl2br(htmlspecialchars($job['desc'], ENT_QUOTES, 'UTF-8')); ?></p>
        <p style="margin-top: 12px;">
          Pekerjaan ini dilakukan secara profesional dengan mengacu pada standar kualitas KarirHub Kemnaker RI. Seluruh komunikasi awal dan penyerahan karya dilakukan melalui platform untuk menjamin perlindungan hak kedua belah pihak.
        </p>
      </section>

      <?php if (!empty($job['deliverables'])): ?>
        <section class="detail-section-card">
          <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            Deliverable &amp; Hasil Akhir yang Diharapkan
          </h2>
          <p><?php echo htmlspecialchars($job['deliverables'], ENT_QUOTES, 'UTF-8'); ?></p>
        </section>
      <?php endif; ?>

      <section class="detail-section-card">
        <h2>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          Keahlian &amp; Skill yang Dibutuhkan
        </h2>
        <div class="project-skill-tags" style="margin-top: 8px;">
          <?php foreach ($job['skills'] as $sk): ?>
            <span class="skill-tag-item" style="font-size: 0.85rem; padding: 6px 14px; background: #eff6ff; color: var(--primary-blue); border: 1px solid #bfdbfe;">
              <?php echo htmlspecialchars($sk, ENT_QUOTES, 'UTF-8'); ?>
            </span>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="detail-section-card" style="background: #f8fafc; border-left: 4px solid var(--primary-blue);">
        <h2 style="font-size: 0.95rem; color: var(--kemnaker-navy);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Perlindungan &amp; Keamanan Akun SIAPKerja
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
          Informasi kontak pribadi Anda (nomor WhatsApp &amp; Email) tidak akan langsung dipublikasikan. Pemberi kerja hanya dapat melihat profil keahlian dan portofolio Anda. Kontak resmi baru akan dibuka setelah kedua belah pihak menyetujui kerja sama proyek ini.
        </p>
      </section>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div>
      <section class="sidebar-summary-card">
        <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 700;">Nilai Anggaran Proyek</div>
        <div class="budget-highlight"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 16px 0; padding: 14px 0; border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle); font-size: 0.84rem;">
          <div>
            <div style="color: var(--text-muted);">Durasi Kerja</div>
            <strong style="color: var(--text-main);"><?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div>
            <div style="color: var(--text-muted);">Kuota Posisi</div>
            <strong style="color: var(--text-main);"><?php echo (int)($job['quota'] ?? 1); ?> Pekerja</strong>
          </div>
          <div>
            <div style="color: var(--text-muted);">Jumlah Pelamar</div>
            <strong style="color: var(--text-main);"><?php echo (int)($job['applicantsCount'] ?? 0); ?> Orang</strong>
          </div>
          <div>
            <div style="color: var(--text-muted);">Batas Akhir</div>
            <strong style="color: var(--text-main);"><?php echo htmlspecialchars($job['deadline'] ?? 'Sesuai Kuota', ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
        </div>

        <div style="background: #f1f5f9; border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 16px;">
          <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Pemberi Kerja</div>
          <strong style="font-size: 0.9rem; color: var(--text-main);"><?php echo htmlspecialchars($job['employer'] ?? 'Pemberi Kerja Terverifikasi SIAPKerja', ENT_QUOTES, 'UTF-8'); ?></strong>
          <div style="font-size: 0.76rem; color: #10b981; font-weight: 700; margin-top: 2px;">&check; Terverifikasi KYC Kemnaker RI</div>
        </div>

        <button id="btnApplyMain" class="btn-apply-hero" type="button" onclick="openApplyModal()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Lamar Proyek Ini
        </button>
      </section>
    </div>
  </div>

  <!-- APPLY MODAL -->
  <div class="modal-backdrop" id="applyModal" onclick="if(event.target.id==='applyModal') closeApplyModal();">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="font-size: 1rem; font-weight: 800;">Ajukan Lamaran Proyek</h3>
        <button type="button" onclick="closeApplyModal()" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">&times;</button>
      </div>
      <div class="modal-body">
        <div style="font-size: 0.88rem; color: var(--text-main); font-weight: 700; margin-bottom: 6px;">
          <?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px;">
          Anggaran: <?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?> &bull; Durasi: <?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label class="form-label">Pesan / Catatan Singkat untuk Pemberi Kerja (Opsional)</label>
          <textarea id="applyNote" class="form-input" rows="3" style="font-size: 0.86rem;" placeholder="Sampaikan pengenalan singkat atau ketersediaan waktu pengerjaan Anda..."></textarea>
        </div>

        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-sm); padding: 12px; font-size: 0.78rem; color: #1e40af; margin-bottom: 20px;">
          ℹ Profil Gig Worker dan daftar portofolio Anda akan dikirimkan ke Pemberi Kerja. Kontak pribadi Anda tetap terlindungi.
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn-action-sm" onclick="closeApplyModal()" style="background:#f1f5f9; color:var(--text-main);">Batal</button>
          <button type="button" class="btn-primary-add" onclick="submitApplication()">Kirim Lamaran Proyek</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function openApplyModal() {
      document.getElementById('applyModal').classList.add('open');
    }

    function closeApplyModal() {
      document.getElementById('applyModal').classList.remove('open');
    }

    function submitApplication() {
      closeApplyModal();
      const btn = document.getElementById('btnApplyMain');
      btn.classList.add('btn-applied-done');
      btn.disabled = true;
      btn.innerHTML = '✓ Lamaran Proyek Terkirim';
      
      if (typeof showToast === 'function') {
        showToast('Lamaran proyek berhasil dikirim ke Pemberi Kerja! Anda dapat memantau statusnya di menu Tugas Aktif.');
      } else {
        alert('Lamaran proyek berhasil dikirim ke Pemberi Kerja!');
      }
    }
  </script>

<?php endif; ?>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
