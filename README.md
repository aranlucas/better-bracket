# Better Bracket

Better Bracket is a small tournament bracket app for making picks, creating groups, and following past games. The original CodeIgniter 2 application has been rewritten on CodeIgniter 4 with a public web root, Composer-managed dependencies, secure sessions, password hashing, CSRF protection, and a responsive vanilla-JS interface.

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

## Checks

```sh
composer check
php spark routes
```

`composer check` runs syntax checks, PHPStan, PHPUnit, strict Composer validation, and the dependency audit.

The application exposes `/health/live` for process health and `/health/ready` for application and database readiness.

Production deployments should set `CI_ENVIRONMENT=production`, use a strong application encryption key, set `cookie.secure = true` behind HTTPS, and run `composer install --no-dev --optimize-autoloader`.
