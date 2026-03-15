# Research: Voetbalpoule & Voorspelsysteem

## Runtime, Architecture, and Delivery Decisions

### Decision: Use a lightweight modular MVC-style monolith with `public/`, `app/`, `resources/views/`, `config/`, `database/`, `storage/`, and `tests/`

- **Rationale**: This keeps deployment compatible with shared hosting, supports SSR naturally, and still isolates domain and infrastructure concerns for TDD-friendly development.
- **Alternatives considered**: Plain page-per-script PHP was rejected because business rules would spread into views; Laravel/Symfony were rejected by the constitution.

### Decision: Use `public/index.php` as a front controller with Apache rewrite rules and a small custom router

- **Rationale**: Central routing allows global enforcement of sessions, CSRF, authorization, and consistent error handling without framework-specific server requirements.
- **Alternatives considered**: Route-per-file PHP endpoints were rejected because they duplicate bootstrap and security checks; Slim or similar micro-frameworks were not selected to keep the stack minimal and explicit.

### Decision: Keep controllers thin and move business rules into application services and domain policies

- **Rationale**: Rules like last-admin protection, deadline enforcement, atomic submission, recalculation, and snapshot generation must remain testable and independent from the HTTP layer.
- **Alternatives considered**: Fat controllers or direct SQL inside templates were rejected because they reduce testability and make invariants fragile.

### Decision: Render HTML with native PHP templates and Bootstrap 5

- **Rationale**: Native templates are zero-friction for shared hosting, while Bootstrap gives responsive layouts required by the constitution.
- **Alternatives considered**: Heavier template engines and SPA frameworks were rejected because they add runtime complexity without solving a core requirement.

## Persistence and Operational Decisions

### Decision: Use PDO exclusively with prepared statements, repository/query services, and explicit transactions

- **Rationale**: PDO is constitutionally required and gives the control needed for atomic submission, atomic CSV import, and deterministic score recalculation.
- **Alternatives considered**: `mysqli` was rejected for weaker portability; ORMs/query builders were rejected to avoid extra abstraction and shared-hosting complexity.

### Decision: Model schema changes as forward-only PHP migrations with idempotent seeders

- **Rationale**: Shared hosting may limit shell access, so migrations must be runnable both locally and via a controlled deployment/admin workflow.
- **Alternatives considered**: CLI-only migrations were rejected because some hosts do not expose SSH; manual ad hoc SQL was rejected because it is not repeatable.

### Decision: Store logos on the filesystem and persist only sanitized relative paths in MySQL

- **Rationale**: Filesystem storage is simpler than blob storage for shared hosting, provided MIME checks, size limits, unique filenames, and non-executable paths are enforced.
- **Alternatives considered**: Database blobs were rejected for operational complexity; trusting original filenames was rejected for security reasons.

## Security and Authentication Decisions

### Decision: Use session-based authentication with `password_hash`, `password_verify`, session regeneration, and synchronizer-token CSRF protection

- **Rationale**: This is the best fit for SSR and shared hosting while meeting the requirement for server-side role and rights checks.
- **Alternatives considered**: JWT/sessionless auth was rejected because the application is not API-first; client-only authorization was rejected because it violates the constitution.

### Decision: Enforce role checks and deadline checks in services, not only in controllers

- **Rationale**: Direct URL access, crafted requests, and future internal scripts must all be blocked by the same domain-enforced rules.
- **Alternatives considered**: Controller-only guards were rejected because they make invariants too easy to bypass during refactors.

## Domain and Scoring Decisions

### Decision: Represent section configuration and point rules as competition-owned data, executed by deterministic evaluator classes

- **Rationale**: This satisfies the “data-driven, no hardcoded scoring rules” requirement while still bounding complexity to known rule families.
- **Alternatives considered**: Hardcoded tournament-specific logic was rejected for maintainability; arbitrary formulas stored in the database were rejected as too risky and opaque.

### Decision: Recalculate standings from canonical predictions and results, then persist snapshots only when points or rank change

- **Rationale**: This supports reproducible ranking history, public standings performance, and the clarification that snapshots are automatic after impactful result changes and manual recalculations.
- **Alternatives considered**: Calculating movement on every page load was rejected for performance; snapshotting every recalculation regardless of change was rejected for noisy history.

### Decision: Store standings as snapshot headers plus snapshot rows for both main competitions and sub-competitions

- **Rationale**: This keeps public rendering fast and preserves movement indicators such as gestegen, gedaald, gelijk, and nieuw.
- **Alternatives considered**: A single denormalized snapshot table was rejected because it is harder to manage for sub-competition derivations and historical comparisons.

## Import and Validation Decisions

### Decision: Implement CSV import as a two-phase process: parse/normalize everything, validate everything, then commit once

- **Rationale**: This directly satisfies the requirement that one invalid or duplicate row must fail the full import with clear diagnostics.
- **Alternatives considered**: Partial import with skipped errors was rejected because it violates the clarified acceptance behavior.

### Decision: Treat entity-backed bonus answers as references to active catalog entities only

- **Rationale**: This supports dropdown rendering, validates answers against active records, and keeps bonus question behavior consistent across competitions.
- **Alternatives considered**: Free-text entity answers were rejected because they are harder to validate and score reliably.

## Testing and Tooling Decisions

### Decision: Use PHPUnit for unit/integration tests, Playwright for browser validation, and PHPStan for static analysis

- **Rationale**: PHPUnit and Playwright are explicitly aligned with the constitution and PRD; PHPStan raises confidence in a custom PHP stack.
- **Alternatives considered**: Pest is optional but not required; Selenium/Cypress were rejected because Playwright is already specified in project tooling.

### Decision: Prioritize tests for scoring, snapshot movement, deadline enforcement, final submission completeness, CSV atomicity, and last-admin protection

- **Rationale**: These are the highest-risk invariants and the most likely to regress.
- **Alternatives considered**: Focusing mostly on controller tests was rejected because it would under-test the domain behavior where the real risk lives.

## Planning Assumptions

### Decision: Plan for hundreds to low-thousands of participants per main competition and dozens of competitions over time

- **Rationale**: This is a realistic scale for a voetbalpoule application on shared hosting and informs the decision to precompute standings via snapshots.
- **Alternatives considered**: Planning only for tiny pools was rejected because it would under-design standings performance; planning for internet-scale traffic was rejected as unnecessary for the hosting constraint.

### Decision: Keep all functionality in a single deployable codebase with no worker processes

- **Rationale**: The constitution explicitly prioritizes shared-hosting compatibility and avoids background infrastructure assumptions.
- **Alternatives considered**: Queue-based recalculation or asynchronous job workers were rejected because they are not dependable on shared hosting.
