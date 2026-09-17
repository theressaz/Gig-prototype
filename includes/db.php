<?php
/**
 * Shared database connection and schema bootstrap.
 * Included once per request. Returns a PDO instance or null if DB is offline.
 */
declare(strict_types=1);

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'Gig');

function gig_db(): ?PDO
{
    static $pdo = null;
    static $tried = false;

    if ($tried) return $pdo;
    $tried = true;

    try {
        $serverDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
        $server = new PDO($serverDsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE  => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT  => 2,
        ]);
        $server->exec(
            "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
             CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE        => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT        => 2,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        // --- project_reviews table ---
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `project_reviews` (
                `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `contract_id`          VARCHAR(50)  NOT NULL,
                `worker_id`            VARCHAR(50)  NOT NULL,
                `employer_username`    VARCHAR(100) NOT NULL,
                `project_title`        VARCHAR(255) NOT NULL,
                `overall_rating`       TINYINT UNSIGNED NOT NULL DEFAULT 5,
                `rating_quality`       TINYINT UNSIGNED NOT NULL DEFAULT 5,
                `rating_communication` TINYINT UNSIGNED NOT NULL DEFAULT 5,
                `rating_timeliness`    TINYINT UNSIGNED NOT NULL DEFAULT 5,
                `comment`              TEXT NOT NULL,
                `badges`               TEXT NOT NULL DEFAULT '',
                `recommend_worker`     TINYINT(1) NOT NULL DEFAULT 1,
                `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_contract` (`contract_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // --- project_completions table ---
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `project_completions` (
                `contract_id`     VARCHAR(50)  NOT NULL PRIMARY KEY,
                `worker_id`       VARCHAR(50)  NOT NULL,
                `employer_username` VARCHAR(100) NOT NULL,
                `rating_given`    TINYINT UNSIGNED NOT NULL DEFAULT 5,
                `review_given`    TEXT NOT NULL,
                `completed_date`  DATE NOT NULL,
                `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

    } catch (Throwable $e) {
        $pdo = null;
    }

    return $pdo;
}
