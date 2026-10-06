<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
$pdo = gig_db();
if ($pdo) {
    $pdo->exec("UPDATE `project_extension_requests` SET `status` = 'cancelled' WHERE `status` = 'pending'");
    echo "Successfully updated pending extensions to cancelled.\n";
} else {
    echo "No DB PDO connection.\n";
}
