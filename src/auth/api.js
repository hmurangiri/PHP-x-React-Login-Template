/**
 * src/auth/api.js
 *
 * PURPOSE:
 * - This file is the ONLY place that knows how to talk to the PHP backend.
 * - It wraps `fetch()` and exposes simple functions like:
 *   - login()
 *   - register()
 *   - me()
 *   - logout()
 *
 * WHY THIS EXISTS:
 * - So the rest of the React app does NOT care about URLs, headers, cookies.
 * - If your backend URL changes, you update ONE place.
 *
 * HOW IT IS USED:
 * - AuthProvider imports this file.
 * - You never call fetch() directly anywhere else.
 */

/**
 * Default API base path.
 * If React is served from the same domain as PHP,
 * this will work without changes.
 */
const DEFAULT_BASE = "/auth/api";

/**
 * Factory function that creates an API client.
 * We use a factory so different projects can pass different base URLs.
 */
export function createAuthApi({ baseUrl = DEFAULT_BASE } = {}) {

    /**
     * Internal helper for JSON requests.
     * - Always sends cookies (credentials: "include")
     * - Always sends JSON headers
     * - Automatically parses JSON response
     */
    async function jsonFetch(path, options = {}) {
        const res = await fetch(`${baseUrl}${path}`, {
            ...options,
            headers: {
                "Content-Type": "application/json",
                ...(options.headers || {}),
            },
            credentials: "include", // IMPORTANT: sends PHP session cookie
        });

        // Try to parse JSON response
        const data = await res.json().catch(() => ({}));

        // If backend returned error status, throw JS error
        if (!res.ok) {
            const message = data?.error || `Request failed (${res.status})`;
            throw new Error(message);
        }

        return data;
    }

    /**
     * Expose clean API methods.
     * These map 1-to-1 to PHP endpoints.
     */
    return {
        csrf: () => jsonFetch("/csrf.php"),
        me: () => jsonFetch("/me.php"),
        login: (payload) =>
            jsonFetch("/login.php", {
                method: "POST",
                body: JSON.stringify(payload),
            }),
        register: (payload) =>
            jsonFetch("/register.php", {
                method: "POST",
                body: JSON.stringify(payload),
            }),
        logout: (payload) =>
            jsonFetch("/logout.php", {
                method: "POST",
                body: JSON.stringify(payload),
            }),
        adminListUsers: () => jsonFetch("/admin/users.php"),
        adminUpdateUserAccess: (payload) =>
            jsonFetch("/admin/update-user-access.php", {
                method: "POST",
                body: JSON.stringify(payload),
            }),
    };
}
