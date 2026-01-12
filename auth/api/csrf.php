<?php
/**
 * auth/api/csrf.php
 * Returns a CSRF token for React to include on POST requests.
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use AuthModule\Csrf;

json_out(['csrfToken' => Csrf::token()]);
