<?php
declare(strict_types=1);
?>
<!-- ADMIN VERIFICATION DECISION MODAL (IMAGE 2 MATCHING DESIGN) -->
<div id="decision-modal-backdrop" class="decision-modal-backdrop" onclick="if(event.target===this){closeDecisionModal();}">
  <div class="decision-modal" onclick="event.stopPropagation();">
    <div class="decision-modal-head">
      <div>
        <div class="decision-modal-title" id="modal-title-text">Ambil Keputusan Verifikasi</div>
        <div class="decision-modal-sub" id="modal-sub-text">Pilih keputusan untuk lowongan ini. Pastikan Anda telah memeriksa seluruh data lowongan dengan seksama.</div>
      </div>
      <button type="button" class="decision-close" onclick="closeDecisionModal()" aria-label="Tutup">&times;</button>
    </div>

    <form method="post" id="decision-form" action="" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
      <input type="hidden" name="action" id="modal-form-action" value="vacancy_decision" />
      <input type="hidden" name="tab" id="modal-form-tab" value="" />
      <input type="hidden" name="id" id="modal-form-id" value="" />
      <input type="hidden" name="vacancy_id" id="modal-form-vacancy-id" value="" />
      <input type="hidden" name="username" id="modal-form-username" value="" />
      <input type="hidden" name="edit_id" id="modal-form-edit-id" value="" />
      <input type="hidden" name="decision" id="modal-form-decision" value="revision" />

      <div class="decision-modal-body">
        <!-- SECTION 1: KETIDAKPATUHAN -->
        <div class="decision-section">
          <div class="decision-sec-header">
            <div class="decision-sec-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="6" x2="20" y2="6"></line>
                <line x1="4" y1="12" x2="20" y2="12"></line>
                <line x1="4" y1="18" x2="20" y2="18"></line>
                <circle cx="9" cy="6" r="2" fill="currentColor"></circle>
                <circle cx="15" cy="12" r="2" fill="currentColor"></circle>
                <circle cx="11" cy="18" r="2" fill="currentColor"></circle>
              </svg>
            </div>
            <div>
              <div class="decision-sec-title">Ketidakpatuhan</div>
              <div class="decision-sec-sub" id="compliance-sec-sub">Aktifkan salah satu item di bawah ini apabila terdapat ketidakpatuhan pada lowongan ini</div>
            </div>
          </div>

          <div class="compliance-list">
            <div class="compliance-card" onclick="toggleComplianceSwitch(this)">
              <span class="compliance-label">Data tidak lengkap</span>
              <div class="switch-track">
                <div class="switch-thumb"></div>
              </div>
              <input type="checkbox" name="compliance_reasons[]" value="Data tidak lengkap" style="display:none;" onchange="onComplianceToggleChange()" />
            </div>

            <div class="compliance-card" onclick="toggleComplianceSwitch(this)">
              <span class="compliance-label">Tidak sesuai substansi</span>
              <div class="switch-track">
                <div class="switch-thumb"></div>
              </div>
              <input type="checkbox" name="compliance_reasons[]" value="Tidak sesuai substansi" style="display:none;" onchange="onComplianceToggleChange()" />
            </div>

            <div class="compliance-card" onclick="toggleComplianceSwitch(this)">
              <span class="compliance-label">Tidak sesuai dengan aturan</span>
              <div class="switch-track">
                <div class="switch-thumb"></div>
              </div>
              <input type="checkbox" name="compliance_reasons[]" value="Tidak sesuai dengan aturan" style="display:none;" onchange="onComplianceToggleChange()" />
            </div>

            <div class="compliance-card" onclick="toggleComplianceSwitch(this)">
              <span class="compliance-label">Tidak sesuai dengan aturan anti diskriminasi</span>
              <div class="switch-track">
                <div class="switch-thumb"></div>
              </div>
              <input type="checkbox" name="compliance_reasons[]" value="Tidak sesuai dengan aturan anti diskriminasi" style="display:none;" onchange="onComplianceToggleChange()" />
            </div>
          </div>
        </div>

        <!-- SECTION 2: KEPUTUSAN -->
        <div class="decision-section">
          <div class="decision-sec-header">
            <div class="decision-sec-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m14 13 5 5m-4-2 3 3m-7-7 2.5-2.5a2.121 2.121 0 0 1 3 3L14 13Zm-5 5 1.5 1.5M3 21l3-3m0 0L3.5 15.5l5.5-5.5 2.5 2.5L6 18Z"></path>
              </svg>
            </div>
            <div>
              <div class="decision-sec-title">Keputusan</div>
              <div class="decision-sec-sub" id="decision-sec-sub">Tentukan keputusan sebelum memverifikasi lowongan ini</div>
            </div>
          </div>

          <div class="decision-radio-list">
            <div class="decision-radio-card" data-opt="approve" onclick="selectDecisionRadio('approve')">
              <div class="custom-radio"></div>
              <div class="decision-opt-icon approve">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
              </div>
              <span class="decision-opt-label">Setujui</span>
              <input type="radio" name="decision_option" value="approve" style="display:none;" />
            </div>

            <div class="decision-radio-card" data-opt="reject" onclick="selectDecisionRadio('reject')">
              <div class="custom-radio"></div>
              <div class="decision-opt-icon reject">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </div>
              <span class="decision-opt-label">Tolak</span>
              <input type="radio" name="decision_option" value="reject" style="display:none;" />
            </div>
          </div>
        </div>

        <!-- NOTES AREA -->
        <div class="decision-note-wrap">
          <label class="decision-note-label">Catatan Tambahan (Opsional)</label>
          <textarea class="decision-note-textarea" name="admin_note" id="modal-note-input" placeholder="Masukkan catatan tambahan untuk pemohon..."></textarea>
        </div>
      </div>

      <div class="decision-modal-foot">
        <button type="button" class="btn-decision-cancel" onclick="closeDecisionModal()">Batalkan</button>
        <button type="submit" id="decision-submit-btn" class="btn-decision-submit revision">Kirim Revisi</button>
      </div>
    </form>
  </div>
