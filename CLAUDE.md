# Project: BigCats (Laravel 11 — Public Website)

## CRITICAL: Production Safety
- **This is a live public website visited by many users. There is no staging environment.**
- **NEVER deploy changes without explicit user approval.** Do not run artisan commands on production, push code, or trigger deployments unless the user directly asks.
- **NEVER modify the database** (migrations, tinker queries, raw SQL) without explicit user approval — the DB is shared with the API project and serves live traffic.
- **NEVER use destructive git commands** (`git checkout <file>`, `git restore`, `git reset --hard`, `git stash`) — these destroy uncommitted changes. Use surgical edits to revert specific changes.

## Overview
Public-facing Laravel 11 website for big cats content. Hosted on the same server as the `../api` project (separate databases, shared hosting).

## Tech Stack
- **Framework**: Laravel 11
- **Language**: PHP 8.3
- **`p`** = `/usr/local/bin/p` (PHP 8.3 binary, NOT an alias). **ALWAYS use `p` locally, NEVER `php`** — system `php` is 7.2 and will break Laravel. Other projects depend on system PHP staying at 7.2
- **`c`** = `/usr/local/bin/c` (Composer binary)
- **Frontend build**: `npm run build` (Vite) — required after changing JS/CSS assets
- **Admin**: Filament
- **Local dev**: `/DATA/xampp/htdocs/bigcats`
- **Local start/stop**: `bigcats start` / `bigcats stop` / `bigcats status` (manages both MySQL and dev server on port 8000)
- **Dev server only**: `systemctl --user start/stop/status bigcats`
- **MariaDB only**: `pkexec systemctl start/stop mariadb` (disabled on boot)

## Shared Infrastructure with API Project
- **Same production server** (`bigcats` SSH host), same MySQL server but **separate databases**
- **API project location**: `../api` (sibling directory) — contains news processing, AI pipelines, queue workers
- **This project** is the public frontend; the API project handles backend processing

## Running Commands on Production via SSH
- **PHP**: `ssh bigcats "/opt/cpanel/ea-php83/root/usr/bin/php artisan <command>"` — do NOT use default `php` (it's `/opt/alt/php83` which has a broken PDO driver that returns integers as binary strings)
- **Composer**: `ssh bigcats "/opt/cpanel/ea-php83/root/usr/bin/php /opt/cpanel/ea-wappspector/composer.phar <command>"`
- **Server path**: `~` (home directory `/home/bigcatso/`) — do NOT use `~/bigcats`, it does not exist

## Testing
- **Run tests locally**: `p vendor/bin/phpunit --no-coverage`

## Environment Files
- **`.env`** — local development config (XAMPP). **NEVER deploy this to production.**
- **`.env.production`** — production config. When deploying, this file is deployed as `.env` on the server.
- **`.env.example`** — template only

## Deployment
- **Deployment method**: IDE auto-syncs on file save (SFTP). Files created/modified by Claude do NOT auto-sync — manually `scp` them: `scp <file> bigcats:~/<relative-path>`
- **BEFORE deploying via scp**: always verify the target path exists on the server first (`ssh bigcats "ls <path>"`). NEVER create directories or upload blindly
- **After deploying new CSS/JS**: run `npm run build` locally first, then upload built assets from `public_html/build/`
- **After `scp`, ALWAYS verify** files landed correctly: `ssh bigcats "ls <path>"` or compare with `diff`. Especially for `public_html/build/` assets — compare `manifest.json` between local and production
- **After `.env` or config changes on production**: `ssh bigcats "/opt/cpanel/ea-php83/root/usr/bin/php artisan config:cache"`

## Database
- **Production**: MariaDB 11, database `bigcatso_new`, user `bigcatso_user`
- **Local**: MariaDB 10.11, database `bigcats`, user `bigcats`@`127.0.0.1` AND `bigcats`@`localhost`
- **Generated columns**: `news.year/month/day` (storedAs SUBSTR of date), `tags.short_name` (storedAs REGEXP_REPLACE of name)
- **Copy prod→local**: `p artisan migrate:fresh --force` then `ssh bigcats "mariadb-dump -u bigcatso_user -p'...' bigcatso_new --no-create-info --skip-triggers --skip-add-locks --ignore-table=bigcatso_new.migrations" | mysql bigcats`

## Gotchas
- **No cross-database access** — `bigcatso_user` cannot query the API database and vice versa. Cross-DB imports require piping between separate `mysql` connections
- **PHP + Node stack** — Node needed for Vite asset builds (`npm run build`)
- **`.env` is for local dev only** — production uses `.env.production` deployed as `.env`
- **Same MySQL server, separate databases** — but schema migrations still require caution on a live server
- **Both prod and local run MariaDB** — direct `mariadb-dump` import works without workarounds
- **Document root**: `public_html/` (not Laravel default `public/`)
- **`artisan serve` spawns a child PHP process** using `PhpExecutableFinder` which finds system `php` (7.2). The systemd service sets `Environment=PHP_BINARY=/usr/local/bin/p` to force 8.3
- **Log timestamps are UTC**, server clock is CET (UTC+1)
