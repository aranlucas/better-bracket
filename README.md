# Better Bracket

Better Bracket is a CodeIgniter 4 tournament bracket and group manager, originally built as a CIS 4301 project. It supports accounts, groups, picks and historical game browsing. See the [roadmap](ROADMAP.md) for the proposed tournament lifecycle and scoring work. [MIT license](LICENSE).

## Run with Docker

```sh
docker compose up --build
```

Open [http://localhost:8080](http://localhost:8080). Compose initializes PostgreSQL 18 from [db.sql](db.sql) when its data volume is empty and runs migrations before starting the app.

Changing `db.sql` does not update an existing database. To reinitialize disposable local data, stop the stack, remove its `better-bracket-db` volume and start it again. This discards all accounts, groups and picks in that volume.

## Run locally

Requires PHP 8.5.12+, Composer 2.10+, PostgreSQL, and PHP extensions `intl`, `mbstring`, `pgsql` and `pdo_pgsql`. Use the Node.js version in [.node-version](.node-version) and the pnpm version in [package.json](package.json).

```sh
cp .env.example .env
composer install
pnpm install --frozen-lockfile
```

Create an empty PostgreSQL database and set `database.default.*` in `.env` to its connection details. Then initialize the schema and included historical tournament data:

```sh
php spark migrate --all
php spark db:seed TournamentSeeder
pnpm run dev
```

The seeder skips import when any teams already exist; it does not repair partial datasets. Portless prints the local URL, normally `https://better-bracket.localhost`; Git worktrees add a branch prefix. Its first run may ask for `sudo` to bind port 443 and trust a local certificate; the app uses the origin supplied by Portless.

## Checks

```sh
composer check
pnpm run lint
pnpm run format:check
```

Install development dependencies first. PHP tests also require `sqlite3` and use an isolated in-memory database. `composer check` includes the JavaScript tests and strict Composer validation.

## Deployment

Serve only `public/`; do not expose `app/`, `writable/`, or `vendor/`. Set `CI_ENVIRONMENT=production`, replace the example encryption key with a strong secret, set `cookie.secure = true` behind HTTPS, and install dependencies with `composer install --no-dev --optimize-autoloader`. The Compose stack uses development settings and credentials.

Use `/health/live` for process health. `/health/ready` runs a database query and returns HTTP 503 when the connection fails; it does not verify that migrations or seed data are complete.
