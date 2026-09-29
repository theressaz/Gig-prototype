<?php
declare(strict_types=1);

/**
 * Persisted avatar URLs so the same user looks identical across KarirHub pages.
 */

function gig_avatar_ensure_schema(?PDO $pdo = null): void
{
    $pdo = $pdo ?? (function_exists('gig_db') ? gig_db() : null);
    if (!$pdo) {
        return;
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_avatars` (
            `account_key`  VARCHAR(150) NOT NULL PRIMARY KEY,
            `avatar_url`   VARCHAR(500) NOT NULL,
            `role`         VARCHAR(20)  NOT NULL DEFAULT 'worker',
            `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function gig_avatar_account_key(?string $override = null): string
{
    if ($override !== null && trim($override) !== '') {
        return strtolower(trim($override));
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        $email = trim((string)($_SESSION['siapkerja_email'] ?? ''));
        if ($email !== '') {
            return strtolower($email);
        }
        $user = trim((string)($_SESSION['username'] ?? ''));
        if ($user !== '') {
            return strtolower($user);
        }
    }
    return '';
}

function gig_avatar_worker_profile_id(string $accountKey): string
{
    $key = strtolower(trim($accountKey));
    if ($key === 'theressaz@pasker.id' || $key === 'tessa' || str_contains($key, 'theressaz')) {
        return 'tessa';
    }
    if (function_exists('gig_find_worker')) {
        $byEmail = gig_find_worker($key);
        if ($byEmail !== null) {
            return (string)($byEmail['id'] ?? $key);
        }
    }
    $local = explode('@', $key)[0] ?? $key;
    return preg_replace('/[^a-z0-9]+/i', '', $local) ?: $key;
}

function gig_avatar_preset_photo(string $profileId): string
{
    $profileId = strtolower(trim($profileId));
    if ($profileId === 'theressaz@pasker.id' || str_contains($profileId, 'theressa')) {
        $profileId = 'tessa';
    }
    if (!function_exists('gig_worker_profiles')) {
        require_once __DIR__ . '/worker-profiles.php';
    }
    $profiles = gig_worker_profiles();
    if (isset($profiles[$profileId]['photo']) && (string)$profiles[$profileId]['photo'] !== '') {
        return (string)$profiles[$profileId]['photo'];
    }
    return 'https://api.dicebear.com/9.x/notionists/svg?seed='
        . rawurlencode($profileId)
        . '&backgroundColor=dbeafe';
}

function gig_avatar_default_url(string $accountKey, string $role = 'worker'): string
{
    $role = strtolower($role);
    if ($role === 'employer') {
        return 'https://api.dicebear.com/9.x/shapes/svg?seed=' . rawurlencode($accountKey);
    }

    return gig_avatar_preset_photo(gig_avatar_worker_profile_id($accountKey));
}

/** Demo / preset workers: keep DB avatar aligned with public profile photo. */
function gig_avatar_force_preset_sync(string $accountKey): bool
{
    $key = strtolower(trim($accountKey));
    return in_array($key, ['theressaz@pasker.id', 'tessa'], true);
}

function gig_get_saved_avatar(string $accountKey): ?string
{
    $accountKey = strtolower(trim($accountKey));
    if ($accountKey === '') {
        return null;
    }
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return null;
    }
    gig_avatar_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT `avatar_url` FROM `user_avatars` WHERE `account_key` = :k LIMIT 1');
    $stmt->execute([':k' => $accountKey]);
    $url = $stmt->fetchColumn();
    return is_string($url) && $url !== '' ? $url : null;
}

function gig_save_user_avatar(string $accountKey, string $avatarUrl, string $role = 'worker'): void
{
    $accountKey = strtolower(trim($accountKey));
    $avatarUrl = trim($avatarUrl);
    if ($accountKey === '' || $avatarUrl === '') {
        return;
    }
    $pdo = function_exists('gig_db') ? gig_db() : null;
    if (!$pdo) {
        return;
    }
    gig_avatar_ensure_schema($pdo);
    $stmt = $pdo->prepare("
        INSERT INTO `user_avatars` (`account_key`, `avatar_url`, `role`)
        VALUES (:k, :url, :role)
        ON DUPLICATE KEY UPDATE `avatar_url` = VALUES(`avatar_url`), `role` = VALUES(`role`)
    ");
    $stmt->execute([
        ':k' => $accountKey,
        ':url' => $avatarUrl,
        ':role' => strtolower($role),
    ]);
}

function gig_bootstrap_user_avatar(?string $accountKey = null, string $role = 'worker'): string
{
    $accountKey = gig_avatar_account_key($accountKey);
    if ($accountKey === '') {
        return gig_avatar_default_url('guest', $role);
    }

    $canonical = gig_avatar_default_url($accountKey, $role);
    $saved = gig_get_saved_avatar($accountKey);
    if ($saved !== null && $saved === $canonical) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['avatar_url'] = $saved;
        }
        return $saved;
    }

    if ($saved !== null && !gig_avatar_force_preset_sync($accountKey)) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['avatar_url'] = $saved;
        }
        return $saved;
    }

    gig_save_user_avatar($accountKey, $canonical, $role);
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['avatar_url'] = $canonical;
    }
    return $canonical;
}

function gig_resolve_user_avatar(?string $accountKey = null, string $role = 'worker'): string
{
    $accountKey = gig_avatar_account_key($accountKey);
    if ($accountKey === '') {
        return gig_avatar_default_url('guest', $role);
    }

    return gig_bootstrap_user_avatar($accountKey, $role);
}

/** Single avatar URL for a logged-in Gig Worker (navbar, profil sendiri, Karirhub). */
function gig_worker_display_photo(?string $profileId = null, ?string $accountKey = null): string
{
    $accountKey = gig_avatar_account_key($accountKey);
    $profileId = $profileId ?? gig_avatar_worker_profile_id($accountKey !== '' ? $accountKey : 'tessa');
    $url = gig_resolve_user_avatar($accountKey !== '' ? $accountKey : null, 'worker');

    foreach (gig_avatar_account_keys_for_worker_id($profileId) as $aliasKey) {
        gig_save_user_avatar($aliasKey, $url, 'worker');
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['avatar_url'] = $url;
    }

    return $url;
}

/** Apply saved avatar to a worker profile array when it belongs to the logged-in account. */
function gig_avatar_account_keys_for_worker_id(string $profileId): array
{
    $id = strtolower(trim($profileId));
    $keys = [$id];
    if ($id === 'tessa' || $id === 'theressaz@pasker.id') {
        $keys[] = 'theressaz@pasker.id';
        $keys[] = 'tessa';
    }
    return array_values(array_unique($keys));
}

function gig_avatar_url_for_worker_id(string $profileId): ?string
{
    foreach (gig_avatar_account_keys_for_worker_id($profileId) as $key) {
        $saved = gig_get_saved_avatar($key);
        if ($saved !== null) {
            return $saved;
        }
    }
    return null;
}
