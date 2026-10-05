<?php
declare(strict_types=1);

function gig_id_month_names(): array
{
    return [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
}

function gig_format_id_date(DateTimeInterface $dt): string
{
    $months = gig_id_month_names();
    return $dt->format('d') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
}

function gig_duration_days(string $label): int
{
    $normalized = strtolower(str_replace(',', '.', $label));
    if (preg_match('/(\d+(?:\.\d+)?)\s*minggu/', $normalized, $m)) {
        return (int)round((float)$m[1] * 7);
    }
    if (preg_match('/(\d+(?:\.\d+)?)\s*bulan/', $normalized, $m)) {
        return (int)round((float)$m[1] * 30);
    }
    if (preg_match('/(\d+)\s*hari/', $normalized, $m)) {
        return (int)$m[1];
    }
    return 21;
}

function gig_add_duration(DateTimeInterface $start, string $durationLabel): DateTimeImmutable
{
    $startImm = DateTimeImmutable::createFromInterface($start);
    return $startImm->modify('+' . gig_duration_days($durationLabel) . ' days');
}

function gig_countdown_parts(DateTimeInterface $deadline, ?DateTimeInterface $now = null): array
{
    $nowImm = $now ? DateTimeImmutable::createFromInterface($now) : new DateTimeImmutable('now');
    $end = DateTimeImmutable::createFromInterface($deadline);
    if ($end <= $nowImm) {
        return ['days' => 0, 'hours' => 0, 'mins' => 0, 'secs' => 0, 'expired' => true];
    }
    $diff = $nowImm->diff($end);
    return [
        'days' => (int)$diff->days,
        'hours' => (int)$diff->h,
        'mins' => (int)$diff->i,
        'secs' => (int)$diff->s,
        'expired' => false,
    ];
}

function gig_extension_ensure_table(?PDO $pdo): void
{
    if (!$pdo) {
        return;
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `project_extension_requests` (
            `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `contract_id`      VARCHAR(50) NOT NULL,
            `requester_role`   VARCHAR(20) NOT NULL,
            `requester_id`     VARCHAR(100) NOT NULL DEFAULT '',
            `amount`           INT NOT NULL,
            `unit`             VARCHAR(10) NOT NULL,
            `days_delta`       INT NOT NULL,
            `reason_note`      VARCHAR(500) NOT NULL DEFAULT '',
            `status`           VARCHAR(20) NOT NULL DEFAULT 'pending',
            `decision_by_role` VARCHAR(20) NOT NULL DEFAULT '',
            `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `reviewed_at`      DATETIME NULL,
            KEY `idx_ext_contract` (`contract_id`),
            KEY `idx_ext_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function gig_extension_unit_to_days(int $amount, string $unit): int
{
    $u = strtolower(trim($unit));
    $amount = max(1, $amount);
    if ($u === 'week') return $amount * 7;
    if ($u === 'month') return $amount * 30;
    return $amount;
}

/** @return list<array<string,mixed>> */
function gig_project_extension_requests(string $contractId): array
{
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return [];
    }
    gig_extension_ensure_table($pdo);
    $stmt = $pdo->prepare(
        "SELECT * FROM `project_extension_requests`
         WHERE `contract_id` = :cid
         ORDER BY `id` DESC"
    );
    $stmt->execute([':cid' => $contractId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function gig_project_pending_extension(string $contractId): ?array
{
    foreach (gig_project_extension_requests($contractId) as $row) {
        if (($row['status'] ?? '') === 'pending') {
            return $row;
        }
    }
    return null;
}

function gig_project_total_approved_extension_days(string $contractId): int
{
    $days = 0;
    foreach (gig_project_extension_requests($contractId) as $row) {
        if (($row['status'] ?? '') === 'approved') {
            $days += (int)($row['days_delta'] ?? 0);
        }
    }
    return max(0, $days);
}

function gig_request_project_extension(string $contractId, string $requesterRole, string $requesterId, int $amount, string $unit, string $reason = ''): array
{
    $role = strtolower(trim($requesterRole));
    if (!in_array($role, ['worker', 'employer'], true)) {
        return ['ok' => false, 'error' => 'Peran pengaju tidak valid.'];
    }
    $unit = strtolower(trim($unit));
    if (!in_array($unit, ['day', 'week', 'month'], true)) {
        return ['ok' => false, 'error' => 'Satuan harus hari/minggu/bulan.'];
    }
    if ($amount < 1 || $amount > 12) {
        return ['ok' => false, 'error' => 'Jumlah perpanjangan harus 1-12.'];
    }
    if (gig_project_pending_extension($contractId)) {
        return ['ok' => false, 'error' => 'Masih ada pengajuan perpanjangan yang menunggu konfirmasi.'];
    }

    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return ['ok' => false, 'error' => 'Database tidak tersedia.'];
    }
    gig_extension_ensure_table($pdo);
    $days = gig_extension_unit_to_days($amount, $unit);
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO `project_extension_requests`
             (`contract_id`,`requester_role`,`requester_id`,`amount`,`unit`,`days_delta`,`reason_note`,`status`,`decision_by_role`)
             VALUES (:cid,:role,:rid,:amt,:unit,:days,:reason,'pending','')"
        );
        $stmt->execute([
            ':cid' => $contractId,
            ':role' => $role,
            ':rid' => $requesterId,
            ':amt' => $amount,
            ':unit' => $unit,
            ':days' => $days,
            ':reason' => $reason,
        ]);
        return ['ok' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Gagal menyimpan pengajuan perpanjangan.'];
    }
}

function gig_decide_project_extension(int $requestId, string $deciderRole, bool $approve): array
{
    $role = strtolower(trim($deciderRole));
    if (!in_array($role, ['worker', 'employer'], true)) {
        return ['ok' => false, 'error' => 'Peran konfirmasi tidak valid.'];
    }
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return ['ok' => false, 'error' => 'Database tidak tersedia.'];
    }
    gig_extension_ensure_table($pdo);
    try {
        $stmt = $pdo->prepare("SELECT * FROM `project_extension_requests` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['ok' => false, 'error' => 'Pengajuan tidak ditemukan.'];
        }
        if (($row['status'] ?? '') !== 'pending') {
            return ['ok' => false, 'error' => 'Pengajuan sudah diproses.'];
        }
        if (($row['requester_role'] ?? '') === $role) {
            return ['ok' => false, 'error' => 'Pengaju tidak bisa memproses pengajuan sendiri.'];
        }
        $up = $pdo->prepare(
            "UPDATE `project_extension_requests`
             SET `status` = :st, `decision_by_role` = :role, `reviewed_at` = NOW()
             WHERE `id` = :id"
        );
        $up->execute([
            ':st' => $approve ? 'approved' : 'rejected',
            ':role' => $role,
            ':id' => $requestId,
        ]);
        return ['ok' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Gagal memproses pengajuan.'];
    }
}

function gig_cancel_project_extension(int $requestId, string $requesterRole, string $requesterId): array
{
    $role = strtolower(trim($requesterRole));
    $reqId = strtolower(trim($requesterId));
    if (!in_array($role, ['worker', 'employer'], true)) {
        return ['ok' => false, 'error' => 'Peran pengaju tidak valid.'];
    }
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return ['ok' => false, 'error' => 'Database tidak tersedia.'];
    }
    gig_extension_ensure_table($pdo);
    try {
        $stmt = $pdo->prepare("SELECT * FROM `project_extension_requests` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['ok' => false, 'error' => 'Pengajuan tidak ditemukan.'];
        }
        if (($row['status'] ?? '') !== 'pending') {
            return ['ok' => false, 'error' => 'Pengajuan tidak bisa dibatalkan karena sudah diproses.'];
        }
        if (($row['requester_role'] ?? '') !== $role) {
            return ['ok' => false, 'error' => 'Hanya pengaju yang bisa membatalkan.'];
        }
        $owner = strtolower(trim((string)($row['requester_id'] ?? '')));
        if ($owner !== '' && $reqId !== '' && $owner !== $reqId) {
            return ['ok' => false, 'error' => 'Anda bukan pemilik pengajuan ini.'];
        }

        $up = $pdo->prepare(
            "UPDATE `project_extension_requests`
             SET `status` = 'cancelled', `decision_by_role` = :role, `reviewed_at` = NOW()
             WHERE `id` = :id"
        );
        $up->execute([':role' => $role, ':id' => $requestId]);
        return ['ok' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Gagal membatalkan pengajuan perpanjangan.'];
    }
}

function gig_format_extension_label(int $amount, string $unit): string
{
    if ($unit === 'week') return $amount . ' minggu';
    if ($unit === 'month') return $amount . ' bulan';
    return $amount . ' hari';
}

/**
 * Canonical active demo contracts.
 * Project tenggat = hire date + agreed duration (not the vacancy apply deadline).
 */
function gig_demo_active_projects(): array
{
    $hireUi = new DateTimeImmutable('2026-09-20 11:13:00');
    $hireApi = new DateTimeImmutable('2026-09-19 09:00:00');
    $hireMob = new DateTimeImmutable('2026-09-22 10:00:00');
    $hireAudit = new DateTimeImmutable('2026-09-24 14:00:00');

    $uiDuration = '3 Minggu';
    $apiDuration = '2 Minggu';
    $mobDuration = '2 Minggu';
    $auditDuration = '10 Hari';

    $endUi = gig_add_duration($hireUi, $uiDuration);
    // Requested: lock this project's deadline to Friday, 9 Oct 2026.
    $endApi = new DateTimeImmutable('2026-10-09 17:00:00');
    $endMob = gig_add_duration($hireMob, $mobDuration);
    // Requested: reactivate this project and set deadline to 10 Oct 2026.
    $endAudit = new DateTimeImmutable('2026-10-10 17:00:00');

    $cdUi = gig_countdown_parts($endUi);
    $cdApi = gig_countdown_parts($endApi);
    $cdMob = gig_countdown_parts($endMob);
    $cdAudit = gig_countdown_parts($endAudit);

    $projects = [
        [
            'contract_id' => 'CTR-GIG-2026-0811',
            'id' => 'GIG-2026-09-001',
            'title' => 'Redesign UI/UX Dashboard Prototype KarirHub',
            'employer' => 'PT Talenta Digital Indonesia',
            'employer_category' => 'IT & Software Partner',
            'employer_phone' => '0812-9988-7766',
            'employer_email' => 'hr@talentadigital.co.id',
            'worker_id' => 'theressaz@pasker.id',
            'worker_name' => 'Theressa Zaratrusha',
            'worker_role' => 'Lead UI/UX Designer',
            'budget' => 'Rp 8.500.000',
            'duration' => $uiDuration,
            'hired_at' => $hireUi->format('Y-m-d H:i:s'),
            'hired_label' => gig_format_id_date($hireUi),
            'deadline' => gig_format_id_date($endUi),
            'deadline_iso' => $endUi->format(DateTimeInterface::ATOM),
            'days_left' => $cdUi['days'],
            'hours_left' => $cdUi['hours'],
            'mins_left' => $cdUi['mins'],
            'secs_left' => $cdUi['secs'],
            'progress' => 65,
            'status_label' => 'Sedang Berjalan',
            'status_badge_class' => 'badge-status active',
            'deliverable_status' => 'Review Prototype UI/UX',
            'deliverable_note' => 'Sedang pengujian internal oleh tim Pemberi Kerja',
            'is_completed' => false,
            'countdown_id' => 'countdown-proj-ui',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0905',
            'id' => 'GIG-2026-09-005',
            'title' => 'Desain UI/UX Mobile App E-Commerce UMKM',
            'employer' => 'CV Visual Studio Creative',
            'employer_category' => 'Design & Creative Agency',
            'employer_phone' => '0811-2233-4455',
            'employer_email' => 'project@visualstudio.co.id',
            'worker_id' => 'theressaz@pasker.id',
            'worker_name' => 'Theressa Zaratrusha',
            'worker_role' => 'Lead UI/UX Designer',
            'budget' => 'Rp 6.500.000',
            'duration' => $mobDuration,
            'hired_at' => $hireMob->format('Y-m-d H:i:s'),
            'hired_label' => gig_format_id_date($hireMob),
            'deadline' => gig_format_id_date($endMob),
            'deadline_iso' => $endMob->format(DateTimeInterface::ATOM),
            'days_left' => $cdMob['days'],
            'hours_left' => $cdMob['hours'],
            'mins_left' => $cdMob['mins'],
            'secs_left' => $cdMob['secs'],
            'progress' => 40,
            'status_label' => 'Sedang Berjalan',
            'status_badge_class' => 'badge-status active',
            'deliverable_status' => 'Wireframe & High Fidelity Screens',
            'deliverable_note' => 'Penyusunan alur checkout dan halaman katalog produk',
            'is_completed' => false,
            'countdown_id' => 'countdown-proj-mob',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0912',
            'id' => 'GIG-2026-09-012',
            'title' => 'Audit Design System & Aksesibilitas Web Portal',
            'employer' => 'PT Nusantara Media Technologi',
            'employer_category' => 'Media & Enterprise Tech',
            'employer_phone' => '0815-6677-8899',
            'employer_email' => 'tech@nusantaramedia.id',
            'worker_id' => 'theressaz@pasker.id',
            'worker_name' => 'Theressa Zaratrusha',
            'worker_role' => 'Lead UI/UX Designer',
            'budget' => 'Rp 5.000.000',
            'duration' => $auditDuration,
            'hired_at' => $hireAudit->format('Y-m-d H:i:s'),
            'hired_label' => gig_format_id_date($hireAudit),
            'deadline' => gig_format_id_date($endAudit),
            'deadline_iso' => $endAudit->format(DateTimeInterface::ATOM),
            'days_left' => $cdAudit['days'],
            'hours_left' => $cdAudit['hours'],
            'mins_left' => $cdAudit['mins'],
            'secs_left' => $cdAudit['secs'],
            'progress' => 20,
            'status_label' => 'Sedang Berjalan',
            'status_badge_class' => 'badge-status active',
            'deliverable_status' => 'Evaluasi WCAG 2.1 & Token Warna',
            'deliverable_note' => 'Peninjauan komponen kontras rasio dan responsivitas',
            'is_completed' => false,
            'countdown_id' => 'countdown-proj-audit',
        ],
        [
            'contract_id' => 'CTR-GIG-2026-0819',
            'id' => 'GIG-2026-09-002',
            'title' => 'Integrasi REST API Modul Notifikasi SMS & WhatsApp',
            'employer' => 'PT Solusi Awan Indonesia',
            'employer_category' => 'Cloud & Infrastructure',
            'employer_phone' => '0813-7766-5544',
            'employer_email' => 'tech@solusiawan.co.id',
            'worker_id' => 'rian',
            'worker_name' => 'Rian Ardiansyah',
            'worker_role' => 'Backend API Developer',
            'budget' => 'Rp 6.000.000',
            'duration' => $apiDuration,
            'hired_at' => $hireApi->format('Y-m-d H:i:s'),
            'hired_label' => gig_format_id_date($hireApi),
            'deadline' => gig_format_id_date($endApi),
            'deadline_iso' => $endApi->format(DateTimeInterface::ATOM),
            'days_left' => $cdApi['days'],
            'hours_left' => $cdApi['hours'],
            'mins_left' => $cdApi['mins'],
            'secs_left' => $cdApi['secs'],
            'progress' => 90,
            'status_label' => 'Sedang Berjalan',
            'status_badge_class' => 'badge-status active',
            'deliverable_status' => 'UAT & Endpoint Test Selesai',
            'deliverable_note' => 'Menunggu verifikasi rilis resmi',
            'is_completed' => false,
            'countdown_id' => 'countdown-proj-api',
        ],
    ];

    $completionMap = gig_worker_project_completions_map();
    foreach ($projects as &$proj) {
        $contractId = (string)$proj['contract_id'];
        $baseDeadline = new DateTimeImmutable((string)$proj['deadline_iso']);
        $approvedDays = gig_project_total_approved_extension_days($contractId);
        $pendingReq = gig_project_pending_extension($contractId);
        $finalDeadline = $baseDeadline->modify('+' . $approvedDays . ' days');
        $cd = gig_countdown_parts($finalDeadline);

        $proj['base_deadline_iso'] = $baseDeadline->format(DateTimeInterface::ATOM);
        $proj['approved_extension_days'] = $approvedDays;
        $proj['pending_extension'] = $pendingReq;
        $proj['deadline_iso'] = $finalDeadline->format(DateTimeInterface::ATOM);
        $proj['deadline'] = gig_format_id_date($finalDeadline);
        $proj['days_left'] = $cd['days'];
        $proj['hours_left'] = $cd['hours'];
        $proj['mins_left'] = $cd['mins'];
        $proj['secs_left'] = $cd['secs'];
        $proj['is_expired'] = (bool)$cd['expired'];

        $isCompleted = isset($completionMap[$contractId]);
        if (!$isCompleted && $proj['is_expired']) {
            $proj['status_label'] = 'Tidak Selesai';
            $proj['status_badge_class'] = 'badge-status cancelled';
            $proj['deliverable_note'] = 'Deadline terlewati dan proyek belum diselesaikan.';
        }
    }
    unset($proj);

    return $projects;
}

function gig_deadline_notice_message(string $vacancyId): string
{
    $proj = gig_demo_active_project_by_id($vacancyId);
    if (!$proj) {
        return 'Buka Proyek Aktif untuk melihat countdown tenggat pengerjaan.';
    }
    return 'Proyek "' . $proj['title'] . '" dimulai ' . $proj['hired_label']
        . ' dengan durasi ' . $proj['duration'] . '. Tenggat pengerjaan: '
        . $proj['deadline'] . ' (sisa ' . (int)$proj['days_left'] . ' hari). Buka Proyek Aktif untuk melihat countdown.';
}

function gig_demo_active_project_by_id(string $vacancyOrContractId): ?array
{
    foreach (gig_demo_active_projects() as $proj) {
        if ($proj['id'] === $vacancyOrContractId || $proj['contract_id'] === $vacancyOrContractId) {
            return $proj;
        }
    }
    return null;
}

function gig_demo_soonest_active_project(): ?array
{
    $list = gig_demo_active_projects();
    usort($list, static fn($a, $b) => strcmp((string)$a['deadline_iso'], (string)$b['deadline_iso']));
    return $list[0] ?? null;
}

function gig_worker_matches_project(array $proj, string $username, string $email = ''): bool
{
    $wid = strtolower(trim((string)($proj['worker_id'] ?? '')));
    $u = strtolower(trim($username));
    $e = strtolower(trim($email));
    if ($wid !== '' && ($wid === $u || ($e !== '' && $wid === $e))) {
        return true;
    }
    $legacyWorkerIds = ['theressaz@pasker.id', 'tessa', 'theressa zaratrusha'];
    if (in_array($wid, $legacyWorkerIds, true)) {
        if (in_array($u, $legacyWorkerIds, true) || ($e !== '' && in_array($e, $legacyWorkerIds, true))) {
            return true;
        }
        if (str_contains($u, 'theressa') || str_contains($e, 'theressaz')) {
            return true;
        }
    }
    return false;
}

function gig_worker_soonest_active_project(string $username, string $email = ''): ?array
{
    $list = gig_worker_ongoing_active_projects($username, $email);
    if ($list === []) {
        return null;
    }
    usort($list, static fn($a, $b) => strcmp((string)$a['deadline_iso'], (string)$b['deadline_iso']));
    return $list[0];
}

function gig_worker_project_completions_map(): array
{
    $completed = [];
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if ($pdo !== null) {
        try {
            $stmt = $pdo->query('SELECT `contract_id` FROM `project_completions`');
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $cid) {
                $completed[(string)$cid] = true;
            }
        } catch (Throwable $ignored) {
        }
    }
    if (session_status() === PHP_SESSION_ACTIVE
        && isset($_SESSION['completed_projects'])
        && is_array($_SESSION['completed_projects'])
    ) {
        foreach (array_keys($_SESSION['completed_projects']) as $cid) {
            $completed[(string)$cid] = true;
        }
    }
    return $completed;
}

/** Active contracts for this worker that are not marked complete (matches Proyek Aktif). */
function gig_worker_ongoing_active_projects(string $username, string $email = ''): array
{
    $completed = gig_worker_project_completions_map();
    $out = [];
    foreach (gig_demo_active_projects() as $proj) {
        if (!gig_worker_matches_project($proj, $username, $email)) {
            continue;
        }
        $cId = (string)($proj['contract_id'] ?? '');
        if ($cId !== '' && isset($completed[$cId])) {
            continue;
        }
        // Expired unfinished projects are history-only ("Tidak Selesai"), not active.
        if (!empty($proj['is_expired'])) {
            continue;
        }
        $out[] = $proj;
    }
    return $out;
}

function gig_worker_active_project_count(string $username, string $email = ''): int
{
    return count(gig_worker_ongoing_active_projects($username, $email));
}

function gig_worker_active_partner_count(string $username, string $email = ''): int
{
    $employers = [];
    foreach (gig_worker_ongoing_active_projects($username, $email) as $proj) {
        $emp = trim((string)($proj['employer'] ?? ''));
        if ($emp !== '') {
            $employers[$emp] = true;
        }
    }
    return count($employers);
}
