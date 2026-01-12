<?php
/**
 * GET /auth/api/admin/users.php
 * Returns a list of users with their roles and permissions.
 */

declare(strict_types=1);

require_once __DIR__ . '/_admin_init.php';

$dbConn = $db->conn();

$res = $dbConn->query("SELECT id, email, name, is_active FROM users ORDER BY id DESC LIMIT 200");

$users = [];
while ($u = $res->fetch_assoc()) {
    $uid = (int) $u['id'];

    // roles
    $stmt = $dbConn->prepare("
    SELECT r.role_key
    FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE ur.user_id = ?
    ORDER BY r.role_key
  ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $rr = $stmt->get_result();
    $roles = [];
    while ($row = $rr->fetch_assoc())
        $roles[] = (string) $row['role_key'];

    // permissions (derived from roles)
    $stmt2 = $dbConn->prepare("
    SELECT DISTINCT p.perm_key
    FROM user_roles ur
    JOIN role_permissions rp ON rp.role_id = ur.role_id
    JOIN permissions p ON p.id = rp.perm_id
    WHERE ur.user_id = ?
    ORDER BY p.perm_key
  ");
    $stmt2->bind_param("i", $uid);
    $stmt2->execute();
    $pr = $stmt2->get_result();
    $perms = [];
    while ($row = $pr->fetch_assoc())
        $perms[] = (string) $row['perm_key'];

    $users[] = [
        'id' => $uid,
        'email' => (string) $u['email'],
        'name' => (string) ($u['name'] ?? ''),
        'is_active' => (int) $u['is_active'],
        'roles' => $roles,
        'permissions' => $perms,
    ];
}

json_out(['users' => $users]);
