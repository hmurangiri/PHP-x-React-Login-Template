/**
 * auth/install.sql (place the SQL below into this file)
 *
 * What this file does:
 * - Creates all tables needed for login + roles + sessions.
 *
 * Tables explained simply:
 * - users: stores user account info (email, password hash).
 * - roles: stores available roles like "admin", "user".
 * - user_roles: connects users to roles (many-to-many).
 * - user_sessions: stores long-lived login sessions (token stored in DB).
 *
 * How to use:
 * 1) Open phpMyAdmin (or MySQL client)
 * 2) Choose your database
 * 3) Run the SQL below
 *
 * NOTE:
 * - Do NOT store plain passwords. We store password_hash only (safe).
 */
CREATE TABLE
    users (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(120) DEFAULT NULL,
        is_active TINYINT (1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_users_email (email)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    roles (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        role_key VARCHAR(50) NOT NULL,
        role_name VARCHAR(80) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_roles_key (role_key)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    user_roles (
        user_id BIGINT UNSIGNED NOT NULL,
        role_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (user_id, role_id),
        CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    user_sessions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        session_token_hash CHAR(64) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        last_seen_at DATETIME DEFAULT NULL,
        ip VARBINARY(16) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_token_hash (session_token_hash),
        KEY idx_user_id (user_id),
        CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    roles (role_key, role_name)
VALUES
    ('admin', 'Administrator'),
    ('user', 'User');

CREATE TABLE
    permissions (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        perm_key VARCHAR(80) NOT NULL, -- example: manage_users
        perm_name VARCHAR(120) NOT NULL, -- example: Manage Users
        PRIMARY KEY (id),
        UNIQUE KEY uq_perm_key (perm_key)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    role_permissions (
        role_id INT UNSIGNED NOT NULL,
        perm_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (role_id, perm_id),
        CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
        CONSTRAINT fk_rp_perm FOREIGN KEY (perm_id) REFERENCES permissions (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Seed some permissions
INSERT INTO
    permissions (perm_key, perm_name)
VALUES
    ('manage_users', 'Manage Users'),
    ('view_reports', 'View Reports');

-- Give admin all permissions (example)
INSERT INTO
    role_permissions (role_id, perm_id)
SELECT
    r.id,
    p.id
FROM
    roles r
    JOIN permissions p
WHERE
    r.role_key = 'admin';