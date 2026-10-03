<?php
declare(strict_types=1);
?>
      </main>
    </div>
  </div>
</div>

<!-- DARK VERIFICATION DETAIL MODAL -->
<div class="dark-modal-backdrop" id="adminDarkDetailModal" onclick="if(event.target.id==='adminDarkDetailModal') closeAdminDarkModal();">
  <div class="dark-modal-window">
    <div class="dark-modal-header">
      <h3 id="darkModalTitle">Detail Verifikasi</h3>
      <button type="button" class="dark-modal-close" onclick="closeAdminDarkModal()">&times;</button>
    </div>
    <form method="post" action="dashboard-admin.php">
      <div class="dark-modal-body" id="darkModalBody">
        <input type="hidden" name="tab" id="modalTargetTab" value="employers" />
        <input type="hidden" name="id" id="modalTargetEmpId" value="" />
        <input type="hidden" name="username" id="modalTargetWorkerUsername" value="" />
        <input type="hidden" name="action" id="modalFormAction" value="employer_approve" />

        <div style="background:#181c26;border:1px solid #282f42;border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;">
          <div class="dark-avatar-badge" id="modalAvatarBadge" style="width:48px;height:48px;font-size:1.2rem;">ST</div>
          <div>
            <h4 id="modalEntityName" style="font-size:1.05rem;font-weight:800;color:#fff;margin:0 0 2px 0;">S-Tank Engineering Co., Ltd.</h4>
            <div id="modalEntityEmail" style="font-size:0.8rem;color:#808ea3;">stankengineering@gmail.com</div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;background:#181c26;border:1px solid #282f42;border-radius:12px;padding:14px;">
          <div>
            <span style="font-size:0.72rem;color:#808ea3;text-transform:uppercase;font-weight:700;display:block;">Telepon / WA Kontak</span>
            <span id="modalEntityPhone" style="font-weight:700;color:#f8fafc;">082552399300</span>
          </div>
          <div>
            <span style="font-size:0.72rem;color:#808ea3;text-transform:uppercase;font-weight:700;display:block;">Lokasi Domisili</span>
            <span id="modalEntityLocation" style="font-weight:700;color:#f8fafc;">Cibeber, Cibeber, Kota Cilegon, Banten</span>
          </div>
        </div>

        <div style="background:#181c26;border:1px solid #282f42;border-radius:12px;padding:14px;">
          <span style="font-size:0.72rem;color:#808ea3;text-transform:uppercase;font-weight:700;display:block;margin-bottom:6px;">Dokumen KYC & Validasi Identitas</span>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <span style="background:#172554;color:#60a5fa;border:1px solid #1e40af;padding:4px 10px;border-radius:6px;font-size:0.76rem;font-weight:700;">✓ KTP / NIB Terlampir</span>
            <span style="background:#172554;color:#60a5fa;border:1px solid #1e40af;padding:4px 10px;border-radius:6px;font-size:0.76rem;font-weight:700;">✓ Surat Kuasa PIC Sah</span>
            <span style="background:#172554;color:#60a5fa;border:1px solid #1e40af;padding:4px 10px;border-radius:6px;font-size:0.76rem;font-weight:700;">✓ Email SiapKerja Verified</span>
          </div>
        </div>

        <div>
          <label style="font-size:0.8rem;font-weight:700;color:#cbd5e1;display:block;margin-bottom:6px;">Catatan Verifikasi Admin</label>
          <textarea name="admin_note" id="modalAdminNote" rows="3" placeholder="Masukkan alasan revisi, penolakan, atau catatan verifikasi..." style="width:100%;background:#141720;border:1px solid #282f42;border-radius:10px;padding:10px;color:#fff;font:inherit;font-size:0.84rem;box-sizing:border-box;"></textarea>
        </div>
      </div>
      <div class="dark-modal-footer">
        <button type="button" onclick="closeAdminDarkModal()" style="background:#1c2230;border:1px solid #2c354a;color:#808ea3;padding:8px 16px;border-radius:8px;font-weight:700;cursor:pointer;">Tutup</button>
        <button type="submit" onclick="document.getElementById('modalFormAction').value=modalType==='emp'?'employer_reject':'worker_reject';" style="background:#7f1d1d;border:1px solid #991b1b;color:#fca5a5;padding:8px 16px;border-radius:8px;font-weight:700;cursor:pointer;">✕ Tolak</button>
        <button type="submit" onclick="document.getElementById('modalFormAction').value=modalType==='emp'?'employer_approve':'worker_approve';" style="background:#065f46;border:1px solid #047857;color:#6ee7b7;padding:8px 16px;border-radius:8px;font-weight:700;cursor:pointer;">✓ Setujui Verifikasi</button>
      </div>
    </form>
  </div>
</div>

<script>
  let modalType = 'emp';
  function openAdminDarkModal(type, idOrUsername, name, email, phone, location, note) {
    modalType = type;
    document.getElementById('adminDarkDetailModal').classList.add('open');
    document.getElementById('darkModalTitle').textContent = (type === 'emp' ? 'Verifikasi Pemberi Kerja · ' : 'Verifikasi Gig Worker · ') + name;
    document.getElementById('modalEntityName').textContent = name;
    document.getElementById('modalEntityEmail').textContent = email;
    document.getElementById('modalEntityPhone').textContent = phone;
    document.getElementById('modalEntityLocation').textContent = location;
    document.getElementById('modalAdminNote').value = note || '';
    document.getElementById('modalAvatarBadge').textContent = (name || 'AB').substring(0, 2).toUpperCase();

    if (type === 'emp') {
      document.getElementById('modalTargetTab').value = 'employers';
      document.getElementById('modalTargetEmpId').value = idOrUsername;
      document.getElementById('modalFormAction').value = 'employer_approve';
    } else {
      document.getElementById('modalTargetTab').value = 'workers';
      document.getElementById('modalTargetWorkerUsername').value = idOrUsername;
      document.getElementById('modalFormAction').value = 'worker_approve';
    }
  }

  function closeAdminDarkModal() {
    document.getElementById('adminDarkDetailModal').classList.remove('open');
  }

  function searchAdminDarkTable(q) {
    const term = (q || '').toLowerCase();
    const rows = document.querySelectorAll('#admin-dark-main-table tbody tr');
    rows.forEach(function (r) {
      const match = r.innerText.toLowerCase().includes(term);
      r.style.display = match ? '' : 'none';
    });
  }
</script>
</body>
</html>
