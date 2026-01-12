<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use AuthModule\Database;
use AuthModule\Auth;

$db = new Database($config);
$auth = new Auth($db, $config);

$user = $auth->user();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>PHP Auth Test</title>
</head>

<body>
    <h1>PHP Auth Test</h1>

    <?php if (!$user): ?>
        <p>Not logged in.</p>
        <ul>
            <li><a href="public/login.php">Login (PHP form)</a></li>
            <li><a href="public/register.php">Register (PHP form)</a></li>
        </ul>
    <?php else: ?>
        <p>Logged in as:
            <?= htmlspecialchars($user['email']) ?>
        </p>
        <p>Roles:
            <?= htmlspecialchars(implode(', ', $user['roles'] ?? [])) ?>
        </p>
        <p>Permissions:
            <?= htmlspecialchars(implode(', ', $user['permissions'] ?? [])) ?>
        </p>
        <p><a href="public/logout.php">Logout</a></p>
    <?php endif; ?>

    <hr>
    <p>API quick checks:</p>
    <ul>
        <li><a href="api/me.php" target="_blank">/auth/api/me.php</a></li>
        <li><a href="api/csrf.php" target="_blank">/auth/api/csrf.php</a></li>
    </ul>
</body>

</html>