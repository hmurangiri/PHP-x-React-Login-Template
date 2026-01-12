/**
 * src/admin/AdminHome.jsx
 *
 * PURPOSE:
 * - Simple admin landing page.
 * - Links to admin features (users, roles, permissions).
 */

import React from "react";
import { Link } from "react-router-dom";

export function AdminHome() {
    return (
        <div>
            <h1>Admin</h1>
            <ul>
                <li><Link to="/admin/users">Manage users</Link></li>
            </ul>
        </div>
    );
}
