/**
 * src/admin/UserRow.jsx
 *
 * PURPOSE:
 * - One row for one user.
 * - Lets admin edit roles and permissions as comma-separated text.
 *
 * NOTE:
 * - This is a simple UI (good for a template).
 * - Later you can replace with multi-select chips.
 */

import React, { useState } from "react";

function listToCsv(list) {
    if (!Array.isArray(list)) return "";
    return list.join(", ");
}

function csvToList(csv) {
    return String(csv || "")
        .split(",")
        .map((s) => s.trim())
        .filter(Boolean);
}

export function UserRow({ user, onSave, saveDisabled = false }) {
    const [rolesCsv, setRolesCsv] = useState(listToCsv(user.roles));
    const [permsCsv, setPermsCsv] = useState(listToCsv(user.permissions));

    return (
        <tr>
            <td>{user.email}</td>
            <td>{user.name || ""}</td>
            <td>
                <input
                    value={rolesCsv}
                    onChange={(e) => setRolesCsv(e.target.value)}
                    placeholder="admin, user"
                    style={{ width: 220 }}
                />
            </td>
            <td>
                <input
                    value={permsCsv}
                    onChange={(e) => setPermsCsv(e.target.value)}
                    placeholder="manage_users, view_reports"
                    style={{ width: 260 }}
                />
            </td>
            <td>
                <button
                    onClick={() => onSave(user.id, csvToList(rolesCsv), csvToList(permsCsv))}
                    disabled={saveDisabled}
                >
                    Save
                </button>
            </td>
        </tr>
    );
}
