<?php
declare(strict_types=1);

/**
 * Shared Project Vacancies dataset.
 * Vacancy statuses:
 * - 'draft' -> Draft (Belum diajukan)
 * - 'review' -> Menunggu Verifikasi (Verifikasi Admin)
 * - 'revision' -> Perlu Revisi (Catatan perbaikan dari Admin)
 * - 'rejected' -> Ditolak (Tidak disetujui Admin)
 * - 'active' -> Tayang Aktif (Sudah disetujui & dipublikasikan)
 */
function gig_project_vacancies(): array
{
    return [
        [
            'id' => 'GIG-2026-09-001',
            'title' => 'Redesign UI/UX Dashboard Prototype KarirHub',
            'category' => 'Desain & Kreatif',
            'status' => 'active',
            'statusLabel' => 'Tayang Aktif',
            'budget' => 'Rp 8.500.000',
            'duration' => '3 Minggu',
            'applicantsCount' => 2,
            'acceptedCount' => 1,
            'quota' => 1,
            'location' => 'Remote / Indonesia',
            'posted' => '01 Sep 2026',
            'skills' => ['Figma', 'UI/UX', 'Design System', 'Prototyping'],
            'desc' => 'Dibutuhkan UI/UX designer berpengalaman untuk merancang prototype interaktif dashboard KarirHub dengan tampilan modern.',
            'deadline' => '20 Sep 2026',
            'adminNote' => 'Lowongan telah diverifikasi dan disetujui oleh Admin KarirHub pada 01 Sep 2026.',
        ],
        [
            'id' => 'GIG-2026-09-002',
            'title' => 'Integrasi REST API Modul Notifikasi SMS & WhatsApp',
            'category' => 'IT & Pemrograman',
            'status' => 'active',
            'statusLabel' => 'Tayang Aktif',
            'budget' => 'Rp 6.000.000',
            'duration' => '2 Minggu',
            'applicantsCount' => 2,
            'acceptedCount' => 1,
            'quota' => 1,
            'location' => 'Remote / Bandung',
            'posted' => '03 Sep 2026',
            'skills' => ['PHP', 'REST API', 'Webhook', 'MySQL'],
            'desc' => 'Mengembangkan endpoint webhook dan mengintegrasikan provider SMS/WA ke core sistem.',
            'deadline' => '18 Sep 2026',
            'adminNote' => 'Lowongan disetujui Admin pada 03 Sep 2026.',
        ],
        [
            'id' => 'GIG-2026-09-003',
            'title' => 'Kampanye Media Sosial & Copywriting Peluncuran Fitur',
            'category' => 'Pemasaran & Konten',
            'status' => 'review',
            'statusLabel' => 'Menunggu Verifikasi',
            'budget' => 'Rp 4.500.000',
            'duration' => '1 Bulan',
            'applicantsCount' => 2,
            'acceptedCount' => 0,
            'quota' => 1,
            'location' => 'Remote / Surabaya',
            'posted' => '07 Sep 2026',
            'skills' => ['Copywriting', 'Social Media', 'Content Plan'],
            'desc' => 'Menyusun strategi konten peluncuran fitur Gig Worker untuk meningkatkan awareness.',
            'deadline' => '28 Sep 2026',
            'adminNote' => 'Permohonan lowongan dalam antrean verifikasi Admin. Estimasi waktu 1x24 jam.',
        ],
        [
            'id' => 'GIG-2026-09-004',
            'title' => 'Audit Aksesibilitas WCAG 2.1 Dashboard Internal',
            'category' => 'Desain & Kreatif',
            'status' => 'revision',
            'statusLabel' => 'Perlu Revisi',
            'budget' => 'Rp 3.500.000',
            'duration' => '2 Minggu',
            'applicantsCount' => 0,
            'acceptedCount' => 0,
            'quota' => 1,
            'location' => 'Remote / Jakarta',
            'posted' => '08 Sep 2026',
            'skills' => ['Figma', 'WCAG', 'UI Audit'],
            'desc' => 'Audit aksesibilitas standar WCAG 2.1 AA untuk komponen dashboard internal perusahaan.',
            'deadline' => '30 Sep 2026',
            'adminNote' => 'Revisi dari Admin: Harap perjelas cakupan rincian kriteria pengujian aksesibilitas dan format laporan yang diharapkan.',
        ],
        [
            'id' => 'GIG-2026-09-005',
            'title' => 'Scraping Data Produk Tanpa Izin Legal',
            'category' => 'IT & Pemrograman',
            'status' => 'rejected',
            'statusLabel' => 'Ditolak',
            'budget' => 'Rp 2.000.000',
            'duration' => '1 Minggu',
            'applicantsCount' => 0,
            'acceptedCount' => 0,
            'quota' => 1,
            'location' => 'Remote',
            'posted' => '05 Sep 2026',
            'skills' => ['Python', 'Web Scraping'],
            'desc' => 'Melakukan pengumpulan data massal dari platform eksternal.',
            'deadline' => '15 Sep 2026',
            'adminNote' => 'Penolakan dari Admin: Lowongan tidak memenuhi Syarat & Ketentuan KarirHub terkait perlindungan data pribadi dan hak cipta.',
        ],
        [
            'id' => 'GIG-2026-09-006',
            'title' => 'Penyusunan Modul Pelatihan Internal Gig Worker',
            'category' => 'Pemasaran & Konten',
            'status' => 'draft',
            'statusLabel' => 'Draft',
            'budget' => 'Rp 5.000.000',
            'duration' => '3 Minggu',
            'applicantsCount' => 0,
            'acceptedCount' => 0,
            'quota' => 1,
            'location' => 'Remote / Jakarta',
            'posted' => '09 Sep 2026',
            'skills' => ['Technical Writing', 'Instructional Design'],
            'desc' => 'Draft materi pelatihan onboarding mitra freelancer baru.',
            'deadline' => '05 Okt 2026',
            'adminNote' => 'Draft lowongan belum dikirim ke Admin.',
        ],
    ];
}

function gig_find_vacancy(string $id): ?array
{
    $vacancies = gig_project_vacancies();
    foreach ($vacancies as $v) {
        if ($v['id'] === $id) return $v;
    }
    return null;
}
