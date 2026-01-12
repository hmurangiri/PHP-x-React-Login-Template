<?php
/**
 * auth/src/Middleware.php
 *
 * What this file does:
 * - Helps you protect pages.
 *
 * Think of it like a "gatekeeper":
 * - requireAuth(): user must be logged in, otherwise redirect to login page.
 * - requireRole('admin'): user must be logged in AND have 'admin' role.
 *
 * How to use in a page:
 * - $mw->requireAuth();
 * - OR $mw->requireRole('admin');
 */

declare(strict_types=1);

namespace AuthModule;

final class Middleware
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Require the user to be logged in.
     * If not logged in, redirect to login page.
     */
    public function requireAuth(): array
    {
        $user = $this->auth->user();
        if (!$user) {
            header('Location: /auth/login.php');
            exit;
        }
        return $user;
    }

    /**
     * Require the user to have a specific role.
     * If logged in but does not have the role, return 403.
     */
    public function requireRole(string $roleKey): array
    {
        $user = $this->requireAuth();
        if (!$this->auth->hasRole($user, $roleKey)) {
            http_response_code(403);
            exit('Forbidden');
        }
        return $user;
    }
}
