<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function gig_vacancy_status_labels(): array
{
    return [
        'draft'    => 'Draft',
        'review'   => 'Menunggu Verifikasi',
        'revision' => 'Perlu Revisi',
        'rejected' => 'Ditolak',
        'active'   => 'Tayang Aktif',
    ];
}

function gig_vacancy_ensure_schema(?PDO $pdo = null): void
{
    $pdo = $pdo ?? gig_db();
    if ($pdo === null) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `project_vacancies` (
            `id`                VARCHAR(50) NOT NULL PRIMARY KEY,
            `employer_username` VARCHAR(100) NOT NULL DEFAULT '',
            `title`             VARCHAR(255) NOT NULL,
            `category`          VARCHAR(100) NOT NULL DEFAULT '',
            `status`            VARCHAR(20) NOT NULL DEFAULT 'review',
            `status_label`      VARCHAR(80) NOT NULL DEFAULT '',
            `admin_note`        TEXT NOT NULL,
            `vacancy_json`      LONGTEXT NOT NULL,
            `source`            VARCHAR(20) NOT NULL DEFAULT 'seed',
            `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `reviewed_at`       DATETIME NULL,
            KEY `idx_status` (`status`),
            KEY `idx_employer` (`employer_username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    gig_vacancy_seed_catalog_if_empty($pdo);
    gig_vacancy_migrate_legacy_tables($pdo);
}

function gig_vacancy_seed_catalog_if_empty(PDO $pdo): void
{
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `project_vacancies`")->fetchColumn();
    if ($count > 0) {
        return;
    }

    require_once __DIR__ . '/project-vacancies.php';
    foreach (gig_project_vacancies_base() as $vacancy) {
        gig_vacancy_upsert_row($pdo, $vacancy, 'seed', false);
    }
}

function gig_vacancy_migrate_legacy_tables(PDO $pdo): void
{
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `project_vacancy_overrides` (
                `vacancy_id` VARCHAR(50) NOT NULL PRIMARY KEY,
                `status` VARCHAR(20) NOT NULL,
                `status_label` VARCHAR(80) NOT NULL DEFAULT '',
                `admin_note` TEXT NOT NULL,
                `reviewed_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $overrides = $pdo->query("SELECT * FROM `project_vacancy_overrides`")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($overrides as $ov) {
            gig_vacancy_set_status((string)$ov['vacancy_id'], (string)$ov['status'], (string)$ov['admin_note'], false);
        }
    } catch (Throwable $e) {
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `project_vacancy_submissions` (
                `id` VARCHAR(50) NOT NULL PRIMARY KEY,
                `employer_username` VARCHAR(100) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `category` VARCHAR(100) NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'review',
                `status_label` VARCHAR(80) NOT NULL DEFAULT 'Menunggu Verifikasi',
                `payload_json` LONGTEXT NOT NULL,
                `admin_note` TEXT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `reviewed_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $subs = $pdo->query("SELECT * FROM `project_vacancy_submissions`")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($subs as $sub) {
            $id = (string)$sub['id'];
            $exists = $pdo->prepare("SELECT 1 FROM `project_vacancies` WHERE `id` = :id LIMIT 1");
            $exists->execute([':id' => $id]);
            if ($exists->fetch()) {
                gig_vacancy_set_status($id, (string)$sub['status'], (string)$sub['admin_note'], false);
                continue;
            }
            $payload = json_decode((string)($sub['payload_json'] ?? ''), true) ?: [];
            $budget = (string)($payload['budget'] ?? 'Gaji dapat dinegosiasikan');
            $vacancy = [
                'id' => $id,
                'title' => (string)$sub['title'],
                'category' => (string)$sub['category'],
                'status' => (string)$sub['status'],
                'statusLabel' => (string)$sub['status_label'],
                'budget' => $budget,
                'duration' => (string)($payload['duration'] ?? ''),
                'applicantsCount' => 0,
                'acceptedCount' => 0,
                'quota' => (int)($payload['quota'] ?? 1),
                'location' => (string)($payload['location'] ?? 'Remote'),
                'posted' => date('d M Y', strtotime((string)$sub['created_at'])),
                'skills' => $payload['skills'] ?? [],
                'desc' => (string)($payload['desc'] ?? ''),
                'deadline' => (string)($payload['deadline'] ?? ''),
                'adminNote' => (string)$sub['admin_note'],
                'employer' => (string)$sub['employer_username'],
                'deliverables' => (string)($payload['deliverables'] ?? ''),
            ];
            gig_vacancy_upsert_row($pdo, $vacancy, 'submission', false);
        }
    } catch (Throwable $e) {
    }
}

/** @param array<string, mixed> $vacancy */
function gig_vacancy_normalize(array $vacancy): array
{
    $labels = gig_vacancy_status_labels();
    $status = (string)($vacancy['status'] ?? 'review');
    $vacancy['status'] = $status;
    $vacancy['statusLabel'] = (string)($vacancy['statusLabel'] ?? ($labels[$status] ?? $status));
    if ($status !== 'active') {
        $vacancy['applicantsCount'] = 0;
        $vacancy['acceptedCount'] = 0;
    }
    return $vacancy;
}

/** @param array<string, mixed> $vacancy */
function gig_vacancy_upsert_row(PDO $pdo, array $vacancy, string $source = 'seed', bool $touchReviewed = false): bool
{
    $vacancy = gig_vacancy_normalize($vacancy);
    $id = (string)($vacancy['id'] ?? '');
    if ($id === '') {
        return false;
    }
    $labels = gig_vacancy_status_labels();
    $status = (string)$vacancy['status'];
    $label = (string)($vacancy['statusLabel'] ?? ($labels[$status] ?? $status));

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO `project_vacancies`
             (`id`, `employer_username`, `title`, `category`, `status`, `status_label`, `admin_note`, `vacancy_json`, `source`, `reviewed_at`)
             VALUES (:id, :emp, :title, :cat, :st, :lbl, :note, :json, :src, :rev)
             ON DUPLICATE KEY UPDATE
               `employer_username` = VALUES(`employer_username`),
               `title` = VALUES(`title`),
               `category` = VALUES(`category`),
               `status` = VALUES(`status`),
               `status_label` = VALUES(`status_label`),
               `admin_note` = VALUES(`admin_note`),
               `vacancy_json` = VALUES(`vacancy_json`),
               `source` = VALUES(`source`),
               `reviewed_at` = COALESCE(VALUES(`reviewed_at`), `reviewed_at`)"
        );
        $stmt->execute([
            ':id'    => $id,
            ':emp'   => (string)($vacancy['employer'] ?? ''),
            ':title' => (string)($vacancy['title'] ?? ''),
            ':cat'   => (string)($vacancy['category'] ?? ''),
            ':st'    => $status,
            ':lbl'   => $label,
            ':note'  => (string)($vacancy['adminNote'] ?? ''),
            ':json'  => json_encode($vacancy, JSON_UNESCAPED_UNICODE),
            ':src'   => $source,
            ':rev'   => $touchReviewed ? date('Y-m-d H:i:s') : null,
        ]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/** @return list<array<string, mixed>> */
function gig_vacancy_load_all(?string $statusFilter = null): array
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return [];
    }
    $sql = "SELECT * FROM `project_vacancies`";
    $params = [];
    if ($statusFilter !== null && $statusFilter !== '') {
        $sql .= " WHERE `status` = :st";
        $params[':st'] = $statusFilter;
    }
    $sql .= " ORDER BY `created_at` DESC";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach ($rows as $row) {
            $vacancy = json_decode((string)($row['vacancy_json'] ?? ''), true);
            if (!is_array($vacancy)) {
                $vacancy = [];
            }
            $vacancy['id'] = (string)$row['id'];
            $vacancy['status'] = (string)$row['status'];
            $vacancy['statusLabel'] = (string)$row['status_label'];
            $vacancy['adminNote'] = (string)$row['admin_note'];
            $vacancy['employer'] = (string)$row['employer_username'];
            $vacancy['db_source'] = (string)$row['source'];
            $out[] = gig_vacancy_normalize($vacancy);
        }
        return $out;
    } catch (Throwable $e) {
        return [];
    }
}

