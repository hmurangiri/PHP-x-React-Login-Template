<?php
/**
 * auth/public/logout.php
 *
 * What this file does:
 * - Logs the user out.
 *
 * Steps:
 * 1) Load bootstrap (config, autoload, session)
 * 2) Create Auth object
 * 3) Call $auth->logout()
 * 4) Redirect to login page
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use AuthModule\Database;
use AuthModule\Auth;

$db = new Database($config);
$auth = new Auth($db, $config);

$auth->logout();

header('Location: login.php');
exit;