</div>

<script>
let currentEntityName = 'lowongan';
let currentEntityType = 'vacancy';

function getComplianceReasonSet(entityType) {
  if (entityType === 'worker') {
    return [
      'Data profil Gig Worker tidak lengkap',
      'Portofolio/berkas pendukung tidak valid',
      'Pengalaman atau keahlian tidak sesuai klaim',
      'Informasi kontak/identitas tidak valid'
    ];
  }
  if (entityType === 'employer') {
    return [
      'Data perusahaan tidak lengkap',
      'Dokumen/legalitas perusahaan tidak valid',
      'Informasi PIC tidak sesuai',
      'Kontak perusahaan tidak dapat diverifikasi'
    ];
  }
  return [
    'Data tidak lengkap',
    'Tidak sesuai substansi',
    'Tidak sesuai dengan aturan',
    'Tidak sesuai dengan aturan anti diskriminasi'
  ];
}

function applyComplianceReasons(entityType) {
  const reasons = getComplianceReasonSet(entityType);
  const cards = document.querySelectorAll('.compliance-card');
  cards.forEach((card, idx) => {
    const reason = reasons[idx] || reasons[0] || 'Data tidak lengkap';
    const label = card.querySelector('.compliance-label');
    const input = card.querySelector('input[type="checkbox"]');
    if (label) label.innerText = reason;
    if (input) input.value = reason;
  });
}

function openAdminDecisionModal(config = {}) {
  const modalBackdrop = document.getElementById('decision-modal-backdrop');
  if (!modalBackdrop) return;

  const entityType = config.entityType || 'vacancy';
  const entityName = config.entityName || (entityType === 'worker' ? 'profil Gig Worker' : (entityType === 'employer' ? 'Pemberi Kerja' : 'lowongan'));
  const id = config.id || '';
  const action = config.action || (entityType === 'worker' ? 'worker_take_decision' : (entityType === 'employer' ? 'employer_decision' : 'vacancy_decision'));
  const tab = config.tab || '';

  currentEntityType = entityType;
  currentEntityName = entityName;

  document.getElementById('modal-form-action').value = action;
  document.getElementById('modal-form-tab').value = tab;
  document.getElementById('modal-form-id').value = id;
  document.getElementById('modal-form-vacancy-id').value = config.vacancyId || id;
  document.getElementById('modal-form-username').value = config.username || '';
  document.getElementById('modal-form-edit-id').value = config.editId || '';

  document.getElementById('modal-title-text').innerText = 'Ambil Keputusan Verifikasi';
  document.getElementById('modal-sub-text').innerText = `Pilih keputusan untuk ${entityName} ini. Pastikan Anda telah memeriksa seluruh data ${entityName} dengan seksama.`;
  document.getElementById('compliance-sec-sub').innerText = `Aktifkan salah satu item di bawah ini apabila terdapat ketidakpatuhan pada ${entityName} ini`;
  document.getElementById('decision-sec-sub').innerText = `Tentukan keputusan sebelum memverifikasi ${entityName} ini`;
  applyComplianceReasons(entityType);

  const checkBoxes = document.querySelectorAll('.compliance-card input[type="checkbox"]');
  checkBoxes.forEach(cb => {
    cb.checked = false;
    cb.closest('.compliance-card').classList.remove('is-active');
  });

  document.getElementById('modal-note-input').value = '';

  deselectAllDecisionRadios();
  updateDecisionState();

  modalBackdrop.style.display = 'flex';
  requestAnimationFrame(() => {
    modalBackdrop.classList.add('show');
  });
}

