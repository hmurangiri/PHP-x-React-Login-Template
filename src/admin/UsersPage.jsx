/**
 * src/admin/UsersPage.jsx
 *
 * PURPOSE:
 * - Fetch list of users from backend (admin endpoint).
 * - Allow editing a user's roles.
 *
 * REQUIREMENTS:
 * - PHP endpoint: GET /auth/api/admin/users.php
 * - PHP endpoint: POST /auth/api/admin/update-user-access.php
 * - AuthProvider must exist to supply csrfToken and refresh()
 */

import React, { useEffect, useMemo, useState } from "react";
import { useAuth } from "../auth/AuthProvider";
import { createAuthApi } from "../auth/api";
import { UserRow } from "./UserRow";

export function UsersPage() {
    const { csrfToken, refresh } = useAuth();
    const api = useMemo(() => createAuthApi(), []);
    const [users, setUsers] = useState([]);
    const [error, setError] = useState("");

    /**
     * Fetch users list from the backend.
     * Cookies must be included because admin auth is session-based.
     */
    async function loadUsers() {
        setError("");
        try {
            const data = await api.adminListUsers();
            setUsers(data.users || []);
        } catch (e) {
            setError(e.message);
        }
    }

    useEffect(() => {
        loadUsers();
    }, []);

    /**
     * Save roles for a given user.
     */
    async function updateAccess(userId, roles) {
        setError("");
        try {
            await api.adminUpdateUserAccess({
                userId,
                roles,
                csrfToken,
            });

            // Reload list, and also refresh current user (in case you edited yourself)
            await loadUsers();
            await refresh();
        } catch (e) {
            setError(e.message);
        }
    }

    return (
        <div>
            <h1>Manage users</h1>

            {error ? <p style={{ color: "red" }}>{error}</p> : null}

            <table border="1" cellPadding="8" style={{ borderCollapse: "collapse" }}>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Roles (comma)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {users.map((u) => (
                        <UserRow key={u.id} user={u} onSave={updateAccess} />
                    ))}
                </tbody>
            </table>
        </div>
    );
}
