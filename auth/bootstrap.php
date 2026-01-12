<?php
/**
 * auth/bootstrap.php
 *
 * What this file does:
 * - Loads config.php (your settings).
 * - Sets up a simple autoloader so PHP can automatically load classes in auth/src/.
 * - Starts PHP sessions with safer cookie settings.
 *
 * Why sessions matter:
 * - A session lets PHP remember that a user is logged in across pages.
 *
 * How it works:
 * 1) Read config.php into $config.
 * 2) Register an autoloader for classes in the AuthModule\ namespace.
 * 3) Start session using the cookie name in config.
 */

declare(strict_types=1);

/** Step 1: Load configuration array into $config */
$config = require __DIR__ . '/config.php';

/**
 * Step 2: Autoloader
 * When you use: new AuthModule\Database(...)
 * PHP will call this function and load the right file from auth/src/.
 */
spl_autoload_register(function (string $class) {
    $prefix = 'AuthModule\\';
    $baseDir = __DIR__ . '/src/';

    // If the class does not start with "AuthModule\", ignore it.
    if (strncmp($prefix, $class, strlen($prefix)) !== 0)
        return;

    // Convert class name into a file path.
    // Example: AuthModule\Database -> auth/src/Database.php
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

    // Only include if it exists.
    if (is_file($file))
        require $file;
});

/**
 * Step 3: Start the session with safer cookie settings.
 * - httponly: JS cannot read the cookie (helps against some attacks).
 * - secure: cookie only sent on HTTPS (in production).
 * - samesite: reduces CSRF risk.
 */
function auth_module_start_session(array $config): void
{
    if (session_status() === PHP_SESSION_ACTIVE)
        return;

    // Name the session cookie. Change this per project so apps do not conflict.
    session_name($config['app']['cookie_name'] ?? 'APPSESSID');

    // If you are using https:// the cookie should be marked secure.
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,     // 0 means "until browser closes"
        'path' => '/',
        'domain' => '',      // empty means current domain
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/** Run session start immediately when bootstrap.php is included */
auth_module_start_session($config);
