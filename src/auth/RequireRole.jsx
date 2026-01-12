/**
 * src/auth/RequireRole.jsx
 *
 * PURPOSE:
 * - Protects UI based on USER ROLE.
 *
 * EXAMPLE:
 * - Only admins can see admin panel.
 */

import React from "react";
import { useAuth } from "./AuthProvider";

export function RequireRole({ role, children, fallback = null }) {
    const { user, loading } = useAuth();

    if (loading) return null;

    const roles = user?.roles || [];

    // If user does not have required role
    if (!roles.includes(role)) {
        return fallback;
    }

    return children;
}
