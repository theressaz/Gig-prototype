<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';

$pageTitle = 'Proyek Aktif';
$pageKey = 'aktif';
$breadcrumbCurrent = 'Proyek Aktif';
require __DIR__ . '/includes/employer-layout-start.php';
?>

    <div class="page-toolbar">
      <h1>Proyek Aktif</h1>
      <div style="font-size:0.82rem;color:var(--text-muted);background:#f1f5f9;padding:6px 12px;border-radius:9999px;">
        Koordinasi langsung perusahaan &amp; mitra
      </div>
    </div>

    <div class="active-projects-list">
      <div class="active-project-card">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title">
              Redesign UI/UX Dashboard Prototype KarirHub
              <span class="badge-status active">Sedang Berjalan</span>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
              No. Kontrak: <strong>CTR-GIG-2026-0811</strong> · Durasi: <strong>3 Minggu (Sisa 8 Hari)</strong>
            </div>
          </div>
          <div style="text-align:right;">
            <span style="font-size:0.74rem;color:var(--text-muted);display:block;">Tipe Kesepakatan</span>
            <span style="font-size:0.95rem;font-weight:800;color:var(--primary-blue);">Kontrak Mandiri Lepas</span>
          </div>
        </div>
        <div class="active-proj-body">
          <div class="freelancer-profile-box">
            <div class="fl-avatar" style="background:#2563eb;">T</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?id=tessa" style="color:inherit;text-decoration:none;">Tessa</a>
                <span class="fl-rating-badge">★ 4.9</span>
              </div>
              <div class="fl-info-sub">Lead UI/UX Designer</div>
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">Kesepakatan disetujui · kontak terbuka</div>
            </div>
          </div>
          <div class="progress-milestone-box">
            <div class="progress-header-label"><span>Kemajuan Proyek</span><span style="color:#2563eb;">65% (Milestone 2/3)</span></div>
            <div class="progress-track-bg"><div class="progress-fill-bar" style="width:65%"></div></div>
            <div style="font-size:0.72rem;color:var(--text-muted);">Target selesai: <strong>22 September 2026</strong></div>
          </div>
          <div class="payment-direct-box">
            <span class="payment-direct-label">Status Deliverable</span>
            <span class="payment-direct-amount" style="font-size:0.95rem;">Review Prototype</span>
            <span style="font-size:0.72rem;color:var(--text-muted);">Sedang pengujian internal</span>
          </div>
        </div>
        <div class="active-proj-actions">
          <div class="milestone-stepper">
            <span class="step-badge" style="background:#ecfdf5;color:#059669;">M1 Wireframe selesai</span>
            <span>→</span>
            <span class="step-badge">M2 Prototype review</span>
            <span>→</span>
            <span class="step-badge" style="background:#f1f5f9;color:#64748b;">M3 Handover</span>
          </div>
          <div style="display:flex;gap:8px;">
            <button class="btn-action-sm" type="button" onclick="copyContact('Tessa','0812-3456-7890','tessa.design@email.com')">Kontak Freelancer</button>
            <a class="btn-outline-blue" href="worker-profile.php?id=tessa">Lihat Profil</a>
          </div>
        </div>
      </div>

      <div class="active-project-card">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title">
              Integrasi REST API Modul Notifikasi SMS &amp; WhatsApp
              <span class="badge-status active">Sedang Berjalan</span>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
              No. Kontrak: <strong>CTR-GIG-2026-0819</strong> · Durasi: <strong>2 Minggu (Sisa 4 Hari)</strong>
            </div>
          </div>
          <div style="text-align:right;">
            <span style="font-size:0.74rem;color:var(--text-muted);display:block;">Tipe Kesepakatan</span>
            <span style="font-size:0.95rem;font-weight:800;color:var(--primary-blue);">Kontrak Mandiri Lepas</span>
          </div>
        </div>
        <div class="active-proj-body">
          <div class="freelancer-profile-box">
            <div class="fl-avatar" style="background:#0891b2;">R</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?id=rian" style="color:inherit;text-decoration:none;">Rian Ardiansyah</a>
                <span class="fl-rating-badge">★ 4.8</span>
              </div>
              <div class="fl-info-sub">Backend API Developer</div>
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">Kesepakatan disetujui · kontak terbuka</div>
            </div>
          </div>
          <div class="progress-milestone-box">
            <div class="progress-header-label"><span>Kemajuan Proyek</span><span style="color:#2563eb;">90% (Milestone 2/2)</span></div>
            <div class="progress-track-bg"><div class="progress-fill-bar" style="width:90%"></div></div>
            <div style="font-size:0.72rem;color:var(--text-muted);">Target selesai: <strong>14 September 2026</strong></div>
          </div>
          <div class="payment-direct-box">
            <span class="payment-direct-label">Status Deliverable</span>
            <span class="payment-direct-amount" style="font-size:0.95rem;">UAT &amp; Deploy Selesai</span>
            <span style="font-size:0.72rem;color:var(--text-muted);">Menunggu verifikasi akhir</span>
          </div>
        </div>
        <div class="active-proj-actions">
          <div class="milestone-stepper">
            <span class="step-badge" style="background:#ecfdf5;color:#059669;">M1 API Architecture selesai</span>
            <span>→</span>
            <span class="step-badge">M2 Production Deploy</span>
          </div>
          <div style="display:flex;gap:8px;">
            <button class="btn-action-sm" type="button" onclick="copyContact('Rian Ardiansyah','0813-8899-7711','rian.dev@email.com')">Kontak Freelancer</button>
            <a class="btn-outline-blue" href="worker-profile.php?id=rian">Lihat Profil</a>
          </div>
        </div>
      </div>
    </div>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
