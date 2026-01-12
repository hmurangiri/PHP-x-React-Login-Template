<?php
/**
 * auth/api/admin/_admin_init.php
 *
 * PURPOSE:
 * - Shared admin-only initializer.
 * - Ensures user is logged in and has permission "manage_users".
 */

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

$user = $auth->user();
if (!$user)
    json_out(['error' => 'Unauthorized'], 401);

if (!in_array('manage_users', $user['permissions'] ?? [], true)) {
    json_out(['error' => 'Forbidden'], 403);
}
