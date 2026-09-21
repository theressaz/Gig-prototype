<?php
declare(strict_types=1);

/**
 * Shared Gig Worker account profiles.
 * Contact details stay hidden until both sides have agreed to work together.
 * Individual review ratings are whole integers (1–5).
 * The overall 'rating' field is a computed average and may be a decimal.
 * 'completed_projects' = projects with status Selesai.
 * 'total_projects' = all projects worked on regardless of status.
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
            'rating' => 5.0, // average of reviews: (5+5+5)/3 = 5.0
            'reviews_count' => 18,
            'completed_projects' => 16, // selesai
            'total_projects' => 18,     // total dikerjakan
            'verified' => true,
            'agreed' => false,
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
                    'id' => 'tessa-port-1',
                    'title' => 'Design System KarirHub',
                    'type' => 'UI Kit & Prototype',
                    'client' => 'Kemnaker RI',
                    'year' => '2026',
                    'deliverable' => 'File Figma, token warna, dan prototype klik-melalui 12 layar.',
                    'files' => [
                        ['name' => 'Design_System_KarirHub_v2.fig', 'type' => 'Figma File', 'size' => '24.5 MB', 'url' => '#'],
                        ['name' => 'Panduan_Interaksi_UI.pdf', 'type' => 'Dokumen PDF', 'size' => '4.8 MB', 'url' => '#'],
                        ['name' => 'Prototype_Interactive_Flow.url', 'type' => 'Tautan Prototype Figma', 'size' => 'Akses Web', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'tessa-port-2',
                    'title' => 'Portal Rekrutmen BUMN',
                    'type' => 'Web App UI',
                    'client' => 'PT Talenta Nusantara',
                    'year' => '2025',
                    'deliverable' => 'High-fidelity mockup desktop/mobile dan panduan interaksi.',
                    'files' => [
                        ['name' => 'HighFidelity_Mockups_BUMN.zip', 'type' => 'Arsip Desain', 'size' => '48.1 MB', 'url' => '#'],
                        ['name' => 'Dokumentasi_Desain_Portal.pdf', 'type' => 'Dokumen PDF', 'size' => '3.2 MB', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'tessa-port-3',
                    'title' => 'Onboarding Gig Worker',
                    'type' => 'UX Flow',
                    'client' => 'Startup Ketenagakerjaan',
                    'year' => '2024',
                    'deliverable' => 'User flow, wireframe, dan hasil usability test 8 partisipan.',
                    'files' => [
                        ['name' => 'UserFlow_Onboarding.pdf', 'type' => 'Dokumen PDF', 'size' => '6.5 MB', 'url' => '#'],
                        ['name' => 'Usability_Testing_Report.xlsx', 'type' => 'Laporan Pengujian', 'size' => '1.2 MB', 'url' => '#']
                    ]
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT ABC',
                    'project' => 'Prototype Dashboard Internal',
                    'rating' => 5, // integer
                    'date' => 'Agu 2026',
                    'comment' => 'Hasil desain rapi, komunikatif, dan tepat waktu. Prototype mudah diuji tim internal.',
                ],
                [
                    'employer' => 'CV Kreasi Digital',
                    'project' => 'Redesign Aplikasi Lowongan',
                    'rating' => 5, // integer
                    'date' => 'Mei 2026',
                    'comment' => 'Sangat memahami kebutuhan pengguna awam. Iterasi cepat setelah umpan balik.',
                ],
                [
                    'employer' => 'Yayasan Kerja Adil',
                    'project' => 'Landing Page Program Pelatihan',
                    'rating' => 5,
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
            'rating' => 5.0, // average: (5+5+5)/3 = 5.0
            'reviews_count' => 24,
            'completed_projects' => 22, // selesai
            'total_projects' => 24,     // total dikerjakan
            'verified' => true,
            'agreed' => false,
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
                    'id' => 'rian-port-1',
                    'title' => 'Webhook Notification Hub',
                    'type' => 'REST API',
                    'client' => 'PT ABC',
                    'year' => '2026',
                    'deliverable' => 'Endpoint production, dokumentasi Postman, dan laporan stress test.',
                    'files' => [
                        ['name' => 'Postman_Collection_Notification.json', 'type' => 'API Specs', 'size' => '850 KB', 'url' => '#'],
                        ['name' => 'Load_Testing_k6_Report.pdf', 'type' => 'Laporan Stress Test', 'size' => '2.1 MB', 'url' => '#'],
                        ['name' => 'Source_Code_Webhook_Handler.zip', 'type' => 'Source Code', 'size' => '12.4 MB', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'rian-port-2',
                    'title' => 'Payment Reconcile Service',
                    'type' => 'Backend Service',
                    'client' => 'Koperasi Digital Mandiri',
                    'year' => '2025',
                    'deliverable' => 'Service Laravel, skema MySQL, dan cron rekonsiliasi.',
                    'files' => [
                        ['name' => 'Schema_Database_Reconciliation.sql', 'type' => 'SQL Script', 'size' => '340 KB', 'url' => '#'],
                        ['name' => 'Dokumentasi_Cronjob.pdf', 'type' => 'Dokumen PDF', 'size' => '1.5 MB', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'rian-port-3',
                    'title' => 'OpenAPI Ticket Queue',
                    'type' => 'API + Docs',
                    'client' => 'Dinas Layanan Publik',
                    'year' => '2024',
                    'deliverable' => 'Spesifikasi OpenAPI 3.0 dan contoh klien PHP.',
                    'files' => [
                        ['name' => 'openapi_v3_spec.yaml', 'type' => 'OpenAPI Spec', 'size' => '120 KB', 'url' => '#'],
                        ['name' => 'PHP_Client_SDK_Sample.php', 'type' => 'Code Sample', 'size' => '45 KB', 'url' => '#']
                    ]
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT ABC',
                    'project' => 'Integrasi Gateway SMS',
                    'rating' => 5,
                    'date' => 'Jul 2026',
                    'comment' => 'Kode bersih, dokumentasi lengkap, dan responsif saat UAT.',
                ],
                [
                    'employer' => 'PT Data Prima',
                    'project' => 'API Pelaporan Kinerja',
                    'rating' => 5,
                    'date' => 'Mar 2026',
                    'comment' => 'Performa stabil di traffic tinggi. Komunikasi teknis jelas.',
                ],
                [
                    'employer' => 'CV Kode Nusantara',
                    'project' => 'Migrasi Monolith ke API',
                    'rating' => 5,
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
            'rating' => 5.0, // average: (5+5)/2 = 5.0
            'reviews_count' => 12,
            'completed_projects' => 11, // selesai
            'total_projects' => 12,     // total dikerjakan
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
                    'id' => 'siti-port-1',
                    'title' => 'Kalender Konten 30 Hari',
                    'type' => 'Content Plan',
                    'client' => 'Brand F&B Nasional',
                    'year' => '2026',
                    'deliverable' => 'Kalender editorial, 20 carousel, dan 8 naskah reels.',
                    'files' => [
                        ['name' => 'Kalender_Editorial_September.pdf', 'type' => 'Dokumen PDF', 'size' => '2.8 MB', 'url' => '#'],
                        ['name' => 'Asset_Carousel_Pack_20Pcs.zip', 'type' => 'Aset Gambar', 'size' => '35.0 MB', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'siti-port-2',
                    'title' => 'Kampanye Awareness BPJS',
                    'type' => 'Copy & Visual',
                    'client' => 'Lembaga Sosial',
                    'year' => '2025',
                    'deliverable' => 'Paket copy + visual edukasi dan laporan jangkauan.',
                    'files' => [
                        ['name' => 'Copywriting_Content_Sheet.xlsx', 'type' => 'Lembar Kerja', 'size' => '940 KB', 'url' => '#'],
                        ['name' => 'Laporan_Analitik_Jangkauan.pdf', 'type' => 'Laporan', 'size' => '1.8 MB', 'url' => '#']
                    ]
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT Media Cerah',
                    'project' => 'Peluncuran Fitur Mobile',
                    'rating' => 5,
                    'date' => 'Jun 2026',
                    'comment' => 'Copy tajam dan visual konsisten. Deadline selalu tertahankan.',
                ],
                [
                    'employer' => 'Kopi Lokal Co.',
                    'project' => 'Konten Instagram 3 Bulan',
                    'rating' => 5,
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
            'rating' => 4.0, // average: (4+4)/2 = 4.0
            'reviews_count' => 9,
            'completed_projects' => 7, // selesai
            'total_projects' => 9,     // total dikerjakan
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
                    'id' => 'budi-port-1',
                    'title' => 'Style Guide Absensi Mobile',
                    'type' => 'UI Kit',
                    'client' => 'PT Karya Waktu',
                    'year' => '2026',
                    'deliverable' => 'Komponen Figma, ikon set, dan panduan spasi/tipografi.',
                    'files' => [
                        ['name' => 'StyleGuide_Absensi_Component.fig', 'type' => 'Figma File', 'size' => '18.2 MB', 'url' => '#'],
                        ['name' => 'IconSet_Custom_SVG.zip', 'type' => 'Aset Vektor', 'size' => '4.1 MB', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'budi-port-2',
                    'title' => 'Website Lembaga Pelatihan',
                    'type' => 'Web UI',
                    'client' => 'LPK Mandiri',
                    'year' => '2024',
                    'deliverable' => 'Mockup 9 halaman dan aset ekspor produksi.',
                    'files' => [
                        ['name' => 'Mockup_Web_Landing_Dashboard.zip', 'type' => 'Aset Gambar', 'size' => '22.0 MB', 'url' => '#']
                    ]
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT Karya Waktu',
                    'project' => 'Redesign Absensi',
                    'rating' => 4,
                    'date' => 'Apr 2026',
                    'comment' => 'Cepat dan rapi. Perlu sedikit arahan di awal, hasil akhir memuaskan.',
                ],
                [
                    'employer' => 'LPK Mandiri',
                    'project' => 'Website Lembaga',
                    'rating' => 4,
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
            'rating' => 5.0, // average: (5+5)/2 = 5.0
            'reviews_count' => 31,
            'completed_projects' => 29, // selesai
            'total_projects' => 31,     // total dikerjakan
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
                    'id' => 'dimas-port-1',
                    'title' => 'Multi-Gateway Adapter',
                    'type' => 'Microservice',
                    'client' => 'PT Cloud Nusantara',
                    'year' => '2026',
                    'deliverable' => 'Service Node.js, tes integrasi, dan runbook operasi.',
                    'files' => [
                        ['name' => 'Microservice_Adapter_Node.zip', 'type' => 'Source Code', 'size' => '8.9 MB', 'url' => '#'],
                        ['name' => 'Ops_Runbook_Deployment.pdf', 'type' => 'Dokumen PDF', 'size' => '1.4 MB', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'dimas-port-2',
                    'title' => 'Event Queue 2M/day',
                    'type' => 'Infrastructure',
                    'client' => 'Startup Logistik',
                    'year' => '2024',
                    'deliverable' => 'Arsitektur Redis + worker dan dashboard metrik.',
                    'files' => [
                        ['name' => 'Redis_Worker_Architecture.pdf', 'type' => 'Dokumen PDF', 'size' => '3.1 MB', 'url' => '#']
                    ]
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'PT Cloud Nusantara',
                    'project' => 'Adapter Notifikasi',
                    'rating' => 5,
                    'date' => 'Agu 2026',
                    'comment' => 'Sangat andal. Dokumentasi operasi memudahkan tim internal.',
                ],
                [
                    'employer' => 'PT Logis Cepat',
                    'project' => 'Event Pipeline',
                    'rating' => 5,
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
            'rating' => 5.0, // average: (5+5)/2 = 5.0
            'reviews_count' => 15,
            'completed_projects' => 14, // selesai
            'total_projects' => 15,     // total dikerjakan
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
                    'id' => 'mega-port-1',
                    'title' => 'Kampanye Magang Nasional',
                    'type' => 'Campaign Kit',
                    'client' => 'Lembaga Pemerintah',
                    'year' => '2026',
                    'deliverable' => 'Key message, 15 aset visual, dan laporan performa 30 hari.',
                    'files' => [
                        ['name' => 'Campaign_Strategy_Deck.pdf', 'type' => 'Dokumen Presentation', 'size' => '12.6 MB', 'url' => '#'],
                        ['name' => 'Visual_Assets_Kit_15Pcs.zip', 'type' => 'Aset Gambar', 'size' => '40.2 MB', 'url' => '#']
                    ]
                ],
                [
                    'id' => 'mega-port-2',
                    'title' => 'Seri Artikel SEO Karir',
                    'type' => 'Content SEO',
                    'client' => 'Portal Karir Daerah',
                    'year' => '2025',
                    'deliverable' => '24 artikel long-form dan peta kata kunci.',
                    'files' => [
                        ['name' => '24_Artikel_SEO_Karir.zip', 'type' => 'Dokumen Teks', 'size' => '5.4 MB', 'url' => '#'],
                        ['name' => 'Keyword_Mapping_Matrix.xlsx', 'type' => 'Lembar Kerja', 'size' => '1.1 MB', 'url' => '#']
                    ]
                ],
            ],
            'reviews' => [
                [
                    'employer' => 'BUMN Karya Digital',
                    'project' => 'Kampanye Program Magang',
                    'rating' => 5,
                    'date' => 'Jul 2026',
                    'comment' => 'Narasi kuat dan terukur. Tim internal mudah mengeksekusi asetnya.',
                ],
                [
                    'employer' => 'Portal Karir Jateng',
                    'project' => 'Konten SEO 6 Bulan',
                    'rating' => 5,
                    'date' => 'Mar 2025',
                    'comment' => 'Trafik naik signifikan. Penulisan tetap ramah pembaca awam.',
                ],
            ],
        ],
    ];

    // ── Merge reviews from DB (primary source) ───────────────────────────────
    // db.php is conditionally included here so worker-profiles.php can work
    // standalone without requiring a DB connection in every consumer.
    if (function_exists('gig_db')) {
        $pdo = gig_db();
        if ($pdo !== null) {
            try {
                // Fetch all reviews stored by any employer for any worker
                $stmt = $pdo->query(
                    "SELECT `worker_id`, `employer_username`, `project_title`,
                            `overall_rating`, `comment`, `badges`, `created_at`
                     FROM `project_reviews`
                     ORDER BY `created_at` DESC"
                );
                $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Group by worker_id and track contracts already merged to avoid
                // double-inserting if both DB and session have the same review.
                $mergedContracts = [];

                foreach ($dbRows as $row) {
                    $wId = $row['worker_id'];
                    if (!isset($profiles[$wId])) continue;

                    $contractKey = $row['employer_username'] . '||' . $row['project_title'];
                    if (isset($mergedContracts[$wId][$contractKey])) continue;
                    $mergedContracts[$wId][$contractKey] = true;

                    $badgesArr = ($row['badges'] !== '')
                        ? explode('||', $row['badges'])
                        : [];

                    // Format date as "Sep 2026" style
                    $reviewDate = date('M Y', strtotime($row['created_at']));

                    array_unshift($profiles[$wId]['reviews'], [
                        'employer' => $row['employer_username'],
                        'project'  => $row['project_title'],
                        'rating'   => (int)$row['overall_rating'],
                        'date'     => $reviewDate,
                        'comment'  => $row['comment'],
                        'badges'   => $badgesArr,
                    ]);

                    $profiles[$wId]['reviews_count']++;
                    $profiles[$wId]['completed_projects']++;
                    $profiles[$wId]['total_projects'] = max(
                        (int)$profiles[$wId]['total_projects'],
                        (int)$profiles[$wId]['completed_projects']
                    );
                }

                // Recalculate average rating for each worker that had new reviews
                foreach (array_keys($mergedContracts) as $wId) {
                    if (!isset($profiles[$wId])) continue;
                    $allRatings = array_column($profiles[$wId]['reviews'], 'rating');
                    $cnt = count($allRatings);
                    $profiles[$wId]['rating'] = $cnt > 0
                        ? round(array_sum($allRatings) / $cnt, 1)
                        : 5.0;
                }
            } catch (Throwable $e) {
                // DB error – fall through to session fallback
            }
        }
    }

    // ── Session fallback (used when DB is offline) ───────────────────────────
    if (session_status() === PHP_SESSION_ACTIVE
        && isset($_SESSION['custom_reviews'])
        && is_array($_SESSION['custom_reviews'])
    ) {
        foreach ($_SESSION['custom_reviews'] as $wId => $revList) {
            if (!isset($profiles[$wId]) || !is_array($revList)) continue;
            foreach ($revList as $r) {
                // Avoid duplicating if already merged from DB
                $contractKey = ($r['employer'] ?? '') . '||' . ($r['project'] ?? '');
                // Simple check: see if any existing review matches
                $alreadyIn = false;
                foreach ($profiles[$wId]['reviews'] as $existing) {
                    if (($existing['project'] ?? '') === ($r['project'] ?? '')
                        && ($existing['employer'] ?? '') === ($r['employer'] ?? '')
                    ) {
                        $alreadyIn = true;
                        break;
                    }
                }
                if ($alreadyIn) continue;

                array_unshift($profiles[$wId]['reviews'], $r);
                $profiles[$wId]['reviews_count']++;
                $profiles[$wId]['completed_projects']++;
                $profiles[$wId]['total_projects'] = max(
                    (int)$profiles[$wId]['total_projects'],
                    (int)$profiles[$wId]['completed_projects']
                );
            }
            $allRatings = array_column($profiles[$wId]['reviews'], 'rating');
            $cnt = count($allRatings);
            $profiles[$wId]['rating'] = $cnt > 0
                ? round(array_sum($allRatings) / $cnt, 1)
                : 5.0;
        }
    }

    return $profiles;
}

function gig_find_worker(string $id): ?array
{
    $profiles = gig_worker_profiles();
    $cleanId = strtolower(trim($id));
    if (isset($profiles[$cleanId])) {
        // If registration data exists for this user, merge registered data
        $reg = gig_get_worker_registration($cleanId);
        if ($reg !== null) {
            if (!empty($reg['bidang_keahlian'])) {
                $profiles[$cleanId]['title'] = $reg['bidang_keahlian'];
            }
            if (!empty($reg['skills'])) {
                $profiles[$cleanId]['skills'] = is_array($reg['skills']) ? $reg['skills'] : array_map('trim', explode(',', $reg['skills']));
            }
            if (!empty($reg['contact_email']) || !empty($reg['contact_wa'])) {
                $profiles[$cleanId]['contact']['email'] = $reg['contact_email'] ?? $profiles[$cleanId]['contact']['email'];
                $profiles[$cleanId]['contact']['wa'] = $reg['contact_wa'] ?? $profiles[$cleanId]['contact']['wa'];
            }
            if (!empty($reg['portfolio']) && is_array($reg['portfolio'])) {
                $profiles[$cleanId]['portfolio'] = array_merge($reg['portfolio'], $profiles[$cleanId]['portfolio'] ?? []);
            }
            if (!empty($reg['previous_projects']) && is_array($reg['previous_projects'])) {
                $profiles[$cleanId]['experience'] = array_merge($reg['previous_projects'], $profiles[$cleanId]['experience'] ?? []);
            }
            if (!empty($reg['video_url'])) {
                $profiles[$cleanId]['video_url'] = $reg['video_url'];
            }
        }
        return $profiles[$cleanId];
    }

    // Check if there is a registration for a user not in preset array
    $reg = gig_get_worker_registration($id);
    if ($reg !== null || gig_is_worker_registered($id)) {
        $siapkerja = gig_get_siapkerja_profile($id);
        $reg = $reg ?? [];
        return [
            'id' => $cleanId,
            'name' => $siapkerja['nama'] ?? ucwords($id),
            'initials' => strtoupper(substr($id, 0, 2)),
            'color' => '#2563eb',
            'photo' => 'https://api.dicebear.com/9.x/notionists/svg?seed=' . urlencode($id) . '&backgroundColor=dbeafe',
            'title' => $reg['bidang_keahlian'] ?? 'Gig Worker Professional',
            'location' => $siapkerja['lokasi'] ?? 'Jakarta, Indonesia',
            'rating' => 5.0,
            'reviews_count' => 5,
            'completed_projects' => 4,
            'total_projects' => 5,
            'verified' => true,
            'agreed' => true,
            'category' => 'general',
            'skills' => is_array($reg['skills'] ?? null) ? $reg['skills'] : array_map('trim', explode(',', $reg['skills'] ?? 'Figma, Web Development')),
            'contact' => [
                'wa' => $reg['contact_wa'] ?? $siapkerja['wa'],
                'email' => $reg['contact_email'] ?? $siapkerja['email'],
            ],
            'experience' => $reg['previous_projects'] ?? $siapkerja['pengalaman_siapkerja'],
            'portfolio' => $reg['portfolio'] ?? [],
            'reviews' => [],
            'video_url' => $reg['video_url'] ?? '',
        ];
    }

    return null;
}

function gig_stars(int|float $rating): string
{
    $intRating = (int) round((float)$rating);
    if ($intRating < 1) $intRating = 1;
    if ($intRating > 5) $intRating = 5;

    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= ($i <= $intRating) ? '★' : '☆';
    }
    return $html;
}

/**
 * Returns pre-filled profile information from the user's SIAPKerja account.
 */
