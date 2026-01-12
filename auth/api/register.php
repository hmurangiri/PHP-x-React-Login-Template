<?php
/**
 * auth/api/register.php
 * POST JSON: { "name": "...", "email": "...", "password": "...", "csrfToken": "..." }
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use AuthModule\Csrf;

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_out(['error' => 'Method not allowed'], 405);

$body = read_json();
$_POST['csrf_token'] = (string) ($body['csrfToken'] ?? '');
Csrf::verifyOrFail();

$name = (string) ($body['name'] ?? '');
$email = (string) ($body['email'] ?? '');
$password = (string) ($body['password'] ?? '');

try {
    $auth->register($email, $password, $name);
    $user = $auth->user();
    json_out(['ok' => true, 'user' => $user], 201);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 400);
}
