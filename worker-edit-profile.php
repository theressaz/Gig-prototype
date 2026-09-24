<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/worker-profiles.php';

$role = $_SESSION['role'] ?? 'worker';
if ($role !== 'worker') {
    header("Location: dashboard-employer.php");
    exit;
}

$username = $_SESSION['username'] ?? $_SESSION['siapkerja_name'] ?? 'Theressa Zaratrusha';
$worker = gig_find_worker($username) ?? gig_find_worker('tessa');

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $title = trim((string)($_POST['title'] ?? ''));
    $location = trim((string)($_POST['location'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $wa = trim((string)($_POST['wa'] ?? ''));
    $skillsRaw = trim((string)($_POST['skills'] ?? ''));
    $proposal = trim((string)($_POST['proposal'] ?? ''));
    $videoUrl = trim((string)($_POST['video_url'] ?? ''));

    if ($name === '' || $email === '') {
        $errorMessage = "Nama Lengkap dan Email Kontak wajib diisi.";
    } else {
        $skillsArr = array_filter(array_map('trim', explode(',', $skillsRaw)));

        // Update session state
        $_SESSION['siapkerja_name'] = $name;
        $_SESSION['siapkerja_email'] = $email;
        $_SESSION['username'] = $name;

        $_SESSION['worker_custom_profile'] = [
            'name' => $name,
            'title' => $title,
            'location' => $location,
            'skills' => $skillsArr,
            'proposal' => $proposal,
            'video_url' => $videoUrl,
            'contact' => [
                'email' => $email,
                'wa' => $wa
            ]
        ];

        // Persist registration in DB
        gig_save_worker_registration([
            'username' => 'tessa',
            'bidang_keahlian' => $title,
            'skills' => $skillsRaw,
            'contact_choice' => 'new',
            'contact_email_new' => $email,
            'contact_wa_new' => $wa,
            'previous_projects' => $worker['experience'] ?? [],
            'portfolio' => $worker['portfolio'] ?? [],
            'video_url' => $videoUrl
        ]);

        $successMessage = "Profil Gig Worker Anda telah berhasil diperbarui!";
        $worker = gig_find_worker($username) ?? gig_find_worker('tessa');
    }
}

$pageTitle = 'Edit Profil Saya';
$pageKey = 'profil';
$breadcrumbCurrent = 'Edit Profil Saya';

require __DIR__ . '/includes/worker-layout-start.php';
?>

<div class="page-toolbar">
    <div>
        <h1 style="font-size:1.4rem;font-weight:800;color:#0f172a;margin-bottom:4px;">Edit Profil Gig Worker</h1>
        <p style="font-size:0.88rem;color:#64748b;">Perbarui informasi profil publik, kontak, keahlian, dan Portofolio Anda.</p>
    </div>
    <a class="btn-action-sm" href="worker-profile.php">← Kembali ke Profil Saya</a>
</div>

<?php if ($successMessage !== ""): ?>
    <div style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:14px 18px;border-radius:10px;font-size:0.9rem;font-weight:700;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
        <span>✓ <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
        <a href="worker-profile.php" style="color:#047857;text-decoration:underline;font-size:0.85rem;">Lihat Profil Publik &rarr;</a>
    </div>
<?php endif; ?>

<?php if ($errorMessage !== ""): ?>
    <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:14px 18px;border-radius:10px;font-size:0.9rem;font-weight:700;margin-bottom:20px;">
        ✕ <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="filter-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,0.03);max-width:840px;">
    <form method="POST" action="worker-edit-profile.php">
        <div style="margin-bottom:24px;">
            <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Informasi Utama Profil</h2>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
                <div>
                    <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Nama Lengkap <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" class="input-text" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;outline:none;" value="<?php echo htmlspecialchars($worker['name'] ?? 'Theressa Zaratrusha', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div>
                    <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Gelar / Bidang Keahlian Utama <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="title" class="input-text" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;outline:none;" value="<?php echo htmlspecialchars($worker['title'] ?? 'Lead UI/UX Designer', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
                <div>
                    <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Email Kontak Publik <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" class="input-text" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;outline:none;" value="<?php echo htmlspecialchars($worker['contact']['email'] ?? 'theressaz@pasker.id', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div>
                    <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Nomor WhatsApp</label>
                    <input type="text" name="wa" class="input-text" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;outline:none;" value="<?php echo htmlspecialchars($worker['contact']['wa'] ?? '0812-3456-7890', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Lokasi Domisili</label>
                <input type="text" name="location" class="input-text" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;outline:none;" value="<?php echo htmlspecialchars($worker['location'] ?? 'Jakarta, Indonesia', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div style="margin-bottom:24px;">
            <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Keahlian & Ringkasan Bio</h2>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Daftar Keahlian / Skill Tags (Pisahkan dengan koma)</label>
                <input type="text" name="skills" class="input-text" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;outline:none;" value="<?php echo htmlspecialchars(is_array($worker['skills']) ? implode(', ', $worker['skills']) : ($worker['skills'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Ringkasan Profil / Bio Singkat</label>
                <textarea name="proposal" rows="4" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;font-family:inherit;outline:none;"><?php echo htmlspecialchars($worker['proposal'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Link Video Profil / Portfolio</label>
                <input type="text" name="video_url" class="input-text" style="width:100%;padding:11px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:0.9rem;outline:none;" value="<?php echo htmlspecialchars($worker['video_url'] ?? 'https://www.youtube.com/watch?v=demo-profile-video', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div style="display:flex;gap:14px;align-items:center;padding-top:16px;border-top:1px solid #f1f5f9;">
            <button type="submit" class="btn-primary" style="background:#2563eb;color:#ffffff;border:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:0.95rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                <span>💾 Simpan Perubahan</span>
            </button>
            <a href="worker-profile.php" style="color:#475569;font-weight:600;font-size:0.9rem;text-decoration:none;">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