function gig_get_siapkerja_profile(string $username): array
{
    $formattedName = ucwords(trim($username));
    if (strtolower($username) === 'tessa') {
        $fullName = 'Tessa Kirana';
    } else {
        $fullName = $formattedName;
    }

    return [
        'username'        => $username,
        'nama'            => $fullName,
        'siapkerja_id'    => 'SK-2026-' . strtoupper(substr(md5($username), 0, 6)),
        'nik'             => '317409' . sprintf('%010d', abs(crc32($username) % 10000000000)),
        'email'           => strtolower(str_replace(' ', '.', $fullName)) . '@siapkerja.kemnaker.go.id',
        'wa'              => '0812-3456-7890',
        'lokasi'          => 'Jakarta Selatan, DKI Jakarta',
        'status_akun'     => 'Terverifikasi (KYC Kemnaker RI)',
        'pengalaman_siapkerja' => [
            [
                'role'        => 'UI/UX Designer & Digital Strategist',
                'institution' => 'PT Teknologi Digital Indonesia (SIAPKerja Verified)',
                'period'      => 'Jan 2024 — Agu 2026',
                'summary'     => 'Merancang desain antarmuka dashboard B2B SaaS dan memimpin pengujian ketergunaan bagi 500+ pengguna aktif.'
            ],
            [
                'role'        => 'Frontend & Product Specialist',
                'institution' => 'Proyek Digital KarirHub Kemnaker RI',
                'period'      => 'Mei 2025 — Des 2025',
                'summary'     => 'Mengembangkan komponen antarmuka web responsif dan standar aksesibilitas bagi calon tenaga kerja Indonesia.'
            ]
        ]
    ];
}

