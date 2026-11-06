# Untangle decision log

Scope: entire Better Bracket repository in isolated branch refactor/untangle-codebase-20261106; starting HEAD fe40373, clean working tree. Exact skill fetched/read from https://raw.githubusercontent.com/caldoai/skills/main/skills/untangle-codebase/SKILL.md. Both optional additions selected. User subsequently authorized commit, push, PR and merge after checks/review requirements pass; deployment remains excluded.

## Inventory before editing

CodeIgniter/PHP/PostgreSQL application: nine controllers, two models, bracket topology/validation service, helpers, PHP views/layout/error templates, three browser modules and CSS. Inspected migrations, historical SQL seed/importer, framework/application config, Docker/Compose/CI, manifests, README/roadmap and all tests. No nested AGENTS.md found. Existing tests cover bracket legality, render/state/save contract, rollback, health and lint failure behavior. Git history shows recent tooling/test/docs cleanup; avoid speculative movement of stable framework support.

## Baseline failures and limitations

Host PHP and Composer are absent; no vendor/node_modules at start. PHP-backed tests, syntax and analysis initially unavailable. Docker queries and package install attempted before editing; tool execution intermittently stalls. Final validation is recorded separately below.

## Design and complete justified work list

1. Separate persistence using existing CodeIgniter Model/Query Builder, no dependency added.
   - Needless link: Games/Bracket depend on GroupModel for unrelated tournament queries.
   - Move bracketTeams/savePicks to BracketModel, pastGames to GameModel. Keep group membership/profile in GroupModel and preserve atomic rollback/logging.
2. Render opening team IDs from bracket definition, indexed into the catalogue.
   - Hidden link: view repeats service seed-to-slot interpretation.
   - Remove teamsByRegion if no callers remain. Keep round/region constants and finals layout as real tournament/display contracts.
3. Remove surplus indirection: Groups authenticatedUser wrapper, parent-only BaseController init override, unused import and Services example. Make is_authenticated use current_user_id rather than duplicate the session predicate.
4. Consolidate password migration into one successful rehash/update.
   - Legacy login hashes twice because second decision reads stale original password. Preserve legacy compatibility and failed login behavior.
5. Use CodeIgniter validation for invitation email (AGENTS.md), retaining blank email self-join and authorization.
6. Remove implementation-restating comments. Preserve types, licensing, external compatibility constraints and useful framework configuration documentation.
7. Test cleanup LAST: keep regression coverage, move rollback test to new domain; remove no meaningful tests. Independently justify additions by behavior/credible mistake/expected result before adding.

## Deliberately retained special cases

Optional champion confirmation/final-four numbering are persisted contracts. Partial brackets, strict IDs, missing teams and predecessor validation protect legal selections. Iterative descendant invalidation supports unordered state. Throttling/legacy hashes/stale account sessions serve existing accounts. HTML and JSON unauthorized responses serve different clients. Empty invitation means self-join; duplicate/missing members have user-facing errors. Transactions and readiness catch/log operational failures. Flash timers express display duration, not synchronization. PostgreSQL migration guards and archived db.sql importer support existing/fresh deployments. Framework config/error templates are runtime contracts, not dead code because direct imports are absent.

## Independent inventory review

Subagent inventory_review examined all domains and confirmed decisions 1–5. No additional worthwhile structural changes found. Existing tests all catch credible regressions and should be retained. Follow-up review after implementation remains required.

## Resumed review and validation

Re-read exact upstream skill on resume. Reviewer final_review found no confirmed regressions or remaining worthwhile restructuring. Pending: verify model construction under CodeIgniter and add password migration behavior coverage. Removed teamsByRegion test because its production helper was deleted; rendered-slot contract tests remain. JavaScript lint, format:check and git diff --check passed. Host PHP/Composer still absent; checking available Docker runtime before PHP validation.

Password coverage added last: credible regressions are rejecting existing accounts, retaining legacy hashes after login, changing a hash on failed login, or needlessly rewriting a current hash. Expected results follow the existing account compatibility contract and PHP password verification API, independently of the authentication implementation.

Runtime setup note: base php:8.5.12-cli already includes mbstring/sqlite3; first temporary image build failed trying to reinstall built-ins. Corrected image compiles only intl/pgsql/pdo_pgsql. A subsequent interrupted build had no surviving process or image; restarted with durable output at /tmp/better-bracket-validation-build.log. Local PHP validation remains pending; CI also provides the required runtime.

Commit gate found pre-existing lint failures in stock error debug.js (excluded by normal lint, included by lint-staged). Replaced obsolete MSIE branches and implicit globals with a private tab collection and explicit window.init/window.toggle entry points used by PHP. CSS.escape preserves generated argument IDs containing punctuation. Diagnostic interaction regression protects tab switching and argument visibility/navigation. Reviewer diagnostic_review found no actionable regressions or worthwhile additional changes. No hooks bypassed.
