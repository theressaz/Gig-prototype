<?php
session_start();
$_SESSION['username'] = 'PT Talenta Digital Indonesia';
$_SESSION['role'] = 'employer';

ob_start();
try {
    require __DIR__ . '/../dashboard-employer.php';
    $output = ob_get_clean();
    echo "SUCCESS: Dashboard rendered " . strlen($output) . " bytes.\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