/**
 * Checks whether the logged in user has completed Gig Worker registration.
 */
function gig_is_worker_registered(string $username): bool
{
    if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['gig_worker_registered_' . $username])) {
        return true;
    }

    $db = gig_db();
    if ($db !== null) {
        try {
            $stmt = $db->prepare("SELECT 1 FROM `gig_worker_registrations` WHERE `username` = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            if ($stmt->fetch()) {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['gig_worker_registered_' . $username] = true;
                }
                return true;
            }
        } catch (Throwable $e) {
            // fallback check session
        }
    }

    return false;
}

/**
 * Gets registration details for a Gig Worker.
 */
function gig_get_worker_registration(string $username): ?array
{
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['gig_worker_registration_data_' . $username])) {
        return $_SESSION['gig_worker_registration_data_' . $username];
    }

    $db = gig_db();
    if ($db !== null) {
        try {
            $stmt = $db->prepare("SELECT * FROM `gig_worker_registrations` WHERE `username` = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['previous_projects'] = json_decode($row['previous_projects'], true) ?: [];
                $row['portfolio'] = json_decode($row['portfolio'], true) ?: [];
                $row['skills'] = explode(',', $row['skills']);
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['gig_worker_registration_data_' . $username] = $row;
                    $_SESSION['gig_worker_registered_' . $username] = true;
                }
                return $row;
            }
        } catch (Throwable $e) {
            // fallback
        }
    }

    return null;
}

