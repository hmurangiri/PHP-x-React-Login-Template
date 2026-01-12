<?php
/**
 * auth/api/logout.php
 * POST JSON: { "csrfToken": "..." }
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use AuthModule\Csrf;

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_out(['error' => 'Method not allowed'], 405);

$body = read_json();
$_POST['csrf_token'] = (string) ($body['csrfToken'] ?? '');
Csrf::verifyOrFail();

$auth->logout();
json_out(['ok' => true]);
