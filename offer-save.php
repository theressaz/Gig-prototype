<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/employer-auth.php';
require_once __DIR__ . '/includes/project-offers.php';
require_once __DIR__ . '/includes/project-applications.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$json = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
$source = is_array($json) ? $json : $_POST;

$workerId = trim((string)($source['worker_id'] ?? ''));
$vacancyId = trim((string)($source['vacancy_id'] ?? ''));
$message = trim((string)($source['message'] ?? ''));

if ($workerId === '' || $vacancyId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Pilih Gig Worker dan proyek yang ditawarkan.']);
    exit;
}

$result = gig_save_offer($username, $workerId, $vacancyId, $message);
if (!empty($result['ok'])) {
    $vacancy = gig_find_vacancy($vacancyId);
    $projTitle = $vacancy['title'] ?? 'Proyek';
    gig_add_worker_notification(
        $workerId,
        'direct_offer',
        '📩 Penawaran Proyek Baru!',
        'Perusahaan ' . $username . ' menawarkan proyek "' . $projTitle . '" secara langsung kepada Anda. Buka menu Penawaran Proyek untuk meninjau detail.'
    );
} else {
    http_response_code(422);
}
echo json_encode($result);
