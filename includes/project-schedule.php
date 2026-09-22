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
    $uiDuration = '3 Minggu';
    $apiDuration = '2 Minggu';
    $endUi = gig_add_duration($hireUi, $uiDuration);
    $endApi = gig_add_duration($hireApi, $apiDuration);
    $cdUi = gig_countdown_parts($endUi);
    $cdApi = gig_countdown_parts($endApi);

    return [
        [
            'contract_id' => 'CTR-GIG-2026-0811',
            'id' => 'GIG-2026-09-001',
            'title' => 'Redesign UI/UX Dashboard Prototype KarirHub',
            'employer' => 'PT Talenta Digital Indonesia',
            'employer_category' => 'IT & Software Partner',
            'employer_phone' => '0812-9988-7766',
            'employer_email' => 'hr@talentadigital.co.id',
            'worker_id' => 'tessa',
            'worker_name' => 'Tessa',
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
