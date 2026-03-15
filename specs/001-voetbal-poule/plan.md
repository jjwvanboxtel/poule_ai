# Implementation Plan: Voetbalpoule & Voorspelsysteem

**Branch**: `001-voetbal-poule` | **Date**: 2026-03-15 | **Spec**: `C:\Github\poule_ai\specs\001-voetbal-poule\spec.md`  
**Input**: Feature specification from `C:\Github\poule_ai\specs\001-voetbal-poule\spec.md`

## Summary

Build a server-side rendered voetbalpoule application that runs on shared hosting, supports multiple tournaments in one PHP 8.4 + MySQL codebase, and lets administrators configure competitions, sections, scoring, participants, payments, standings snapshots, sub-competitions, dynamic knock-out rounds, participant knock-out predictions, and CSV imports without code changes. The implementation approach is a modular monolith with a front controller, thin HTTP controllers, explicit application services, PDO-backed repositories, deterministic score recalculation, snapshot-based leaderboard publication, and a data model that supports a variable number of knock-out rounds with round-dependent team counts validated against active competition countries/teams.

## Technical Context

**Language/Version**: PHP 8.4.x  
**Primary Dependencies**: Native PHP 8.4, Composer autoloading, PDO for MySQL, Bootstrap 5, PHPUnit 11, Playwright, PHPStan 1.x  
**Storage**: MySQL 8.x for relational data, filesystem storage for competition logos, PHP session storage for authenticated SSR flows  
**Testing**: PHPUnit 11 for domain/application tests, Playwright for SSR UI validation, PHPStan 1.x for static analysis  
**Target Platform**: Shared-hosting LAMP stack (Apache 2.4 + PHP-FPM/mod_php + MySQL) with desktop and mobile browsers  
**Project Type**: Server-side rendered web application (modular monolith)  
**Performance Goals**: Public competition and standings pages should render from precomputed snapshot data in under 500 ms p95; prediction submission should commit atomically in under 2 seconds; full standings recalculation for a typical tournament should complete in a few seconds without background workers  
**Constraints**: Must run on shared hosting; Laravel and Symfony are not allowed; no queues, daemons, containers, or framework-specific server features; use PDO prepared statements only; server-side authorization and CSRF are mandatory; predictions become read-only after deadline; competitions cannot open without active sections; CSV imports fail atomically on any invalid or duplicate row  
**Scale/Scope**: Single codebase for multiple tournaments, dozens of competitions over time, hundreds of matches/entities per competition, and hundreds to low-thousands of participants per main competition plus subset-based sub-competitions

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Research Gate Review

| Gate | Status | Notes |
|------|--------|-------|
| Shared-hosting compatible architecture | PASS | Plan uses a lightweight modular monolith with `public/` front controller and Apache rewrite support only. |
| No Laravel/Symfony dependency | PASS | Architecture stays on native PHP + minimal Composer packages. |
| PDO + prepared statements | PASS | All database access is routed through PDO repositories/query services. |
| SSR required | PASS | UI is designed as server-rendered HTML with Bootstrap-enhanced templates, not SPA/API-first. |
| Security baseline | PASS | Sessions, password hashing, CSRF tokens, output escaping, and server-side RBAC are included in the architecture. |
| Deadline and submission invariants | PASS | Final submissions are atomic, complete, and immutable after deadline. |
| Last-admin safeguard | PASS | Admin mutation services will enforce a hard guard against removing/deactivating the final active admin. |
| Testability requirement | PASS | Plan includes PHPUnit for domain rules and Playwright for end-to-end UI validation. |

### Post-Design Recheck

| Gate | Status | Notes |
|------|--------|-------|
| Data-driven scoring | PASS | Scoring is modeled as configurable section/rule data executed by deterministic evaluators. |
| Dynamic knock-out structure | PASS | Design supports multiple configurable knock-out rounds per competition with explicit round-team assignments and round-dependent team counts. |
| Knock-out participant prediction flow | PASS | Participants can submit per-round knock-out selections, validated against active competition countries/teams and round slot counts. |
| Deterministic standings snapshots | PASS | Snapshot headers + rows preserve reproducible ranking history for main and sub-competitions. |
| CSV import integrity | PASS | Two-phase validation + single transaction guarantees all-or-nothing imports with row diagnostics. |
| Shared-hosting operations | PASS | Migrations, uploads, session handling, and recalculation flows avoid worker/cron assumptions. |
| Documentation obligations | PASS | `research.md`, `data-model.md`, `contracts/`, and `quickstart.md` were generated for downstream work. |

## Project Structure

### Documentation (this feature)

```text
specs/001-voetbal-poule/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── csv-import.md
│   └── http-interface.md
└── tasks.md
```

### Source Code (repository root)

```text
public/
├── index.php
├── .htaccess
└── assets/

app/
├── Application/
│   ├── Auth/
│   ├── Competitions/
│   ├── Imports/
│   ├── Predictions/
│   ├── Scoring/
│   └── Standings/
├── Domain/
│   ├── Competition/
│   ├── EntityCatalog/
│   ├── Prediction/
│   ├── Scoring/
│   ├── Standings/
│   └── User/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── ViewModels/
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Pdo/
│   │   └── Migrations/
│   ├── Security/
│   └── Storage/
└── Support/
    ├── Config/
    ├── Routing/
    ├── Sessions/
    ├── Validation/
    └── View/

bootstrap/
├── app.php
└── dependencies.php

config/
├── app.php
├── database.php
├── security.php
└── scoring.php

database/
├── migrations/
└── seeders/

resources/
└── views/
    ├── admin/
    ├── auth/
    ├── competitions/
    ├── participants/
    ├── public/
    └── standings/

storage/
├── cache/
├── logs/
└── uploads/
    └── logos/

bin/
├── migrate.php
├── seed.php
└── recalculate-standings.php

docs/
├── architecture.md
└── data-model.md

tests/
├── Contract/
├── Integration/
├── Unit/
└── E2E/
```

