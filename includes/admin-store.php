<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function gig_admin_ensure_schema(?PDO $pdo = null): void
{
    $pdo = $pdo ?? gig_db();
    if ($pdo === null) {
        return;
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `employer_gig_registrations` (
                `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `siapkerja_email` VARCHAR(150) NOT NULL,
                `company_name`    VARCHAR(150) NOT NULL DEFAULT '',
                `industry`        VARCHAR(150) NOT NULL DEFAULT '',
                `nama_pic`        VARCHAR(150) NOT NULL,
                `nik_pic`         VARCHAR(50)  NOT NULL DEFAULT '',
                `email_pic`       VARCHAR(150) NOT NULL,
                `phone_pic`       VARCHAR(50)  NOT NULL DEFAULT '',
                `status`          VARCHAR(20)  NOT NULL DEFAULT 'pending',
                `admin_note`      TEXT NOT NULL,
                `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `reviewed_at`     DATETIME NULL,
                KEY `idx_status` (`status`),
                KEY `idx_email` (`siapkerja_email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        try {
            $pdo->exec("ALTER TABLE `gig_worker_registrations`
                ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER `video_url`");
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec("ALTER TABLE `gig_worker_registrations`
                ADD COLUMN `admin_note` TEXT NOT NULL AFTER `status`");
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec("ALTER TABLE `gig_worker_registrations`
                ADD COLUMN `reviewed_at` DATETIME NULL AFTER `admin_note`");
        } catch (Throwable $e) {
        }

        $pdo->exec("UPDATE `gig_worker_registrations` SET `status` = 'approved'
            WHERE `username` IN ('Theressa Zaratrusha', 'Tessa', 'theressaz@pasker.id')");

        require_once __DIR__ . '/vacancy-store.php';
        gig_vacancy_ensure_schema($pdo);
    } catch (Throwable $e) {
        // offline / partial schema
    }
}

/** @return list<array<string, mixed>> */
function gig_admin_list_worker_registrations(?string $statusFilter = null): array
{
    gig_admin_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return [];
    }
    $sql = "SELECT * FROM `gig_worker_registrations`";
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
            $row['previous_projects'] = json_decode((string)($row['previous_projects'] ?? ''), true) ?: [];
            $row['portfolio'] = json_decode((string)($row['portfolio'] ?? ''), true) ?: [];
            $row['skills'] = array_filter(array_map('trim', explode(',', (string)($row['skills'] ?? ''))));
        }
        unset($row);
        return $rows;
    } catch (Throwable $e) {
        return [];
    }
}

/** @return list<array<string, mixed>> */
function gig_admin_list_employer_registrations(?string $statusFilter = null): array
{
    gig_admin_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return [];
    }
    $sql = "SELECT * FROM `employer_gig_registrations`";
    $params = [];
    if ($statusFilter !== null && $statusFilter !== '') {
        $sql .= " WHERE `status` = :st";
        $params[':st'] = $statusFilter;
    }
    $sql .= " ORDER BY `created_at` DESC";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

function gig_admin_set_worker_status(string $username, string $status, string $adminNote = ''): bool
{
    gig_admin_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return false;
    }
    $allowed = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }
    try {
        $stmt = $db->prepare(
            "UPDATE `gig_worker_registrations`
             SET `status` = :st, `admin_note` = :note, `reviewed_at` = NOW()
             WHERE `username` = :u"
        );
        $stmt->execute([':st' => $status, ':note' => $adminNote, ':u' => $username]);
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION['gig_worker_registered_' . $username], $_SESSION['gig_worker_registration_data_' . $username]);
        }
        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function gig_admin_set_employer_status(int $id, string $status, string $adminNote = ''): bool
{
    gig_admin_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return false;
    }
    $allowed = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }
    try {
        $stmt = $db->prepare(
            "UPDATE `employer_gig_registrations`
             SET `status` = :st, `admin_note` = :note, `reviewed_at` = NOW()
             WHERE `id` = :id"
        );
        $stmt->execute([':st' => $status, ':note' => $adminNote, ':id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function gig_save_employer_gig_registration(array $data): bool
{
    gig_admin_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return false;
    }
    try {
        $stmt = $db->prepare(
            "INSERT INTO `employer_gig_registrations`
             (`siapkerja_email`, `company_name`, `industry`, `nama_pic`, `nik_pic`, `email_pic`, `phone_pic`, `status`, `admin_note`)
             VALUES (:email, :company, :industry, :nama, :nik, :epic, :phone, 'pending', '')"
        );
        $stmt->execute([
            ':email'   => $data['siapkerja_email'] ?? '',
            ':company' => $data['company_name'] ?? '',
            ':industry'=> $data['industry'] ?? '',
            ':nama'    => $data['nama_pic'] ?? '',
            ':nik'     => $data['nik_pic'] ?? '',
            ':epic'    => $data['email_pic'] ?? '',
            ':phone'   => $data['phone_pic'] ?? '',
        ]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function gig_employer_gig_registration_status(string $siapkerjaEmail): ?string
{
    gig_admin_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return null;
    }
    try {
        $stmt = $db->prepare(
            "SELECT `status` FROM `employer_gig_registrations`
             WHERE `siapkerja_email` = :e ORDER BY `id` DESC LIMIT 1"
        );
        $stmt->execute([':e' => $siapkerjaEmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string)$row['status'] : null;
    } catch (Throwable $e) {
        return null;
    }
}

function gig_worker_registration_status(string $username): ?string
{
    gig_admin_ensure_schema();
    $db = gig_db();
    if ($db === null) {
        return null;
    }
    try {
        $stmt = $db->prepare("SELECT `status` FROM `gig_worker_registrations` WHERE `username` = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string)$row['status'] : null;
    } catch (Throwable $e) {
        return null;
    }
}
