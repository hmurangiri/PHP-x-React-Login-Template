<?php
/**
 * auth/api/_init.php
 * Shared bootstrap for API endpoints.
 * - Loads config, autoloader, starts session
 * - Creates $db and $auth objects
 * - Sends JSON headers
 */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$config = require __DIR__ . '/../config.php';
$allowed = $config['cors']['allowed_origins'] ?? [];

function origin_is_allowed(string $origin, array $allowed): bool
{
    if ($origin === '') {
        return false;
    }

    foreach ($allowed as $pattern) {
        if (!is_string($pattern) || $pattern === '') {
            continue;
        }

        if ($pattern === $origin) {
            return true;
        }

        if (str_starts_with($pattern, '/') && str_ends_with($pattern, '/')) {
            if (@preg_match($pattern, $origin) === 1) {
                return true;
            }
            continue;
        }

        if (str_contains($pattern, '*') && fnmatch($pattern, $origin)) {
            return true;
        }
    }

    return false;
}

if (origin_is_allowed($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


declare(strict_types=1);

define('AUTH_API', true);

require_once __DIR__ . '/../bootstrap.php';

use AuthModule\Database;
use AuthModule\Auth;

header('Content-Type: application/json; charset=utf-8');

$db = new Database($config);
$auth = new Auth($db, $config);

/**
 * Helper: read JSON body
 */
function read_json(): array
{
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || trim($raw) === '')
        return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Helper: output JSON and stop
 */
function json_out(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

/**
 * Helper: output JSON error and stop
 */
function json_error(string $message, string $code, int $status = 400): void
{
    json_out([
        'ok' => false,
        'error' => $message,
        'code' => $code,
    ], $status);
}
