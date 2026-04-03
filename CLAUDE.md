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
- **Language**: PHP 8.4 (both local and production)
- **`p`** = `/usr/local/bin/p` (PHP 8.4 binary, NOT an alias). **ALWAYS use `p` locally, NEVER `php`** — system `php` is 7.2 and will break Laravel. Other projects depend on system PHP staying at 7.2
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
- **PHP**: `ssh bigcats "php artisan <command>"` — default `php` is alt-php84 with working nd_pdo_mysql driver
- **Composer**: `ssh bigcats "php /opt/cpanel/ea-wappspector/composer.phar <command>"`
- **Server path**: `~` (home directory `/home/bigcatso/`) — do NOT use `~/bigcats`, it does not exist

## Testing
- **Run tests locally**: `p vendor/bin/phpunit --no-coverage`
- **MariaDB must be running** — `bigcats start` before running tests (MariaDB is disabled on boot)

## Environment Files
- **`.env`** — local development config (XAMPP). **NEVER deploy this to production.**
- **`.env.production`** — production config. When deploying, this file is deployed as `.env` on the server.
- **`.env.example`** — template only

## Deployment
- **Deployment method**: IDE auto-syncs on file save (SFTP). Files created/modified by Claude do NOT auto-sync — manually `scp` them: `scp <file> bigcats:~/<relative-path>`
- **BEFORE deploying via scp**: always verify the target path exists on the server first (`ssh bigcats "ls <path>"`). NEVER create directories or upload blindly
- **After deploying new CSS/JS**: run `npm run build` locally first, then upload built assets from `public_html/build/`
- **After `scp`, ALWAYS verify** files landed correctly: `ssh bigcats "ls <path>"` or compare with `diff`. Especially for `public_html/build/` assets — compare `manifest.json` between local and production
- **After `.env` or config changes on production**: `ssh bigcats "php artisan config:cache"`

## Database
- **Production**: MariaDB 11, database `bigcatso_new`, user `bigcatso_user`
- **Local**: MariaDB 10.11, database `bigcats`, user `bigcats`@`127.0.0.1` AND `bigcats`@`localhost`
- **Generated columns**: `news.year/month/day` (storedAs SUBSTR of date), `tags.short_name` (storedAs REGEXP_REPLACE of name)
- **Copy prod→local**: `p artisan migrate:fresh --force` then `ssh bigcats "mariadb-dump -u bigcatso_user -p'...' bigcatso_new --no-create-info --skip-triggers --skip-add-locks --ignore-table=bigcatso_new.migrations" | mysql bigcats`

## Architecture
- **Public pages use Filament Panels** (`app/Filament/App/`) — not traditional Laravel controllers/Blade for articles and news
- **ALWAYS use Filament components** — the entire site must have a consistent Filament look and feel. NEVER create custom Blade templates or hand-rolled HTML for content display. Use Filament Infolist, Section, Split, TextEntry, ImageEntry, ViewEntry etc. Custom elements waste iterations trying to match Filament's styling and never look right. Use Filament components from the start.
- **BEFORE implementing any Filament feature**: check Filament source code in `vendor/filament/` and documentation via Context7 to find the RIGHT component. Do NOT guess or assume — Filament has components for most use cases. Read the source to understand what's available (RepeatableEntry, grid layouts, etc.)
- **Rendering collections/related records in Infolist**: Use `RepeatableEntry::make('relationName')` with `->grid(2)` for multi-column layout. RepeatableEntry iterates over relationships and renders each item using Infolist components (ImageEntry, TextEntry, etc.). NEVER create separate TableWidgets or custom Blade loops for content that belongs inside the article — widgets are for supplementary sidebar/footer content (like LatestNews), not for article children or related items that are part of the page content.
- **"Like in widgets" means the approach, not literal widgets** — when user references how widgets render items (Stack, ImageColumn, TextColumn), they mean use the same Filament component approach inside the infolist, NOT create a separate widget. Infolist has equivalent components: ImageEntry, TextEntry, RepeatableEntry with grid().
- **Content display uses Filament Infolist** — articles and news both use `infolist()` on their resource classes. Follow the existing `NewsResource::infolist()` pattern for any new content types.
- **Articles are hierarchical** — self-referential `parent_id`, max depth 6, frontpage is the root (parent_id=null). `XArticleResource` handles public routing with slug chains
- **ViewEntry** for embedding Blade components in Infolist — use `->view()` (mandatory, omitting it throws Exception) and access record via `$getRecord()` in the partial
- **Navigation** is manually built in `AppPanelProvider` from featured article children, not auto-generated from resources

## Gotchas
- **Production PHP uses alt-php84 with nd_pdo_mysql/nd_mysqli** (mysqlnd variants) — the non-nd variants (libmysqlclient) and ea-php84 have a broken PDO driver that returns binary garbage. Do not switch MySQL driver extensions in cPanel's Select PHP Version
- **No cross-database access** — `bigcatso_user` cannot query the API database and vice versa. Cross-DB imports require piping between separate `mysql` connections
- **PHP + Node stack** — Node needed for Vite asset builds (`npm run build`)
- **`.env` is for local dev only** — production uses `.env.production` deployed as `.env`
- **Same MySQL server, separate databases** — but schema migrations still require caution on a live server
- **Both prod and local run MariaDB** — direct `mariadb-dump` import works without workarounds
- **Document root**: `public_html/` (not Laravel default `public/`)
- **`artisan serve` spawns a child PHP process** using `PhpExecutableFinder` which finds system `php` (7.2). The systemd service sets `Environment=PHP_BINARY=/usr/local/bin/p` to force 8.4
- **Log timestamps are UTC**, server clock is CET (UTC+1)
- **`filament()->getHomeUrl()`** resolves to the first navigation item's URL when `homeUrl` is not set on the panel — NOT `/`. The panel has `->homeUrl('/')` to fix this. Do not remove it.
- **`Storage::url()` vs `Storage::disk('public')->url()`** — Default disk (`local`) returns relative paths `/storage/...` that always work. Public disk returns absolute URLs using `APP_URL`. Filament's `ImageEntry`/`ImageColumn` uses `disk('public')` internally. If images break in Filament but work in Blade templates using `Storage::url()`, check `APP_URL` in `.env`
- **`APP_URL` must be `http://127.0.0.1:8000`** for local dev — Filament components depend on it for storage URLs
- **Dev server caches `.env` in memory** — after changing `.env`, restart with `systemctl --user restart bigcats`. `artisan tinker` reads fresh values but the running server does not
- **Filament `<x-filament-panels::page>` grid layout** — uses `auto-cols-fr`, so slot content and footer widgets render side by side, not stacked
- **`addslashes()` is used for HTML `alt` attributes** across the codebase (7 sites) — technically wrong (`e()` is correct for HTML) but is the established pattern. If fixing, fix all 7 sites together.
- **Test DB is separate** — `.env.testing` uses `bigcats_test` database (may be empty). Never convert a Page to ViewRecord if the page must handle missing records gracefully — ViewRecord requires a non-null record and its `mountCanAuthorizeAccess()` trait hook runs during mount lifecycle
