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
    $endApi = gig_add_duration($hireApi, $apiDuration);
    $endMob = gig_add_duration($hireMob, $mobDuration);
    $endAudit = gig_add_duration($hireAudit, $auditDuration);

    $cdUi = gig_countdown_parts($endUi);
    $cdApi = gig_countdown_parts($endApi);
    $cdMob = gig_countdown_parts($endMob);
    $cdAudit = gig_countdown_parts($endAudit);

    return [
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
            'status_label' => 'Revisi Terakhir',
            'status_badge_class' => 'badge-status active',
            'deliverable_status' => 'UAT & Endpoint Test Selesai',
            'deliverable_note' => 'Menunggu verifikasi rilis resmi',
            'is_completed' => false,
            'countdown_id' => 'countdown-proj-api',
        ],
    ];
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
    $list = array_values(array_filter(
        gig_demo_active_projects(),
        static fn(array $p) => gig_worker_matches_project($p, $username, $email)
    ));
    if ($list === []) {
        return null;
    }
    usort($list, static fn($a, $b) => strcmp((string)$a['deadline_iso'], (string)$b['deadline_iso']));
    return $list[0];
}
