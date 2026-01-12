/**
 * src/auth/RequirePermission.jsx
 *
 * PURPOSE:
 * - Only allow rendering children if user has a specific permission.
 *
 * EXAMPLE:
 * <RequirePermission perm="manage_users"> ... </RequirePermission>
 */

import React from "react";
import { useAuth } from "./AuthProvider";

export function RequirePermission({ perm, children, fallback = null }) {
    const { user, loading } = useAuth();
    if (loading) return null;

    const perms = user?.permissions || [];
    if (!perms.includes(perm)) return fallback;

    return children;
}
