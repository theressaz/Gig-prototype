<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function gig_vacancy_ensure_schema(?PDO $pdo = null): void
{
    $pdo = $pdo ?? gig_db();
    if ($pdo === null) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `project_vacancy_overrides` (
            `vacancy_id`    VARCHAR(50) NOT NULL PRIMARY KEY,
            `status`        VARCHAR(20) NOT NULL,
            `status_label`  VARCHAR(80) NOT NULL DEFAULT '',
            `admin_note`    TEXT NOT NULL,
            `reviewed_at`   DATETIME NULL,
            `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `project_vacancy_submissions` (
            `id`                VARCHAR(50) NOT NULL PRIMARY KEY,
            `employer_username` VARCHAR(100) NOT NULL,
            `title`             VARCHAR(255) NOT NULL,
            `category`          VARCHAR(100) NOT NULL,
            `status`            VARCHAR(20) NOT NULL DEFAULT 'review',
            `status_label`      VARCHAR(80) NOT NULL DEFAULT 'Menunggu Verifikasi',
            `payload_json`      LONGTEXT NOT NULL,
            `admin_note`        TEXT NOT NULL,
            `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `reviewed_at`       DATETIME NULL,
            KEY `idx_employer` (`employer_username`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

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

function gig_vacancy_save_submission(string $employerUsername, array $vacancy): ?string
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return null;
    }
    $id = 'GIG-' . date('Y-m') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    try {
        $stmt = $db->prepare(
            "INSERT INTO `project_vacancy_submissions`
             (`id`, `employer_username`, `title`, `category`, `status`, `status_label`, `payload_json`, `admin_note`)
             VALUES (:id, :emp, :title, :cat, 'review', 'Menunggu Verifikasi', :payload, '')"
        );
        $stmt->execute([
            ':id'      => $id,
            ':emp'     => $employerUsername,
            ':title'   => $vacancy['title'] ?? '',
            ':cat'     => $vacancy['category'] ?? '',
            ':payload' => json_encode($vacancy, JSON_UNESCAPED_UNICODE),
        ]);
        return $id;
    } catch (Throwable $e) {
        return null;
    }
}

/** @return array<string, array<string, mixed>> */
function gig_vacancy_overrides_map(): array
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return [];
    }
    try {
        $rows = $db->query("SELECT * FROM `project_vacancy_overrides`")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $map = [];
        foreach ($rows as $row) {
            $map[(string)$row['vacancy_id']] = $row;
        }
        return $map;
    } catch (Throwable $e) {
        return [];
    }
}

/** @return list<array<string, mixed>> */
function gig_vacancy_submissions_list(?string $statusFilter = null): array
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return [];
    }
    $sql = "SELECT * FROM `project_vacancy_submissions`";
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
        foreach ($rows as &$row) {
            $row['payload'] = json_decode((string)($row['payload_json'] ?? ''), true) ?: [];
        }
        unset($row);
        return $rows;
    } catch (Throwable $e) {
        return [];
    }
}

function gig_admin_set_vacancy_override(string $vacancyId, string $status, string $adminNote): bool
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return false;
    }
    $labels = gig_vacancy_status_labels();
    $label = $labels[$status] ?? $status;
    try {
        $stmt = $db->prepare(
            "REPLACE INTO `project_vacancy_overrides`
             (`vacancy_id`, `status`, `status_label`, `admin_note`, `reviewed_at`)
             VALUES (:id, :st, :lbl, :note, NOW())"
        );
        $stmt->execute([':id' => $vacancyId, ':st' => $status, ':lbl' => $label, ':note' => $adminNote]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function gig_admin_set_submission_status(string $vacancyId, string $status, string $adminNote): bool
{
    gig_vacancy_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return false;
    }
    $labels = gig_vacancy_status_labels();
    $label = $labels[$status] ?? $status;
    try {
        $stmt = $db->prepare(
            "UPDATE `project_vacancy_submissions`
             SET `status` = :st, `status_label` = :lbl, `admin_note` = :note, `reviewed_at` = NOW()
             WHERE `id` = :id"
        );
        $stmt->execute([':st' => $status, ':lbl' => $label, ':note' => $adminNote, ':id' => $vacancyId]);
        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/** @param list<array<string, mixed>> $baseVacancies */
function gig_vacancy_merge_catalog(array $baseVacancies): array
{
    $overrides = gig_vacancy_overrides_map();
    foreach ($baseVacancies as &$vacancy) {
        $id = (string)($vacancy['id'] ?? '');
        if ($id !== '' && isset($overrides[$id])) {
            $ov = $overrides[$id];
            $vacancy['status'] = $ov['status'];
            $vacancy['statusLabel'] = $ov['status_label'] ?: (gig_vacancy_status_labels()[$ov['status']] ?? $ov['status']);
            $vacancy['adminNote'] = $ov['admin_note'] ?? ($vacancy['adminNote'] ?? '');
            if ($vacancy['status'] !== 'active') {
                $vacancy['applicantsCount'] = 0;
                $vacancy['acceptedCount'] = 0;
            }
        }
    }
    unset($vacancy);

    foreach (gig_vacancy_submissions_list() as $sub) {
        $payload = $sub['payload'] ?? [];
        $budget = (string)($payload['budget'] ?? 'Gaji dapat dinegosiasikan');
        $baseVacancies[] = [
            'id' => $sub['id'],
            'title' => $sub['title'],
            'category' => $sub['category'],
            'status' => $sub['status'],
            'statusLabel' => $sub['status_label'],
            'budget' => $budget,
            'duration' => (string)($payload['duration'] ?? ''),
            'applicantsCount' => $sub['status'] === 'active' ? 0 : 0,
            'acceptedCount' => 0,
            'quota' => (int)($payload['quota'] ?? 1),
            'location' => (string)($payload['location'] ?? 'Remote'),
            'posted' => date('d M Y', strtotime((string)$sub['created_at'])),
            'skills' => $payload['skills'] ?? [],
            'desc' => (string)($payload['desc'] ?? ''),
            'deadline' => (string)($payload['deadline'] ?? ''),
            'adminNote' => (string)($sub['admin_note'] ?? ''),
            'employer' => (string)($sub['employer_username'] ?? ''),
            'deliverables' => (string)($payload['deliverables'] ?? ''),
            'is_submission' => true,
        ];
    }

    return $baseVacancies;
}
