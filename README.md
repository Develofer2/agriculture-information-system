# Agriculture Information System

The Agriculture Information System is a web-based management platform for organizing farm operations and agricultural production records. It gives administrators and staff one place to manage farmers, farms, crops, planting activities, harvests, reports, and system activity.

The application is designed for agricultural offices or farm organizations that need reliable records, role-based access, searchable data, and a clear overview of current operations.

## Features

- Secure login with administrator and regular-user access levels.
- Farmer and farm profile management.
- Crop, planting, and harvest record management.
- Lookup data for soil types, irrigation, seasons, statuses, grades, and remarks.
- Dashboard summaries for farms, crops, plantings, and harvests.
- Searchable records, reports, pagination, and confirmation prompts.
- Audit logging for important account and record actions.
- CSRF protection, password hashing, secure sessions, and prepared database queries.
- Responsive layout with a collapsible sidebar, dark mode, and offline shell.

## Technology

- PHP 8.2+
- PostgreSQL through Supabase
- PDO with `pdo_pgsql`
- HTML, CSS, and vanilla JavaScript
- Progressive Web App manifest and service worker

## Project structure

```text
config/                 Application bootstrap, database, and shared helpers
database/migrations/    Database schema and optional sample data
public/                 Public web root and front controller
public/pages/admin/     Administrator page routes
public/pages/user/      Regular-user page routes
public/pages/shared/    Shared page implementations
public/assets/          CSS, JavaScript, and icons
scripts/                Command-line administration utilities
certs/                  PostgreSQL/Supabase SSL certificate
storage/logs/           Application error logs
```

## Setup

1. Create a Supabase project.
2. Run `database/migrations/001_ais_schema.sql` in the Supabase SQL editor.
3. Optionally run `database/migrations/002_seed_data.sql` to add lookup and sample records.
4. Copy `.env.example` to `.env` and replace every placeholder with server-specific values.
5. Create the first administrator:

   ```powershell
   php scripts/create-admin.php "Administrator Name" admin@example.com "use-a-long-unique-password"
   ```

6. Point the web server document root to `public/`.
7. Use PHP 8.2+ with `pdo_pgsql`, HTTPS, and the Supabase SSL certificate.

For local development, set `APP_ENV=local` and `SESSION_SECURE=false` in the local `.env`, then run:

```powershell
php -S localhost:8000 -t public
```

## Database migrations

Run the migrations in this order:

1. `001_ais_schema.sql` creates the tables, indexes, functions, and triggers.
2. `002_seed_data.sql` inserts lookup values and optional sample records.

Do not upload `.env` or `storage/logs/` to GitHub or the public web root. See [GITHUB_PUBLISHING.md](GITHUB_PUBLISHING.md) for GitHub publishing instructions.
