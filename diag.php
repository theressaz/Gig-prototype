<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = gig_db();
if ($pdo === null) {
    echo "❌ DB connection FAILED\n"; exit;
}
echo "✅ DB connected\n\n";

// Check tables exist
foreach (['project_reviews', 'project_completions'] as $t) {
    $r = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "Table `$t`: $r row(s)\n";
}

echo "\n--- project_reviews ---\n";
$rows = $pdo->query("SELECT * FROM project_reviews")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "(empty)\n";
} else {
    foreach ($rows as $row) {
        echo "  contract={$row['contract_id']} worker={$row['worker_id']} employer={$row['employer_username']} rating={$row['overall_rating']}\n";
        echo "  comment=" . substr($row['comment'], 0, 80) . "\n";
        echo "  badges={$row['badges']}\n";
        echo "  created_at={$row['created_at']}\n\n";
    }
}

echo "\n--- project_completions ---\n";
$rows = $pdo->query("SELECT * FROM project_completions")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "(empty)\n";
} else {
    foreach ($rows as $row) {
        echo "  contract={$row['contract_id']} worker={$row['worker_id']} rating={$row['rating_given']}\n";
    }
}

echo "\n--- gig_worker_profiles() merge test ---\n";
require_once __DIR__ . '/includes/worker-profiles.php';
$profiles = gig_worker_profiles();
foreach (['tessa','rian','siti','dimas','budi','mega'] as $wid) {
    if (!isset($profiles[$wid])) continue;
    $p = $profiles[$wid];
    echo "{$wid}: rating={$p['rating']} reviews_count={$p['reviews_count']} reviews=".count($p['reviews'])."\n";
    foreach ($p['reviews'] as $rev) {
        echo "   [{$rev['employer']}] {$rev['project']} => ★{$rev['rating']} — ".substr($rev['comment'],0,50)."\n";
    }
}

echo "\n--- SESSION ---\n";
echo "custom_reviews: " . (isset($_SESSION['custom_reviews']) ? json_encode(array_keys($_SESSION['custom_reviews'])) : 'none') . "\n";
echo "completed_projects: " . (isset($_SESSION['completed_projects']) ? json_encode(array_keys($_SESSION['completed_projects'])) : 'none') . "\n";
