<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';
require_once __DIR__ . '/includes/admin-store.php';

$role = $_SESSION['role'] ?? 'worker';
if ($role !== 'worker') {
    header("Location: dashboard-employer.php");
    exit;
}

$username = (string)($_SESSION['username'] ?? $_SESSION['siapkerja_name'] ?? 'Theressa Zaratrusha');
$worker = gig_find_worker($username) ?? gig_find_worker('tessa');
$workerKey = gig_worker_profile_edit_key((string)($worker['id'] ?? $username));

$successMessage = '';
$errorMessage = '';
$hasPendingEdit = gig_worker_has_pending_profile_edit($workerKey);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $title = trim((string)($_POST['title'] ?? ''));
    $location = trim((string)($_POST['location'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $wa = trim((string)($_POST['wa'] ?? ''));
    $skillsRaw = trim((string)($_POST['skills'] ?? ''));
    $proposal = trim((string)($_POST['proposal'] ?? ''));
    $videoUrl = trim((string)($_POST['video_url'] ?? ''));
    $changeSummary = trim((string)($_POST['change_summary'] ?? ''));
    $editedField = trim((string)($_POST['edited_field'] ?? ''));
    $reasonCode = trim((string)($_POST['reason_code'] ?? ''));
    $reasonDetail = trim((string)($_POST['reason_detail'] ?? ''));

    $experience = [];
    $expRole = $_POST['exp_role'] ?? [];
    $expProject = $_POST['exp_project'] ?? [];
    $expPeriod = $_POST['exp_period'] ?? [];
    $expSummary = $_POST['exp_summary'] ?? [];
    if (is_array($expRole)) {
        foreach ($expRole as $idx => $roleVal) {
            $r = trim((string)$roleVal);
            $p = trim((string)($expProject[$idx] ?? ''));
            $per = trim((string)($expPeriod[$idx] ?? ''));
            $sum = trim((string)($expSummary[$idx] ?? ''));
            if ($r === '' && $p === '' && $per === '' && $sum === '') {
                continue;
            }
            $experience[] = [
                'role' => $r,
                'project' => $p,
                'period' => $per,
                'summary' => $sum,
            ];
        }
    }

    $portfolio = [];
    $portTitle = $_POST['port_title'] ?? [];
    $portType = $_POST['port_type'] ?? [];
    $portClient = $_POST['port_client'] ?? [];
    $portYear = $_POST['port_year'] ?? [];
    $portDeliverable = $_POST['port_deliverable'] ?? [];
    if (is_array($portTitle)) {
        foreach ($portTitle as $idx => $titleVal) {
            $pt = trim((string)$titleVal);
            $ty = trim((string)($portType[$idx] ?? ''));
            $cl = trim((string)($portClient[$idx] ?? ''));
            $yr = trim((string)($portYear[$idx] ?? ''));
            $dl = trim((string)($portDeliverable[$idx] ?? ''));
            if ($pt === '' && $ty === '' && $cl === '' && $yr === '' && $dl === '') {
                continue;
            }
            $portfolio[] = [
                'id' => 'edit-port-' . $idx . '-' . substr(md5($pt . $yr), 0, 6),
                'title' => $pt,
                'type' => $ty !== '' ? $ty : 'Proyek Portfolio',
                'client' => $cl !== '' ? $cl : 'Klien Terverifikasi',
                'year' => $yr !== '' ? $yr : date('Y'),
                'deliverable' => $dl,
                'files' => [],
            ];
        }
    }

    if ($name === '' || $email === '') {
        $errorMessage = 'Nama Lengkap dan Email Kontak wajib diisi.';
    } elseif ($changeSummary === '') {
        $errorMessage = 'Ringkasan perubahan wajib diisi sebelum pengajuan verifikasi.';
    } elseif ($editedField === '' || $reasonCode === '' || $reasonDetail === '') {
        $errorMessage = 'Pilih bidang yang diubah, alasan perubahan, dan jelaskan alasannya pada pop-up verifikasi.';
    } elseif ($hasPendingEdit) {
        $errorMessage = 'Masih ada pengajuan edit profil yang menunggu verifikasi admin. Selesaikan dulu pengajuan sebelumnya.';
    } else {
        $skillsArr = array_values(array_filter(array_map('trim', explode(',', $skillsRaw))));
        $payload = [
            'name' => $name,
            'title' => $title,
            'location' => $location,
            'email' => $email,
            'wa' => $wa,
            'skills' => $skillsArr,
            'proposal' => $proposal,
            'video_url' => $videoUrl,
            'experience' => $experience,
            'portfolio' => $portfolio,
        ];

        $ok = gig_create_worker_profile_edit_request($workerKey, [
            'worker_email' => (string)($_SESSION['siapkerja_email'] ?? $email),
            'edited_field' => $editedField,
            'reason_code' => $reasonCode,
            'reason_detail' => $reasonDetail,
            'change_summary' => $changeSummary,
            'proposed_payload' => $payload,
        ]);

        if ($ok) {
            $successMessage = 'Pengajuan edit profil berhasil dikirim. Perubahan akan tampil setelah diverifikasi admin.';
            $hasPendingEdit = true;
            // Keep displayed profile based on currently-approved data.
            // Proposed edits are stored for admin review and applied only after approval.
            $worker = gig_find_worker($username) ?? gig_find_worker('tessa');
        } else {
            $errorMessage = 'Gagal mengirim pengajuan edit profil ke sistem verifikasi admin.';
        }
    }
}

$experienceList = is_array($worker['experience'] ?? null) ? $worker['experience'] : [];
if ($experienceList === []) {
    $experienceList = [['role' => '', 'project' => '', 'period' => '', 'summary' => '']];
}
$portfolioList = is_array($worker['portfolio'] ?? null) ? $worker['portfolio'] : [];
if ($portfolioList === []) {
    $portfolioList = [['title' => '', 'type' => '', 'client' => '', 'year' => '', 'deliverable' => '', 'files' => []]];
}

$pageTitle = 'Edit Profil Saya';
$pageKey = 'profil';
$breadcrumbCurrent = 'Edit Profil Saya';

require __DIR__ . '/includes/worker-layout-start.php';
?>

<style>
.gig-verify-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
  z-index: 1200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.gig-verify-modal-card {
  background: #ffffff;
  border-radius: 18px;
  max-width: 680px;
  width: 100%;
  max-height: 88vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
  overflow: hidden;
  animation: gigModalSlideIn .22s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes gigModalSlideIn {
  from { opacity: 0; transform: translateY(12px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.gig-verify-modal-header {
  padding: 16px 22px 14px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: #ffffff;
  flex-shrink: 0;
}
.gig-verify-modal-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 2px 0;
}
.gig-verify-modal-sub {
  font-size: 0.82rem;
  color: #64748b;
  margin: 0;
}
.gig-verify-modal-close {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  font-size: 1.2rem;
  line-height: 1;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all .16s ease;
  flex-shrink: 0;
}
.gig-verify-modal-close:hover {
  background: #f8fafc;
  color: #0f172a;
}
.gig-verify-modal-body {
  padding: 18px 22px;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 14px;
  background: #f8fafc;
}
.gig-modal-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.gig-modal-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: #1e293b;
}
.gig-modal-label .req {
  color: #ef4444;
}
.gig-modal-input {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-family: inherit;
  font-size: 0.86rem;
  background: #ffffff;
  color: #0f172a;
  resize: vertical;
  transition: border-color .16s ease, box-shadow .16s ease;
}
.gig-modal-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}
.gig-modal-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
@media (max-width: 640px) {
  .gig-modal-grid-2 {
    grid-template-columns: 1fr;
  }
}
.gig-radio-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.gig-radio-option {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 10px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  border-radius: 8px;
  font-size: 0.82rem;
  color: #334155;
  font-weight: 600;
  cursor: pointer;
  transition: all .16s ease;
}
.gig-radio-option:hover {
  border-color: #bfdbfe;
  background: #eff6ff;
}
.gig-radio-option input[type="radio"] {
  accent-color: #2563eb;
  flex-shrink: 0;
}
.gig-verify-modal-footer {
  padding: 14px 22px;
  border-top: 1px solid #e2e8f0;
  background: #ffffff;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  flex-shrink: 0;
}
.gig-verify-modal-footer .btn-cancel {
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  padding: 9px 16px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.86rem;
  cursor: pointer;
  transition: all .16s ease;
}
.gig-verify-modal-footer .btn-cancel:hover {
  background: #f1f5f9;
  color: #0f172a;
}
.gig-verify-modal-footer .btn-submit {
  border: none;
  background: #2563eb;
  color: #ffffff;
  padding: 9px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.86rem;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
  transition: all .16s ease;
}
.gig-verify-modal-footer .btn-submit:hover {
  background: #1d4ed8;
}
</style>

<div class="page-toolbar">
  <div>
    <h1 style="font-size:1.4rem;font-weight:800;color:#0f172a;margin-bottom:4px;">Edit Profil Gig Worker</h1>
    <p style="font-size:0.88rem;color:#64748b;">Perubahan profil akan diverifikasi admin sebelum ditayangkan.</p>
  </div>
  <a class="btn-action-sm" href="worker-profile.php">← Kembali ke Profil Saya</a>
</div>

<?php if ($successMessage !== ''): ?>
  <div style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:14px 18px;border-radius:10px;font-size:0.9rem;font-weight:700;margin-bottom:20px;">
    ✓ <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<?php if ($errorMessage !== ''): ?>
  <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:14px 18px;border-radius:10px;font-size:0.9rem;font-weight:700;margin-bottom:20px;">
    ✕ <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<?php if ($hasPendingEdit): ?>
  <div style="background:#eff6ff;color:#1e3a8a;border:1px solid #bfdbfe;padding:14px 18px;border-radius:10px;font-size:0.88rem;font-weight:600;margin-bottom:20px;">
    Pengajuan edit profil Anda sedang menunggu verifikasi admin. Anda tetap bisa mengisi form, namun pengajuan baru belum bisa dikirim sampai pengajuan sebelumnya diproses.
  </div>
<?php endif; ?>

<div class="filter-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,0.03);max-width:960px;">
  <form method="POST" action="worker-edit-profile.php" id="workerEditForm">
    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Informasi Utama Profil</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Nama Lengkap <span style="color:#ef4444;">*</span></label>
          <input type="text" name="name" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;" value="<?php echo htmlspecialchars($worker['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Gelar / Bidang Keahlian Utama <span style="color:#ef4444;">*</span></label>
          <input type="text" name="title" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;" value="<?php echo htmlspecialchars($worker['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Email Kontak Publik <span style="color:#ef4444;">*</span></label>
          <input type="email" name="email" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;" value="<?php echo htmlspecialchars($worker['contact']['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div>
          <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Nomor WhatsApp</label>
          <input type="text" name="wa" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;" value="<?php echo htmlspecialchars($worker['contact']['wa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
      </div>
      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Lokasi Domisili</label>
        <input type="text" name="location" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;" value="<?php echo htmlspecialchars($worker['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      </div>
    </div>

    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Keahlian & Ringkasan Bio</h2>
      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Daftar Keahlian / Skill Tags (Pisahkan dengan koma)</label>
        <input type="text" name="skills" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;" value="<?php echo htmlspecialchars(is_array($worker['skills'] ?? null) ? implode(', ', $worker['skills']) : (string)($worker['skills'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
      </div>
      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Ringkasan Profil / Bio Singkat</label>
        <textarea name="proposal" rows="4" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;font-family:inherit;"><?php echo htmlspecialchars($worker['proposal'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
      </div>
      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Link Video Profil / Portfolio</label>
        <input type="text" name="video_url" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;" value="<?php echo htmlspecialchars((string)($worker['video_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
      </div>
    </div>

    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Pengalaman</h2>
      <div id="experienceList" style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ($experienceList as $idx => $exp): ?>
          <div class="exp-item" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <input name="exp_role[]" placeholder="Peran (contoh: Lead UI/UX Designer)" value="<?php echo htmlspecialchars((string)($exp['role'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
              <input name="exp_project[]" placeholder="Nama proyek / perusahaan" value="<?php echo htmlspecialchars((string)($exp['project'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
            </div>
            <input name="exp_period[]" placeholder="Periode (contoh: 2025 — 2026)" value="<?php echo htmlspecialchars((string)($exp['period'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="margin-top:10px;width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
            <textarea name="exp_summary[]" placeholder="Ringkasan pengalaman" rows="2" style="margin-top:10px;width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;"><?php echo htmlspecialchars((string)($exp['summary'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <button type="button" onclick="removeCard(this)" style="margin-top:8px;border:none;background:#fef2f2;color:#b91c1c;padding:6px 10px;border-radius:8px;font-weight:700;cursor:pointer;">Hapus</button>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" onclick="addExperience()" style="margin-top:10px;border:1px dashed #2563eb;background:#eff6ff;color:#1d4ed8;padding:8px 12px;border-radius:8px;font-weight:700;cursor:pointer;">+ Tambah Pengalaman</button>
    </div>

    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Portofolio</h2>
      <div id="portfolioList" style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ($portfolioList as $item): ?>
          <div class="port-item" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <input name="port_title[]" placeholder="Judul portofolio" value="<?php echo htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
              <input name="port_type[]" placeholder="Tipe (UI/UX, API, dsb)" value="<?php echo htmlspecialchars((string)($item['type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;">
              <input name="port_client[]" placeholder="Klien / perusahaan" value="<?php echo htmlspecialchars((string)($item['client'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
              <input name="port_year[]" placeholder="Tahun" value="<?php echo htmlspecialchars((string)($item['year'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
            </div>
            <textarea name="port_deliverable[]" placeholder="Ringkasan deliverable" rows="2" style="margin-top:10px;width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;"><?php echo htmlspecialchars((string)($item['deliverable'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <button type="button" onclick="removeCard(this)" style="margin-top:8px;border:none;background:#fef2f2;color:#b91c1c;padding:6px 10px;border-radius:8px;font-weight:700;cursor:pointer;">Hapus</button>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" onclick="addPortfolio()" style="margin-top:10px;border:1px dashed #2563eb;background:#eff6ff;color:#1d4ed8;padding:8px 12px;border-radius:8px;font-weight:700;cursor:pointer;">+ Tambah Portofolio</button>
    </div>

    <div style="display:flex;gap:14px;align-items:center;padding-top:16px;border-top:1px solid #f1f5f9;">
      <button type="button" id="openVerifyModalBtn" style="background:#2563eb;color:#ffffff;border:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:0.95rem;cursor:pointer;" <?php echo $hasPendingEdit ? 'disabled' : ''; ?>>Kirim untuk Verifikasi Admin</button>
      <a href="worker-profile.php" style="color:#475569;font-weight:600;font-size:0.9rem;text-decoration:none;">Batal</a>
    </div>

    <div id="verifyModal" class="gig-verify-modal-backdrop" style="display:none;" onclick="if(event.target===this) closeVerifyModal();">
      <div class="gig-verify-modal-card">
        <div class="gig-verify-modal-header">
          <div>
            <h3 class="gig-verify-modal-title">Konfirmasi Pengajuan Edit Profil</h3>
            <p class="gig-verify-modal-sub">Admin akan memverifikasi perincian perubahan Anda sebelum ditayangkan.</p>
          </div>
          <button type="button" class="gig-verify-modal-close" onclick="closeVerifyModal()">&times;</button>
        </div>

        <div class="gig-verify-modal-body">
          <div class="gig-modal-field">
            <label class="gig-modal-label">Ringkasan perubahan yang Anda lakukan <span class="req">*</span></label>
            <textarea name="change_summary" id="change_summary" rows="2" placeholder="Contoh: Menambahkan 2 pengalaman kerja terbaru dan memperbarui 1 portofolio proyek." class="gig-modal-input"></textarea>
          </div>

          <div class="gig-modal-grid-2">
            <div class="gig-modal-field">
              <label class="gig-modal-label">Bidang utama yang diubah <span class="req">*</span></label>
              <div class="gig-radio-group">
                <label class="gig-radio-option"><input type="radio" name="edited_field" value="identity_contact"> <span>Informasi utama & kontak</span></label>
                <label class="gig-radio-option"><input type="radio" name="edited_field" value="skills_bio"> <span>Keahlian & bio</span></label>
                <label class="gig-radio-option"><input type="radio" name="edited_field" value="experience"> <span>Pengalaman kerja</span></label>
                <label class="gig-radio-option"><input type="radio" name="edited_field" value="portfolio"> <span>Portofolio</span></label>
                <label class="gig-radio-option"><input type="radio" name="edited_field" value="multiple_fields"> <span>Lebih dari satu bidang</span></label>
              </div>
            </div>

            <div class="gig-modal-field">
              <label class="gig-modal-label">Alasan edit profil <span class="req">*</span></label>
              <div class="gig-radio-group">
                <label class="gig-radio-option"><input type="radio" name="reason_code" value="latest_work_update"> <span>Menambahkan hasil kerja terbaru</span></label>
                <label class="gig-radio-option"><input type="radio" name="reason_code" value="data_correction"> <span>Koreksi data kurang tepat</span></label>
                <label class="gig-radio-option"><input type="radio" name="reason_code" value="rebranding"> <span>Rebranding / reposisi profil</span></label>
                <label class="gig-radio-option"><input type="radio" name="reason_code" value="admin_request"> <span>Catatan admin/mitra</span></label>
                <label class="gig-radio-option"><input type="radio" name="reason_code" value="other"> <span>Alasan lainnya</span></label>
              </div>
            </div>
          </div>

          <div class="gig-modal-field">
            <label class="gig-modal-label">Penjelasan alasan edit <span class="req">*</span></label>
            <textarea name="reason_detail" rows="2" placeholder="Jelaskan secara singkat alasan kenapa profil perlu diubah." class="gig-modal-input"></textarea>
          </div>
        </div>

        <div class="gig-verify-modal-footer">
          <button type="button" class="btn-cancel" onclick="closeVerifyModal()">Batal</button>
          <button type="submit" class="btn-submit">Kirim Pengajuan</button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function removeCard(btn) {
  const parent = btn.closest('.exp-item, .port-item');
  if (parent) parent.remove();
}

function addExperience() {
  const wrapper = document.getElementById('experienceList');
  const el = document.createElement('div');
  el.className = 'exp-item';
  el.style.cssText = 'border:1px solid #e2e8f0;border-radius:10px;padding:12px;';
  el.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <input name="exp_role[]" placeholder="Peran (contoh: Lead UI/UX Designer)" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
      <input name="exp_project[]" placeholder="Nama proyek / perusahaan" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
    </div>
    <input name="exp_period[]" placeholder="Periode (contoh: 2025 — 2026)" style="margin-top:10px;width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
    <textarea name="exp_summary[]" rows="2" placeholder="Ringkasan pengalaman" style="margin-top:10px;width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;"></textarea>
    <button type="button" onclick="removeCard(this)" style="margin-top:8px;border:none;background:#fef2f2;color:#b91c1c;padding:6px 10px;border-radius:8px;font-weight:700;cursor:pointer;">Hapus</button>
  `;
  wrapper.appendChild(el);
}

function addPortfolio() {
  const wrapper = document.getElementById('portfolioList');
  const el = document.createElement('div');
  el.className = 'port-item';
  el.style.cssText = 'border:1px solid #e2e8f0;border-radius:10px;padding:12px;';
  el.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <input name="port_title[]" placeholder="Judul portofolio" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
      <input name="port_type[]" placeholder="Tipe (UI/UX, API, dsb)" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;">
      <input name="port_client[]" placeholder="Klien / perusahaan" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
      <input name="port_year[]" placeholder="Tahun" style="padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;">
    </div>
    <textarea name="port_deliverable[]" rows="2" placeholder="Ringkasan deliverable" style="margin-top:10px;width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;"></textarea>
    <button type="button" onclick="removeCard(this)" style="margin-top:8px;border:none;background:#fef2f2;color:#b91c1c;padding:6px 10px;border-radius:8px;font-weight:700;cursor:pointer;">Hapus</button>
  `;
  wrapper.appendChild(el);
}

function openVerifyModal() {
  const form = document.getElementById('workerEditForm');
  if (!form.reportValidity()) return;
  document.getElementById('verifyModal').style.display = 'flex';
}

function closeVerifyModal() {
  document.getElementById('verifyModal').style.display = 'none';
}

document.getElementById('openVerifyModalBtn').addEventListener('click', openVerifyModal);
document.getElementById('workerEditForm').addEventListener('submit', function (e) {
  const modal = document.getElementById('verifyModal');
  if (modal.style.display !== 'flex') {
    e.preventDefault();
    openVerifyModal();
    return;
  }
  const editedField = document.querySelector('input[name="edited_field"]:checked');
  const reasonCode = document.querySelector('input[name="reason_code"]:checked');
  const reasonDetail = document.querySelector('textarea[name="reason_detail"]');
  const summary = document.getElementById('change_summary');
  if (!summary.value.trim() || !editedField || !reasonCode || !reasonDetail.value.trim()) {
    e.preventDefault();
    alert('Lengkapi ringkasan perubahan, bidang yang diubah, alasan edit, dan penjelasan alasan sebelum mengirim.');
  }
});
</script>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
