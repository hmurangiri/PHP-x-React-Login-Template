<?php
/**
 * auth/api/_init.php
 * Shared bootstrap for API endpoints.
 * - Loads config, autoloader, starts session
 * - Creates $db and $auth objects
 * - Sends JSON headers
 */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'http://localhost:5173',
    'http://localhost:3000',
];

if (in_array($origin, $allowed, true)) {
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
