<?php
/**
 * Application & Recruitment Lifecycle Management for Gig Workers & Employers.
 * Lifecycle:
 *  1. 'applied'                     : Worker applied for vacancy
 *  2. 'accepted_by_employer'        : Employer accepted applicant, waiting worker confirmation
 *  3. 'confirmed_by_worker'         : Worker confirmed -> Officially Recruited! Active contract created.
 *  4. 'declined_by_worker'          : Worker declined confirmation
 *  5. 'rejected_by_employer'        : Employer rejected applicant
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/project-vacancies.php';

function gig_apps_session_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['gig_applications']) || !is_array($_SESSION['gig_applications'])) {
        $_SESSION['gig_applications'] = [];
    }
    if (!isset($_SESSION['gig_employer_notifications']) || !is_array($_SESSION['gig_employer_notifications'])) {
        $_SESSION['gig_employer_notifications'] = [];
    }
    if (!isset($_SESSION['gig_worker_notifications']) || !is_array($_SESSION['gig_worker_notifications'])) {
        $_SESSION['gig_worker_notifications'] = [];
    }
}

function gig_apps_ensure_tables(?PDO $pdo): void
{
    if (!$pdo) {
        return;
    }
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `project_applications` (
                `id`                 VARCHAR(100) PRIMARY KEY,
                `vacancy_id`         VARCHAR(50)  NOT NULL,
                `worker_id`          VARCHAR(50)  NOT NULL,
                `worker_name`        VARCHAR(100) NOT NULL,
                `employer_username`  VARCHAR(100) NOT NULL,
                `bid_amount`         VARCHAR(50)  NOT NULL DEFAULT '',
                `status`             VARCHAR(40)  NOT NULL DEFAULT 'applied',
                `note`               TEXT,
                `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_app_vac` (`vacancy_id`),
                KEY `idx_app_worker` (`worker_id`),
                KEY `idx_app_emp` (`employer_username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `employer_notifications` (
                `id`                 VARCHAR(100) PRIMARY KEY,
                `employer_username`  VARCHAR(100) NOT NULL,
                `type`               VARCHAR(50)  NOT NULL DEFAULT 'info',
                `title`              VARCHAR(150) NOT NULL,
                `message`            TEXT         NOT NULL,
                `is_read`            TINYINT(1)   NOT NULL DEFAULT 0,
                `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_notif_emp` (`employer_username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `worker_notifications` (
                `id`                 VARCHAR(100) PRIMARY KEY,
                `worker_id`          VARCHAR(50)  NOT NULL,
                `type`               VARCHAR(50)  NOT NULL DEFAULT 'info',
                `title`              VARCHAR(150) NOT NULL,
                `message`            TEXT         NOT NULL,
                `is_read`            TINYINT(1)   NOT NULL DEFAULT 0,
                `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_notif_worker` (`worker_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } catch (Throwable $e) {
        // Table fallback
    }
}

function gig_seed_demo_applications_if_needed(): void
{
    gig_apps_session_start();
    if (!empty($_SESSION['gig_applications_seeded'])) {
        return;
    }

    $defaultApps = [
        [
            'id'                 => 'APP-2026-001',
            'vacancy_id'         => 'GIG-2026-09-001',
            'worker_id'          => 'tessa',
            'worker_name'        => 'Tessa',
            'employer_username'  => 'PT Talenta Digital Indonesia',
            'bid_amount'         => 'Rp 8.500.000',
            'status'             => 'accepted_by_employer', // Waiting worker confirmation
            'note'               => 'Saya memiliki pengalaman 4+ tahun dalam merancang UI/UX dashboard SaaS.',
            'created_at'         => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at'         => date('Y-m-d H:i:s', strtotime('-1 days')),
        ],
        [
            'id'                 => 'APP-2026-002',
            'vacancy_id'         => 'GIG-2026-09-002',
            'worker_id'          => 'rian',
            'worker_name'        => 'Rian Ardiansyah',
            'employer_username'  => 'PT Solusi Awan Indonesia',
            'bid_amount'         => 'Rp 6.000.000',
            'status'             => 'confirmed_by_worker', // Officially recruited
            'note'               => 'Siap mengintegrasikan REST API SMS & WA dengan sertifikasi AWS Backend.',
            'created_at'         => date('Y-m-d H:i:s', strtotime('-4 days')),
            'updated_at'         => date('Y-m-d H:i:s', strtotime('-3 days')),
        ],
        [
            'id'                 => 'APP-2026-003',
            'vacancy_id'         => 'GIG-2026-09-003',
            'worker_id'          => 'fajar',
            'worker_name'        => 'Fajar Pratama',
            'employer_username'  => 'PT Media Digital Nusantara',
            'bid_amount'         => 'Rp 4.500.000',
            'status'             => 'applied', // Applied, waiting employer decision
            'note'               => 'Portofolio kampanye copywriting sosial media dengan engagement rate > 8%.',
            'created_at'         => date('Y-m-d H:i:s', strtotime('-1 day')),
            'updated_at'         => date('Y-m-d H:i:s', strtotime('-1 day')),
        ],
    ];

    foreach ($defaultApps as $app) {
        $_SESSION['gig_applications'][$app['id']] = $app;
    }

    // Default seed notifications for employer
    $_SESSION['gig_employer_notifications'][] = [
        'id'                => 'NOTIF-2026-001',
        'employer_username' => 'PT Talenta Digital Indonesia',
        'type'              => 'worker_accepted',
        'title'             => 'Persetujuan Dikirimkan ke Tessa',
        'message'           => 'Anda telah menyetujui lamaran Tessa untuk proyek "Redesign UI/UX Dashboard Prototype KarirHub". Menunggu konfirmasi akhir dari Tessa.',
        'is_read'           => 0,
        'created_at'        => date('Y-m-d H:i:s', strtotime('-1 day')),
    ];

    $_SESSION['gig_employer_notifications'][] = [
        'id'                => 'NOTIF-2026-002',
        'employer_username' => 'PT Solusi Awan Indonesia',
        'type'              => 'worker_confirmed',
        'title'             => '🎉 Rian Ardiansyah RESMI DIREKRUT!',
        'message'           => 'Rian Ardiansyah telah MENGONFIRMASI dan RESMI DIREKRUT untuk proyek "Integrasi REST API Modul Notifikasi SMS & WhatsApp". Kontrak proyek telah aktif.',
        'is_read'           => 0,
        'created_at'        => date('Y-m-d H:i:s', strtotime('-3 days')),
    ];

    // Default seed notifications for Gig Worker Tessa
    $_SESSION['gig_worker_notifications'][] = [
        'id'         => 'WNOTIF-2026-001',
        'worker_id'  => 'tessa',
        'type'       => 'app_approved',
        'title'      => '🎉 Lamaran Proyek Disetujui!',
        'message'    => 'PT Talenta Digital Indonesia menyetujui lamaran Anda untuk proyek "Redesign UI/UX Dashboard Prototype KarirHub". Harap lakukan konfirmasi ketersediaan Anda di menu Penawaran.',
        'is_read'    => 0,
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
    ];

    $_SESSION['gig_worker_notifications'][] = [
        'id'         => 'WNOTIF-2026-002',
        'worker_id'  => 'tessa',
        'type'       => 'direct_offer',
        'title'      => '📩 Penawaran Proyek Baru!',
        'message'    => 'PT ABC Indonesia menawarkan proyek secara langsung kepada Anda. Buka menu Penawaran Proyek untuk meninjau rincian proyek.',
        'is_read'    => 0,
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
    ];

    $_SESSION['gig_applications_seeded'] = true;
}

function gig_get_all_applications(): array
{
    gig_apps_session_start();
    gig_seed_demo_applications_if_needed();

    $merged = [];
    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $rows = $pdo->query("SELECT * FROM `project_applications` ORDER BY `updated_at` DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $merged[$r['id']] = $r;
            }
        } catch (Throwable $ignored) {}
    }

    foreach ($_SESSION['gig_applications'] as $id => $app) {
        if (!isset($merged[$id])) {
            $merged[$id] = $app;
        }
    }

    return array_values($merged);
}

function gig_get_application_by_id(string $appId): ?array
{
    foreach (gig_get_all_applications() as $app) {
        if ($app['id'] === $appId) {
            return $app;
        }
    }
    return null;
}

function gig_get_applications_for_worker(string $workerId): array
{
    $cleanId = strtolower(trim($workerId));
    $out = [];
    foreach (gig_get_all_applications() as $app) {
        if (strtolower(trim((string)$app['worker_id'])) === $cleanId) {
            $out[] = $app;
        }
    }
    return $out;
}

function gig_get_applications_for_employer(string $employerName): array
{
    $cleanEmp = strtolower(trim($employerName));
    $out = [];
    foreach (gig_get_all_applications() as $app) {
        $empInApp = strtolower(trim((string)$app['employer_username']));
        if ($empInApp === $cleanEmp || str_contains($cleanEmp, $empInApp) || str_contains($empInApp, $cleanEmp)) {
            $out[] = $app;
        }
    }
    return $out;
}

function gig_apply_for_project(string $workerId, string $workerName, string $vacancyId, string $note = ''): array
{
    gig_apps_session_start();
    $vacancy = gig_find_vacancy($vacancyId);
    if (!$vacancy) {
        return ['ok' => false, 'error' => 'Lowongan proyek tidak ditemukan.'];
    }

    // Check if worker already applied
    foreach (gig_get_applications_for_worker($workerId) as $existing) {
        if ($existing['vacancy_id'] === $vacancyId && !in_array($existing['status'], ['rejected_by_employer', 'declined_by_worker'], true)) {
            return ['ok' => false, 'error' => 'Anda sudah mengajukan lamaran untuk proyek ini.'];
        }
    }

    $appId = 'APP-' . date('Ymd') . '-' . rand(1000, 9999);
    $appData = [
        'id'                 => $appId,
        'vacancy_id'         => $vacancyId,
        'worker_id'          => $workerId,
        'worker_name'        => $workerName,
        'employer_username'  => $vacancy['employer'] ?? $vacancy['client'] ?? 'PT Perusahaan',
        'bid_amount'         => $vacancy['budget'] ?? 'Rp 5.000.000',
        'status'             => 'applied',
        'note'               => $note,
        'created_at'         => date('Y-m-d H:i:s'),
        'updated_at'         => date('Y-m-d H:i:s'),
    ];

    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $stmt = $pdo->prepare("
                INSERT INTO `project_applications`
                    (`id`, `vacancy_id`, `worker_id`, `worker_name`, `employer_username`, `bid_amount`, `status`, `note`, `created_at`, `updated_at`)
                VALUES
                    (:id, :vac, :wid, :wname, :emp, :bid, 'applied', :note, NOW(), NOW())
            ");
            $stmt->execute([
                ':id'    => $appData['id'],
                ':vac'   => $appData['vacancy_id'],
                ':wid'   => $appData['worker_id'],
                ':wname' => $appData['worker_name'],
                ':emp'   => $appData['employer_username'],
                ':bid'   => $appData['bid_amount'],
                ':note'  => $appData['note'],
            ]);
        } catch (Throwable $ignored) {}
    }

    $_SESSION['gig_applications'][$appId] = $appData;

    // Notify Employer of new application
    gig_add_employer_notification(
        $appData['employer_username'],
        'new_application',
        '📩 Lamaran Proyek Baru!',
        'Gig Worker ' . $workerName . ' telah mengajukan lamaran untuk proyek "' . ($vacancy['title'] ?? 'Proyek') . '". Kunjungi menu Kandidat untuk meninjau profil.'
    );

    return ['ok' => true, 'application' => $appData];
}

/**
 * Employer decides to accept or reject candidate's application.
 */
