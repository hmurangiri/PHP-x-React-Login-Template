/**
 * src/components/Loading.jsx
 *
 * PURPOSE:
 * - Display a small loading indicator while auth/permissions are checked.
 */

import React from "react";

export function Loading({ message = "Loading..." }) {
    return (
        <div aria-live="polite" role="status">
            {message}
        </div>
    );
}
