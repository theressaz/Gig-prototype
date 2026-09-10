<?php
declare(strict_types=1);
?>
      </main>

      <div class="modal-backdrop" id="postProjectModal" onclick="handleBackdropClick(event)">
        <div class="modal-window">
          <div class="modal-header">
            <h3>Pasang Lowongan Proyek Gig Baru</h3>
            <button class="modal-close-btn" type="button" onclick="closePostProjectModal()">&times;</button>
          </div>
          <form id="newProjectForm" onsubmit="handleCreateProject(event)">
            <div class="modal-body">
              <div class="form-row">
                <label for="proj_title">Judul Proyek Freelance *</label>
                <input type="text" id="proj_title" required placeholder="Contoh: Pembuatan Landing Page Interaktif" />
              </div>
              <div class="form-grid-2">
                <div class="form-row">
                  <label for="proj_category">Kategori Keahlian *</label>
                  <select id="proj_category" required>
                    <option value="IT & Pemrograman">IT &amp; Pemrograman Web</option>
                    <option value="Desain & Kreatif">Desain Grafis &amp; UI/UX</option>
                    <option value="Pemasaran & Konten">Pemasaran Digital &amp; Konten</option>
                  </select>
                </div>
                <div class="form-row">
                  <label for="proj_duration">Estimasi Durasi Proyek *</label>
                  <select id="proj_duration" required>
                    <option value="1 Minggu">1 Minggu</option>
                    <option value="2 Minggu" selected>2 Minggu</option>
                    <option value="1 Bulan">1 Bulan</option>
                  </select>
                </div>
              </div>
              <div class="form-grid-2">
                <div class="form-row">
                  <label for="proj_budget">Anggaran / Fee Proyek (Rp) *</label>
                  <input type="text" id="proj_budget" required placeholder="Contoh: Rp 7.500.000" />
                </div>
                <div class="form-row">
                  <label for="proj_deadline">Batas Waktu Lamaran *</label>
                  <input type="date" id="proj_deadline" required value="2026-09-25" />
                </div>
              </div>
              <div class="form-row">
                <label for="proj_skills">Keahlian yang Dibutuhkan</label>
                <input type="text" id="proj_skills" placeholder="Figma, React.js, API Integration" />
              </div>
              <div class="form-row">
                <label for="proj_desc">Deskripsi Pekerjaan *</label>
                <textarea id="proj_desc" rows="4" required placeholder="Jelaskan ruang lingkup proyek dan deliverable..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn-secondary" onclick="closePostProjectModal()">Batal</button>
              <button type="submit" class="btn-primary">Publikasikan Lowongan</button>
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
    showToast('Lowongan "' + title + '" berhasil dipublikasikan.');
    setTimeout(function () { window.location.href = 'employer-lowongan.php'; }, 700);
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