function gig_employer_respond_application(string $appId, string $decision, string $employerUsername): array
{
    gig_apps_session_start();
    $app = gig_get_application_by_id($appId);
    if (!$app) {
        return ['ok' => false, 'error' => 'Lamaran tidak ditemukan.'];
    }

    $vacancy = gig_find_vacancy($app['vacancy_id']);
    $projectTitle = $vacancy['title'] ?? 'Proyek';

    $newStatus = ($decision === 'accept') ? 'accepted_by_employer' : 'rejected_by_employer';
    $app['status'] = $newStatus;
    $app['updated_at'] = date('Y-m-d H:i:s');

    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $stmt = $pdo->prepare("UPDATE `project_applications` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id");
            $stmt->execute([':st' => $newStatus, ':id' => $appId]);
        } catch (Throwable $ignored) {}
    }

    $_SESSION['gig_applications'][$appId] = $app;

    if ($decision === 'accept') {
        gig_add_employer_notification(
            $employerUsername,
            'waiting_confirmation',
            '⏳ Menunggu Konfirmasi ' . $app['worker_name'],
            'Persetujuan telah dikirimkan ke ' . $app['worker_name'] . '. Menunggu konfirmasi ketersediaan dari Gig Worker.'
        );

        // NOTIFY WORKER OF APPROVAL
        gig_add_worker_notification(
            $app['worker_id'],
            'app_approved',
            '🎉 Lamaran Proyek Disetujui!',
            'Perusahaan ' . $employerUsername . ' menyetujui lamaran Anda untuk proyek "' . $projectTitle . '". Harap lakukan konfirmasi ketersediaan Anda di menu Penawaran Proyek.'
        );
    } else {
        // NOTIFY WORKER OF REJECTION
        gig_add_worker_notification(
            $app['worker_id'],
            'app_rejected',
            'Lamaran Proyek Belum Disetujui',
            'Lamaran Anda untuk proyek "' . $projectTitle . '" belum dapat disetujui oleh ' . $employerUsername . '.'
        );
    }

    return ['ok' => true, 'application' => $app];
}

