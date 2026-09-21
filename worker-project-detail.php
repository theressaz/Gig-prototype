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

$pageTitle = $job ? $job['title'] : 'Detail Proyek';
$pageKey = 'bursa';
$breadcrumbCurrent = 'Detail Proyek';
require __DIR__ . '/includes/worker-layout-start.php';
?>

<style>
  .detail-header-card {
    background: #ffffff;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 28px;
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
    margin-bottom: 16px;
    transition: transform 0.2s;
  }

  .detail-back-link:hover {
    transform: translateX(-3px);
  }

  .detail-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
    margin: 0 0 10px 0;
    text-transform: uppercase;
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

  .detail-section-card h3 {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 14px;
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
    margin: 6px 0;
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
    <a href="worker-bursa.php" class="btn-primary-add" style="display: inline-flex; margin-top: 16px;">Kembali ke Cari Proyek</a>
  </div>
<?php else: ?>

  <!-- TOP HEADER CARD -->
  <section class="detail-header-card">
    <a href="worker-bursa.php" class="detail-back-link">&larr; Kembali ke Cari Proyek</a>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
      <div style="display: flex; gap: 18px; align-items: flex-start;">
        <!-- Logo / Avatar Icon Thumbnail -->
        <div style="width: 64px; height: 64px; border-radius: 12px; background: #f1f5f9; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
          <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>

        <div>
          <h1 class="detail-title"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h1>

          <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.88rem; color: #64748b;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <span>📍</span> <strong><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
              <span>📅 Diposting <?php echo htmlspecialchars($job['posted'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span>&bull;</span>
              <span>👥 Kuota: <strong><?php echo (int)($job['quota'] ?? 1); ?> Freelancer</strong></span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; color: #dc2626; font-weight: 600;">
              <span>🔔</span> Batas waktu penawaran: <strong><?php echo htmlspecialchars($job['deadline'] ?? '31 Des 2026', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Header Action Button Right -->
      <div>
        <button type="button" onclick="openApplyModal()" style="padding: 12px 28px; font-size: 0.95rem; font-weight: 800; border-radius: 10px; background: #2563eb; color: #ffffff; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); transition: all 0.2s;">
          Lamar Proyek
        </button>
      </div>
    </div>

    <!-- Share Social Row -->
    <div style="margin-top: 20px; padding-top: 14px; border-top: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 12px; font-size: 0.84rem; color: #64748b;">
      <span style="font-weight: 600;">Bagikan:</span>
      <a href="#" onclick="event.preventDefault(); alert('Link proyek berhasil disalin!');" style="color: #25d366; text-decoration: none; font-size: 1.1rem;" title="WhatsApp">💬</a>
      <a href="#" onclick="event.preventDefault(); alert('Link proyek berhasil disalin!');" style="color: #0a66c2; text-decoration: none; font-size: 1.1rem;" title="LinkedIn">🔗</a>
      <a href="#" onclick="event.preventDefault(); alert('Link proyek berhasil disalin!');" style="color: #1da1f2; text-decoration: none; font-size: 1.1rem;" title="Twitter / X">❌</a>
      <a href="#" onclick="event.preventDefault(); alert('Link proyek berhasil disalin!');" style="color: #1877f2; text-decoration: none; font-size: 1.1rem;" title="Facebook">📘</a>
      <a href="#" onclick="event.preventDefault(); alert('Link proyek disalin ke clipboard!');" style="color: #64748b; text-decoration: none; font-size: 0.82rem; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-weight: 600; margin-left: 4px;">📋 Salin Link</a>
    </div>
  </section>

  <!-- MAIN GRID -->
  <div class="detail-grid">
    <!-- LEFT CONTENT COLUMN (SHOWING EMPLOYER INPUT FIELDS FOR PROJECT OPENING) -->
    <div>
      <!-- 1. RINCIAN PROYEK (CONTAINING EMPLOYER INPUT SPECIFICATIONS) -->
      <section class="detail-section-card">
        <h3 style="margin-bottom: 16px;">Rincian Proyek</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 16px; font-size: 0.86rem;">
          <div>
            <div style="color: #64748b; font-size: 0.8rem; margin-bottom: 4px;">Kategori Proyek</div>
            <strong style="color: #0f172a;"><?php echo htmlspecialchars($job['category'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div>
            <div style="color: #64748b; font-size: 0.8rem; margin-bottom: 4px;">Anggaran / Fee Proyek</div>
            <strong style="color: #2563eb; font-weight: 800;"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div>
            <div style="color: #64748b; font-size: 0.8rem; margin-bottom: 4px;">Estimasi Durasi</div>
            <strong style="color: #0f172a;"><?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div>
            <div style="color: #64748b; font-size: 0.8rem; margin-bottom: 4px;">Lokasi Penempatan</div>
            <strong style="color: #0f172a;"><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div>
            <div style="color: #64748b; font-size: 0.8rem; margin-bottom: 4px;">Kuota Freelancer</div>
            <strong style="color: #0f172a;"><?php echo (int)($job['quota'] ?? 1); ?> Freelancer</strong>
          </div>
          <div>
            <div style="color: #64748b; font-size: 0.8rem; margin-bottom: 4px;">Batas Akhir Penawaran</div>
            <strong style="color: #dc2626; font-weight: 700;"><?php echo htmlspecialchars($job['deadline'] ?? 'Sesuai Kuota', ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
        </div>
      </section>

      <!-- 2. DESKRIPSI PROYEK -->
      <section class="detail-section-card">
        <h3>Deskripsi &amp; Ruang Lingkup Proyek</h3>
        <p style="font-size: 0.92rem; line-height: 1.6; color: #334155; margin-bottom: 12px;">
          <?php echo nl2br(htmlspecialchars($job['desc'], ENT_QUOTES, 'UTF-8')); ?>
        </p>
        <p style="font-size: 0.88rem; line-height: 1.6; color: #64748b;">
          Seluruh pengerjaan proyek dilaksanakan secara profesional sesuai dengan kesepakatan dan standar kualitas SIAPKerja. Penyerahan hasil karya dilakukan melalui platform untuk menjamin perlindungan hak kedua belah pihak.
        </p>
      </section>

      <!-- 3. TARGET DELIVERABLE & HASIL AKHIR -->
      <?php if (!empty($job['deliverables'])): ?>
        <section class="detail-section-card">
          <h3>Target Deliverable &amp; Hasil Akhir</h3>
          <p style="font-size: 0.9rem; line-height: 1.6; color: #334155; margin-bottom: 12px;">
            <?php echo htmlspecialchars($job['deliverables'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
        </section>
      <?php endif; ?>

      <!-- 4. KEAHLIAN & SKILL YANG DIBUTUHKAN -->
      <section class="detail-section-card">
        <h3>Keahlian &amp; Skill yang Dibutuhkan</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
          <?php foreach ($job['skills'] as $sk): ?>
            <span style="font-size: 0.84rem; font-weight: 600; padding: 6px 14px; background: #f1f5f9; color: #334155; border-radius: 9999px;">
              <?php echo htmlspecialchars($sk, ENT_QUOTES, 'UTF-8'); ?>
            </span>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <!-- RIGHT SIDEBAR (EMPLOYER & SUMMARY CARD) -->
    <div>
      <!-- EMPLOYER COMPANY CARD -->
      <section class="detail-section-card" style="border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; background: #ffffff; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 6px; line-height: 1.3;">
          <?php echo htmlspecialchars((string)($job['employer'] ?? 'PT Talenta Digital Indonesia'), ENT_QUOTES, 'UTF-8'); ?>
        </h3>
        
        <div style="margin-bottom: 14px;">
          <span style="font-size: 0.76rem; background: #f1f5f9; color: #475569; font-weight: 600; padding: 4px 10px; border-radius: 6px; display: inline-block;">
            <?php echo htmlspecialchars($job['category'], ENT_QUOTES, 'UTF-8'); ?>
          </span>
        </div>

        <a href="#" onclick="event.preventDefault(); alert('Profil pemberi kerja dapat dilihat setelah penawaran disetujui.');" style="font-size: 0.84rem; color: #2563eb; font-weight: 700; text-decoration: none; display: inline-block; margin-bottom: 16px;">
          Lihat Profil Pemberi Kerja
        </a>

        <div style="padding-top: 12px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; color: #64748b;">
          <span>Proyek dari KarirHub</span>
          <span style="font-weight: 800; color: #2563eb; display: flex; align-items: center; gap: 4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            KarirHub
          </span>
        </div>
      </section>

      <!-- SIDEBAR SUMMARY CARD & CTA BUTTON -->
      <section class="sidebar-summary-card">
        <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 700;">Nilai Anggaran Proyek</div>
        <div class="budget-highlight"><?php echo htmlspecialchars($job['budget'], ENT_QUOTES, 'UTF-8'); ?></div>
        
        <!-- PROMINENT KUOTA BOX -->
        <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: var(--radius-md); padding: 14px 16px; margin: 14px 0 16px 0;">
          <div style="font-size: 0.78rem; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.05em;">Kuota Freelancer</div>
          <div style="font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
            <span>👤 <?php echo (int)($job['quota'] ?? 1); ?> Freelancer Dibutuhkan</span>
          </div>
          <div style="font-size: 0.78rem; color: #2563eb; margin-top: 4px;">
            Menerima hingga <strong><?php echo (int)($job['quota'] ?? 1); ?> orang</strong> freelancer yang memenuhi kriteria
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 16px 0; padding: 14px 0; border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle); font-size: 0.84rem;">
          <div>
            <div style="color: var(--text-muted);">Durasi Pengerjaan</div>
            <strong style="color: var(--text-main);"><?php echo htmlspecialchars($job['duration'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div>
            <div style="color: var(--text-muted);">Kuota Posisi</div>
            <strong style="color: var(--primary-blue); font-weight: 800;"><?php echo (int)($job['quota'] ?? 1); ?> Freelancer</strong>
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
