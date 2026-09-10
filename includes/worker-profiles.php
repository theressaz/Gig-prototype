<?php
declare(strict_types=1);

/**
 * Shared Gig Worker account profiles.
 * Contact details stay hidden until both sides have agreed to work together.
 */
function gig_worker_profiles(): array
{
    return [
        'tessa' => [
            'id' => 'tessa',
            'name' => 'Tessa',
            'initials' => 'TE',
            'color' => '#2563eb',
            'photo' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Tessa&backgroundColor=dbeafe',
            'title' => 'Lead UI/UX Designer',
            'location' => 'Jakarta, Indonesia',
            'rating' => 4.9,
            'reviews_count' => 18,
            'completed_projects' => 18,
            'top_rated' => true,
            'verified' => true,
            'agreed' => true,
            'category' => 'ui-ux',
            'applied_project' => 'Redesign UI/UX Dashboard Prototype KarirHub',
            'bid' => 'Rp 8.000.000',
            'eta' => '14 Hari Kerja',
            'proposal' => 'Berpengalaman 4+ tahun merancang antarmuka sistem web pemerintahan dan B2B SaaS dengan design system yang rapi di Figma.',
            'skills' => ['Figma Design', 'UI/UX Prototyping', 'Design System', 'Usability Testing', 'Wireframing'],
            'contact' => [
                'wa' => '0812-3456-7890',
                'email' => 'tessa.design@email.com',
            ],
            'experience' => [
                [
                    'role' => 'Lead UI/UX Designer',
                    'project' => 'Redesign Dashboard KarirHub (berjalan)',
                    'period' => 'Sep 2026 — sekarang',
                    'summary' => 'Merancang alur, wireframe, dan prototype interaktif dashboard pemberi kerja.',
                ],
                [
                    'role' => 'Product Designer',
                    'project' => 'Portal Lowongan Digital BUMN',
                    'period' => 'Jan 2025 — Jun 2026',
                    'summary' => 'Menyusun design system 80+ komponen dan uji keterbacaan untuk 3 modul layanan.',
                ],
                [
                    'role' => 'UI Designer',
                    'project' => 'Aplikasi Pelaporan Pekerja Lepas',
                    'period' => 'Mar 2024 — Des 2024',
                    'summary' => 'Mendesain alur onboarding dan dashboard pelaporan harian untuk mitra lapangan.',
                ],
            ],
            'portfolio' => [
                [
                    'title' => 'Design System KarirHub',
                    'type' => 'UI Kit & Prototype',
                    'client' => 'Kemnaker RI',
                    'year' => '2026',
                    'deliverable' => 'File Figma, token warna, dan prototype klik-melalui 12 layar.',
                ],
                [
                    'title' => 'Portal Rekrutmen BUMN',
                    'type' => 'Web App UI',
                    'client' => 'PT Talenta Nusantara',
                    'year' => '2025',
                    'deliverable' => 'High-fidelity mockup desktop/mobile dan panduan interaksi.',
                ],
                [
                    'title' => 'Onboarding Gig Worker',
                    'type' => 'UX Flow',
                    'client' => 'Startup Ketenagakerjaan',
                    'year' => '2024',
                    'deliverable' => 'User flow, wireframe, dan hasil usability test 8 partisipan.',
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT ABC',
                    'project' => 'Prototype Dashboard Internal',
                    'rating' => 5.0,
                    'date' => 'Agu 2026',
                    'comment' => 'Hasil desain rapi, komunikatif, dan tepat waktu. Prototype mudah diuji tim internal.',
                ],
                [
                    'employer' => 'CV Kreasi Digital',
                    'project' => 'Redesign Aplikasi Lowongan',
                    'rating' => 4.8,
                    'date' => 'Mei 2026',
                    'comment' => 'Sangat memahami kebutuhan pengguna awam. Iterasi cepat setelah umpan balik.',
                ],
                [
                    'employer' => 'Yayasan Kerja Adil',
                    'project' => 'Landing Page Program Pelatihan',
                    'rating' => 5.0,
                    'date' => 'Jan 2026',
                    'comment' => 'Visual konsisten dan aksesibel. Direkomendasikan untuk proyek pemerintahan.',
                ],
            ],
        ],
        'rian' => [
            'id' => 'rian',
            'name' => 'Rian Ardiansyah',
            'initials' => 'RA',
            'color' => '#0891b2',
            'photo' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Rian&backgroundColor=cffafe',
            'title' => 'Fullstack / Backend API Developer',
            'location' => 'Bandung, Indonesia',
            'rating' => 4.8,
            'reviews_count' => 24,
            'completed_projects' => 24,
            'top_rated' => false,
            'verified' => true,
            'agreed' => true,
            'category' => 'backend',
            'applied_project' => 'Integrasi REST API Modul Notifikasi',
            'bid' => 'Rp 6.000.000',
            'eta' => '10 Hari Kerja',
            'proposal' => 'Siap mengintegrasikan webhook gateway dan memastikan load testing API mampu menangani 5000+ request per menit.',
            'skills' => ['PHP / Laravel', 'REST API', 'MySQL', 'Webhook', 'Node.js'],
            'contact' => [
                'wa' => '0813-8899-7711',
                'email' => 'rian.dev@email.com',
            ],
            'experience' => [
                [
                    'role' => 'Backend API Developer',
                    'project' => 'Integrasi Notifikasi SMS & WhatsApp (berjalan)',
                    'period' => 'Sep 2026 — sekarang',
                    'summary' => 'Membangun endpoint webhook dan uji beban ke production.',
                ],
                [
                    'role' => 'Backend Engineer',
                    'project' => 'Gateway Pembayaran UMKM',
                    'period' => 'Feb 2025 — Jul 2026',
                    'summary' => 'Integrasi 4 payment provider dan dashboard rekonsiliasi harian.',
                ],
                [
                    'role' => 'PHP Developer',
                    'project' => 'Sistem Antrian Tiket Layanan',
                    'period' => '2023 — 2025',
                    'summary' => 'API antrian real-time dan dokumentasi OpenAPI untuk mitra eksternal.',
                ],
            ],
            'portfolio' => [
                [
                    'title' => 'Webhook Notification Hub',
                    'type' => 'REST API',
                    'client' => 'PT ABC',
                    'year' => '2026',
                    'deliverable' => 'Endpoint production, dokumentasi Postman, dan laporan stress test.',
                ],
                [
                    'title' => 'Payment Reconcile Service',
                    'type' => 'Backend Service',
                    'client' => 'Koperasi Digital Mandiri',
                    'year' => '2025',
                    'deliverable' => 'Service Laravel, skema MySQL, dan cron rekonsiliasi.',
                ],
                [
                    'title' => 'OpenAPI Ticket Queue',
                    'type' => 'API + Docs',
                    'client' => 'Dinas Layanan Publik',
                    'year' => '2024',
                    'deliverable' => 'Spesifikasi OpenAPI 3.0 dan contoh klien PHP.',
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT ABC',
                    'project' => 'Integrasi Gateway SMS',
                    'rating' => 4.9,
                    'date' => 'Jul 2026',
                    'comment' => 'Kode bersih, dokumentasi lengkap, dan responsif saat UAT.',
                ],
                [
                    'employer' => 'PT Data Prima',
                    'project' => 'API Pelaporan Kinerja',
                    'rating' => 4.7,
                    'date' => 'Mar 2026',
                    'comment' => 'Performa stabil di traffic tinggi. Komunikasi teknis jelas.',
                ],
                [
                    'employer' => 'CV Kode Nusantara',
                    'project' => 'Migrasi Monolith ke API',
                    'rating' => 4.8,
                    'date' => 'Nov 2025',
                    'comment' => 'Migrasi berjalan tanpa downtime. Timeline terpenuhi.',
                ],
            ],
        ],
        'siti' => [
            'id' => 'siti',
            'name' => 'Siti Nurhaliza',
            'initials' => 'SN',
            'color' => '#7c3aed',
            'photo' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Siti&backgroundColor=ede9fe',
            'title' => 'Social Media Specialist',
            'location' => 'Surabaya, Indonesia',
            'rating' => 5.0,
            'reviews_count' => 12,
            'completed_projects' => 12,
            'top_rated' => true,
            'verified' => true,
            'agreed' => false,
            'category' => 'marketing',
            'applied_project' => 'Kampanye Media Sosial & Copywriting Peluncuran Fitur',
            'bid' => 'Rp 4.500.000',
            'eta' => '20 Hari Kerja',
            'proposal' => 'Menyediakan paket 20 konten carousel edukatif, naskah reels/TikTok, dan kalender konten terstruktur.',
            'skills' => ['Copywriting', 'Social Media', 'Content Plan', 'Canva', 'Instagram Ads'],
            'contact' => [
                'wa' => '0857-1122-3344',
                'email' => 'siti.marketing@email.com',
            ],
            'experience' => [
                [
                    'role' => 'Social Media Lead',
                    'project' => 'Kampanye Peluncuran Fitur Fintech',
                    'period' => '2025 — 2026',
                    'summary' => 'Mengelola 3 kanal dan menaikkan engagement organik 42% dalam 8 minggu.',
                ],
                [
                    'role' => 'Content Specialist',
                    'project' => 'Edukasi Jaminan Sosial Pekerja',
                    'period' => '2024 — 2025',
                    'summary' => 'Menulis serial konten edukatif dan skrip video pendek untuk audiens informal.',
                ],
            ],
            'portfolio' => [
                [
                    'title' => 'Kalender Konten 30 Hari',
                    'type' => 'Content Plan',
                    'client' => 'Brand F&B Nasional',
                    'year' => '2026',
                    'deliverable' => 'Kalender editorial, 20 carousel, dan 8 naskah reels.',
                ],
                [
                    'title' => 'Kampanye Awareness BPJS',
                    'type' => 'Copy & Visual',
                    'client' => 'Lembaga Sosial',
                    'year' => '2025',
                    'deliverable' => 'Paket copy + visual edukasi dan laporan jangkauan.',
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT Media Cerah',
                    'project' => 'Peluncuran Fitur Mobile',
                    'rating' => 5.0,
                    'date' => 'Jun 2026',
                    'comment' => 'Copy tajam dan visual konsisten. Deadline selalu tertahankan.',
                ],
                [
                    'employer' => 'Kopi Lokal Co.',
                    'project' => 'Konten Instagram 3 Bulan',
                    'rating' => 5.0,
                    'date' => 'Feb 2026',
                    'comment' => 'Naik follower organik tanpa iklan berlebih. Sangat direkomendasikan.',
                ],
            ],
        ],
        'budi' => [
            'id' => 'budi',
            'name' => 'Budi Wicaksono',
            'initials' => 'BW',
            'color' => '#059669',
            'photo' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Budi&backgroundColor=d1fae5',
            'title' => 'UI Designer',
            'location' => 'Yogyakarta, Indonesia',
            'rating' => 4.7,
            'reviews_count' => 9,
            'completed_projects' => 9,
            'top_rated' => false,
            'verified' => true,
            'agreed' => false,
            'category' => 'ui-ux',
            'applied_project' => 'Redesign UI/UX Dashboard Prototype KarirHub',
            'bid' => 'Rp 7.500.000',
            'eta' => '10 Hari Kerja',
            'proposal' => 'Siap deliver cepat dalam 10 hari lengkap dengan usability testing dan panduan style guide.',
            'skills' => ['UI Design', 'Wireframing', 'Figma', 'Style Guide'],
            'contact' => [
                'wa' => '0819-2233-4455',
                'email' => 'budi.design@email.com',
            ],
            'experience' => [
                [
                    'role' => 'UI Designer',
                    'project' => 'Redesign Aplikasi Absensi',
                    'period' => '2025 — 2026',
                    'summary' => 'Merapikan 18 layar mobile dan menyusun style guide komponen dasar.',
                ],
                [
                    'role' => 'Visual Designer',
                    'project' => 'Website Lembaga Pelatihan',
                    'period' => '2024',
                    'summary' => 'Desain landing dan dashboard peserta pelatihan daring.',
                ],
            ],
            'portfolio' => [
                [
                    'title' => 'Style Guide Absensi Mobile',
                    'type' => 'UI Kit',
                    'client' => 'PT Karya Waktu',
                    'year' => '2026',
                    'deliverable' => 'Komponen Figma, ikon set, dan panduan spasi/tipografi.',
                ],
                [
                    'title' => 'Website Lembaga Pelatihan',
                    'type' => 'Web UI',
                    'client' => 'LPK Mandiri',
                    'year' => '2024',
                    'deliverable' => 'Mockup 9 halaman dan aset ekspor produksi.',
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT Karya Waktu',
                    'project' => 'Redesign Absensi',
                    'rating' => 4.7,
                    'date' => 'Apr 2026',
                    'comment' => 'Cepat dan rapi. Perlu sedikit arahan di awal, hasil akhir memuaskan.',
                ],
                [
                    'employer' => 'LPK Mandiri',
                    'project' => 'Website Lembaga',
                    'rating' => 4.6,
                    'date' => 'Okt 2024',
                    'comment' => 'Visual bersih. Revisi ditangani dengan baik.',
                ],
            ],
        ],
        'dimas' => [
            'id' => 'dimas',
            'name' => 'Dimas Prasetyo',
            'initials' => 'DP',
            'color' => '#ea580c',
            'photo' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Dimas&backgroundColor=ffedd5',
            'title' => 'Cloud API Engineer',
            'location' => 'Depok, Indonesia',
            'rating' => 4.9,
            'reviews_count' => 31,
            'completed_projects' => 31,
            'top_rated' => true,
            'verified' => true,
            'agreed' => false,
            'category' => 'backend',
            'applied_project' => 'Integrasi REST API Modul Notifikasi',
            'bid' => 'Rp 6.500.000',
            'eta' => '7 Hari Kerja',
            'proposal' => 'Spesialis integrasi cloud API dan microservices. Telah menyelesaikan puluhan integrasi gateway serupa.',
            'skills' => ['Node.js', 'REST API', 'Microservices', 'AWS', 'Redis'],
            'contact' => [
                'wa' => '0821-9988-7766',
                'email' => 'dimas.code@email.com',
            ],
            'experience' => [
                [
                    'role' => 'Cloud API Engineer',
                    'project' => 'Integrasi Gateway Multi-Provider',
                    'period' => '2024 — 2026',
                    'summary' => 'Membangun layer abstraksi API untuk SMS, email, dan push notification.',
                ],
                [
                    'role' => 'Backend Developer',
                    'project' => 'Platform Antrian Cloud',
                    'period' => '2022 — 2024',
                    'summary' => 'Merancang antrean Redis dan worker Node.js untuk 2 juta event/hari.',
                ],
            ],
            'portfolio' => [
                [
                    'title' => 'Multi-Gateway Adapter',
                    'type' => 'Microservice',
                    'client' => 'PT Cloud Nusantara',
                    'year' => '2026',
                    'deliverable' => 'Service Node.js, tes integrasi, dan runbook operasi.',
                ],
                [
                    'title' => 'Event Queue 2M/day',
                    'type' => 'Infrastructure',
                    'client' => 'Startup Logistik',
                    'year' => '2024',
                    'deliverable' => 'Arsitektur Redis + worker dan dashboard metrik.',
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT Cloud Nusantara',
                    'project' => 'Adapter Notifikasi',
                    'rating' => 5.0,
                    'date' => 'Agu 2026',
                    'comment' => 'Sangat andal. Dokumentasi operasi memudahkan tim internal.',
                ],
                [
                    'employer' => 'PT Logis Cepat',
                    'project' => 'Event Pipeline',
                    'rating' => 4.8,
                    'date' => 'Des 2025',
                    'comment' => 'Performa sesuai janji. Komunikasi teknis ringkas dan jelas.',
                ],
            ],
        ],
        'mega' => [
            'id' => 'mega',
            'name' => 'Mega Lestari',
            'initials' => 'ML',
            'color' => '#db2777',
            'photo' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Mega&backgroundColor=fce7f3',
            'title' => 'Digital Campaign Strategist',
            'location' => 'Semarang, Indonesia',
            'rating' => 4.9,
            'reviews_count' => 15,
            'completed_projects' => 15,
            'top_rated' => false,
            'verified' => true,
            'agreed' => false,
            'category' => 'marketing',
            'applied_project' => 'Kampanye Media Sosial & Copywriting Peluncuran Fitur',
            'bid' => 'Rp 5.000.000',
            'eta' => '14 Hari Kerja',
            'proposal' => 'Portofolio mencakup campaign viral BUMN dan startup teknologi. Siap mulai riset audience segera.',
            'skills' => ['Digital Campaign', 'SEO Writing', 'Brand Story', 'Analytics'],
            'contact' => [
                'wa' => '0878-3344-5566',
                'email' => 'mega.content@email.com',
            ],
            'experience' => [
                [
                    'role' => 'Campaign Strategist',
                    'project' => 'Peluncuran Program Magang Nasional',
                    'period' => '2025 — 2026',
                    'summary' => 'Menyusun pesan kampanye lintas kanal dan laporan insight mingguan.',
                ],
                [
                    'role' => 'SEO Content Lead',
                    'project' => 'Portal Karir Daerah',
                    'period' => '2023 — 2025',
                    'summary' => 'Menaikkan trafik organik 3x melalui seri artikel edukasi kerja lepas.',
                ],
            ],
            'portfolio' => [
                [
                    'title' => 'Kampanye Magang Nasional',
                    'type' => 'Campaign Kit',
                    'client' => 'Lembaga Pemerintah',
                    'year' => '2026',
                    'deliverable' => 'Key message, 15 aset visual, dan laporan performa 30 hari.',
                ],
                [
                    'title' => 'Seri Artikel SEO Karir',
                    'type' => 'Content SEO',
                    'client' => 'Portal Karir Daerah',
                    'year' => '2025',
                    'deliverable' => '24 artikel long-form dan peta kata kunci.',
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'BUMN Karya Digital',
                    'project' => 'Kampanye Program Magang',
                    'rating' => 5.0,
                    'date' => 'Jul 2026',
                    'comment' => 'Narasi kuat dan terukur. Tim internal mudah mengeksekusi asetnya.',
                ],
                [
                    'employer' => 'Portal Karir Jateng',
                    'project' => 'Konten SEO 6 Bulan',
                    'rating' => 4.8,
                    'date' => 'Mar 2025',
                    'comment' => 'Trafik naik signifikan. Penulisan tetap ramah pembaca awam.',
                ],
            ],
        ],
    ];
}

function gig_find_worker(string $id): ?array
{
    $profiles = gig_worker_profiles();
    return $profiles[$id] ?? null;
}

function gig_stars(float $rating): string
{
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.4;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '★';
        } elseif ($i === $full + 1 && $half) {
            $html .= '☆';
        } else {
            $html .= '☆';
        }
    }
    return $html;
}
