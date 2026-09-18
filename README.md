# User Management System

A complete, secure user management web application built with Laravel 12. It provides
server-rendered pages (Blade + Bootstrap 5) for browsers and a versioned, token-authenticated
REST API for programmatic access — both backed by the same MySQL database and business logic.

## 1. Overview

Two roles are supported:

- **User** — can register, log in, view/edit their own profile, change password, and delete
  their own account.
- **Admin** — there is no public admin registration form or API. Admin accounts can only be
  created via an Artisan command or a database seeder (see below). Admins get a dashboard with
  stats and full user management (search, filter, paginate, view, edit, delete), with
  guardrails against self-lockout (an admin can't delete their own account or demote/delete the
  last remaining administrator).

Every feature available in the browser is also available as a JSON REST API under `/api/v1`,
authenticated with [Laravel Sanctum](https://laravel.com/docs/sanctum) API tokens.

## 2. Technology Stack

| Layer          | Technology                                             |
|----------------|---------------------------------------------------------|
| Backend        | Laravel 12 (PHP 8.2)                                    |
| Frontend       | Blade templates, HTML5, CSS3, Bootstrap 5 (via CDN), JS |
| Database       | MySQL 8.0                                                |
| Auth (browser) | Laravel session auth (`web` guard)                      |
| Auth (API)     | Laravel Sanctum (personal access tokens)                |
| ORM            | Eloquent                                                 |
| Local env      | Docker / Docker Compose                                 |
| Tests          | PHPUnit (Feature + Unit), 76 tests                       |

## 3. Prerequisites

This project runs entirely in Docker, so you do **not** need PHP, Composer, or MySQL installed
on your host — only:

- Docker Engine
- Docker Compose v2 (`docker compose ...`)

This repository was built and verified against a host with PHP 8.2.30 already installed but
**no** system-wide Composer or MySQL — everything Laravel needs (Composer, PHP extensions,
MySQL) is provided by the Docker image/containers instead, so it will not conflict with any
other project's PHP/MySQL setup on the same machine.

> **Port note:** the app is exposed on host port **8080** (not 80), because port 80/443 may
> already be bound by other local tooling. MySQL is bound to `127.0.0.1:3306` only (not exposed
> publicly) so it doesn't collide with another local MySQL instance either.

## 4. Getting Started (Ubuntu / any Docker host)

```bash
# 1. Clone / cd into the project
cd user-management

# 2. Copy the example environment file and edit the secrets described below
cp .env.example .env

# 3. Build the application image
docker compose build

# 4. Start MySQL and wait for it to become healthy, then start the app
docker compose up -d

# 5. Install PHP dependencies and generate an app key (already done if you
#    used the provided .env, but run this after any composer.json change)
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 6. Run database migrations
docker compose exec app php artisan migrate

# 7. Create the initial administrator (pick ONE of the two options below — these
#    are the ONLY ways to create an admin account; there is no public admin
#    registration page or API)
docker compose exec app php artisan app:create-admin \
  --name="System Administrator" --email="admin@example.com" --password="ChangeMe123!"
# OR, using the seeder (reads INITIAL_ADMIN_* from .env):
docker compose exec app php artisan db:seed --class=AdminSeeder

# 8. Visit the app
open http://localhost:8080
```

### Everyday commands

```bash
docker compose up -d        # start (build first with `docker compose build` if needed)
docker compose down         # stop and remove containers (data volume is preserved)
docker compose restart app  # restart just the app container
docker compose logs -f app  # tail application logs
docker compose exec app php artisan <command>   # run any artisan command
docker compose exec app php artisan test        # run the test suite
```

## 5. Environment Configuration

Copy `.env.example` to `.env` and review these keys before starting:

| Variable                                            | Purpose                                                                 |
|------------------------------------------------------|--------------------------------------------------------------------------|
| `APP_URL`                                             | Set to `http://localhost:8080` to match the Docker port mapping         |
| `DB_HOST` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | Must match the values in `docker-compose.yml`'s `db` service        |
| `INITIAL_ADMIN_NAME` / `INITIAL_ADMIN_EMAIL` / `INITIAL_ADMIN_PASSWORD` | Optional — only used by `php artisan db:seed --class=AdminSeeder` to bootstrap the very first admin |
| `DOCKER_UID` / `DOCKER_GID`                           | Your host user's `id -u`/`id -g` (defaults to 1000/1000). Lets the containerized Apache process read/write the bind-mounted project files without permission errors. |

## 6. Database

MySQL runs in its own container (`db` service), with a persistent named volume
(`um_mysql_data`) so data survives `docker compose down` / restarts (use
`docker compose down -v` to wipe it). It is **not** exposed beyond `127.0.0.1`.

If you already run MySQL outside Docker on this machine, you can skip the `db` service
entirely: remove/comment it out of `docker-compose.yml`, then set `DB_HOST` in `.env` to
`host.docker.internal` (or your host's Docker-bridge IP) and point `DB_USERNAME`/`DB_PASSWORD`/
`DB_DATABASE` at a database you've created there — Laravel doesn't care which MySQL server it
talks to as long as the credentials are correct.

Migrations create: `users` (with `role` enum `user`/`admin`), `password_reset_tokens`,
`sessions`, `cache`, `jobs`, and `personal_access_tokens` (Sanctum).

```bash
docker compose exec app php artisan migrate           # run pending migrations
docker compose exec app php artisan migrate:fresh --seed   # DANGER: drops all tables, re-migrates, seeds
```

## 7. Frontend URLs

| URL                    | Description                                  | Auth required |
|-------------------------|-----------------------------------------------|----------------|
| `/`                     | Public landing page                          | No             |
| `/register`              | User registration                            | No (guest only) |
| `/login`                 | User login                                   | No (guest only) |
| `/admin/login`           | Admin login (no public admin registration exists) | No (guest only) |
| `/dashboard`             | User dashboard                               | Yes (user)     |
| `/profile`               | Edit profile / change password / delete account | Yes (user)  |
| `/admin/dashboard`       | Admin dashboard (stats, recent registrations) | Yes (admin)   |
| `/admin/users`           | User management (search, filter, paginate)   | Yes (admin)    |
| `/admin/users/{id}`      | View a user                                  | Yes (admin)    |
| `/admin/users/{id}/edit` | Edit a user (name, email, role)              | Yes (admin)    |

## 8. REST API (`/api/v1`)

All API responses are JSON. Protected endpoints require an `Authorization: Bearer <token>`
header (a Sanctum personal access token returned by register/login). Always send
`Accept: application/json` so Laravel returns JSON error responses instead of HTML.

### Authentication

| Method | Endpoint              | Auth      | Description                       |
|--------|------------------------|-----------|------------------------------------|
| POST   | `/api/v1/register`      | Public    | Register a new regular user       |
| POST   | `/api/v1/login`         | Public    | Log in, receive a token            |
| POST   | `/api/v1/logout`        | Sanctum   | Revoke the current token           |
| GET    | `/api/v1/me`            | Sanctum   | Get the authenticated user         |

**Register** — `POST /api/v1/register`

```bash
curl -X POST http://localhost:8080/api/v1/register \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Jane Doe","email":"jane@example.com","password":"Password123!","password_confirmation":"Password123!"}'
```

Success `201`:
```json
{
  "message": "Registration successful.",
  "data": {
    "user": { "id": 4, "name": "Jane Doe", "email": "jane@example.com", "role": "user", "created_at": "...", "updated_at": "..." },
    "token": "1|abcdef..."
  }
}
```

Validation error `422`:
```json
{ "message": "The email has already been taken.", "errors": { "email": ["The email has already been taken."] } }
```

**Login** — `POST /api/v1/login` — body: `{ "email": "...", "password": "..." }`. Success `200`
returns the same `{ user, token }` shape. Invalid credentials → `401`
`{ "message": "The provided credentials are incorrect." }`.

### Profile (authenticated user)

| Method | Endpoint                    | Auth    | Description                        |
|--------|-------------------------------|---------|--------------------------------------|
| GET    | `/api/v1/profile`             | Sanctum | View own profile                    |
| PUT    | `/api/v1/profile`              | Sanctum | Update own name/email               |
| PUT    | `/api/v1/profile/password`     | Sanctum | Change own password                 |
| DELETE | `/api/v1/profile`               | Sanctum | Delete own account                  |

**Update profile** — `PUT /api/v1/profile` — body: `{ "name": "...", "email": "..." }`. Any
`role` field in the body is silently ignored — role can never be changed through this endpoint.

**Change password** — `PUT /api/v1/profile/password` — body:
`{ "current_password": "...", "password": "...", "password_confirmation": "..." }`. Wrong
current password → `422` with `errors.current_password`.

**Delete account** — `DELETE /api/v1/profile` — body: `{ "current_password": "..." }`.

### Admin

There is intentionally **no** admin registration endpoint, in code or in the API — admin
accounts can only be created via `php artisan app:create-admin` or the `AdminSeeder` (see the
"Getting Started" section above), never through a public form or request.

| Method | Endpoint                     | Auth              | Description                     |
|--------|--------------------------------|-------------------|-----------------------------------|
| POST   | `/api/v1/admin/login`          | Public             | Admin login, receive a token      |
| GET    | `/api/v1/admin/users`          | Sanctum + admin    | Paginated user list (search/filter) |
| POST   | `/api/v1/admin/users`          | Sanctum + admin    | Create a regular user             |
| GET    | `/api/v1/admin/users/{id}`     | Sanctum + admin    | View a user                       |
| PUT    | `/api/v1/admin/users/{id}`     | Sanctum + admin    | Update a user (name/email/role)   |
| DELETE | `/api/v1/admin/users/{id}`     | Sanctum + admin    | Delete a user                     |

**Admin login** — `POST /api/v1/admin/login` — body: `{ "email": "...", "password": "..." }`.
A non-admin account gets `403` `{ "message": "These credentials do not have administrator access." }`.

**List users** — `GET /api/v1/admin/users?search=jane&role=user&per_page=15`

```json
{
  "data": [ { "id": 4, "name": "Jane Doe", "email": "jane@example.com", "role": "user", "created_at": "...", "updated_at": "..." } ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

**Create user** — `POST /api/v1/admin/users` — body: `{ "name", "email", "password", "password_confirmation" }`.
Always creates a **regular user** — any `role` field sent is ignored, by design (the only ways
to create an admin account at all are `php artisan app:create-admin` and the `AdminSeeder`).

**Update user** — `PUT /api/v1/admin/users/{id}` — body: `{ "name", "email", "role" }`
(`role` is `user` or `admin`). Demoting the last remaining admin returns `422` with
`errors.role`.

**Delete user** — `DELETE /api/v1/admin/users/{id}`. An admin deleting their own account, or the
last remaining admin, returns `422`:
```json
{ "message": "Unable to delete user.", "errors": { "user": ["You cannot delete your own administrator account."] } }
```

A regular user (or unauthenticated request) calling any `/api/v1/admin/*` endpoint gets `403`
(`401` if unauthenticated):
```json
{ "message": "This action is unauthorized." }
```

## 9. Security Notes

- Passwords are hashed with bcrypt (`Hash::make`); plaintext passwords/tokens are never stored
  or returned in responses.
- CSRF protection is active on all browser (session) forms; API routes are stateless/token-based
  and CSRF-exempt by design (standard for token APIs).
- All authentication endpoints (register/login, both user and admin, web and API) are
  individually rate-limited (5 attempts/minute per endpoint per IP).
- `role` is excluded from every user-facing Form Request's validated data — the only code paths
  that ever set `role` are: user registration (always `user`), the admin seeder/Artisan command
  (always `admin`), and the admin "update user" endpoint (itself gated by the `role:admin`
  middleware). There is no request path, web or API, that lets anyone self-assign the admin role.
- Role-based access is enforced by an `EnsureUserHasRole` middleware (aliased as `role:admin`)
  on both web and API admin routes — not just by hiding navigation links.
- IDOR protection: profile routes always operate on `$request->user()` (the authenticated
  user), never on an arbitrary user ID from the URL — there is no route that lets a regular
  user target another user's profile.
- An admin can never delete their own account, and can never delete or demote the last
  remaining administrator (enforced server-side in `App\Services\AdminUserService`, not just in
  the UI).

## 10. Testing

```bash
docker compose exec app php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`), completely
isolated from your dev MySQL data — safe to run anytime. The suite covers: user registration &
login, admin login, dashboard redirects by role, profile view/edit/password-change/delete,
admin user listing/search/filter/pagination, admin edit/delete with self-lockout and
last-admin protections, full API authentication, and negative cases (unauthorized admin
access, cross-user IDOR attempts, role-escalation attempts, invalid input, duplicate emails).

## 11. Troubleshooting

- **500 error / "tempnam(): file created in the system's temporary directory" on first
  request** — the containerized Apache process can't write to `storage/` or
  `bootstrap/cache/`. Make sure `DOCKER_UID`/`DOCKER_GID` in `.env` match your host user
  (`id -u` / `id -g`), then `docker compose up -d --force-recreate app`.
- **Files created by `docker compose exec app ...` are now owned by `root`** — the container's
  default shell user for `exec` is root. Fix with:
  `docker compose exec app chown -R "$(id -u)":"$(id -g)" /var/www/html`.
- **`docker compose up` fails to bind port 8080/3306** — another process is already using it;
  change the left-hand side of the port mapping in `docker-compose.yml` (e.g. `"8081:80"`).
- **API request returns an HTML error page instead of JSON** — make sure you're sending
  `Accept: application/json` on every API request.
- **"SQLSTATE[HY000] [2002] Connection refused" during migrate** — the `db` container isn't
  ready yet; `docker compose up -d` waits for its healthcheck, but if you run artisan commands
  immediately after `docker compose build`, wait a few seconds or check `docker compose ps`.
- **Changed `.env` but nothing changed** — clear the config cache:
  `docker compose exec app php artisan config:clear`.
- **A stray `user_management` file (a physical SQLite database) appears in the project root**
  — this means `DB_*` variables got set as real container-level environment variables again
  (e.g. re-added to `docker-compose.yml`'s `environment:` for the `app` service). Those take
  precedence over `.env` and even `phpunit.xml`'s test overrides, so `php artisan test` ends up
  writing to a file named after `DB_DATABASE` instead of using `:memory:`. Keep `DB_HOST` /
  `DB_DATABASE` / etc. defined only in `.env`, not duplicated in `docker-compose.yml`.
