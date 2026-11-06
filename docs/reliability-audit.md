# Correctness and reliability audit — 2026-11-06

Baseline: remote default `65fc7a9` (after merged cleanup #39 and index #40). Clean isolated worktree. Tests and builds run only in CI.

## Inventory and boundaries

- CodeIgniter HTTP routes: registration/login/logout, dashboard/profile, group discovery/creation/join/invitation, historical games, bracket rendering and POST `/bracket/picks`, liveness/readiness. Public APIs and partial bracket semantics are retained.
- Session identity is server-side; login rotates the session. Global CSRF protects mutations. Query Builder binds database input; views escape user text. Group discovery/self-join are intentionally open to signed-in accounts. Only members may save picks for a group.
- PostgreSQL holds accounts/profiles, groups/profiles/membership, teams/games/scores and picks. Creation and pick replacement use transactions; unique email/membership/slot indexes and membership foreign keys constrain writes. Existing SQLite tests cover legacy authentication and failed replacement rollback; Vitest covers bracket path invalidation. Docker builds and PHP/JS checks already run in CI.
- Browser JavaScript selects winners and prunes invalid descendants, then posts a complete replacement. No external API/model is needed. Seeder skips nonempty teams by documented design. Health readiness checks connectivity only, also documented.

## Findings recorded before implementation

1. **High, confirmed: browser saves fail before validation.** `app/Controllers/Bracket.php::save` calls `getJSON(true)` unconditionally, while `public/assets/js/bracket.js` sends `URLSearchParams`. CodeIgniter 4.7.5 `IncomingRequest::getJSON` throws on invalid JSON rather than returning null, so the form fallback is unreachable for a real nonempty URL-encoded body. Expected: accept existing form and JSON contracts, persist valid picks and return JSON. Fix by selecting the parser by content type and returning a controlled 422 for malformed JSON. CI regression must exercise the real browser POST and inspect persisted rows.
2. **High, confirmed: a rejected save makes the next save fail CSRF.** `app/Config/Security.php` regenerates tokens on every verified mutation, before controller validation. Controller errors omit the new hash and JS throws before updating tokens. Trigger: reject an invalid path/nonmember group, then correct and retry. Expected: show the error, then allow correction without reload. Return the current hash for all controller JSON responses and consume it before testing success. Update the logout hidden field too; it shares the rotated session token.

3. **High, confirmed by independent review: authentication throws before validation.** `Auth::allowAuthAttempt` includes `:` in its cache key; `Config/Cache.php::$reservedCharacters` reserves it. CodeIgniter Throttler passes the key through to FileHandler/BaseHandler, which throws on reserved characters. Real login and registration cannot complete on the default cache. Use a safe underscore separator; the identity remains hashed and action-specific.

## Reviewed hypotheses and limits

- Group member visibility/self-join/invite-by-member are existing explicit UI behavior, not proven authorization defects. No private-group policy invented.
- Draft hydration/deadlines/scoring are proposed in ROADMAP.md, not existing guarantees; no feature expansion.
- Replacing picks is scoped to both user and group; rollback protects previous rows on insert failure. Independent review identified that two sessions can both delete an empty bracket then insert opposing winners, producing a union although each request promises replacement. This is finding 4 (high confidence); serialize on the stable membership row using a Query Builder no-op update before deleting. A PostgreSQL process regression checks blocking and final replacement, without relying on timing races.
- Strict bracket validation rejects forged slot/team paths, duplicate winners and excess selections. Empty partial brackets remain permitted (existing behavior).
- Account duplicate races are constrained by a unique lower-email index; creation exceptions return failure. Legacy hashes upgrade after successful verification. No production data/credentials used.
- All controllers/models/services, migrations/schema/seed, views/browser assets, configuration, Docker and CI inspected. Rejected hypotheses above distinguish current contracts from roadmap work. This is static evidence, not a claim that every runtime failure has been reproduced.

## Regression and framework plan

Use published `e2e@0.23.0` and `@e2e-dev/web@0.15.5` (registry checked 2026-11-06; Node ^22.22.3 or >=24.8, repository uses 24.22.1). Read upstream quickstart, web, config and CI guides. These are the requested tester-army packages. Deterministic browser actions/assertions need no model or credentials. CI will use disposable PostgreSQL, migrate/seed and exercise registration, group creation, real pick submission, rejected save/correction, logout and sign-in; assert database state separately in CI. Existing tests/build remain. Add an isolated baseline reproduction using the same regression, expecting failure before restoring fixes. Reports/traces are CI artifacts.

## Outcome

Independent static review of `262b787` found no remaining substantive blockers. First CI run [42300022294](https://github.com/aranlucas/better-bracket/actions/runs/42300022294) passed PHP syntax/static analysis, existing behavior tests, JavaScript lint/tests and container build, but the new PostgreSQL subprocess fixture failed to connect because its explicit connection config omitted `charset`. Corrected the fixture to use UTF-8 and expose child-process diagnostics. Browser and pre-fix evidence were not reached; corrected CI pending. No tests/builds run locally.

CI follow-up: [42300489262](https://github.com/aranlucas/better-bracket/actions/runs/42300489262) passed all PHP checks (including the PostgreSQL lock regression), JS lint/tests and container build. The baseline demonstration stopped before tests because shallow checkout omitted `65fc7a9`; checkout now fetches history. Browser and pre-fix evidence remain pending.

CI follow-up: [42300952202](https://github.com/aranlucas/better-bracket/actions/runs/42300952202) passed the fixed PHP/JS/build gates but the browser registration timed out. The e2e command environment is restricted by default (installed config guide); app URL/database variables were not inherited. Added an explicit allowlist for CI environment, app and database settings, and enabled hidden-file artifact upload for `.e2e/`. Baseline browser failures in this run are **not** accepted as defect evidence because they failed at this common setup issue. Corrected browser evidence pending.
