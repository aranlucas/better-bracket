# Better Bracket

[![CI](https://github.com/aranlucas/better-bracket/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/aranlucas/better-bracket/actions/workflows/ci.yml)
[![MIT License](https://img.shields.io/github/license/aranlucas/better-bracket)](LICENSE)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4-EF4223?logo=codeigniter&logoColor=white)

![Illustrated tournament bracket bringing a group of picks together](docs/images/readme-cover.png)

*Concept artwork for tournament night; it is not an application screenshot.*


**Make your picks. Bring your group. See who called it.**

Better Bracket is a tournament bracket and group manager, originally built as a CIS 4301 project and later modernized on CodeIgniter 4. Set up a bracket, invite your group to make picks, then follow results and look back at past games.

## Bracket night, made easy

1. Create or join a group around a tournament.
2. Fill in your bracket and compare picks with the group.
3. Follow recorded games and see how everyone's predictions hold up.

The app combines a responsive web interface with PostgreSQL-backed tournament data. The included dataset keeps historical games available for browsing.

## Get it running

## Requirements

- PHP 8.5 or newer (the Docker image follows the rolling PHP 8.5 line)
- Composer 2.10+
- PostgreSQL 14+ (PostgreSQL 18 is used by the included Docker setup)
- PHP extensions: `intl`, `mbstring`, `pgsql`, and `pdo_pgsql`

## Run with Docker

```sh
docker compose up --build
```

Open [http://localhost:8080](http://localhost:8080). The database is initialized from `db.sql` on the first run. To reinitialize it after changing the seed data, remove the named `better-bracket-db` volume and start the stack again.

## Run locally

```sh
cp .env.example .env
composer install
php spark serve
```

Create a PostgreSQL database, run `db.sql`, and set the `database.default.*` values in `.env`. The web server document root must be the `public/` directory; do not expose `app/`, `writable/`, or `vendor/` directly.

For a migration-managed database, run `php spark migrate --all` followed by `php spark db:seed TournamentSeeder`. The seeder is idempotent and preserves the included historical tournament dataset.


## Local URLs with Portless

Complete the PHP, Composer, PostgreSQL, and `.env` setup above first. The command
starts CodeIgniter's loopback development server on the assigned port and sets
`app.baseURL` to the actual Portless URL for this process, including worktrees.

The standard development command uses [Portless](https://github.com/vercel-labs/portless).
Install its pinned CLI once with Node.js 24 or newer, then run this repository's command after the
normal dependency and environment setup:

```sh
npm install -g portless@0.15.7
composer dev
```

The main checkout uses `https://better-bracket.localhost` with the default proxy settings.
Use the URL printed by Portless if you have changed its proxy port, TLS, or TLD.
Linked Git worktrees get a branch prefix, so each checkout has its own origin.
The first HTTPS run can request local administrator permission to bind port 443,
trust its development certificate, and synchronize local hostnames. Ctrl+C stops
the child server and removes its route.

Docker Compose keeps its normal published ports. To browse an already running Docker app, run
`portless proxy start` followed by `portless alias better-bracket-docker 8080`;
remove that persistent alias afterward with
`portless alias --remove better-bracket-docker`. Configure the Docker app's
`app.baseURL` to match its alias before testing redirects.

## Checks

```sh
composer check
php spark routes
```

`composer check` runs syntax checks, PHPStan, PHPUnit, browser-state contract tests, strict Composer validation, and the dependency audit. Checks also require Node.js 18+ and the PHP `sqlite3` extension; the database regression uses an isolated in-memory database. Browser-state tests render the real bracket template with synthetic teams and verify submitted picks against the server validator.

The application exposes `/health/live` for process health and `/health/ready` for application and database readiness.

Production deployments should set `CI_ENVIRONMENT=production`, use a strong application encryption key, set `cookie.secure = true` behind HTTPS, and run `composer install --no-dev --optimize-autoloader`.
