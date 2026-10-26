# Feature roadmap

Planning baseline: `6c561cbce7a5` on `origin/main`, 2026-10-26. These are proposed releases, not delivered features or a commitment to a tournament date.

## Outcome

Let a small group complete one tournament together: submit picks, know when they become final, and understand the result. The current [application](README.md) has accounts, groups, bracket picks and historical games. [Pick submission](app/Controllers/Bracket.php) checks membership and bracket validity; the next release should complete the tournament lifecycle around it.

## 1. A tournament with a clear submission deadline

Add a tournament edition and an organizer-set deadline, shown in each participant's timezone. Make saving a draft and submitting final picks distinguishable. Enforce the deadline on the server and retain a receipt of the accepted picks.

Acceptance:

- A participant can resume their own draft and confirm which tournament and group it belongs to.
- A request arriving after the deadline cannot change accepted picks, including a tab opened before the deadline.
- Moving the deadline is an explicit organizer action visible to members; a failed save never appears successful.

Pilot one group using a fictional tournament first. Observe completion, help needed, abandoned submissions and disagreement about what was saved.

## 2. Explain the standings

Define scoring and tie rules before accepting picks. Add organizer-entered results and a standings view that explains each member's points by game. Correcting a result recomputes the standings and records the correction.

Acceptance: the same accepted picks and results always produce the same ranking; participants can identify the exact games behind a score. Separate historical browsing from the active edition.

## 3. Reuse a group next season

Offer an explicit invitation link with expiry and revocation, then let an organizer start a new edition without carrying over old picks. Require membership checks when reading private group results as well as when writing.

## Scope and evidence

This remains a social bracket tool. Payments, betting, paid prediction advice and live sports-provider ingestion are deferred. Use native database transactions, CodeIgniter validation and the existing test runners for release contracts. Success means a group finishes and wants another tournament; no adoption or commercial impact has been measured.
