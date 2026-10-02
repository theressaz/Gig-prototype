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

    <div id="verifyModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:1200;align-items:center;justify-content:center;padding:18px;">
      <div style="background:#fff;border-radius:14px;max-width:620px;width:100%;padding:20px 20px 16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <h3 style="font-size:1rem;font-weight:800;color:#0f172a;">Konfirmasi Pengajuan Edit Profil</h3>
          <button type="button" onclick="closeVerifyModal()" style="border:none;background:transparent;font-size:1.2rem;cursor:pointer;">&times;</button>
        </div>
        <p style="font-size:0.84rem;color:#64748b;margin-bottom:12px;">Pilih bidang utama yang Anda ubah, alasan perubahan, lalu jelaskan alasannya. Admin akan memverifikasi sebelum perubahan ditayangkan.</p>

        <div style="margin-bottom:12px;">
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Ringkasan perubahan yang Anda lakukan <span style="color:#ef4444;">*</span></label>
          <textarea name="change_summary" id="change_summary" rows="3" placeholder="Contoh: Menambahkan 2 pengalaman kerja terbaru dan memperbarui 1 portofolio proyek." style="width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;"></textarea>
        </div>

        <div style="margin-bottom:12px;">
          <div style="font-size:0.82rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Bidang utama yang diubah <span style="color:#ef4444;">*</span></div>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="edited_field" value="identity_contact"> Informasi utama & kontak</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="edited_field" value="skills_bio"> Keahlian & bio</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="edited_field" value="experience"> Pengalaman kerja</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="edited_field" value="portfolio"> Portofolio</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="edited_field" value="multiple_fields"> Lebih dari satu bidang</label>
        </div>

        <div style="margin-bottom:12px;">
          <div style="font-size:0.82rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Alasan edit profil <span style="color:#ef4444;">*</span></div>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="reason_code" value="latest_work_update"> Menambahkan hasil kerja terbaru</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="reason_code" value="data_correction"> Koreksi data yang kurang tepat</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="reason_code" value="rebranding"> Rebranding / reposisi profil</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="reason_code" value="admin_request"> Menindaklanjuti catatan admin/mitra</label>
          <label style="display:block;margin-bottom:4px;"><input type="radio" name="reason_code" value="other"> Alasan lainnya</label>
        </div>

        <div style="margin-bottom:14px;">
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Penjelasan alasan edit <span style="color:#ef4444;">*</span></label>
          <textarea name="reason_detail" rows="3" placeholder="Jelaskan kenapa profil perlu diubah." style="width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;"></textarea>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" onclick="closeVerifyModal()" style="border:1px solid #cbd5e1;background:#fff;color:#334155;padding:9px 14px;border-radius:8px;font-weight:700;cursor:pointer;">Batal</button>
          <button type="submit" style="border:none;background:#2563eb;color:#fff;padding:9px 16px;border-radius:8px;font-weight:700;cursor:pointer;">Kirim Pengajuan</button>
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