**Structure Decision**: Use a single SSR web application with clear separation between HTTP entrypoints, application services, domain rules, and infrastructure adapters. This keeps deployment simple for shared hosting while preserving enough modularity for data-driven scoring, snapshot recomputation, CSV import validation, and multi-tournament reuse.

## Architecture Overview

### 1. Presentation Layer

- `public/index.php` is the only web entrypoint and bootstraps routing, sessions, configuration, CSRF checks, and controller dispatch.
- Apache rewrite rules route all non-static requests through the front controller.
- Views live under `resources/views` and use native PHP templates, partials, and HTML escaping helpers.
- Bootstrap 5 provides responsive layout without introducing a client-heavy SPA.

### 2. Application Layer

- Controllers remain thin and delegate to use-case services for registration, competition management, submission, imports, scoring, and standings publication.
- Bonus-question management, participant enrollment, match/venue/group administration, and protected maintenance actions are handled as explicit application services rather than ad hoc controller logic.
- Request DTOs / validated request objects normalize input before invoking services.
- Mutating operations that must be atomic use explicit PDO transactions.

### 3. Domain Layer

- Competition domain owns sections, point rules, deadlines, and activation/open-state constraints.
- Prediction domain enforces the “all active sections complete, then one final submission” rule, including complete knock-out round selections when the knock-out section is active.
- Prediction domain also validates bonus-question answers, including entity-backed dropdown selections that must resolve to active competition entities.
- Competition domain also owns dynamic knock-out round configuration, including ordered rounds and explicit team assignments per round.
- Competition domain defines how registered users become competition participants so enrollment remains an explicit, auditable workflow.
- Entity catalog and prediction validation together ensure knock-out selections reference active competition countries/teams only.
- Scoring domain evaluates persisted predictions against actual results using configurable rule sets rather than hardcoded tournament logic.
- Scoring domain evaluates bonus-question answers through bounded evaluator types so configurability remains data-driven without becoming an open-ended formula engine.
- Standings domain recalculates totals deterministically and derives movement indicators from the previous stored snapshot.

### 4. Infrastructure Layer

- PDO repositories encapsulate prepared statements and mapping logic.
- File storage services own logo uploads, safe filenames, and writable directory handling.
- Security services manage session lifecycle, password hashing, CSRF token generation, and role/permission gates.
- Migration and seeding scripts remain PHP-native so they can run locally or via shared-hosting compatible admin workflows if shell access is limited.
- Shared-hosting operations also require a protected maintenance workflow for migrations and manual standings recalculation when no CLI or SSH access is available.

## Risks & Tradeoffs

- A custom lightweight stack gives maximum hosting compatibility, but the team must implement more routing, security, and validation infrastructure than with Laravel/Symfony.
- Fully data-driven scoring improves tournament flexibility, but the design must constrain rule types to prevent an unbounded “formula engine.”
- Dynamic knock-out rounds improve tournament flexibility, but they require strict validation so each round has the correct number of team slots and downstream predictions stay internally consistent when round structures change.
- Per-round participant knock-out predictions add UX and validation complexity because both admin configuration and participant input must stay synchronized with the active country/team catalog.
- Bonus-question support increases validation and scoring surface area because admin-managed question types, participant answers, and public output all need consistent handling.
- Snapshot-based standings make reads fast and history reproducible, but they require strict recalculation discipline and transaction handling.
- All-or-nothing CSV imports protect integrity, but administrators may need strong validation feedback to correct large files efficiently.
- Shared hosting simplifies deployment targets, but it constrains background processing, filesystem permissions, operational tooling, and environment management.
- Performance goals must be validated explicitly during implementation because snapshot-backed reads and synchronous recalculation are core architectural promises.

## Implementation Approach

### Phase 0 - Research

Phase 0 confirms the runtime and architectural choices required by the constitution: native PHP 8.4 on shared hosting, SSR views, PDO-only persistence, deterministic scoring/snapshotting, transaction-safe final submission, and atomic CSV imports. Research results are captured in `research.md`.

### Phase 1 - Design

Phase 1 models the core relational entities and invariants, including dynamic knock-out rounds with round-specific team assignments, bonus-question types and answers, participant round predictions, explicit competition enrollment, documents the SSR HTTP contract and CSV import contract, and defines a local/shared-hosting-compatible quickstart. The design keeps domain logic isolated so later tasks can be organized by application slice instead of page-by-page scripts.

### Phase 2 - Task Planning Readiness

The next phase should break implementation into slices that preserve architectural boundaries: bootstrap/security foundation, admin competition management, participant enrollment, bonus-question management, match/group/venue administration, protected maintenance workflows, knock-out round configuration, participant submission flow including per-round knock-out selections and bonus answers, results/scoring engine, standings snapshots/sub-competitions, performance validation, and documentation/testing. This plan intentionally stops before generating executable tasks.

## Complexity Tracking

No constitution violations or exceptions are currently required by the design.
