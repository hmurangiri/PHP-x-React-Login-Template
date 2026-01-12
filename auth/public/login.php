<?php
/**
 * auth/public/login.php
 *
 * What this file does:
 * - Shows a login form.
 * - On submit (POST), it:
 *   1) checks CSRF token
 *   2) calls $auth->login(email, password)
 *   3) redirects to "/" if successful
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use AuthModule\Database;
use AuthModule\Auth;
use AuthModule\Csrf;

/** Step 1: Create DB + Auth objects */
$db = new Database($config);
$auth = new Auth($db, $config);

/** Step 2: Handle form submit */
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyOrFail();

    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($auth->login($email, $password)) {
        header('Location: /');
        exit;
    }
    $error = 'Invalid login.';
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login</title>
</head>

<body>
    <h1>Login</h1>

    <?php if ($error): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">

        <label>Email</label><br>
        <input name="email" type="email" required><br><br>

        <label>Password</label><br>
        <input name="password" type="password" required><br><br>

        <button type="submit">Sign in</button>
    </form>
</body>

</html>