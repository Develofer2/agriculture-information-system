# Publish This Project to GitHub

Follow these steps in PowerShell from the project folder.

## 1. Protect your credentials

Make sure your real `.env` file is not uploaded:

```powershell
Get-Content .gitignore
```

The project already ignores `.env`, `.env.*`, and log files. Do not copy database passwords, API keys, or other secrets into `README.md` or source files.

If a real password was previously uploaded anywhere, change or rotate it before publishing.

## 2. Create an empty GitHub repository

1. Sign in to [GitHub](https://github.com).
2. Select **New repository**.
3. Enter a repository name, such as `agriculture-information-system`.
4. Choose **Private** or **Public**.
5. Do not add a README, `.gitignore`, or license because this project already contains them.
6. Select **Create repository**.

Copy the repository URL. It will look like one of these:

```text
https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git@github.com:YOUR_USERNAME/YOUR_REPOSITORY.git
```

## 3. Open the project folder

```powershell
Set-Location "C:\Users\Dell\Documents\SchoolWorks\PDO\finalmidterm"
```

## 4. Initialize Git

Run this only if the folder is not already a Git repository:

```powershell
git init
git branch -M main
```

## 5. Review the files before staging

```powershell
git status --short
git check-ignore -v .env storage/logs/php-error.log
```

The second command should show that `.env` and the log file are ignored.

If `.env` appears in the files that will be staged, stop and fix `.gitignore` before continuing.

## 6. Add and commit the project

```powershell
git add .
git status
git commit -m "Initial project release"
```

Review the staged file list before committing. The `.env` file and database credentials must not appear.

## 7. Connect the local project to GitHub

Replace the URL with your own repository URL:

```powershell
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git remote -v
```

If `origin` already exists, update it instead:

```powershell
git remote set-url origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
```

## 8. Push the project

```powershell
git push -u origin main
```

GitHub may open a browser window for authentication. Use a GitHub personal access token or Git Credential Manager if Git asks for a password; do not use your GitHub account password in the terminal.

## 9. Verify the repository

Open the repository page on GitHub and confirm:

- `.env` is not present.
- Database passwords are not present.
- `config/`, `public/`, `database/`, `scripts/`, and `README.md` are present.
- The repository visibility is correct.

## 10. Set up a published server

GitHub stores the source code; it does not run the PHP application by itself. On the hosting server:

1. Clone the repository.
2. Create a server-side `.env` from `.env.example`.
3. Fill in the production Supabase credentials.
4. Set `APP_ENV=production`, `APP_DEBUG=false`, and `SESSION_SECURE=true`.
5. Keep the web server document root pointed at `public/`.
6. Ensure PHP 8.2+, `pdo_pgsql`, HTTPS, and the Supabase SSL certificate are available.
7. Run the schema migration once, then run the seed migration only if sample data is needed.
