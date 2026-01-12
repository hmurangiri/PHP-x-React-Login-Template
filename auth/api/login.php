<?php
/**
 * auth/api/login.php
 * POST JSON: { "email": "...", "password": "...", "csrfToken": "..." }
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use AuthModule\Csrf;

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_out(['error' => 'Method not allowed'], 405);

$body = read_json();
$_POST['csrf_token'] = (string) ($body['csrfToken'] ?? ''); // reuse Csrf::verifyOrFail()
Csrf::verifyOrFail();

$email = (string) ($body['email'] ?? '');
$password = (string) ($body['password'] ?? '');

if ($auth->login($email, $password)) {
    $user = $auth->user();
    json_out(['ok' => true, 'user' => $user], 200);
}

json_out(['ok' => false, 'error' => 'Invalid login'], 401);
