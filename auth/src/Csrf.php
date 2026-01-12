<?php
/**
 * auth/src/Csrf.php
 *
 * What this file does:
 * - Protects your forms (login/register) against CSRF attacks.
 *
 * CSRF in simple terms:
 * - A bad website can trick a logged-in user into submitting a form to your website.
 * - CSRF token is a secret random string stored in the session.
 * - Every form includes this token.
 * - On submit, we check if the token matches. If not, we block.
 *
 * How to use:
 * - In a form: <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
 * - On POST: Csrf::verifyOrFail();
 */

declare(strict_types=1);

namespace AuthModule;

final class Csrf
{
    /**
     * Create or return the CSRF token.
     * - Stored inside $_SESSION so it persists across pages.
     */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['csrf_token'];
    }

    /**
     * Verify the token submitted by the form.
     * If invalid, stop the request.
     */
    public static function verifyOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        // Basic checks
        if (!is_string($token)) {
            if (defined('AUTH_API') && function_exists('json_error')) {
                json_error('Invalid CSRF token', 'INVALID_CSRF', 403);
            }

            http_response_code(403);
            exit('Invalid CSRF token');
        }

        // Must exist and must match
        if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
            if (defined('AUTH_API') && function_exists('json_error')) {
                json_error('Invalid CSRF token', 'INVALID_CSRF', 403);
            }

            http_response_code(403);
            exit('Invalid CSRF token');
        }
    }
}