function gig_vacancy_save_submission(string $employerUsername, array $vacancy): ?string
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return null;
    }
    $id = 'GIG-' . date('Y-m') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $budgetRaw = trim((string)($vacancy['budget'] ?? ''));
    $record = [
        'id' => $id,
        'title' => (string)($vacancy['title'] ?? ''),
        'category' => (string)($vacancy['category'] ?? ''),
        'status' => 'review',
        'statusLabel' => 'Menunggu Verifikasi',
        'budget' => $budgetRaw !== '' ? $budgetRaw : 'Gaji dapat dinegosiasikan',
        'duration' => (string)($vacancy['duration'] ?? ''),
        'applicantsCount' => 0,
        'acceptedCount' => 0,
        'quota' => (int)($vacancy['quota'] ?? 1),
        'location' => (string)($vacancy['location'] ?? 'Remote'),
        'posted' => date('d M Y'),
        'skills' => $vacancy['skills'] ?? [],
        'desc' => (string)($vacancy['desc'] ?? ''),
        'deadline' => (string)($vacancy['deadline'] ?? ''),
        'adminNote' => '',
        'employer' => $employerUsername,
        'deliverables' => (string)($vacancy['deliverables'] ?? ''),
    ];
    if (!gig_vacancy_upsert_row($db, $record, 'submission')) {
        return null;
    }

    try {
        $payload = json_encode($vacancy, JSON_UNESCAPED_UNICODE);
        $stmt = $db->prepare(
            "INSERT INTO `project_vacancy_submissions`
             (`id`, `employer_username`, `title`, `category`, `status`, `status_label`, `payload_json`, `admin_note`)
             VALUES (:id, :emp, :title, :cat, 'review', 'Menunggu Verifikasi', :payload, '')
             ON DUPLICATE KEY UPDATE `payload_json` = VALUES(`payload_json`)"
        );
        $stmt->execute([
            ':id' => $id,
            ':emp' => $employerUsername,
            ':title' => $record['title'],
            ':cat' => $record['category'],
            ':payload' => $payload,
        ]);
    } catch (Throwable $e) {
    }

    return $id;
}

