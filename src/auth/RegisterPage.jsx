/**
 * src/auth/RegisterPage.jsx
 *
 * PURPOSE:
 * - Shows a registration form (name, email, password).
 * - Calls auth.register() from AuthProvider.
 * - After successful registration:
 *   - user is logged in automatically (PHP session cookie is created)
 *   - user is redirected to the app home page (or the page they wanted)
 *
 * REQUIREMENTS:
 * - You must wrap your app in <AuthProvider>.
 * - Your PHP backend must have /auth/api/register.php.
 * - Your backend must support CSRF token (we already added /auth/api/csrf.php).
 */

import React, { useState } from "react";
import { useAuth } from "./AuthProvider";
import { useLocation, useNavigate } from "react-router-dom";

export function RegisterPage() {
    /**
     * Auth actions from context
     */
    const { register } = useAuth();

    /**
     * Local state for form inputs
     */
    const [name, setName] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");

    /**
     * Local state for errors
     */
    const [error, setError] = useState("");
    const [fieldErrors, setFieldErrors] = useState({});

    /**
     * Router helpers for redirect after signup
     */
    const navigate = useNavigate();
    const location = useLocation();

    /**
     * Handle form submit
     * Steps:
     * 1) prevent browser refresh
     * 2) clear any old error
     * 3) call register(name, email, password)
     * 4) redirect user to:
     *    - the page they originally wanted, or
     *    - "/" (home)
     */
    async function onSubmit(e) {
        e.preventDefault();
        setError("");
        setFieldErrors({});

        try {
            await register(name, email, password);

            const redirectTo = location.state?.from || "/";
            navigate(redirectTo, { replace: true });
        } catch (err) {
            if (err.field) {
                setFieldErrors({ [err.field]: err.message });
                return;
            }

            setError(err.message);
        }
    }

    return (
        <div style={{ maxWidth: 360 }}>
            <h1>Register</h1>

            {error ? <p style={{ color: "red" }}>{error}</p> : null}

            <form onSubmit={onSubmit}>
                <label>Name</label><br />
                <input
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="John"
                />
                {fieldErrors.name ? (
                    <p style={{ color: "red", margin: "4px 0 0" }}>{fieldErrors.name}</p>
                ) : null}
                <br /><br />

                <label>Email</label><br />
                <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    placeholder="john@example.com"
                />
                {fieldErrors.email ? (
                    <p style={{ color: "red", margin: "4px 0 0" }}>{fieldErrors.email}</p>
                ) : null}
                <br /><br />

                <label>Password</label><br />
                <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                    placeholder="At least 8 characters"
                />
                {fieldErrors.password ? (
                    <p style={{ color: "red", margin: "4px 0 0" }}>{fieldErrors.password}</p>
                ) : null}
                <br /><br />

                <button type="submit">Create account</button>
            </form>
        </div>
    );
}