function closeDecisionModal() {
  const modalBackdrop = document.getElementById('decision-modal-backdrop');
  if (!modalBackdrop) return;
  modalBackdrop.classList.remove('show');
  setTimeout(() => {
    modalBackdrop.style.display = 'none';
  }, 250);
}

function toggleComplianceSwitch(cardEl) {
  const cb = cardEl.querySelector('input[type="checkbox"]');
  if (!cb) return;
  cb.checked = !cb.checked;
  if (cb.checked) {
    cardEl.classList.add('is-active');
  } else {
    cardEl.classList.remove('is-active');
  }
  onComplianceToggleChange();
}

function onComplianceToggleChange() {
  const activeCount = document.querySelectorAll('.compliance-card input[type="checkbox"]:checked').length;
  if (activeCount > 0) {
    deselectAllDecisionRadios();
    updateDecisionState('revision');
  } else {
    updateDecisionState();
  }
}

function selectDecisionRadio(optValue) {
  if (optValue === 'approve' || optValue === 'reject') {
    const checkBoxes = document.querySelectorAll('.compliance-card input[type="checkbox"]');
    checkBoxes.forEach(cb => {
      cb.checked = false;
      cb.closest('.compliance-card').classList.remove('is-active');
    });
  }

  const radioCards = document.querySelectorAll('.decision-radio-card');
  radioCards.forEach(card => {
    if (card.getAttribute('data-opt') === optValue) {
      card.classList.add('is-selected');
      const rad = card.querySelector('input[type="radio"]');
      if (rad) rad.checked = true;
    } else {
      card.classList.remove('is-selected');
      const rad = card.querySelector('input[type="radio"]');
      if (rad) rad.checked = false;
    }
  });

  updateDecisionState(optValue);
}

function deselectAllDecisionRadios() {
  const radioCards = document.querySelectorAll('.decision-radio-card');
  radioCards.forEach(card => {
    card.classList.remove('is-selected');
    const rad = card.querySelector('input[type="radio"]');
    if (rad) rad.checked = false;
  });
}

function updateDecisionState(forcedOpt = null) {
  const activeToggles = document.querySelectorAll('.compliance-card input[type="checkbox"]:checked');
  const selectedRadio = document.querySelector('.decision-radio-card.is-selected');
  const submitBtn = document.getElementById('decision-submit-btn');
  const decisionHidden = document.getElementById('modal-form-decision');

  let state = forcedOpt;
  if (!state) {
    if (activeToggles.length > 0) {
      state = 'revision';
    } else if (selectedRadio) {
      state = selectedRadio.getAttribute('data-opt');
    } else {
      state = 'revision';
    }
  }

  submitBtn.className = 'btn-decision-submit ' + state;
  decisionHidden.value = state;

  const entityTitle = currentEntityType === 'worker' ? 'Worker' : (currentEntityType === 'employer' ? 'Pemberi Kerja' : (currentEntityType === 'vacancy' ? 'Lowongan' : ''));

  if (state === 'revision' || activeToggles.length > 0) {
    submitBtn.innerText = 'Kirim Revisi';
    submitBtn.className = 'btn-decision-submit revision';
    decisionHidden.value = 'revision';
  } else if (state === 'approve') {
    submitBtn.innerText = entityTitle ? `Setujui ${entityTitle}` : 'Setujui';
    submitBtn.className = 'btn-decision-submit approve';
    decisionHidden.value = 'approve';
  } else if (state === 'reject') {
    submitBtn.innerText = entityTitle ? `Tolak ${entityTitle}` : 'Tolak';
    submitBtn.className = 'btn-decision-submit reject';
    decisionHidden.value = 'reject';
  }
}
</script>
