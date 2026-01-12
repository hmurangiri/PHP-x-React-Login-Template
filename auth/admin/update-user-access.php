<?php
/**
 * POST /auth/api/admin/update-user-access.php
 *
 * Body JSON:
 * {
 *   "userId": 12,
 *   "roles": ["admin", "user"],
 *   "permissions": ["manage_users"],
 *   "csrfToken": "..."
 * }
 *
 * IMPORTANT:
 * - Permissions are derived from roles in this design.
 * - So "permissions" input is optional here.
 * - If you want direct user permissions, tell me and we add user_permissions table.
 */

declare(strict_types=1);

require_once __DIR__ . '/_admin_init.php';

use AuthModule\Csrf;

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_out(['error' => 'Method not allowed'], 405);

$body = read_json();
$_POST['csrf_token'] = (string) ($body['csrfToken'] ?? '');
Csrf::verifyOrFail();

$userId = (int) ($body['userId'] ?? 0);
$roles = $body['roles'] ?? [];

if ($userId <= 0)
    json_out(['error' => 'Invalid userId'], 400);
if (!is_array($roles))
    json_out(['error' => 'roles must be an array'], 400);

$dbConn = $db->conn();

// Remove all roles first (simple and safe)
$stmtDel = $dbConn->prepare("DELETE FROM user_roles WHERE user_id = ?");
$stmtDel->bind_param("i", $userId);
$stmtDel->execute();

// Add roles back
$stmtRoleId = $dbConn->prepare("SELECT id FROM roles WHERE role_key = ? LIMIT 1");
$stmtAdd = $dbConn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");

foreach ($roles as $rk) {
    $rk = trim((string) $rk);
    if ($rk === '')
        continue;

    $stmtRoleId->bind_param("s", $rk);
    $stmtRoleId->execute();
    $role = $stmtRoleId->get_result()->fetch_assoc();
    if (!$role)
        continue;

    $rid = (int) $role['id'];
    $stmtAdd->bind_param("ii", $userId, $rid);
    $stmtAdd->execute();
}

json_out(['ok' => true]);
