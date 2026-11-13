# Agriculture Information System

PHP 8.2+ and Supabase PostgreSQL implementation of the AP3 Agriculture Information System specification.

## Deploy

1. Create a Supabase project and run `database/migrations/001_ais_schema.sql` in the SQL editor.
2. Optionally run `database/migrations/002_seed_data.sql` for lookup and sample records.
3. Copy `.env.example` to `.env`, replace every placeholder, and keep `.env` outside version control.
4. Create the first administrator from a terminal using a new strong password:
   `php scripts/create-admin.php "Administrator Name" admin@example.com "use-a-long-unique-password"`
5. Point the web server document root at `public/` and run PHP 8.2+ with `pdo_pgsql`, HTTPS, and environment variables enabled.

For a local preview, use a local `.env` with `APP_ENV=local` and `SESSION_SECURE=false`, then run:
`php -S localhost:8000 -t public`.

## Included

- Login, role-based admin restrictions, password hashing, CSRF protection, sessions, audit trail.
- CRUD for farmers, farms, crops, plantings, and harvests; lookup dropdowns and referential constraints.
- Live dashboard summaries, joined records, reports, search, pagination, deletion confirmation, dark mode, responsive sidebar, and safe PWA offline shell.
- The optional seed migration includes sample farmers, farms, crops, plantings, and harvests matching the provided records.

The database migration provides 15 normalized tables, lookup seed data, integrity constraints, audit timestamps, and harvest date validation.

## Page structure

`public/index.php` is the front controller and keeps the existing `?r=` URLs working. The login and logout handlers stay directly in `public/pages/`. Regular user pages are in `public/pages/user/`, administrator pages are in `public/pages/admin/`, and shared page implementations are in `public/pages/shared/`. Shared navigation, layout, lookup choices, and CRUD logic are kept in `config/app.php` and `public/pages/shared/records.php` to avoid duplicating code across similar pages.

Run `database/migrations/001_ais_schema.sql` first to create the tables and database logic. Run `database/migrations/002_seed_data.sql` afterward only when lookup values and sample records are needed. Do not upload `.env` or `storage/logs/` to the public web root.
