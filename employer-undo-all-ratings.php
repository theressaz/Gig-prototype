<?php
/**
 * One-shot script to undo ALL ratings stored in this session and in the DB.
 * Opens in browser once, then redirects to the active projects page.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/db.php';

// Clear DB records for this employer
$pdo = gig_db();
if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare("DELETE FROM `project_reviews`      WHERE `employer_username` = :emp");
        $stmt->execute([':emp' => $username]);
        $stmt = $pdo->prepare("DELETE FROM `project_completions`  WHERE `employer_username` = :emp");
        $stmt->execute([':emp' => $username]);
    } catch (Throwable $e) {
        // silently ignore
    }
}

// Clear session cache
unset($_SESSION['completed_projects'], $_SESSION['custom_reviews']);

header('Location: employer-proyek-aktif.php');
exit;
