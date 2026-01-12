<?php
/**
 * auth/public/register.php
 *
 * What this file does:
 * - Shows a registration form.
 * - On submit (POST), it:
 *   1) checks CSRF token
 *   2) calls $auth->register(email, password, name)
 *   3) user gets default role (from config)
 *   4) user is logged in automatically
 *   5) redirects to "/"
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use AuthModule\Database;
use AuthModule\Auth;
use AuthModule\Csrf;

/** Step 1: Create DB + Auth objects */
$db = new Database($config);
$auth = new Auth($db, $config);

/** Step 2: Handle registration submit */
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyOrFail();

    $name = (string) ($_POST['name'] ?? '');
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    try {
        $auth->register($email, $password, $name);
        header('Location: /');
        exit;
    } catch (Throwable $e) {
        // Example errors: "Invalid email", "Password must be at least 8 characters"
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Register</title>
</head>

<body>
    <h1>Register</h1>

    <?php if ($error): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">

        <label>Name</label><br>
        <input name="name" type="text"><br><br>

        <label>Email</label><br>
        <input name="email" type="email" required><br><br>

        <label>Password</label><br>
        <input name="password" type="password" required><br><br>

        <button type="submit">Create account</button>
    </form>
</body>

</html>