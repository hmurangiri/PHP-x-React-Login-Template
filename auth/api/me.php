<?php
/**
 * auth/api/me.php
 * Returns current logged-in user (or null).
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

$user = $auth->user();

json_out([
    'user' => $user ? [
        'id' => (int) $user['id'],
        'email' => (string) $user['email'],
        'name' => (string) ($user['name'] ?? ''),
        'roles' => $user['roles'] ?? [],
        'permissions' => $user['permissions'] ?? [],
    ] : null
]);

