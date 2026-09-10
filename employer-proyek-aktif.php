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
      <!-- Project 1 -->
      <div class="active-project-card">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title">
              Redesign UI/UX Dashboard Prototype KarirHub
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
              No. Kontrak: <strong>CTR-GIG-2026-0811</strong> · Durasi Disepakati: <strong>3 Minggu</strong>
            </div>
          </div>
        </div>

        <div class="active-proj-body">
          <div class="freelancer-profile-box">
            <div class="fl-avatar" style="background:#2563eb;">T</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?active=1&id=tessa" style="color:inherit;text-decoration:none;">Tessa</a>
                <span class="fl-rating-badge">★ 5</span>
              </div>
              <div class="fl-info-sub">Lead UI/UX Designer</div>
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">Kesepakatan disetujui · kontak terbuka</div>
            </div>
          </div>

          <!-- Countdown Widget (Replaces Milestones) -->
          <div class="countdown-widget-box" style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
              <span style="font-size:0.72rem;color:#2563eb;font-weight:700;">Tenggat: 22 Sep 2026</span>
            </div>
            <div style="display:flex;gap:8px;text-align:center;" id="countdown-proj-1">
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-days" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;">12</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Hari</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-hours" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;">14</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Jam</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-mins" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;">32</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Menit</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-secs" style="font-size:1.1rem;font-weight:800;color:#2563eb;display:block;">45</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Detik</span>
              </div>
            </div>
          </div>

          <div class="payment-direct-box">
            <span class="payment-direct-label">Status Deliverable</span>
            <span class="payment-direct-amount" style="font-size:0.95rem;">Review Prototype</span>
            <span style="font-size:0.72rem;color:var(--text-muted);">Sedang pengujian internal</span>
          </div>
        </div>

        <div class="active-proj-actions" style="justify-content:flex-end;">
          <div style="display:flex;gap:8px;">
            <button class="btn-action-sm" type="button" onclick="copyContact('Tessa','0812-3456-7890','tessa.design@email.com')">Kontak Freelancer</button>
            <a class="btn-outline-blue" href="worker-profile.php?active=1&id=tessa">Lihat Profil</a>
          </div>
        </div>
      </div>

      <!-- Project 2 -->
      <div class="active-project-card">
        <div class="active-proj-header">
          <div>
            <div class="active-proj-title">
              Integrasi REST API Modul Notifikasi SMS &amp; WhatsApp
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
              No. Kontrak: <strong>CTR-GIG-2026-0819</strong> · Durasi Disepakati: <strong>2 Minggu</strong>
            </div>
          </div>
        </div>

        <div class="active-proj-body">
          <div class="freelancer-profile-box">
            <div class="fl-avatar" style="background:#0891b2;">R</div>
            <div>
              <div class="fl-info-name">
                <a href="worker-profile.php?active=1&id=rian" style="color:inherit;text-decoration:none;">Rian Ardiansyah</a>
                <span class="fl-rating-badge">★ 5</span>
              </div>
              <div class="fl-info-sub">Backend API Developer</div>
              <div style="font-size:0.72rem;color:#10b981;font-weight:700;margin-top:2px;">Kesepakatan disetujui · kontak terbuka</div>
            </div>
          </div>

          <!-- Countdown Widget (Replaces Milestones) -->
          <div class="countdown-widget-box" style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="font-size:0.78rem;font-weight:700;color:var(--text-dark);">Countdown Durasi Proyek</span>
              <span style="font-size:0.72rem;color:#0891b2;font-weight:700;">Tenggat: 17 Sep 2026</span>
            </div>
            <div style="display:flex;gap:8px;text-align:center;" id="countdown-proj-2">
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-days" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;">7</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Hari</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-hours" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;">08</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Jam</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-mins" style="font-size:1.1rem;font-weight:800;color:#1e293b;display:block;">15</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Menit</span>
              </div>
              <div style="background:#fff;border:1px solid #cbd5e1;padding:4px 8px;border-radius:6px;min-width:44px;">
                <span class="c-secs" style="font-size:1.1rem;font-weight:800;color:#0891b2;display:block;">20</span>
                <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;">Detik</span>
              </div>
            </div>
          </div>

          <div class="payment-direct-box">
            <span class="payment-direct-label">Status Deliverable</span>
            <span class="payment-direct-amount" style="font-size:0.95rem;">UAT &amp; Deploy Selesai</span>
            <span style="font-size:0.72rem;color:var(--text-muted);">Menunggu verifikasi akhir</span>
          </div>
        </div>

        <div class="active-proj-actions" style="justify-content:flex-end;">
          <div style="display:flex;gap:8px;">
            <button class="btn-action-sm" type="button" onclick="copyContact('Rian Ardiansyah','0813-8899-7711','rian.dev@email.com')">Kontak Freelancer</button>
            <a class="btn-outline-blue" href="worker-profile.php?active=1&id=rian">Lihat Profil</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Countdown Timer Script -->
    <script>
      (function startCountdowns() {
        setInterval(function() {
          ['countdown-proj-1', 'countdown-proj-2'].forEach(function(id) {
            const container = document.getElementById(id);
            if (!container) return;
            const secEl = container.querySelector('.c-secs');
            if (secEl) {
              let s = parseInt(secEl.innerText, 10);
              if (s > 0) {
                s--;
              } else {
                s = 59;
                const minEl = container.querySelector('.c-mins');
                if (minEl) {
                  let m = parseInt(minEl.innerText, 10);
                  if (m > 0) {
                    m--;
                    minEl.innerText = m < 10 ? '0' + m : String(m);
                  }
                }
              }
              secEl.innerText = s < 10 ? '0' + s : String(s);
            }
          });
        }, 1000);
      })();
    </script>

<?php require __DIR__ . '/includes/employer-layout-end.php'; ?>
