/**
 * src/auth/AuthProvider.jsx
 *
 * PURPOSE:
 * - This file manages authentication STATE for the entire React app.
 * - It stores:
 *   - current user
 *   - CSRF token
 *   - loading state
 *
 * WHY THIS EXISTS:
 * - React needs a global place to know:
 *   "Is the user logged in?"
 * - Context is the correct tool for this.
 *
 * HOW IT IS USED:
 * - Wrap your entire app with <AuthProvider>
 * - Any component can call useAuth()
 */

import React, { createContext, useContext, useEffect, useMemo, useState } from "react";
import { createAuthApi } from "./api";

/**
 * Create a React Context for auth data.
 */
const AuthContext = createContext(null);

export function AuthProvider({ baseUrl, children }) {
    /**
     * Create API client ONCE.
     * useMemo prevents re-creating it on every render.
     */
    const api = useMemo(() => createAuthApi({ baseUrl }), [baseUrl]);

    /**
     * State:
     * - user: null OR user object from backend
     * - csrfToken: required for POST requests
     * - loading: true while we check session
     */
    const [user, setUser] = useState(null);
    const [csrfToken, setCsrfToken] = useState("");
    const [loading, setLoading] = useState(true);

    /**
     * Refresh auth state:
     * - Get CSRF token
     * - Get current user (me endpoint)
     */
    async function refresh() {
        const [csrfRes, meRes] = await Promise.all([
            api.csrf(),
            api.me(),
        ]);

        setCsrfToken(csrfRes.csrfToken);
        setUser(meRes.user);
    }

    /**
     * On first app load:
     * - Check if user is already logged in (session cookie)
     */
    useEffect(() => {
        refresh().finally(() => setLoading(false));
    }, []);

    /**
     * Login function exposed to UI.
     */
    async function login(email, password) {
        const res = await api.login({ email, password, csrfToken });
        setUser(res.user);
    }

    /**
     * Register function exposed to UI.
     */
    async function register(name, email, password) {
        const res = await api.register({ name, email, password, csrfToken });
        setUser(res.user);
    }

    /**
     * Logout function exposed to UI.
     */
    async function logout() {
        await api.logout({ csrfToken });
        setUser(null);
        await refresh();
    }

    /**
     * Everything the app can access.
     */
    const value = {
        api,
        user,
        csrfToken,
        loading,
        login,
        register,
        logout,
        refresh,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

/**
 * Custom hook so components can simply call:
 * const { user, login } = useAuth();
 */
export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) {
        throw new Error("useAuth must be used inside AuthProvider");
    }
    return ctx;
}