/**
 * Saves a new Gig Worker registration.
 */
function gig_save_worker_registration(string $username, array $data): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['gig_worker_registered_' . $username] = true;
        $_SESSION['gig_worker_registration_data_' . $username] = $data;
    }

    $db = gig_db();
    if ($db !== null) {
        try {
            $stmt = $db->prepare("
                REPLACE INTO `gig_worker_registrations`
                (`username`, `bidang_keahlian`, `skills`, `contact_choice`, `contact_email`, `contact_wa`, `previous_projects`, `portfolio`, `video_url`)
                VALUES (:u, :bidang, :skills, :choice, :email, :wa, :projects, :portfolio, :video)
            ");
            $stmt->execute([
                ':u'        => $username,
                ':bidang'   => $data['bidang_keahlian'] ?? '',
                ':skills'   => is_array($data['skills'] ?? null) ? implode(', ', $data['skills']) : ($data['skills'] ?? ''),
                ':choice'   => $data['contact_choice'] ?? 'siapkerja',
                ':email'    => $data['contact_email'] ?? '',
                ':wa'       => $data['contact_wa'] ?? '',
                ':projects' => json_encode($data['previous_projects'] ?? []),
                ':portfolio'=> json_encode($data['portfolio'] ?? []),
                ':video'    => $data['video_url'] ?? '',
            ]);
        } catch (Throwable $e) {
            // fallback saved to session
        }
    }

    return true;
}


