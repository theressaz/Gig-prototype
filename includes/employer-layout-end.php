<?php
declare(strict_types=1);
?>
      </main>

      <div class="modal-backdrop" id="postProjectModal" onclick="handleBackdropClick(event)">
        <div class="modal-window" style="max-width:680px;">
          <div class="modal-header">
            <h3>Pasang Lowongan Proyek Gig Baru</h3>
            <button class="modal-close-btn" type="button" onclick="closePostProjectModal()">&times;</button>
          </div>
          <form id="newProjectForm" onsubmit="handleCreateProject(event)">
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;padding:20px;">

              <div style="font-size:0.8rem;color:#1e40af;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;margin-bottom:16px;">
                &#9432; Lowongan baru akan diajukan ke Admin KarirHub untuk diverifikasi sebelum tayang.
              </div>

              <div class="form-grid-2">
                <div class="form-row">
                  <label for="proj_title">Judul Proyek *</label>
                  <input type="text" id="proj_title" required placeholder="Contoh: Pembuatan Landing Page Interaktif" />
                </div>
                <div class="form-row">
                  <label for="proj_category">Kategori / Bidang Proyek *</label>
                  <select id="proj_category" required>
                    <option value="">&#8212; Pilih Kategori &#8212;</option>
                    <option value="IT &amp; Pemrograman">IT &amp; Pemrograman Web</option>
                    <option value="Desain &amp; Kreatif">Desain Grafis &amp; UI/UX</option>
                    <option value="Pemasaran &amp; Konten">Pemasaran Digital &amp; Konten</option>
                    <option value="Penulisan &amp; Terjemahan">Penulisan &amp; Terjemahan</option>
                    <option value="Akuntansi &amp; Keuangan">Akuntansi &amp; Keuangan</option>
                    <option value="Manajemen Proyek">Manajemen Proyek</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                </div>
              </div>

              <div class="form-grid-2">
                <div class="form-row">
                  <label for="proj_kbji">Posisi (Kode KBJI) *</label>
                  <input type="text" id="proj_kbji" required placeholder="Contoh: 2512 &#8212; Pengembang Perangkat Lunak" />
                  <div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px;">KBJI = Klasifikasi Baku Jabatan Indonesia. <a href="#" onclick="event.preventDefault();showToast('Kode KBJI mengacu pada Klasifikasi Baku Jabatan Indonesia. Contoh: 2512 untuk Software Developer.');" style="color:var(--primary-blue);">Pelajari</a></div>
                </div>
                <div class="form-row">
                  <label for="proj_duration">Estimasi Durasi Proyek *</label>
                  <select id="proj_duration" required>
                    <option value="1 Minggu">1 Minggu</option>
                    <option value="2 Minggu" selected>2 Minggu</option>
                    <option value="1 Bulan">1 Bulan</option>
                    <option value="2 Bulan">2 Bulan</option>
                    <option value="3 Bulan">3 Bulan</option>
                    <option value="6 Bulan">6 Bulan</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <label for="proj_desc">Deskripsi Proyek *</label>
                <textarea id="proj_desc" rows="3" required placeholder="Jelaskan konteks, latar belakang, dan ruang lingkup umum proyek..."></textarea>
              </div>

              <div class="form-row">
                <label for="proj_target">Target / Deliverable Proyek *</label>
                <textarea id="proj_target" rows="3" required placeholder="Contoh: Prototype Figma 10 layar, Laporan Usability Test, Panduan Style Guide..."></textarea>
              </div>

              <div class="form-row">
                <label for="proj_kualifikasi">Kualifikasi yang Dibutuhkan</label>
                <textarea id="proj_kualifikasi" rows="3" placeholder="Contoh: Min. 2 tahun pengalaman UI/UX, familiar dengan design system..."></textarea>
              </div>

              <div class="form-grid-2">
                <div class="form-row">
                  <label for="proj_quota">Jumlah Kuota Gig Worker *</label>
                  <input type="number" id="proj_quota" required min="1" max="20" value="1" />
                </div>
                <div class="form-row">
                  <label for="proj_deadline">Batas Waktu Lamaran *</label>
                  <input type="date" id="proj_deadline" required value="2026-09-30" />
                </div>
              </div>

              <div class="form-row">
                <label>Lokasi Penempatan *</label>
                <div style="display:flex;gap:16px;margin-top:4px;flex-wrap:wrap;">
                  <label style="display:flex;align-items:center;gap:6px;font-weight:400;font-size:0.87rem;cursor:pointer;">
                    <input type="radio" name="proj_lokasi_type" value="remote" checked onchange="toggleLokasiInput(this)"> Remote (Seluruh Indonesia)
                  </label>
                  <label style="display:flex;align-items:center;gap:6px;font-weight:400;font-size:0.87rem;cursor:pointer;">
                    <input type="radio" name="proj_lokasi_type" value="hybrid" onchange="toggleLokasiInput(this)"> Hybrid
                  </label>
                  <label style="display:flex;align-items:center;gap:6px;font-weight:400;font-size:0.87rem;cursor:pointer;">
                    <input type="radio" name="proj_lokasi_type" value="luring" onchange="toggleLokasiInput(this)"> Luring / On-site
                  </label>
                </div>
                <div id="lokasi-detail-wrap" style="display:none;margin-top:8px;">
                  <input type="text" id="proj_lokasi_detail" placeholder="Contoh: Jakarta Selatan, Jl. Sudirman No. 12" />
                </div>
              </div>

              <div class="form-row">
                <label for="proj_budget">Gaji / Fee Proyek (Rp) *</label>
                <input type="text" id="proj_budget" required placeholder="Contoh: 7.500.000" />
                <label style="display:flex;align-items:center;gap:8px;margin-top:8px;font-weight:400;font-size:0.85rem;cursor:pointer;">
                  <input type="checkbox" id="proj_show_salary" checked />
                  <span>Tampilkan nominal gaji di postingan lowongan</span>
                </label>
                <div style="font-size:0.76rem;color:var(--text-muted);margin-top:3px;">Jika tidak ditampilkan, akan tertulis <em>"Gaji dapat dinegosiasikan"</em>.</div>
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn-secondary" onclick="closePostProjectModal()">Batal</button>
              <button type="submit" class="btn-primary">Ajukan untuk Verifikasi &#8594;</button>
            </div>
          </form>
        </div>
      </div>

      <div class="toast-container" id="toastContainer"></div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('click', function (e) {
    const menu = document.querySelector('.profile-menu');
    if (menu && !menu.contains(e.target)) {
      const drop = document.getElementById('profileDropdown');
      if (drop) drop.classList.remove('open');
    }
  });

  function openPostProjectModal() {
    document.getElementById('postProjectModal').classList.add('open');
  }
  function closePostProjectModal() {
    document.getElementById('postProjectModal').classList.remove('open');
  }
  function handleBackdropClick(e) {
    if (e.target.id === 'postProjectModal') closePostProjectModal();
  }
  function handleCreateProject(e) {
    e.preventDefault();
    const title = document.getElementById('proj_title').value;
    closePostProjectModal();
    document.getElementById('newProjectForm').reset();
    document.getElementById('lokasi-detail-wrap').style.display = 'none';
    showToast('Lowongan "' + title + '" berhasil diajukan. Menunggu verifikasi Admin KarirHub.');
    setTimeout(function () { window.location.href = 'employer-lowongan.php'; }, 700);
  }
  function toggleLokasiInput(radio) {
    const wrap = document.getElementById('lokasi-detail-wrap');
    if (!wrap) return;
    wrap.style.display = (radio.value === 'hybrid' || radio.value === 'luring') ? 'block' : 'none';
    const input = document.getElementById('proj_lokasi_detail');
    if (input) input.required = (radio.value !== 'remote');
  }
  function copyContact(name, phone, email) {
    const textToCopy = 'Nama: ' + name + '\nWhatsApp: ' + phone + '\nEmail: ' + email;
    if (navigator.clipboard) navigator.clipboard.writeText(textToCopy);
    showToast('Kontak ' + name + ' disalin. WA: ' + phone);
  }
  function hireApplicant(name) {
    showToast('Undangan kerja sama dikirim ke ' + name + '. Kontak terbuka setelah Gig Worker menyetujui.');
  }
  function showToast(message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast-card';
    toast.innerHTML = '<span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(function () { toast.remove(); }, 300);
    }, 3500);
  }
  function filterApplicants(category, btn) {
    document.querySelectorAll('.toolbar-filter .filter-btn-pill').forEach(function (b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    const cards = document.querySelectorAll('.applicant-card');
    let count = 0;
    cards.forEach(function (card) {
      const cat = card.getAttribute('data-category');
      const show = category === 'all' || cat === category;
      card.style.display = show ? 'grid' : 'none';
      if (show) count++;
    });
    const el = document.getElementById('applicants-visible-count');
    if (el) el.innerText = String(count);
  }
  function searchApplicants(query) {
    const q = (query || '').toLowerCase();
    const cards = document.querySelectorAll('.applicant-card');
    let count = 0;
    cards.forEach(function (card) {
      const match = card.innerText.toLowerCase().includes(q);
      card.style.display = match ? 'grid' : 'none';
      if (match) count++;
    });
    const el = document.getElementById('applicants-visible-count');
    if (el) el.innerText = String(count);
  }
  function filterVacancyRows(status, btn) {
    document.querySelectorAll('.toolbar-filter .filter-btn-pill').forEach(function (b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    document.querySelectorAll('#vacancy-table-body tr').forEach(function (row) {
      const rowStatus = row.getAttribute('data-status');
      row.style.display = (status === 'all' || rowStatus === status) ? '' : 'none';
    });
  }
  function searchVacancies(query) {
    const q = (query || '').toLowerCase();
    document.querySelectorAll('#vacancy-table-body tr').forEach(function (row) {
      row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
  }
</script>
</body>
</html>
