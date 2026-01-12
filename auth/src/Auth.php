<?php
/**
 * auth/src/Auth.php
 *
 * What this file does:
 * - This is the "engine" of the login system.
 *
 * Features:
 * 1) login(email, password)
 *    - Checks user password
 *    - Creates a session (stores token in DB + in PHP session)
 *
 * 2) register(email, password, name)
 *    - Creates a new user
 *    - Assigns default role (like "user")
 *    - Logs the user in
 *
 * 3) user()
 *    - Returns the current logged-in user or null if not logged in
 *    - Also loads roles (admin/user/etc)
 *
 * 4) hasRole(user, roleKey)
 *    - Checks if current user has a role like "admin"
 *
 * 5) logout()
 *    - Removes session from DB and clears PHP session
 *
 * Why we store sessions in the database:
 * - You can force logout by deleting rows in user_sessions.
 * - More secure than only using PHP session id.
 *
 * Security basics included:
 * - Passwords are hashed using password_hash() and verified using password_verify()
 * - Session token is random and stored hashed in DB (so token is not stored in plain text)
 */

declare(strict_types=1);

namespace AuthModule;

use DateTimeImmutable;
use mysqli_sql_exception;

final class Auth
{
    private Database $db;
    private array $config;

    public function __construct(Database $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Step-by-step login:
     * 1) Find user by email
     * 2) Verify password
     * 3) If ok, create a login session
     */
    public function login(string $email, string $password): bool
    {
        $email = strtolower(trim($email));

        $stmt = $this->db->conn()->prepare(
            "SELECT id, password_hash, is_active FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        if (!$row)
            return false;
        if ((int) $row['is_active'] !== 1)
            return false;

        // Verify password against the stored hash
        if (!password_verify($password, (string) $row['password_hash']))
            return false;

        // Create a DB session + PHP session values
        $this->createSession((int) $row['id']);
        return true;
    }

    /**
     * Step-by-step register:
     * 1) Validate email and password length
     * 2) Create user in DB (password stored as hash)
     * 3) Assign default role
     * 4) Create session (auto-login)
     * Returns new user ID.
     */
    public function register(string $email, string $password, ?string $name = null): int
    {
        $email = strtolower(trim($email));
        $name = $name !== null ? trim($name) : null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email');
        }
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }

        $stmt = $this->db->conn()->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existingUser = $stmt->get_result()->fetch_assoc();
        if ($existingUser) {
            throw new \InvalidArgumentException('Email already registered');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->conn()->prepare(
            "INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $email, $hash, $name);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) {
                throw new \InvalidArgumentException('Email already registered');
            }
            throw $e;
        }

        $userId = (int) $this->db->conn()->insert_id;

        // Assign default role (example: "user")
        $defaultRole = (string) ($this->config['app']['default_role_key'] ?? 'user');
        $this->assignRole($userId, $defaultRole);

        // Login immediately
        $this->createSession($userId);

        return $userId;
    }

    /**
     * Get permissions for user based on their roles.
     * Returns array like: ["manage_users", "view_reports"]
     */
    private function permissions(int $userId): array
    {
        $stmt = $this->db->conn()->prepare("
    SELECT DISTINCT p.perm_key
    FROM user_roles ur
    JOIN role_permissions rp ON rp.role_id = ur.role_id
    JOIN permissions p ON p.id = rp.perm_id
    WHERE ur.user_id = ?
    ORDER BY p.perm_key
  ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $res = $stmt->get_result();
        $perms = [];
        while ($row = $res->fetch_assoc()) {
            $perms[] = (string) $row['perm_key'];
        }
        return $perms;
    }

    public function hasPermission(array $user, string $permKey): bool
    {
        $perms = $user['permissions'] ?? [];
        return is_array($perms) && in_array($permKey, $perms, true);
    }

    /**
     * Get current user:
     * - Reads userId + token from PHP session
     * - Validates that token exists in DB and is not expired
     * - Loads roles
     */
    public function user(): ?array
    {
        $userId = $_SESSION['auth_user_id'] ?? null;
        $token = $_SESSION['auth_session_token'] ?? null;

        // If these are missing, not logged in
        if (!(is_int($userId) || (is_string($userId) && ctype_digit($userId))))
            return null;
        if (!is_string($token) || $token === '')
            return null;

        $uid = (int) $userId;

        // Hash token before comparing to DB stored hash
        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->conn()->prepare("
      SELECT u.id, u.email, u.name, u.is_active
      FROM user_sessions s
      JOIN users u ON u.id = s.user_id
      WHERE s.user_id = ?
        AND s.session_token_hash = ?
        AND s.expires_at > NOW()
      LIMIT 1
    ");
        $stmt->bind_param("is", $uid, $tokenHash);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || (int) $user['is_active'] !== 1)
            return null;

        // Update last_seen so you can track activity
        $this->touchSession($uid, $tokenHash);

        // Add roles list into the user array
        $user['roles'] = $this->roles($uid);

        $user['permissions'] = $this->permissions($uid);

        return $user;
    }

    /**
     * Check if a user has a role.
     * Example: $auth->hasRole($user, 'admin')
     */
    public function hasRole(array $user, string $roleKey): bool
    {
        $roles = $user['roles'] ?? [];
        return is_array($roles) && in_array($roleKey, $roles, true);
    }

    /**
     * Logout:
     * 1) Delete this session from DB (so token no longer valid)
     * 2) Clear PHP session values
     */
    public function logout(): void
    {
        $userId = $_SESSION['auth_user_id'] ?? null;
        $token = $_SESSION['auth_session_token'] ?? null;

        if ((is_int($userId) || (is_string($userId) && ctype_digit($userId))) && is_string($token) && $token !== '') {
            $uid = (int) $userId;
            $tokenHash = hash('sha256', $token);

            $stmt = $this->db->conn()->prepare(
                "DELETE FROM user_sessions WHERE user_id = ? AND session_token_hash = ?"
            );
            $stmt->bind_param("is", $uid, $tokenHash);
            $stmt->execute();
        }

        // Clear session in PHP
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Create a session record in DB, and also store session info in PHP session.
     *
     * Steps:
     * 1) Generate random token for this login
     * 2) Store only the HASH of token in DB
     * 3) Store the plain token in PHP session (server-side)
     */
    private function createSession(int $userId): void
    {
        $days = (int) ($this->config['app']['session_days'] ?? 7);

        // Random token used to prove the user is logged in
        $token = bin2hex(random_bytes(32));

        // Store hashed token in DB
        $tokenHash = hash('sha256', $token);

        // Expiry time
        $expiresAt = (new DateTimeImmutable('now'))
            ->modify("+{$days} days")
            ->format('Y-m-d H:i:s');

        // Basic device info
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

        // IP stored as VARBINARY(16)
        $ipBin = $this->ipBinary();

        $stmt = $this->db->conn()->prepare("
      INSERT INTO user_sessions (user_id, session_token_hash, expires_at, last_seen_at, ip, user_agent)
      VALUES (?, ?, ?, NOW(), ?, ?)
    ");
        $stmt->bind_param("issss", $userId, $tokenHash, $expiresAt, $ipBin, $ua);
        $stmt->execute();

        // Save login state in PHP session
        $_SESSION['auth_user_id'] = $userId;
        $_SESSION['auth_session_token'] = $token;

        // Prevent session fixation
        session_regenerate_id(true);
    }

    /**
     * Update last_seen_at for the current session.
     */
    private function touchSession(int $userId, string $tokenHash): void
    {
        $stmt = $this->db->conn()->prepare(
            "UPDATE user_sessions SET last_seen_at = NOW() WHERE user_id = ? AND session_token_hash = ?"
        );
        $stmt->bind_param("is", $userId, $tokenHash);
        $stmt->execute();
    }

    /**
     * Get role keys for a user.
     * Returns array like: ["admin", "user"]
     */
    private function roles(int $userId): array
    {
        $stmt = $this->db->conn()->prepare("
      SELECT r.role_key
      FROM user_roles ur
      JOIN roles r ON r.id = ur.role_id
      WHERE ur.user_id = ?
      ORDER BY r.role_key
    ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $res = $stmt->get_result();
        $roles = [];

        while ($row = $res->fetch_assoc()) {
            $roles[] = (string) $row['role_key'];
        }

        return $roles;
    }

    /**
     * Assign a role to a user (example: "user", "admin").
     * Uses INSERT IGNORE so it does not crash if role already assigned.
     */
    private function assignRole(int $userId, string $roleKey): void
    {
        // Find role id
        $stmt = $this->db->conn()->prepare("SELECT id FROM roles WHERE role_key = ? LIMIT 1");
        $stmt->bind_param("s", $roleKey);
        $stmt->execute();

        $role = $stmt->get_result()->fetch_assoc();
        if (!$role)
            return;

        $roleId = (int) $role['id'];

        // Attach role to user
        $stmt2 = $this->db->conn()->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $userId, $roleId);
        $stmt2->execute();
    }

    /**
     * Convert IP string to binary for VARBINARY(16) storage.
     * - Supports IPv4 and IPv6
     */
    private function ipBinary(): ?string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (!$ip)
            return null;

        $packed = @inet_pton($ip);
        return $packed === false ? null : $packed;
    }
}
