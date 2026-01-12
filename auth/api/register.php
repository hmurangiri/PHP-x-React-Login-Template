<?php
/**
 * auth/api/register.php
 * POST JSON: { "name": "...", "email": "...", "password": "...", "csrfToken": "..." }
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use AuthModule\Csrf;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

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
} catch (InvalidArgumentException $e) {
    $message = $e->getMessage();
    $lowerMessage = strtolower($message);
    $field = null;

    if (str_contains($lowerMessage, 'email')) {
        $field = 'email';
    } elseif (str_contains($lowerMessage, 'password')) {
        $field = 'password';
    }

    json_out([
        'ok' => false,
        'error' => $message,
        'field' => $field,
    ], 400);
} catch (Throwable $e) {
    json_error($e->getMessage(), 'REGISTER_FAILED', 400);
}
