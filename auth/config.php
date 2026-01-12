<?php
/**
 * auth/config.php
 *
 * What this file does (newbie friendly):
 * - Holds settings for the auth module.
 * - You edit this file for every project you copy the auth folder into.
 *
 * What to change:
 * - db.host, db.name, db.user, db.pass: your MySQL details.
 * - app.cookie_name: change per project so different apps do not fight over sessions.
 * - app.session_days: how long a login stays valid.
 * - app.default_role_key: role given to new users.
 */

declare(strict_types=1);

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'test1',
        'user' => 'root',
        'pass' => '',
        'port' => 8111,
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'cookie_name' => 'TEST1APPSESSID',
        'session_days' => 1,
        'default_role_key' => 'user',
    ],
    'cors' => [
        'allowed_origins' => [
            'http://localhost:5173',
            'http://localhost:3000',
        ],
    ],
];