/**
 * Gig Worker confirms or declines the accepted project application/offer.
 */
function gig_worker_confirm_application(string $appId, string $action, string $workerId): array
{
    gig_apps_session_start();
    $app = gig_get_application_by_id($appId);
    if (!$app) {
        return ['ok' => false, 'error' => 'Data lamaran/penawaran tidak ditemukan.'];
    }

    $vacancy = gig_find_vacancy($app['vacancy_id']);
    $projectTitle = $vacancy['title'] ?? 'Proyek';

    if ($action === 'confirm') {
        $newStatus = 'confirmed_by_worker';
        $app['status'] = $newStatus;
        $app['updated_at'] = date('Y-m-d H:i:s');

        // Notify Employer that worker officially confirmed & is recruited!
        gig_add_employer_notification(
            $app['employer_username'],
            'worker_confirmed',
            '🎉 ' . $app['worker_name'] . ' RESMI DIREKRUT!',
            'Gig Worker ' . $app['worker_name'] . ' telah MENGONFIRMASI persetujuan dan RESMI DIREKRUT untuk proyek "' . $projectTitle . '"! Kontak komunikasi resmi kini terbuka di Proyek Aktif.'
        );

        // Notify Worker of recruitment confirmation
        gig_add_worker_notification(
            $workerId,
            'recruited',
            '🚀 Resmi Direkrut!',
            'Anda telah MENGONFIRMASI proyek "' . $projectTitle . '" bersama ' . $app['employer_username'] . '. Selamat bekerja! Rincian proyek kini aktif di menu Proyek Aktif.'
        );

    } else {
        $newStatus = 'declined_by_worker';
        $app['status'] = $newStatus;
        $app['updated_at'] = date('Y-m-d H:i:s');

        // Notify Employer that worker declined
        gig_add_employer_notification(
            $app['employer_username'],
            'worker_declined',
            '⚠️ Penawaran Ditolak oleh ' . $app['worker_name'],
            'Gig Worker ' . $app['worker_name'] . ' MENOLAK penawaran/kesepakatan untuk proyek "' . $projectTitle . '". Lowongan proyek tetap dibuka bagi kandidat lain.'
        );

        // Notify Worker of decline confirmation
        gig_add_worker_notification(
            $workerId,
            'declined',
            'Penawaran Ditolak',
            'Anda telah menolak penawaran proyek "' . $projectTitle . '". Pemberi kerja telah diberitahukan.'
        );
    }

    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $stmt = $pdo->prepare("UPDATE `project_applications` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id");
            $stmt->execute([':st' => $newStatus, ':id' => $appId]);
        } catch (Throwable $ignored) {}
    }

    $_SESSION['gig_applications'][$appId] = $app;

    return [
        'ok' => true,
        'action' => $action,
        'application' => $app,
    ];
}

