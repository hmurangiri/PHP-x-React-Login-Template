import React from "react";
import {
  BrowserRouter,
  Routes,
  Route,
  Link,
} from "react-router-dom";

/* Auth system */
import { AuthProvider, useAuth } from "./auth/AuthProvider";
import { RequireAuth } from "./auth/RequireAuth";
import { RequirePermission } from "./auth/RequirePermission";

/* Pages */
import { LoginPage } from "./auth/LoginPage";
import { RegisterPage } from "./auth/RegisterPage";

/* Admin pages */
import { AdminHome } from "./admin/AdminHome";
import { UsersPage } from "./admin/UsersPage";

/* -------------------------
   Basic pages
-------------------------- */

function Home() {
  const { user, logout } = useAuth();

  return (
    <div style={{ padding: 20 }}>
      <h1>React Auth Test</h1>

      {!user ? (
        <>
          <p>Not logged in</p>
          <Link to="/login">Login</Link>{" | "}
          <Link to="/register">Register</Link>
        </>
      ) : (
        <>
          <p><b>Email:</b> {user.email}</p>
          <p><b>Roles:</b> {(user.roles || []).join(", ")}</p>
          <p><b>Permissions:</b> {(user.permissions || []).join(", ")}</p>
          <button onClick={logout}>Logout</button>
        </>
      )}

      <hr />

      <nav>
        <Link to="/">Home</Link>{" | "}
        <Link to="/private">Private</Link>{" | "}
        <Link to="/admin">Admin</Link>{" | "}
        <Link to="/admin/users">Manage Users</Link>
      </nav>
    </div>
  );
}

function PrivatePage() {
  return (
    <div style={{ padding: 20 }}>
      <h2>Private Page</h2>
      <p>You are logged in.</p>
    </div>
  );
}

function Forbidden() {
  return (
    <div style={{ padding: 20 }}>
      <h2>Forbidden</h2>
      <p>You do not have permission to view this page.</p>
    </div>
  );
}

/* -------------------------
   App root
-------------------------- */

export default function App() {
  const baseUrl =
    process.env.REACT_APP_AUTH_BASE_URL ??
    "http://localhost/LoginTemplate/auth/api/";

  return (
    // <AuthProvider baseUrl="http://localhost:8000/auth/api">
    <AuthProvider baseUrl={baseUrl}>
      <BrowserRouter>
        <Routes>

          {/* Public routes */}
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />

          {/* Any logged-in user */}
          <Route
            path="/private"
            element={
              <RequireAuth>
                <PrivatePage />
              </RequireAuth>
            }
          />

          {/* Admin home */}
          <Route
            path="/admin"
            element={
              <RequireAuth>
                <RequirePermission
                  perm="manage_users"
                  fallback={<Forbidden />}
                >
                  <AdminHome />
                </RequirePermission>
              </RequireAuth>
            }
          />

          {/* Admin users page */}
          <Route
            path="/admin/users"
            element={
              <RequireAuth>
                <RequirePermission
                  perm="manage_users"
                  fallback={<Forbidden />}
                >
                  <UsersPage />
                </RequirePermission>
              </RequireAuth>
            }
          />

          {/* Catch-all */}
          <Route
            path="*"
            element={
              <div style={{ padding: 20 }}>
                <h2>404</h2>
                <p>Page not found</p>
              </div>
            }
          />

        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}
