<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/worker-auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$siapkerja = gig_get_siapkerja_profile($username);
$isRegistered = gig_is_worker_registered($username);
$isEditMode = !empty($_GET['edit']) || $isRegistered;

$existingReg = gig_get_worker_registration($username);
$workerProfile = gig_find_worker($username);

$currentBidang = $_POST['bidang_keahlian'] ?? ($existingReg['bidang_keahlian'] ?? ($workerProfile['title'] ?? ''));
$currentSkills = $_POST['skills'] ?? (is_array($existingReg['skills'] ?? null) ? implode(', ', $existingReg['skills']) : ($existingReg['skills'] ?? (is_array($workerProfile['skills'] ?? null) ? implode(', ', $workerProfile['skills']) : '')));
$currentContactChoice = $_POST['contact_choice'] ?? ($existingReg['contact_choice'] ?? 'siapkerja');
$currentContactEmail = $_POST['contact_email_new'] ?? ($existingReg['contact_email'] ?? '');
$currentContactWa = $_POST['contact_wa_new'] ?? ($existingReg['contact_wa'] ?? '');
$currentVideoUrl = $_POST['video_url'] ?? ($existingReg['video_url'] ?? ($workerProfile['video_url'] ?? ''));

$currentPortfolio = !empty($existingReg['portfolio']) && is_array($existingReg['portfolio']) ? $existingReg['portfolio'] : ($workerProfile['portfolio'] ?? []);
$currentProjects = !empty($existingReg['previous_projects']) && is_array($existingReg['previous_projects']) ? $existingReg['previous_projects'] : ($workerProfile['experience'] ?? []);
if (is_array($currentProjects)) {
    gig_sort_experience_timeline($currentProjects);
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "register_gig_worker") {
    $bidangKeahlian = trim((string)($_POST["bidang_keahlian"] ?? ""));
    $skillsRaw = trim((string)($_POST["skills"] ?? ""));
    $contactChoice = trim((string)($_POST["contact_choice"] ?? "siapkerja"));
    $contactEmail = $contactChoice === 'new' ? trim((string)($_POST["contact_email_new"] ?? "")) : $siapkerja['email'];
    $contactWa = $contactChoice === 'new' ? trim((string)($_POST["contact_wa_new"] ?? "")) : $siapkerja['wa'];
    $videoUrl = trim((string)($_POST["video_url"] ?? ""));

    // Process previous projects
    $projects = [];
    if (!empty($_POST["project_title"]) && is_array($_POST["project_title"])) {
        foreach ($_POST["project_title"] as $idx => $title) {
            $t = trim((string)$title);
            if ($t !== "") {
                $projects[] = [
                    'role'    => trim((string)($_POST["project_role"][$idx] ?? $t)),
                    'project' => $t,
                    'period'  => trim((string)($_POST["project_period"][$idx] ?? date('Y'))),
                    'summary' => trim((string)($_POST["project_summary"][$idx] ?? "")),
                ];
            }
        }
    }
    // Include SIAPKerja default experience if selected
    if (!empty($_POST["include_siapkerja_exp"])) {
        foreach ($siapkerja['pengalaman_siapkerja'] as $skExp) {
            $projects[] = [
                'role'    => $skExp['role'],
                'project' => $skExp['institution'],
                'period'  => $skExp['period'],
                'summary' => $skExp['summary'],
            ];
        }
    }
    gig_sort_experience_timeline($projects);

    // Process portfolios with multiple attached files
    $portfolios = [];
    if (!empty($_POST["portfolio_title"]) && is_array($_POST["portfolio_title"])) {
        foreach ($_POST["portfolio_title"] as $idx => $pTitle) {
            $pt = trim((string)$pTitle);
            if ($pt !== "") {
                $itemFiles = [];
                if (!empty($_POST["portfolio_file_name"][$idx]) && is_array($_POST["portfolio_file_name"][$idx])) {
                    foreach ($_POST["portfolio_file_name"][$idx] as $fIdx => $fName) {
                        $fn = trim((string)$fName);
                        $fu = trim((string)($_POST["portfolio_file_url"][$idx][$fIdx] ?? '#'));
                        $ft = trim((string)($_POST["portfolio_file_type"][$idx][$fIdx] ?? 'Dokumen PDF'));
                        if ($fn !== "" || ($fu !== "" && $fu !== "#")) {
                            $itemFiles[] = [
                                'name' => $fn !== "" ? $fn : 'Berkas_' . ($fIdx + 1),
                                'type' => $ft !== "" ? $ft : 'Dokumen PDF',
                                'size' => 'Akses Web / File',
                                'url'  => $fu !== "" ? $fu : '#'
                            ];
                        }
                    }
                }

                // Fallback if legacy portfolio_url field was filled
                if (empty($itemFiles) && !empty($_POST["portfolio_url"][$idx])) {
                    $fu = trim((string)$_POST["portfolio_url"][$idx]);
                    if ($fu !== "") {
                        $itemFiles[] = [
                            'name' => 'Berkas_Deliverable_Utama',
                            'type' => trim((string)($_POST["portfolio_type"][$idx] ?? 'Dokumen PDF')),
                            'size' => 'Akses Web',
                            'url'  => $fu
                        ];
                    }
                }

                $portfolios[] = [
                    'id'          => 'port-' . uniqid(),
                    'title'       => $pt,
                    'type'        => trim((string)($_POST["portfolio_type"][$idx] ?? "Proyek Portfolio")),
                    'deliverable' => trim((string)($_POST["portfolio_desc"][$idx] ?? "")),
                    'url'         => !empty($itemFiles[0]['url']) ? $itemFiles[0]['url'] : '#',
                    'client'      => 'Klien Terverifikasi',
                    'year'        => date('Y'),
                    'files'       => $itemFiles
                ];
            }
        }
    }

    if ($bidangKeahlian === "") {
        $errorMessage = "Harap pilih Bidang Keahlian Anda.";
    } elseif ($skillsRaw === "") {
        $errorMessage = "Harap cantumkan minimal 1 Skill / Keahlian spesifik.";
    } else {
        $skillsList = array_map('trim', explode(',', $skillsRaw));
        
        $registrationData = [
            'username'          => $username,
            'bidang_keahlian'   => $bidangKeahlian,
            'skills'            => $skillsList,
            'contact_choice'    => $contactChoice,
            'contact_email'     => $contactEmail,
            'contact_wa'        => $contactWa,
            'previous_projects' => $projects,
            'portfolio'         => $portfolios,
            'video_url'         => $videoUrl,
            'registered_at'     => date('Y-m-d H:i:s')
        ];

        gig_save_worker_registration($username, $registrationData);
        $profileId = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode(' ', $username)[0] ?? $username));
        header("Location: worker-profile.php?id=" . urlencode($profileId) . "&updated=1");
        exit;
    }
}

