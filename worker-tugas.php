<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/worker-auth.php';

$pageTitle = 'Tugas Aktif';
$pageKey = 'tugas';
$breadcrumbCurrent = 'Tugas Aktif';
require __DIR__ . '/includes/worker-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Tugas Aktif</h1>
    </div>

    <div class="active-projects-list">
      <div class="active-project-card">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title">
              Redesign UI/UX Dashboard Prototype KarirHub
              <span class="badge-status active">Sedang Berjalan</span>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">Pemberi kerja: <strong>PT ABC</strong> · Sisa 8 hari</div>
          </div>
        </div>
        <div class="progress-milestone-box" style="margin-top:12px;">
          <div class="progress-header-label"><span>Kemajuan</span><span style="color:#2563eb;">65%</span></div>
          <div class="progress-track-bg"><div class="progress-fill-bar" style="width:65%"></div></div>
        </div>
      </div>
      <div class="active-project-card">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title">
              Integrasi REST API Modul Notifikasi
              <span class="badge-status active">Sedang Berjalan</span>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">Pemberi kerja: <strong>PT ABC</strong> · Sisa 4 hari</div>
          </div>
        </div>
        <div class="progress-milestone-box" style="margin-top:12px;">
          <div class="progress-header-label"><span>Kemajuan</span><span style="color:#2563eb;">90%</span></div>
          <div class="progress-track-bg"><div class="progress-fill-bar" style="width:90%"></div></div>
        </div>
      </div>
    </div>

<?php require __DIR__ . '/includes/worker-layout-end.php'; ?>
