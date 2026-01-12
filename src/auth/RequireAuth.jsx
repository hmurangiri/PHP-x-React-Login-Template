/**
 * src/auth/RequireAuth.jsx
 *
 * PURPOSE:
 * - Protects routes that require a logged-in user.
 *
 * WHAT IT DOES:
 * - If auth is still loading → show nothing
 * - If user is NOT logged in → redirect to /login
 * - If user IS logged in → show the page
 */

import React from "react";
import { Navigate, useLocation } from "react-router-dom";
import { useAuth } from "./AuthProvider";

export function RequireAuth({ children }) {
    const { user, loading } = useAuth();
    const location = useLocation();

    // Still checking session
    if (loading) return null;

    // Not logged in → redirect to login
    if (!user) {
        return <Navigate to="/login" state={{ from: location.pathname }} replace />;
    }

    // Logged in → allow access
    return children;
}
