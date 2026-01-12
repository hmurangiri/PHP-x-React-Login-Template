/**
 * src/auth/LoginPage.jsx
 *
 * PURPOSE:
 * - Displays login form
 * - Calls auth.login()
 * - Redirects user after successful login
 */

import React, { useState } from "react";
import { useAuth } from "./AuthProvider";
import { useLocation, useNavigate } from "react-router-dom";

export function LoginPage() {
    const { login } = useAuth();

    // Local form state
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");

    const navigate = useNavigate();
    const location = useLocation();

    /**
     * Handle form submit
     */
    async function onSubmit(e) {
        e.preventDefault();
        setError("");

        try {
            await login(email, password);

            // Redirect user back to original page or home
            const redirectTo = location.state?.from || "/";
            navigate(redirectTo, { replace: true });
        } catch (err) {
            setError(err.message);
        }
    }

    return (
        <div style={{ maxWidth: 360 }}>
            <h1>Login</h1>

            {error && <p style={{ color: "red" }}>{error}</p>}

            <form onSubmit={onSubmit}>
                <label>Email</label><br />
                <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                /><br /><br />

                <label>Password</label><br />
                <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                /><br /><br />

                <button type="submit">Sign in</button>
            </form>
        </div>
    );
}