function gig_add_employer_notification(string $employerUsername, string $type, string $title, string $message): void
{
    gig_apps_session_start();
    $notifId = 'NOTIF-' . date('YmdHis') . '-' . rand(100, 999);
    $notif = [
        'id'                => $notifId,
        'employer_username' => $employerUsername,
        'type'              => $type,
        'title'             => $title,
        'message'           => $message,
        'is_read'           => 0,
        'created_at'        => date('Y-m-d H:i:s'),
    ];

    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $stmt = $pdo->prepare("
                INSERT INTO `employer_notifications`
                    (`id`, `employer_username`, `type`, `title`, `message`, `is_read`, `created_at`)
                VALUES
                    (:id, :emp, :type, :title, :msg, 0, NOW())
            ");
            $stmt->execute([
                ':id'    => $notif['id'],
                ':emp'   => $notif['employer_username'],
                ':type'  => $notif['type'],
                ':title' => $notif['title'],
                ':msg'   => $notif['message'],
            ]);
        } catch (Throwable $ignored) {}
    }

    $_SESSION['gig_employer_notifications'][] = $notif;
}

function gig_get_employer_notifications(?string $employerUsername = null): array
{
    gig_apps_session_start();
    gig_seed_demo_applications_if_needed();

    $merged = [];
    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $rows = $pdo->query("SELECT * FROM `employer_notifications` ORDER BY `created_at` DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $merged[$r['id']] = $r;
            }
        } catch (Throwable $ignored) {}
    }

    foreach ($_SESSION['gig_employer_notifications'] as $n) {
        if (!isset($merged[$n['id']])) {
            $merged[$n['id']] = $n;
        }
    }

    $list = array_values($merged);
    usort($list, static fn($a, $b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return $list;
}

function gig_add_worker_notification(string $workerId, string $type, string $title, string $message): void
{
    gig_apps_session_start();
    $cleanId = strtolower(trim($workerId));
    $notifId = 'WNOTIF-' . date('YmdHis') . '-' . rand(100, 999);
    $notif = [
        'id'         => $notifId,
        'worker_id'  => $cleanId,
        'type'       => $type,
        'title'      => $title,
        'message'    => $message,
        'is_read'    => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $stmt = $pdo->prepare("
                INSERT INTO `worker_notifications`
                    (`id`, `worker_id`, `type`, `title`, `message`, `is_read`, `created_at`)
                VALUES
                    (:id, :wid, :type, :title, :msg, 0, NOW())
            ");
            $stmt->execute([
                ':id'    => $notif['id'],
                ':wid'   => $notif['worker_id'],
                ':type'  => $notif['type'],
                ':title' => $notif['title'],
                ':msg'   => $notif['message'],
            ]);
        } catch (Throwable $ignored) {}
    }

    $_SESSION['gig_worker_notifications'][] = $notif;
}

function gig_get_worker_notifications(?string $workerId = null): array
{
    gig_apps_session_start();
    gig_seed_demo_applications_if_needed();

    $cleanId = $workerId ? strtolower(trim($workerId)) : null;
    $merged = [];
    $pdo = gig_db();
    if ($pdo) {
        try {
            gig_apps_ensure_tables($pdo);
            $rows = $pdo->query("SELECT * FROM `worker_notifications` ORDER BY `created_at` DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $merged[$r['id']] = $r;
            }
        } catch (Throwable $ignored) {}
    }

    foreach ($_SESSION['gig_worker_notifications'] as $n) {
        if (!isset($merged[$n['id']])) {
            $merged[$n['id']] = $n;
        }
    }

    $list = array_values($merged);
    if ($cleanId !== null) {
        $list = array_values(array_filter($list, function ($n) use ($cleanId) {
            $w = strtolower(trim((string)($n['worker_id'] ?? '')));
            return $w === $cleanId || str_contains($cleanId, $w) || str_contains($w, $cleanId);
        }));
    }
    usort($list, static fn($a, $b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return array_values($list);
}
