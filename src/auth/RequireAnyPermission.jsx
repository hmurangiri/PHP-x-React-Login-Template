/**
 * src/auth/RequireAnyPermission.jsx
 *
 * PURPOSE:
 * - Allow if user has at least one permission in a list.
 */

import React from "react";
import { useAuth } from "./AuthProvider";

export function RequireAnyPermission({ perms = [], children, fallback = null }) {
    const { user, loading } = useAuth();
    if (loading) return null;

    const userPerms = user?.permissions || [];
    const ok = perms.some((p) => userPerms.includes(p));

    if (!ok) return fallback;
    return children;
}
