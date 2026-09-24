<?php
/**
 * Past-project history and reviews persisted in MySQL.
 */
declare(strict_types=1);

function gig_history_worker_key(string $value): string
{
    $cleanId = strtolower(trim($value));
    if ($cleanId === 'theressaz@pasker.id' || $cleanId === 'theressaz' || str_contains($cleanId, 'theressa') || $cleanId === 'tessa' || $cleanId === 'pencaker') {
        return 'tessa';
    }
    if (str_contains($cleanId, 'rian')) return 'rian';
    if (str_contains($cleanId, 'siti')) return 'siti';
    if (str_contains($cleanId, 'budi')) return 'budi';
    if (str_contains($cleanId, 'dimas')) return 'dimas';
    if (str_contains($cleanId, 'mega')) return 'mega';

    $first = explode(' ', $cleanId)[0] ?? $cleanId;
    return strtolower(preg_replace('/[^a-z0-9]+/i', '', $first) ?: $cleanId);
}

function gig_history_ensure_table(?PDO $pdo): void
{
    if (!$pdo) {
        return;
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `project_history` (
            `contract_id`         VARCHAR(50)  NOT NULL PRIMARY KEY,
            `worker_id`           VARCHAR(50)  NOT NULL,
            `worker_name`         VARCHAR(100) NOT NULL DEFAULT '',
            `worker_role`         VARCHAR(150) NOT NULL DEFAULT '',
            `worker_avatar`       VARCHAR(500) NOT NULL DEFAULT '',
            `employer_username`   VARCHAR(100) NOT NULL,
            `project_title`       VARCHAR(255) NOT NULL,
            `status`              VARCHAR(20)  NOT NULL DEFAULT 'completed',
            `budget`              VARCHAR(50)  NOT NULL DEFAULT '',
            `duration`            VARCHAR(50)  NOT NULL DEFAULT '',
            `start_date`          VARCHAR(40)  NOT NULL DEFAULT '',
            `end_date`            VARCHAR(40)  NOT NULL DEFAULT '',
            `summary`             TEXT NOT NULL,
            `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_hist_worker` (`worker_id`),
            KEY `idx_hist_employer` (`employer_username`),
            KEY `idx_hist_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function gig_history_catalog(): array
{
    return [
        // ── TESSA (3 completed, 1 cancelled = 3/4) ──────────────────────────
        [
            'contract_id' => 'CTR-GIG-2026-0815',
            'worker_id' => 'tessa',
            'worker_name' => 'Theressa Zaratrusha',
            'worker_role' => 'Lead UI/UX Designer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Theressa&backgroundColor=dbeafe',
            'employer_username' => 'PT ABC',
            'project_title' => 'Prototype Dashboard Internal',
            'status' => 'completed',
            'budget' => 'Rp 8.000.000',
            'duration' => '3 Minggu',
            'start_date' => '01 Agu 2026',
            'end_date' => '22 Agu 2026',
            'summary' => 'Prototype dashboard internal dan pengujian alur kerja tim pemberi kerja.',
            'rating' => 5,
            'comment' => 'Hasil desain rapi, komunikatif, dan tepat waktu. Prototype mudah diuji tim internal.',
            'completed_at' => '2026-08-22 10:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0531',
            'worker_id' => 'tessa',
            'worker_name' => 'Theressa Zaratrusha',
            'worker_role' => 'Lead UI/UX Designer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Theressa&backgroundColor=dbeafe',
            'employer_username' => 'CV Kreasi Digital',
            'project_title' => 'Redesign Aplikasi Lowongan',
            'status' => 'completed',
            'budget' => 'Rp 7.500.000',
            'duration' => '1 Bulan',
            'start_date' => '01 Mei 2026',
            'end_date' => '31 Mei 2026',
            'summary' => 'Perbaikan alur pencarian lowongan dan uji keterbacaan untuk pengguna baru.',
            'rating' => 5,
            'comment' => 'Sangat memahami kebutuhan pengguna awam. Iterasi cepat setelah umpan balik.',
            'completed_at' => '2026-05-31 16:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0128',
            'worker_id' => 'tessa',
            'worker_name' => 'Theressa Zaratrusha',
            'worker_role' => 'Lead UI/UX Designer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Theressa&backgroundColor=dbeafe',
            'employer_username' => 'Yayasan Kerja Adil',
            'project_title' => 'Landing Page Program Pelatihan',
            'status' => 'completed',
            'budget' => 'Rp 4.500.000',
            'duration' => '3 Minggu',
            'start_date' => '06 Jan 2026',
            'end_date' => '28 Jan 2026',
            'summary' => 'Landing page kampanye pelatihan dan aset visual pendukung.',
            'rating' => 5,
            'comment' => 'Visual konsisten dan aksesibel. Direkomendasikan untuk proyek pemerintahan.',
            'completed_at' => '2026-01-28 11:30:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2025-1120',
            'worker_id' => 'tessa',
            'worker_name' => 'Theressa Zaratrusha',
            'worker_role' => 'Lead UI/UX Designer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Theressa&backgroundColor=dbeafe',
            'employer_username' => 'Startup Ketenagakerjaan',
            'project_title' => 'Aplikasi Pelaporan Pekerja Lepas',
            'status' => 'cancelled',
            'budget' => 'Rp 9.000.000',
            'duration' => '2 Bulan',
            'start_date' => '01 Nov 2025',
            'end_date' => '20 Nov 2025',
            'summary' => 'Kontrak dihentikan bersama karena perubahan ruang lingkup produk internal.',
            'rating' => null,
            'comment' => '',
            'completed_at' => '2025-11-20 09:00:00',
        ],

        // ── RIAN (3 completed, 1 cancelled = 3/4) ───────────────────────────
        [
            'contract_id' => 'CTR-GIG-2026-0720',
            'worker_id' => 'rian',
            'worker_name' => 'Rian Ardiansyah',
            'worker_role' => 'Fullstack / Backend API Developer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Rian&backgroundColor=cffafe',
            'employer_username' => 'PT ABC',
            'project_title' => 'Integrasi Gateway SMS & WhatsApp',
            'status' => 'completed',
            'budget' => 'Rp 6.000.000',
            'duration' => '2 Minggu',
            'start_date' => '01 Jul 2026',
            'end_date' => '20 Jul 2026',
            'summary' => 'Kode bersih, integrasi webhook pesan otomatis dan dokumentasi Postman.',
            'rating' => 5,
            'comment' => 'Kode bersih, dokumentasi lengkap, dan responsif saat UAT.',
            'completed_at' => '2026-07-20 14:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0315',
            'worker_id' => 'rian',
            'worker_name' => 'Rian Ardiansyah',
            'worker_role' => 'Fullstack / Backend API Developer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Rian&backgroundColor=cffafe',
            'employer_username' => 'PT Data Prima',
            'project_title' => 'API Pelaporan Kinerja Realtime',
            'status' => 'completed',
            'budget' => 'Rp 8.500.000',
            'duration' => '1 Bulan',
            'start_date' => '15 Feb 2026',
            'end_date' => '15 Mar 2026',
            'summary' => 'Pengembangan endpoint REST API kinerja high-concurrency.',
            'rating' => 5,
            'comment' => 'Performa stabil di traffic tinggi. Komunikasi teknis jelas.',
            'completed_at' => '2026-03-15 16:30:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2025-1110',
            'worker_id' => 'rian',
            'worker_name' => 'Rian Ardiansyah',
            'worker_role' => 'Fullstack / Backend API Developer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Rian&backgroundColor=cffafe',
            'employer_username' => 'CV Kode Nusantara',
            'project_title' => 'Migrasi Monolith ke REST API',
            'status' => 'completed',
            'budget' => 'Rp 10.000.000',
            'duration' => '2 Bulan',
            'start_date' => '01 Sep 2025',
            'end_date' => '10 Nov 2025',
            'summary' => 'Refactoring modul legacy menjadi arsitektur microservices API.',
            'rating' => 5,
            'comment' => 'Migrasi berjalan tanpa downtime. Timeline terpenuhi.',
            'completed_at' => '2025-11-10 11:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2025-0805',
            'worker_id' => 'rian',
            'worker_name' => 'Rian Ardiansyah',
            'worker_role' => 'Fullstack / Backend API Developer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Rian&backgroundColor=cffafe',
            'employer_username' => 'Koperasi Digital Mandiri',
            'project_title' => 'Payment Reconcile Service',
            'status' => 'cancelled',
            'budget' => 'Rp 7.000.000',
            'duration' => '3 Minggu',
            'start_date' => '15 Jul 2025',
            'end_date' => '05 Agu 2025',
            'summary' => 'Proyek dibatalkan karena penyesuaian skema agregator pembayaran pihak ketiga.',
            'rating' => null,
            'comment' => '',
            'completed_at' => '2025-08-05 10:00:00',
        ],

        // ── SITI (2 completed, 0 cancelled = 2/2) ────────────────────────────
        [
            'contract_id' => 'CTR-GIG-2026-0618',
            'worker_id' => 'siti',
            'worker_name' => 'Siti Nurhaliza',
            'worker_role' => 'Social Media Specialist',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Siti&backgroundColor=ede9fe',
            'employer_username' => 'PT Media Cerah',
            'project_title' => 'Peluncuran Fitur Mobile Campaign',
            'status' => 'completed',
            'budget' => 'Rp 4.500.000',
            'duration' => '1 Bulan',
            'start_date' => '15 Mei 2026',
            'end_date' => '18 Jun 2026',
            'summary' => 'Penyusunan copywriting kampanye dan pembuatan 15 materi carousel.',
            'rating' => 5,
            'comment' => 'Copy tajam dan visual konsisten. Deadline selalu tertahankan.',
            'completed_at' => '2026-06-18 10:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0210',
            'worker_id' => 'siti',
            'worker_name' => 'Siti Nurhaliza',
            'worker_role' => 'Social Media Specialist',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Siti&backgroundColor=ede9fe',
            'employer_username' => 'Kopi Lokal Co.',
            'project_title' => 'Konten Instagram 3 Bulan',
            'status' => 'completed',
            'budget' => 'Rp 5.000.000',
            'duration' => '3 Bulan',
            'start_date' => '01 Nov 2025',
            'end_date' => '10 Feb 2026',
            'summary' => 'Pengelolaan feed dan strategi reels peningkatan engagement.',
            'rating' => 5,
            'comment' => 'Naik follower organik tanpa iklan berlebih. Sangat direkomendasikan.',
            'completed_at' => '2026-02-10 15:00:00',
        ],

        // ── BUDI (2 completed, 1 cancelled = 2/3) ────────────────────────────
        [
            'contract_id' => 'CTR-GIG-2026-0412',
            'worker_id' => 'budi',
            'worker_name' => 'Budi Wicaksono',
            'worker_role' => 'UI Designer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Budi&backgroundColor=d1fae5',
            'employer_username' => 'PT Karya Waktu',
            'project_title' => 'Redesign Absensi Mobile UI',
            'status' => 'completed',
            'budget' => 'Rp 5.500.000',
            'duration' => '1 Bulan',
            'start_date' => '10 Mar 2026',
            'end_date' => '12 Apr 2026',
            'summary' => 'Merapikan 18 layar mobile dan menyusun style guide komponen dasar.',
            'rating' => 4,
            'comment' => 'Cepat dan rapi. Perlu sedikit arahan di awal, hasil akhir memuaskan.',
            'completed_at' => '2026-04-12 16:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2024-1005',
            'worker_id' => 'budi',
            'worker_name' => 'Budi Wicaksono',
            'worker_role' => 'UI Designer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Budi&backgroundColor=d1fae5',
            'employer_username' => 'LPK Mandiri',
            'project_title' => 'Website Lembaga Pelatihan',
            'status' => 'completed',
            'budget' => 'Rp 4.000.000',
            'duration' => '3 Minggu',
            'start_date' => '15 Sep 2024',
            'end_date' => '05 Okt 2024',
            'summary' => 'Desain landing page dan dashboard peserta pelatihan daring.',
            'rating' => 4,
            'comment' => 'Visual bersih. Revisi ditangani dengan baik.',
            'completed_at' => '2024-10-05 12:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0305',
            'worker_id' => 'budi',
            'worker_name' => 'Budi Wicaksono',
            'worker_role' => 'UI Designer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Budi&backgroundColor=d1fae5',
            'employer_username' => 'PT ABC',
            'project_title' => 'Migrasi Database Legacy ke PostgreSQL',
            'status' => 'cancelled',
            'budget' => 'Rp 5.000.000',
            'duration' => '2 Minggu',
            'start_date' => '01 Mar 2026',
            'end_date' => '08 Mar 2026',
            'summary' => 'Proyek dibatalkan secara bersama karena perubahan spesifikasi arsitektur internal.',
            'rating' => null,
            'comment' => '',
            'completed_at' => '2026-03-08 12:00:00',
        ],

        // ── DIMAS (2 completed, 1 cancelled = 2/3) ───────────────────────────
        [
            'contract_id' => 'CTR-GIG-2026-0810',
            'worker_id' => 'dimas',
            'worker_name' => 'Dimas Prasetyo',
            'worker_role' => 'Cloud API Engineer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Dimas&backgroundColor=ffedd5',
            'employer_username' => 'PT Cloud Nusantara',
            'project_title' => 'Adapter Notifikasi Multi-Gateway',
            'status' => 'completed',
            'budget' => 'Rp 6.500.000',
            'duration' => '3 Minggu',
            'start_date' => '15 Jul 2026',
            'end_date' => '10 Agu 2026',
            'summary' => 'Membangun microservice adapter Node.js dan dokumentasi runbook.',
            'rating' => 5,
            'comment' => 'Sangat andal. Dokumentasi operasi memudahkan tim internal.',
            'completed_at' => '2026-08-10 17:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2025-1215',
            'worker_id' => 'dimas',
            'worker_name' => 'Dimas Prasetyo',
            'worker_role' => 'Cloud API Engineer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Dimas&backgroundColor=ffedd5',
            'employer_username' => 'PT Logis Cepat',
            'project_title' => 'Event Pipeline & Queue Worker',
            'status' => 'completed',
            'budget' => 'Rp 8.000.000',
            'duration' => '1 Bulan',
            'start_date' => '10 Nov 2025',
            'end_date' => '15 Des 2025',
            'summary' => 'Pengembangan antrian Redis dan worker Node.js 2M event/hari.',
            'rating' => 5,
            'comment' => 'Performa sesuai janji. Komunikasi teknis ringkas dan jelas.',
            'completed_at' => '2025-12-15 14:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0512',
            'worker_id' => 'dimas',
            'worker_name' => 'Dimas Prasetyo',
            'worker_role' => 'Cloud API Engineer',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Dimas&backgroundColor=ffedd5',
            'employer_username' => 'PT ABC',
            'project_title' => 'Audit Keamanan & PenTesting Microservice Gateway',
            'status' => 'cancelled',
            'budget' => 'Rp 7.000.000',
            'duration' => '2 Minggu',
            'start_date' => '10 Mei 2026',
            'end_date' => '24 Mei 2026',
            'summary' => 'Proyek dihentikan di pertengahan jalan karena pergeseran prioritas tim pengembang.',
            'rating' => null,
            'comment' => '',
            'completed_at' => '2026-05-24 15:00:00',
        ],

        // ── MEGA (2 completed, 0 cancelled = 2/2) ────────────────────────────
        [
            'contract_id' => 'CTR-GIG-2026-0715',
            'worker_id' => 'mega',
            'worker_name' => 'Mega Lestari',
            'worker_role' => 'Digital Campaign Strategist',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Mega&backgroundColor=fce7f3',
            'employer_username' => 'BUMN Karya Digital',
            'project_title' => 'Kampanye Program Magang Nasional',
            'status' => 'completed',
            'budget' => 'Rp 5.000.000',
            'duration' => '1 Bulan',
            'start_date' => '10 Jun 2026',
            'end_date' => '15 Jul 2026',
            'summary' => 'Key message, 15 aset visual, dan laporan performa kampanye 30 hari.',
            'rating' => 5,
            'comment' => 'Narasi kuat dan terukur. Tim internal mudah mengeksekusi asetnya.',
            'completed_at' => '2026-07-15 11:00:00',
        ],
        [
            'contract_id' => 'CTR-GIG-2025-0320',
            'worker_id' => 'mega',
            'worker_name' => 'Mega Lestari',
            'worker_role' => 'Digital Campaign Strategist',
            'worker_avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Mega&backgroundColor=fce7f3',
            'employer_username' => 'Portal Karir Jateng',
            'project_title' => 'Konten SEO Karir & Edukasi',
            'status' => 'completed',
            'budget' => 'Rp 4.500.000',
            'duration' => '6 Bulan',
            'start_date' => '01 Sep 2024',
            'end_date' => '20 Mar 2025',
            'summary' => 'Penulisan 24 artikel long-form SEO karir dan optimasi kata kunci.',
            'rating' => 5,
            'comment' => 'Trafik naik signifikan. Penulisan tetap ramah pembaca awam.',
            'completed_at' => '2025-03-20 16:00:00',
        ],
    ];
}

function gig_seed_project_history(?PDO $pdo): void
{
    if (!$pdo) {
        return;
    }
    static $seeded = false;
    if ($seeded) {
        return;
    }
    $seeded = true;

    gig_history_ensure_table($pdo);

    $hist = $pdo->prepare(
        "INSERT IGNORE INTO `project_history`
            (`contract_id`, `worker_id`, `worker_name`, `worker_role`, `worker_avatar`,
             `employer_username`, `project_title`, `status`, `budget`, `duration`,
             `start_date`, `end_date`, `summary`, `created_at`)
         VALUES
            (:cid, :wid, :wname, :wrole, :avatar, :emp, :title, :status, :budget, :duration,
             :start, :end, :summary, :created)"
    );
    $rev = $pdo->prepare(
        "INSERT IGNORE INTO `project_reviews`
            (`contract_id`, `worker_id`, `employer_username`, `project_title`,
             `overall_rating`, `rating_quality`, `rating_communication`, `rating_timeliness`,
             `comment`, `badges`, `recommend_worker`, `created_at`)
         VALUES
            (:cid, :wid, :emp, :title, :rating, :rating, :rating, :rating, :comment, '', 1, :created)"
    );
    $comp = $pdo->prepare(
        "INSERT IGNORE INTO `project_completions`
            (`contract_id`, `worker_id`, `employer_username`, `rating_given`, `review_given`, `completed_date`, `created_at`)
         VALUES
            (:cid, :wid, :emp, :rating, :comment, :cdate, :created)"
    );

    foreach (gig_history_catalog() as $row) {
        $hist->execute([
            ':cid' => $row['contract_id'],
            ':wid' => $row['worker_id'],
            ':wname' => $row['worker_name'],
            ':wrole' => $row['worker_role'],
            ':avatar' => $row['worker_avatar'],
            ':emp' => $row['employer_username'],
            ':title' => $row['project_title'],
            ':status' => $row['status'],
            ':budget' => $row['budget'],
            ':duration' => $row['duration'],
            ':start' => $row['start_date'],
            ':end' => $row['end_date'],
            ':summary' => $row['summary'],
            ':created' => $row['completed_at'],
        ]);

        if ($row['status'] !== 'completed' || $row['rating'] === null) {
            continue;
        }
        $rev->execute([
            ':cid' => $row['contract_id'],
            ':wid' => $row['worker_id'],
            ':emp' => $row['employer_username'],
            ':title' => $row['project_title'],
            ':rating' => (int)$row['rating'],
            ':comment' => $row['comment'],
            ':created' => $row['completed_at'],
        ]);
        $comp->execute([
            ':cid' => $row['contract_id'],
            ':wid' => $row['worker_id'],
            ':emp' => $row['employer_username'],
            ':rating' => (int)$row['rating'],
            ':comment' => $row['comment'],
            ':cdate' => substr($row['completed_at'], 0, 10),
            ':created' => $row['completed_at'],
        ]);
    }

    $pdo->exec("UPDATE `project_history` SET `worker_name` = 'Theressa Zaratrusha' WHERE `worker_id` = 'tessa' OR `worker_name` = 'Tessa'");
}

function gig_upsert_project_history(array $row): void
{
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return;
    }
    gig_history_ensure_table($pdo);
    $stmt = $pdo->prepare(
        "INSERT INTO `project_history`
            (`contract_id`, `worker_id`, `worker_name`, `worker_role`, `worker_avatar`,
             `employer_username`, `project_title`, `status`, `budget`, `duration`,
             `start_date`, `end_date`, `summary`)
         VALUES
            (:cid, :wid, :wname, :wrole, :avatar, :emp, :title, :status, :budget, :duration,
             :start, :end, :summary)
         ON DUPLICATE KEY UPDATE
            `status` = VALUES(`status`),
            `summary` = VALUES(`summary`),
            `end_date` = VALUES(`end_date`)"
    );
    $stmt->execute([
        ':cid' => $row['contract_id'],
        ':wid' => $row['worker_id'],
        ':wname' => $row['worker_name'] ?? '',
        ':wrole' => $row['worker_role'] ?? '',
        ':avatar' => $row['worker_avatar'] ?? '',
        ':emp' => $row['employer_username'],
        ':title' => $row['project_title'],
        ':status' => $row['status'] ?? 'completed',
        ':budget' => $row['budget'] ?? '',
        ':duration' => $row['duration'] ?? '',
        ':start' => $row['start_date'] ?? '',
        ':end' => $row['end_date'] ?? date('d M Y'),
        ':summary' => $row['summary'] ?? 'Proyek telah selesai dikerjakan.',
    ]);
}

function gig_history_map_row(array $row): array
{
    $status = (string)($row['status'] ?? 'completed');
    $workerId = (string)($row['worker_id'] ?? '');
    $workerName = (string)($row['worker_name'] ?? '');

    if (function_exists('gig_find_worker') && $workerId !== '') {
        $workerProfile = gig_find_worker($workerId);
        if (!empty($workerProfile['name'])) {
            $workerName = $workerProfile['name'];
        }
    }
    if ($workerId === 'tessa' || $workerName === 'Tessa') {
        $workerName = 'Theressa Zaratrusha';
    }

    return [
        'id' => $row['contract_id'],
        'title' => $row['project_title'],
        'status' => $status === 'cancelled' ? 'Tidak Selesai' : 'Selesai',
        'statusCode' => $status === 'cancelled' ? 'cancelled' : 'completed',
        'worker' => $workerName,
        'workerRole' => $row['worker_role'] ?? '',
        'workerId' => $workerId,
        'workerAvatar' => $row['worker_avatar'] ?? '',
        'employer' => $row['employer_username'],
        'duration' => $row['duration'] ?? '',
        'startDate' => $row['start_date'] ?? '',
        'endDate' => $row['end_date'] ?? '',
        'budget' => $row['budget'] ?? '',
        'ratingGiven' => isset($row['overall_rating']) && $row['overall_rating'] !== null ? (int)$row['overall_rating'] : null,
        'reviewGiven' => $row['comment'] ?? null,
        'summary' => $row['summary'] ?? '',
        'createdAt' => $row['created_at'] ?? '',
    ];
}

function gig_history_from_catalog(string $column, string $value): array
{
    $out = [];
    foreach (gig_history_catalog() as $row) {
        $match = $column === 'worker_id'
            ? ($row['worker_id'] === $value)
            : strcasecmp((string)$row['employer_username'], $value) === 0;
        if (!$match) {
            continue;
        }
        $mapped = gig_history_map_row([
            'contract_id' => $row['contract_id'],
            'project_title' => $row['project_title'],
            'status' => $row['status'],
            'worker_name' => $row['worker_name'],
            'worker_role' => $row['worker_role'],
            'worker_id' => $row['worker_id'],
            'worker_avatar' => $row['worker_avatar'],
            'employer_username' => $row['employer_username'],
            'duration' => $row['duration'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'budget' => $row['budget'],
            'overall_rating' => $row['rating'],
            'comment' => $row['comment'],
            'summary' => $row['summary'],
            'created_at' => $row['completed_at'],
        ]);
        $out[] = $mapped;
    }
    return $out;
}

function gig_fetch_history_rows(string $column, string $value): array
{
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return gig_history_from_catalog($column, $value);
    }
    gig_seed_project_history($pdo);

    $allowed = ['worker_id' => true, 'employer_username' => true];
    if (!isset($allowed[$column])) {
        return [];
    }

    $stmt = $pdo->prepare(
        "SELECT h.*, r.`overall_rating`, r.`comment`
         FROM `project_history` h
         LEFT JOIN `project_reviews` r ON r.`contract_id` = h.`contract_id`
         WHERE h.`$column` = :val
           AND h.`status` IN ('completed', 'cancelled')
         ORDER BY h.`created_at` DESC"
    );
    $stmt->execute([':val' => $value]);
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $out[] = gig_history_map_row($row);
    }
    return $out;
}

function gig_get_reviews_for_worker(string $username): array
{
    $pdo = function_exists('gig_db') ? gig_db() : null;
    $wid = gig_history_worker_key($username);
    if (!$pdo) {
        $out = [];
        foreach (gig_history_catalog() as $row) {
            if ($row['worker_id'] !== $wid || $row['status'] !== 'completed' || $row['rating'] === null) {
                continue;
            }
            $out[] = [
                'contractId' => $row['contract_id'],
                'employer' => $row['employer_username'],
                'project' => $row['project_title'],
                'rating' => (int)$row['rating'],
                'date' => date('M Y', strtotime($row['completed_at'])),
                'comment' => $row['comment'],
                'badges' => [],
            ];
        }
        return $out;
    }
    gig_seed_project_history($pdo);
    $stmt = $pdo->prepare(
        "SELECT `contract_id`, `employer_username`, `project_title`, `overall_rating`, `comment`, `badges`, `created_at`
         FROM `project_reviews`
         WHERE `worker_id` = :wid
         ORDER BY `created_at` DESC"
    );
    $stmt->execute([':wid' => $wid]);
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $out[] = [
            'contractId' => $row['contract_id'],
            'employer' => $row['employer_username'],
            'project' => $row['project_title'],
            'rating' => (int)$row['overall_rating'],
            'date' => date('M Y', strtotime((string)$row['created_at'])),
            'comment' => $row['comment'],
            'badges' => $row['badges'] !== '' ? explode('||', (string)$row['badges']) : [],
        ];
    }
    return $out;
}

function gig_get_history_for_worker(string $username): array
{
    return gig_fetch_history_rows('worker_id', gig_history_worker_key($username));
}

function gig_get_history_for_employer(string $username): array
{
    return gig_fetch_history_rows('employer_username', $username);
}