$pageTitle = $isEditMode ? 'Edit Profil Gig Worker' : 'Pendaftaran Gig Worker';
$pageKey = 'profil';
$breadcrumbCurrent = $isEditMode ? 'Edit Profil' : 'Pendaftaran';
require __DIR__ . '/includes/worker-layout-start.php';
$backHref = $isEditMode
    ? 'worker-profile.php?id=' . urlencode(strtolower(preg_replace('/[^a-z0-9]+/i', '', explode(' ', $username)[0] ?? $username)))
    : 'dashboard-worker.php';
?>
<style>
  .register-form-card { display:flex; flex-direction:column; gap:28px; }
  .siapkerja-card { position:relative; margin-bottom:20px; }
  .siapkerja-badge {
    position:absolute; top:20px; right:24px;
    display:inline-flex; align-items:center; gap:6px;
    background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;
    padding:4px 12px; border-radius:9999px; font-size:0.75rem; font-weight:700;
  }
  .siapkerja-header { display:flex; align-items:center; gap:16px; margin-bottom:18px; padding-right:180px; }
  .siapkerja-avatar {
    width:56px; height:56px; border-radius:50%;
    background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; font-weight:800; flex-shrink:0;
  }
  .siapkerja-info h3 { font-size:1.15rem; font-weight:800; color:var(--text-main); }
  .siapkerja-meta { display:flex; flex-wrap:wrap; gap:12px; font-size:0.84rem; color:var(--text-muted); margin-top:4px; }
  .siapkerja-exp-box { background:#f8fafc; border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:14px 16px; }
  .exp-title { font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:8px; }
  .exp-item { font-size:0.86rem; color:var(--text-main); padding:4px 0; display:flex; align-items:flex-start; gap:8px; }
  .section-title {
    font-size:1.08rem; font-weight:800; color:var(--kemnaker-navy);
    padding-bottom:10px; border-bottom:2px solid #f1f5f9;
    display:flex; align-items:center; gap:10px;
  }
  .section-icon {
    width:28px; height:28px; border-radius:6px; background:#eff6ff; color:var(--primary-blue);
    display:flex; align-items:center; justify-content:center; font-size:0.9rem;
  }
  .form-group { display:flex; flex-direction:column; gap:8px; }
  .form-label { font-size:0.88rem; font-weight:700; color:var(--text-main); }
  .form-hint { font-size:0.78rem; color:var(--text-muted); }
  .form-input, .form-select, .form-textarea {
    width:100%; padding:10px 14px; border:1px solid var(--border-light);
    border-radius:var(--radius-sm); font-size:0.9rem; color:var(--text-main); background:#fff;
  }
  .form-input:focus, .form-select:focus, .form-textarea:focus {
    outline:none; border-color:var(--primary-blue); box-shadow:0 0 0 3px rgba(22,87,193,0.12);
  }
  .contact-options { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:4px; }
  @media (max-width:640px) {
    .contact-options, .new-contact-fields { grid-template-columns:1fr !important; }
    .siapkerja-header { padding-right:0; }
    .siapkerja-badge { position:static; margin-bottom:12px; }
  }
  .contact-option-card {
    border:1.5px solid var(--border-light); border-radius:var(--radius-md); padding:16px;
    cursor:pointer; display:flex; align-items:flex-start; gap:12px; background:#f8fafc;
  }
  .contact-option-card:hover { border-color:#93c5fd; background:#fff; }
  .contact-option-card.active { border-color:var(--primary-blue); background:#eff6ff; }
  .contact-radio { margin-top:3px; accent-color:var(--primary-blue); }
  .new-contact-fields {
    display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:14px; padding:16px;
    background:#f8fafc; border:1px dashed #bfdbfe; border-radius:var(--radius-md);
  }
  .dynamic-item { background:#f8fafc; border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:18px; position:relative; margin-bottom:12px; }
  .btn-remove-item { background:#fee2e2; color:#dc2626; border:none; padding:4px 10px; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:700; cursor:pointer; }
  .btn-add-item {
    display:inline-flex; align-items:center; gap:6px; background:#eff6ff; color:var(--primary-blue);
    border:1px dashed #bfdbfe; padding:10px 18px; border-radius:var(--radius-sm); font-size:0.85rem; font-weight:700; cursor:pointer;
  }
  .btn-add-item:hover { background:#dbeafe; border-color:var(--primary-blue); }
  .btn-submit {
    background:linear-gradient(135deg, var(--hero-blue-mid) 0%, var(--hero-blue-end) 100%);
    color:#fff; border:none; padding:12px 24px; border-radius:var(--radius-pill);
    font-size:0.95rem; font-weight:800; cursor:pointer;
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
  }
  .alert { padding:14px 18px; border-radius:var(--radius-md); font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:10px; margin-bottom:16px; }
  .alert-success { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
  .alert-error { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; }
  .register-cancel { padding:12px 18px; text-decoration:none; color:var(--text-muted); font-weight:700; font-size:0.9rem; }
</style>

    <div class="page-toolbar">
      <h1><?php echo $isEditMode ? 'Edit Profil Gig Worker' : 'Pendaftaran Gig Worker'; ?></h1>
      <a class="btn-secondary" href="<?php echo htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8'); ?>">Kembali</a>
    </div>

    <section class="hero-banner" style="margin-bottom:20px;">
      <div class="hero-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?php echo $isEditMode ? 'Pembaruan Profil Gig Worker' : 'Pendaftaran Resmi Gig Worker'; ?>
      </div>
      <h2 class="hero-title"><?php echo $isEditMode ? 'Edit Informasi Profil Gig Worker' : 'Bergabung Sebagai Gig Worker Kemnaker'; ?></h2>
      <p class="hero-desc">
        <?php echo $isEditMode ? 'Perbarui bidang keahlian, skill spesifik, proyek portofolio, dan tautan video profil Anda agar calon Pemberi Kerja mendapatkan informasi terbaru.' : 'Gunakan akun SIAPKerja Anda untuk melengkapi profil profesional Gig Worker. Dapatkan akses ke berbagai penawaran proyek dari Pemberi Kerja terverifikasi dan perlindungan ekosistem tenaga kerja mandiri.'; ?>
      </p>
    </section>

    <?php if ($successMessage !== ""): ?>
      <div class="alert alert-success">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <div><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
      </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ""): ?>
      <div class="alert alert-error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    <?php endif; ?>

    <section class="white-card siapkerja-card">
      <div class="siapkerja-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        Terintegrasi SIAPKerja
      </div>
      
      <div class="siapkerja-header">
        <div class="siapkerja-avatar">
          <?php echo strtoupper(substr($siapkerja['nama'], 0, 2)); ?>
        </div>
        <div class="siapkerja-info">
          <h3><?php echo htmlspecialchars($siapkerja['nama'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <div class="siapkerja-meta">
            <span><strong>ID SIAPKerja:</strong> <?php echo htmlspecialchars($siapkerja['siapkerja_id'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span>&bull;</span>
            <span><strong>Status Akun:</strong> <?php echo htmlspecialchars($siapkerja['status_akun'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span>&bull;</span>
            <span><strong>Domisili:</strong> <?php echo htmlspecialchars($siapkerja['lokasi'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>
      </div>

      <div class="siapkerja-exp-box">
        <div class="exp-title">Informasi Otomatis Terhubung Dari Akun SIAPKerja</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
          <div style="font-size: 0.85rem;">
            <strong style="color: var(--text-muted);">Nama Lengkap:</strong><br />
            <span><?php echo htmlspecialchars($siapkerja['nama'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div style="font-size: 0.85rem;">
            <strong style="color: var(--text-muted);">Kontak Default SIAPKerja:</strong><br />
            <span>WA: <?php echo htmlspecialchars($siapkerja['wa'], ENT_QUOTES, 'UTF-8'); ?> &bull; Email: <?php echo htmlspecialchars($siapkerja['email'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>
        <div class="exp-title" style="margin-top: 12px;">Pengalaman Kerja / Proyek Tercatat di SIAPKerja:</div>
        <?php foreach ($siapkerja['pengalaman_siapkerja'] as $exp): ?>
          <div class="exp-item">
            <span style="color: var(--primary-blue); font-weight:700;">&bull;</span>
            <div>
              <strong><?php echo htmlspecialchars($exp['role'], ENT_QUOTES, 'UTF-8'); ?></strong> &mdash; 
              <span><?php echo htmlspecialchars($exp['institution'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($exp['period'], ENT_QUOTES, 'UTF-8'); ?>)</span>
              <div style="font-size:0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($exp['summary'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- FORM PENDAFTARAN GIG WORKER -->
    <form method="POST" action="" class="white-card register-form-card">
      <input type="hidden" name="action" value="register_gig_worker" />

      <!-- BAGIAN 1: PILIHAN KONTAK -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">1</span>
          Informasi Kontak Gig Worker
        </h2>
        <p class="form-hint" style="margin-top: 6px; margin-bottom: 12px;">
          Pilih apakah Anda ingin menggunakan informasi kontak resmi dari SIAPKerja atau mencantumkan kontak baru khusus layanan Gig Worker.
        </p>

        <div class="contact-options">
          <label class="contact-option-card <?php echo $currentContactChoice === 'siapkerja' ? 'active' : ''; ?>" id="opt-siapkerja" onclick="selectContactOption('siapkerja')">
            <input type="radio" name="contact_choice" value="siapkerja" class="contact-radio" <?php echo $currentContactChoice === 'siapkerja' ? 'checked' : ''; ?> />
            <div>
              <strong style="font-size:0.9rem; color:var(--text-main);">Gunakan Kontak Akun SIAPKerja</strong>
              <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">
                Email: <?php echo htmlspecialchars($siapkerja['email'], ENT_QUOTES, 'UTF-8'); ?><br />
                WhatsApp: <?php echo htmlspecialchars($siapkerja['wa'], ENT_QUOTES, 'UTF-8'); ?>
              </div>
            </div>
          </label>

          <label class="contact-option-card <?php echo $currentContactChoice === 'new' ? 'active' : ''; ?>" id="opt-new" onclick="selectContactOption('new')">
            <input type="radio" name="contact_choice" value="new" class="contact-radio" <?php echo $currentContactChoice === 'new' ? 'checked' : ''; ?> />
            <div>
              <strong style="font-size:0.9rem; color:var(--text-main);">Input Informasi Kontak Baru</strong>
              <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">
                Gunakan alamat email atau nomor WA alternatif khusus untuk proyek Gig Worker.
              </div>
            </div>
          </label>
        </div>

        <div class="new-contact-fields" id="newContactContainer" style="display: <?php echo $currentContactChoice === 'new' ? 'grid' : 'none'; ?>;">
          <div class="form-group">
            <label class="form-label" for="contact_email_new">Email Kontak Baru</label>
            <input type="email" id="contact_email_new" name="contact_email_new" class="form-input" value="<?php echo htmlspecialchars($currentContactEmail, ENT_QUOTES, 'UTF-8'); ?>" placeholder="contoh: tessa.gig@email.com" />
          </div>

          <div class="form-group">
            <label class="form-label" for="contact_wa_new">Nomor WhatsApp / HP Baru</label>
            <input type="text" id="contact_wa_new" name="contact_wa_new" class="form-input" value="<?php echo htmlspecialchars($currentContactWa, ENT_QUOTES, 'UTF-8'); ?>" placeholder="contoh: 0812-9988-7766" />
          </div>
        </div>
      </section>

      <!-- BAGIAN 2: BIDANG KEAHLIAN & SKILL -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">2</span>
          Bidang Keahlian &amp; Skill Spesifik
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 14px;">
          <div class="form-group">
            <label class="form-label" for="bidang_keahlian">Bidang Keahlian Utama <span style="color:#ef4444;">*</span></label>
            <?php 
              $bidangOpts = [
                "UI/UX Design & Product Interface",
                "Web Development (Frontend / Backend / Fullstack)",
                "Mobile Application Development",
                "Digital Marketing & Social Media Strategy",
                "Data Analytics & Data Entry",
                "Copywriting, Content Writing & Translation",
                "Graphic Design, Video Editing & Multimedia",
                "Administrative & Virtual Assistant"
              ];
            ?>
            <select id="bidang_keahlian" name="bidang_keahlian" class="form-select" required>
              <option value="">-- Pilih Bidang Keahlian --</option>
              <?php foreach ($bidangOpts as $bOpt): ?>
                <option value="<?php echo htmlspecialchars($bOpt, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $currentBidang === $bOpt ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($bOpt, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="skills">Skill / Keahlian Spesifik <span style="color:#ef4444;">*</span></label>
            <input type="text" id="skills" name="skills" class="form-input" value="<?php echo htmlspecialchars($currentSkills, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: Figma, Wireframing, React, Node.js, Copywriting" required />
            <span class="form-hint">Pisahkan skill dengan tanda koma ( , )</span>
          </div>
        </div>
      </section>

      <!-- BAGIAN 3: PORTOFOLIO -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">3</span>
          Portofolio Hasil Pekerjaan
        </h2>
        <p class="form-hint" style="margin-top: 6px; margin-bottom: 12px;">
          Tampilkan contoh hasil proyek terbaik Anda (link berkas, desain Figma, atau repositori code) agar calon Pemberi Kerja dapat menilai kualitas kerja Anda.
        </p>

        <div id="portfolioContainer">
          <?php if (!empty($currentPortfolio) && is_array($currentPortfolio)): ?>
            <?php foreach ($currentPortfolio as $pIdx => $pItem): ?>
              <div class="dynamic-item" id="port-item-<?php echo $pIdx; ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
                  <strong style="font-size: 0.95rem; color: var(--kemnaker-navy);">Portofolio #<?php echo $pIdx + 1; ?></strong>
                  <?php if ($pIdx > 0): ?>
                    <button type="button" class="btn-remove-item" onclick="document.getElementById('port-item-<?php echo $pIdx; ?>').remove()">Hapus Portofolio</button>
                  <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                  <div class="form-group">
                    <label class="form-label">Judul Portofolio <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="portfolio_title[]" class="form-input" value="<?php echo htmlspecialchars($pItem['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="contoh: Redesign Mobile App E-Commerce" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Tipe / Kategori Deliverable <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="portfolio_type[]" class="form-input" value="<?php echo htmlspecialchars($pItem['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="contoh: Figma UI Kit / Web Prototype" required />
                  </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                  <label class="form-label">Deskripsi Singkat Portofolio</label>
                  <input type="text" name="portfolio_desc[]" class="form-input" value="<?php echo htmlspecialchars($pItem['deliverable'] ?? ($pItem['desc'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ringkasan deliverable dan peran Anda" />
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 14px; margin-top: 10px;">
                  <div style="font-size: 0.85rem; font-weight: 700; color: var(--kemnaker-navy); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span>📂 Lampiran Berkas / Link File (Bisa Mengunggah Lebih Dari 1 File)</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">PDF, Figma, Code Repo, ZIP, Video, dll.</span>
                  </div>

                  <div id="file-list-<?php echo $pIdx; ?>">
                    <?php 
                      $files = !empty($pItem['files']) && is_array($pItem['files']) ? $pItem['files'] : [];
                      if (empty($files) && !empty($pItem['url'])) {
                          $files = [['name' => 'Berkas Deliverable Utama', 'type' => ($pItem['type'] ?? 'Dokumen PDF'), 'url' => $pItem['url']]];
                      }
                      if (empty($files)) {
                          $files = [['name' => '', 'type' => 'Dokumen PDF / Link', 'url' => '']];
                      }
                    ?>
                    <?php foreach ($files as $fIdx => $f): ?>
                      <div class="file-item-row" style="display: grid; grid-template-columns: 2fr 1.5fr 3fr 30px; gap: 8px; align-items: center; margin-bottom: 8px;">
                        <input type="text" name="portfolio_file_name[<?php echo $pIdx; ?>][]" class="form-input" style="font-size:0.82rem;" value="<?php echo htmlspecialchars($f['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nama Berkas (e.g. Wireframe UI PDF)" />
                        <input type="text" name="portfolio_file_type[<?php echo $pIdx; ?>][]" class="form-input" style="font-size:0.82rem;" value="<?php echo htmlspecialchars($f['type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tipe (PDF, Figma, Code)" />
                        <input type="url" name="portfolio_file_url[<?php echo $pIdx; ?>][]" class="form-input" style="font-size:0.82rem;" value="<?php echo htmlspecialchars($f['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://..." />
                        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ef4444; font-size:1.2rem; font-weight:bold; cursor:pointer;" title="Hapus file ini">&times;</button>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <button type="button" class="btn-add-item" style="font-size: 0.78rem; padding: 5px 12px; margin-top: 4px; background: #eff6ff; color: var(--primary-blue); border: 1px dashed var(--primary-blue);" onclick="addFileToPortfolio(<?php echo $pIdx; ?>)">
                    + Tambah Berkas / Link File Lainnya
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="dynamic-item" id="port-item-0">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
                <strong style="font-size: 0.95rem; color: var(--kemnaker-navy);">Portofolio #1</strong>
              </div>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                <div class="form-group">
                  <label class="form-label">Judul Portofolio <span style="color:#ef4444;">*</span></label>
                  <input type="text" name="portfolio_title[]" class="form-input" placeholder="contoh: Redesign Mobile App E-Commerce" required />
                </div>
                <div class="form-group">
                  <label class="form-label">Tipe / Kategori Deliverable <span style="color:#ef4444;">*</span></label>
                  <input type="text" name="portfolio_type[]" class="form-input" placeholder="contoh: Figma UI Kit / Web Prototype" required />
                </div>
              </div>
              <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label">Deskripsi Singkat Portofolio</label>
                <input type="text" name="portfolio_desc[]" class="form-input" placeholder="Ringkasan deliverable dan peran Anda" />
              </div>

              <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 14px; margin-top: 10px;">
                <div style="font-size: 0.85rem; font-weight: 700; color: var(--kemnaker-navy); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                  <span>📂 Lampiran Berkas / Link File (Bisa Mengunggah Lebih Dari 1 File)</span>
                  <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">PDF, Figma, Code Repo, ZIP, Video, dll.</span>
                </div>

                <div id="file-list-0">
                  <div class="file-item-row" style="display: grid; grid-template-columns: 2fr 1.5fr 3fr 30px; gap: 8px; align-items: center; margin-bottom: 8px;">
                    <input type="text" name="portfolio_file_name[0][]" class="form-input" style="font-size:0.82rem;" placeholder="Nama Berkas (e.g. Wireframe UI PDF)" />
                    <input type="text" name="portfolio_file_type[0][]" class="form-input" style="font-size:0.82rem;" placeholder="Tipe (PDF, Figma, Code)" />
                    <input type="url" name="portfolio_file_url[0][]" class="form-input" style="font-size:0.82rem;" placeholder="https://..." />
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ef4444; font-size:1.2rem; font-weight:bold; cursor:pointer;" title="Hapus file ini">&times;</button>
                  </div>
                </div>

                <button type="button" class="btn-add-item" style="font-size: 0.78rem; padding: 5px 12px; margin-top: 4px; background: #eff6ff; color: var(--primary-blue); border: 1px dashed var(--primary-blue);" onclick="addFileToPortfolio(0)">
                  + Tambah Berkas / Link File Lainnya
                </button>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <button type="button" class="btn-add-item" onclick="addPortfolioItem()">
          + Tambah Portofolio
        </button>
      </section>

      <!-- BAGIAN 4: LINK VIDEO PROFIL -->
      <section>
        <h2 class="section-title">
          <span class="section-icon">4</span>
          Link Video Profil Gig Worker
        </h2>
        <p class="form-hint" style="margin-top: 6px; margin-bottom: 12px;">
          Sampaikan perkenalan singkat diri dan keahlian Anda melalui video (misal: YouTube, Loom, atau Google Drive Video).
        </p>

        <div class="form-group">
          <label class="form-label" for="video_url">Tautan / URL Video Profil</label>
          <input type="url" id="video_url" name="video_url" class="form-input" value="<?php echo htmlspecialchars($currentVideoUrl, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=... atau https://www.loom.com/share/..." oninput="checkVideoPreview(this.value)" />
          <div id="videoPreviewStatus" style="font-size: 0.8rem; margin-top: 4px; display: none;"></div>
        </div>
      </section>

      <!-- SUBMIT -->
      <div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 10px;">
        <a href="dashboard-worker.php" class="register-cancel">Batal</a>
        <button type="submit" class="btn-submit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <?php echo $isEditMode ? 'Simpan Pembaruan Profil' : 'Daftar & Aktifkan Profil Gig Worker'; ?>
        </button>
      </div>
    </form>


  <script>
    function selectContactOption(choice) {
      const optSiapkerja = document.getElementById('opt-siapkerja');
      const optNew = document.getElementById('opt-new');
      const container = document.getElementById('newContactContainer');

      if (choice === 'new') {
        optSiapkerja.classList.remove('active');
        optNew.classList.add('active');
        container.style.display = 'grid';
      } else {
        optNew.classList.remove('active');
        optSiapkerja.classList.add('active');
        container.style.display = 'none';
      }
    }

    let projectCount = 0;
    function addProjectItem() {
      projectCount++;
      const container = document.getElementById('projectContainer');
      const div = document.createElement('div');
      div.className = 'dynamic-item';
      div.id = 'proj-item-' + projectCount;
      div.innerHTML = `
        <button type="button" class="btn-remove-item" onclick="document.getElementById('proj-item-${projectCount}').remove()">Hapus</button>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
          <div class="form-group">
            <label class="form-label">Nama Proyek / Perusahaan</label>
            <input type="text" name="project_title[]" class="form-input" placeholder="contoh: Portal E-Government" required />
          </div>
          <div class="form-group">
            <label class="form-label">Peran / Posisi Anda</label>
            <input type="text" name="project_role[]" class="form-input" placeholder="contoh: UI Designer / Web Dev" />
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
          <div class="form-group">
            <label class="form-label">Periode Waktu</label>
            <input type="text" name="project_period[]" class="form-input" placeholder="contoh: 2025 (6 Bulan)" />
          </div>
          <div class="form-group">
            <label class="form-label">Ringkasan Tugas & Hasil</label>
            <input type="text" name="project_summary[]" class="form-input" placeholder="Deskripsikan peran dan pencapaian Anda" />
          </div>
        </div>
      `;
      container.appendChild(div);
    }

    let portfolioCount = <?php echo !empty($currentPortfolio) && is_array($currentPortfolio) ? count($currentPortfolio) : 1; ?>;

    function addPortfolioItem() {
      const pIdx = portfolioCount;
      portfolioCount++;
      const container = document.getElementById('portfolioContainer');
      const div = document.createElement('div');
      div.className = 'dynamic-item';
      div.id = 'port-item-' + pIdx;
      div.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
          <strong style="font-size: 0.95rem; color: var(--kemnaker-navy);">Portofolio #${pIdx + 1}</strong>
          <button type="button" class="btn-remove-item" onclick="document.getElementById('port-item-${pIdx}').remove()">Hapus Portofolio</button>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
          <div class="form-group">
            <label class="form-label">Judul Portofolio <span style="color:#ef4444;">*</span></label>
            <input type="text" name="portfolio_title[]" class="form-input" placeholder="contoh: Web App Dashboard" required />
          </div>
          <div class="form-group">
            <label class="form-label">Tipe / Kategori Deliverable <span style="color:#ef4444;">*</span></label>
            <input type="text" name="portfolio_type[]" class="form-input" placeholder="contoh: React Code / Figma Spec" required />
          </div>
        </div>
        <div class="form-group" style="margin-bottom: 14px;">
          <label class="form-label">Deskripsi Singkat Portofolio</label>
          <input type="text" name="portfolio_desc[]" class="form-input" placeholder="Ringkasan deliverable dan fitur utama" />
        </div>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 14px; margin-top: 10px;">
          <div style="font-size: 0.85rem; font-weight: 700; color: var(--kemnaker-navy); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
            <span>📂 Lampiran Berkas / Link File (Bisa Mengunggah Lebih Dari 1 File)</span>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">PDF, Figma, Code Repo, ZIP, Video, dll.</span>
          </div>
          <div id="file-list-${pIdx}">
            <div class="file-item-row" style="display: grid; grid-template-columns: 2fr 1.5fr 3fr 30px; gap: 8px; align-items: center; margin-bottom: 8px;">
              <input type="text" name="portfolio_file_name[${pIdx}][]" class="form-input" style="font-size:0.82rem;" placeholder="Nama Berkas (e.g. Dokumen Specs PDF)" />
              <input type="text" name="portfolio_file_type[${pIdx}][]" class="form-input" style="font-size:0.82rem;" placeholder="Tipe (PDF, Figma, Code)" />
              <input type="url" name="portfolio_file_url[${pIdx}][]" class="form-input" style="font-size:0.82rem;" placeholder="https://..." />
              <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ef4444; font-size:1.2rem; font-weight:bold; cursor:pointer;" title="Hapus file ini">&times;</button>
            </div>
          </div>
          <button type="button" class="btn-add-item" style="font-size: 0.78rem; padding: 5px 12px; margin-top: 4px; background: #eff6ff; color: var(--primary-blue); border: 1px dashed var(--primary-blue);" onclick="addFileToPortfolio(${pIdx})">
            + Tambah Berkas / Link File Lainnya
          </button>
        </div>
      `;
      container.appendChild(div);
    }

    function addFileToPortfolio(pIdx) {
      const fileList = document.getElementById('file-list-' + pIdx);
      if (!fileList) return;
      const row = document.createElement('div');
      row.className = 'file-item-row';
      row.style.cssText = 'display: grid; grid-template-columns: 2fr 1.5fr 3fr 30px; gap: 8px; align-items: center; margin-bottom: 8px;';
      row.innerHTML = `
        <input type="text" name="portfolio_file_name[${pIdx}][]" class="form-input" style="font-size:0.82rem;" placeholder="Nama Berkas (e.g. Dokumen Specs PDF)" />
        <input type="text" name="portfolio_file_type[${pIdx}][]" class="form-input" style="font-size:0.82rem;" placeholder="Tipe (PDF, Figma, Code)" />
        <input type="url" name="portfolio_file_url[${pIdx}][]" class="form-input" style="font-size:0.82rem;" placeholder="https://..." />
        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ef4444; font-size:1.2rem; font-weight:bold; cursor:pointer;" title="Hapus file ini">&times;</button>
      `;
      fileList.appendChild(row);
    }

    function checkVideoPreview(url) {
      const statusDiv = document.getElementById('videoPreviewStatus');
      if (!url.trim()) {
        statusDiv.style.display = 'none';
        return;
      }
      statusDiv.style.display = 'block';
      if (url.includes('youtube.com') || url.includes('youtu.be') || url.includes('loom.com') || url.includes('drive.google.com')) {
        statusDiv.style.color = '#10b981';
        statusDiv.innerHTML = '✓ Format video terdeteksi. Video profil siap ditampilkan di profil Gig Worker Anda.';
      } else {
        statusDiv.style.color = '#d97706';
        statusDiv.innerHTML = 'ℹ Tautan video terdaftar. Pastikan akses video diset ke Publik/Unlisted.';
      }
    }
  </script>
<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