function gig_vacancy_set_status(string $vacancyId, string $status, string $adminNote = '', bool $touchReviewed = true): bool
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return false;
    }
    $allowed = ['draft', 'review', 'revision', 'rejected', 'active'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }
    $labels = gig_vacancy_status_labels();
    $label = $labels[$status] ?? $status;

    try {
        $stmt = $db->prepare("SELECT * FROM `project_vacancies` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $vacancyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        $vacancy = json_decode((string)($row['vacancy_json'] ?? ''), true);
        if (!is_array($vacancy)) {
            $vacancy = [];
        }
        $vacancy['id'] = $vacancyId;
        $vacancy['status'] = $status;
        $vacancy['statusLabel'] = $label;
        $vacancy['adminNote'] = $adminNote;
        $vacancy = gig_vacancy_normalize($vacancy);

        if ($touchReviewed) {
            $upd = $db->prepare(
                "UPDATE `project_vacancies`
                 SET `status` = :st, `status_label` = :lbl, `admin_note` = :note,
                     `vacancy_json` = :json, `reviewed_at` = NOW()
                 WHERE `id` = :id"
            );
        } else {
            $upd = $db->prepare(
                "UPDATE `project_vacancies`
                 SET `status` = :st, `status_label` = :lbl, `admin_note` = :note,
                     `vacancy_json` = :json
                 WHERE `id` = :id"
            );
        }
        $upd->execute([
            ':st' => $status,
            ':lbl' => $label,
            ':note' => $adminNote,
            ':json' => json_encode($vacancy, JSON_UNESCAPED_UNICODE),
            ':id' => $vacancyId,
        ]);

        try {
            $sub = $db->prepare(
                "UPDATE `project_vacancy_submissions`
                 SET `status` = :st, `status_label` = :lbl, `admin_note` = :note, `reviewed_at` = NOW()
                 WHERE `id` = :id"
            );
            $sub->execute([':st' => $status, ':lbl' => $label, ':note' => $adminNote, ':id' => $vacancyId]);
        } catch (Throwable $e) {
        }

        return $upd->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/** Backward-compatible aliases used by admin dashboard. */
function gig_admin_set_vacancy_override(string $vacancyId, string $status, string $adminNote): bool
{
    return gig_vacancy_set_status($vacancyId, $status, $adminNote);
}

function gig_admin_set_submission_status(string $vacancyId, string $status, string $adminNote): bool
{
    return gig_vacancy_set_status($vacancyId, $status, $adminNote);
}

/** @return list<array<string, mixed>> */
function gig_admin_list_project_vacancies(?string $statusFilter = null): array
{
    return gig_vacancy_load_all($statusFilter);
}
