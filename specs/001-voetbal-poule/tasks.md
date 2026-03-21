---
description: "Task list for feature implementation"
---

# Tasks: Voetbalpoule & Voorspelsysteem

**Input**: Design documents from `C:\Github\poule_ai\specs\001-voetbal-poule\`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts\`

**Tests**: Include PHPUnit, HTTP contract, and Playwright tasks because the feature specification explicitly requires unit-testable domain logic and Playwright validation for functional UI flows.

**Organization**: Tasks are grouped by user story so each story remains independently implementable and testable.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel when dependencies are already complete and the tasks touch different files
- **[Story]**: User story label for traceability (`[US1]`, `[US2]`, `[US3]`)
- Every task includes exact repository file paths

## Path Conventions

- Application code: `app/`, `bootstrap/`, `config/`, `public/`
- Persistence and scripts: `database/`, `bin/`
- SSR templates: `resources/views/`
- Automated checks: `tests/`
- Documentation: `docs/`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the project skeleton, package manifests, and runtime configuration needed by all later slices.

- [X] T001 Initialize PHP package metadata, PSR-4 autoloading, and developer scripts in `composer.json`
- [X] T002 [P] Initialize Node tooling and Playwright package scripts in `package.json` and `playwright.config.ts`
- [X] T003 [P] Create the front controller and bootstrap entrypoints in `public/index.php`, `public/.htaccess`, `bootstrap/app.php`, and `bootstrap/dependencies.php`
- [X] T004 [P] Create environment and runtime configuration scaffolding in `.env.example`, `config/app.php`, `config/database.php`, `config/security.php`, and `config/scoring.php`
- [X] T005 [P] Create writable application directories and placeholders in `storage/cache/.gitkeep`, `storage/logs/.gitkeep`, `storage/uploads/logos/.gitkeep`, and `resources/views/layouts/app.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build the reusable platform services that block every user story until complete.

**CRITICAL**: No user story work should begin until this phase is complete.

- [X] T006 Create the core schema and migration runner in `database/migrations/001_create_core_tables.php` and `bin/migrate.php`
- [X] T007 [P] Create PDO connection and base repository infrastructure in `app/Infrastructure/Persistence/Pdo/ConnectionFactory.php` and `app/Infrastructure/Persistence/Pdo/AbstractPdoRepository.php`
- [X] T008 [P] Create request routing and HTTP error handling primitives in `app/Support/Routing/Router.php`, `app/Http/Requests/Request.php`, and `app/Http/Controllers/ErrorController.php`
- [X] T009 [P] Create session, authentication, and CSRF middleware/services in `app/Support/Sessions/SessionManager.php`, `app/Infrastructure/Security/SessionAuthenticator.php`, `app/Http/Middleware/RequireAuth.php`, and `app/Http/Middleware/VerifyCsrfToken.php`
- [X] T010 [P] Create shared SSR rendering, escaping, flash messaging, and validation support in `app/Support/View/ViewRenderer.php`, `app/Support/View/Escaper.php`, `app/Support/Validation/ValidationException.php`, and `resources/views/partials/flash.php`
- [X] T011 [P] Create foundational user and competition domain models in `app/Domain/User/User.php`, `app/Domain/Competition/Competition.php`, `app/Domain/Competition/CompetitionSection.php`, and `app/Domain/Competition/CompetitionRule.php`
- [X] T012 Create baseline repositories and local seed tooling in `app/Infrastructure/Persistence/Pdo/PdoUserRepository.php`, `app/Infrastructure/Persistence/Pdo/PdoCompetitionRepository.php`, `database/seeders/DevSeeder.php`, and `bin/seed.php`

**Checkpoint**: The application can bootstrap requests, connect to MySQL, render SSR pages, and enforce session/CSRF/authentication rules.

---

## Phase 3: User Story 1 - Deelnemer levert volledige voorspelling in (Priority: P1) 🎯 MVP

**Goal**: Let a participant register, authenticate, complete every active prediction section including knock-out selections and bonus-question answers, submit exactly one final prediction before the deadline, and review the saved prediction read-only afterward.

**Independent Test**: Open an active competition with seeded sections, complete knock-out and bonus-question answers, submit one complete participant prediction, confirm the submission is stored atomically, and verify the page becomes read-only and deadline-protected.

### Tests for User Story 1

- [X] T013 [P] [US1] Add unit tests for completeness, deadline, bonus-answer validation, and immutable-final-submission invariants in `tests/Unit/Predictions/SubmitPredictionServiceTest.php`
- [X] T014 [P] [US1] Add participant prediction route contract tests for `/dashboard`, `/competitions/{slug}/prediction`, and `/competitions/{slug}/prediction/submit` in `tests/Contract/ParticipantPredictionRoutesTest.php`
- [X] T015 [P] [US1] Add Playwright coverage for participant registration, bonus-question entry, final submission, unpaid marker visibility, and read-only review in `tests/E2E/participant-prediction-submission.spec.ts`

### Implementation for User Story 1

- [X] T016 [P] [US1] Create prediction persistence tables in `database/migrations/002_create_prediction_tables.php`
- [X] T017 [P] [US1] Create prediction domain models in `app/Domain/Prediction/PredictionSubmission.php`, `app/Domain/Prediction/MatchPrediction.php`, `app/Domain/Prediction/KnockoutRoundPrediction.php`, and `app/Domain/Prediction/BonusAnswer.php`
- [X] T018 [P] [US1] Implement participant registration and session login/logout flows in `app/Http/Controllers/Auth/RegisterController.php`, `app/Http/Controllers/Auth/LoginController.php`, `resources/views/auth/register.php`, and `resources/views/auth/login.php`
- [X] T019 [P] [US1] Implement prediction repositories and section-aware payload mapping in `app/Infrastructure/Persistence/Pdo/PdoPredictionSubmissionRepository.php`, `app/Infrastructure/Persistence/Pdo/PdoMatchPredictionRepository.php`, and `app/Infrastructure/Persistence/Pdo/PdoBonusAnswerRepository.php`
- [X] T020 [US1] Implement final submission orchestration and transactional persistence in `app/Application/Predictions/SubmitPredictionService.php` and `app/Application/Predictions/PredictionPayloadValidator.php`
- [X] T021 [US1] Implement knock-out round validation against active competition entities in `app/Application/Predictions/KnockoutPredictionValidator.php` and `app/Infrastructure/Persistence/Pdo/PdoKnockoutRoundRepository.php`
- [X] T022 [US1] Implement participant bonus-question answer validation, persistence rules, and dropdown rendering for entity-backed questions in `app/Application/Predictions/BonusAnswerValidator.php`, `app/Http/ViewModels/PredictionFormViewModel.php`, and `resources/views/participants/prediction-form.php`
- [X] T023 [US1] Implement participant dashboard, prediction form, confirmation page, and read-only rendering in `app/Http/Controllers/Participant/DashboardController.php`, `app/Http/Controllers/Participant/PredictionController.php`, `resources/views/participants/dashboard.php`, `resources/views/participants/prediction-form.php`, and `resources/views/participants/prediction-confirmation.php`
- [X] T024 [US1] Wire participant routes and deadline enforcement in `bootstrap/app.php` and `app/Http/Middleware/EnforceCompetitionDeadline.php`

**Checkpoint**: A participant can complete and final-submit one full prediction, while incomplete or late submissions are rejected and the stored submission is read-only afterward.

---

## Phase 4: User Story 2 - Beheerder beheert competities, deelnemers en puntregels (Priority: P2)

**Goal**: Let administrators manage tournaments, sections, scoring, participants, payment status, bonus questions, knock-out round structure, match data, CSV imports, maintenance actions, and last-admin safety rules without code changes.

**Independent Test**: As an administrator, create or edit a competition, enroll a registered user, configure bonus questions and knock-out rounds, manage group/venue/match data, mark a participant paid/unpaid, run a CSV import, trigger a protected maintenance action, and verify the last active admin cannot be removed or downgraded.

### Tests for User Story 2

- [X] T025 [P] [US2] Add unit tests for competition activation, prize distribution, enrollment, and last-active-admin protection in `tests/Unit/Competitions/UpdateCompetitionServiceTest.php`, `tests/Unit/Competitions/EnrollParticipantServiceTest.php`, and `tests/Unit/Auth/AdminRoleGuardTest.php`
- [X] T026 [P] [US2] Add HTTP contract tests for admin competition, bonus-question, payment, match-management, maintenance, and CSV import routes in `tests/Contract/AdminCompetitionRoutesTest.php`, `tests/Contract/AdminBonusQuestionRoutesTest.php`, `tests/Contract/AdminMatchManagementRoutesTest.php`, and `tests/Contract/CsvImportRoutesTest.php`
- [X] T027 [P] [US2] Add Playwright coverage for admin competition-management, enrollment, and maintenance flows in `tests/E2E/admin-competition-management.spec.ts`

### Implementation for User Story 2

- [X] T028 [P] [US2] Create competition-management and entity-catalog tables in `database/migrations/003_create_competition_management_tables.php` and `database/migrations/004_create_catalog_tables.php`
- [X] T029 [P] [US2] Create bonus-question tables in `database/migrations/005_create_bonus_question_tables.php`
- [X] T030 [P] [US2] Create group, venue, and match management tables in `database/migrations/006_create_match_management_tables.php`
- [X] T031 [P] [US2] Implement competition, section, rule, and participant-management services in `app/Application/Competitions/CreateCompetitionService.php`, `app/Application/Competitions/UpdateCompetitionService.php`, `app/Application/Competitions/UpdateCompetitionSectionsService.php`, `app/Application/Competitions/UpdateCompetitionRulesService.php`, and `app/Application/Competitions/UpdateParticipantPaymentStatusService.php`
- [X] T032 [US2] Implement admin bonus-question management, including entity-backed option configuration for dropdown-based questions, in `app/Application/Competitions/UpdateBonusQuestionsService.php`, `app/Http/Controllers/Admin/BonusQuestionController.php`, and `resources/views/admin/competitions/bonus-questions.php`
- [X] T033 [US2] Implement participant enrollment flow in `app/Application/Competitions/EnrollParticipantService.php` and `app/Http/Controllers/Admin/CompetitionEnrollmentController.php`
- [X] T034 [US2] Implement repositories and logo file storage for admin-managed competition data in `app/Infrastructure/Persistence/Pdo/PdoCompetitionSectionRepository.php`, `app/Infrastructure/Persistence/Pdo/PdoCompetitionRuleRepository.php`, `app/Infrastructure/Persistence/Pdo/PdoCompetitionParticipantRepository.php`, and `app/Infrastructure/Storage/LogoStorage.php`
- [X] T035 [US2] Implement admin competition create/edit/list controllers and SSR forms in `app/Http/Controllers/Admin/CompetitionController.php`, `resources/views/admin/competitions/index.php`, `resources/views/admin/competitions/create.php`, and `resources/views/admin/competitions/edit.php`
- [X] T036 [US2] Implement participant payment management UI with unpaid markers in `app/Http/Controllers/Admin/CompetitionParticipantController.php` and `resources/views/admin/competitions/participants.php`
- [X] T037 [US2] Implement last-active-admin guards for role and status mutations in `app/Application/Auth/UpdateUserRoleService.php`, `app/Application/Auth/UpdateUserStatusService.php`, `app/Http/Controllers/Admin/UserManagementController.php`, and `app/Infrastructure/Persistence/Pdo/PdoAdminAuditLogRepository.php`
- [X] T038 [US2] Implement dynamic knock-out round configuration and slot validation in `app/Domain/Competition/KnockoutRound.php`, `app/Domain/Competition/KnockoutRoundTeam.php`, `app/Application/Competitions/UpdateKnockoutRoundsService.php`, and `resources/views/admin/competitions/knockout-rounds.php`
- [X] T039 [US2] Implement CSV entity import parsing, validation, and transactional commit flow in `app/Application/Imports/EntityCsvImportService.php`, `app/Application/Imports/Csv/EntityCsvParser.php`, `app/Application/Imports/Csv/EntityCsvValidator.php`, `app/Http/Controllers/Admin/EntityImportController.php`, and `resources/views/admin/imports/entities.php`
- [X] T040 [US2] Implement group, venue, and match administration in `app/Http/Controllers/Admin/MatchManagementController.php` and `resources/views/admin/matches/`
- [X] T041 [US2] Implement protected maintenance workflow for migrations and recalculation in `app/Http/Controllers/Admin/MaintenanceController.php` and `resources/views/admin/maintenance/index.php`
- [X] T042 [US2] Implement admin match-result entry in `app/Http/Controllers/Admin/MatchResultController.php` and `resources/views/admin/results/edit.php`
- [X] T043 [US2] Implement manual standings recalculation action in `app/Http/Controllers/Admin/StandingsRecalculationController.php`
- [X] T044 [US2] Wire admin routes, authorization middleware, and CSRF-protected handlers in `bootstrap/app.php` and `app/Http/Middleware/RequireAdmin.php`

**Checkpoint**: Administrators can manage competitions and related reference data entirely through SSR screens while guarded invariants remain enforced server-side.

---

## Phase 5: User Story 3 - Gast en deelnemer bekijken publieke standen en competitie-informatie (Priority: P3)

**Goal**: Publish snapshot-backed competition information, results, main standings, and sub-competition standings to guests and participants without requiring extra privileges.

**Independent Test**: Seed one active competition with results, bonus scoring, and snapshots, then open the landing page, competition page, results page, main standings, and sub-competition standings as a guest and confirm snapshot-based rankings, movement indicators, and public match metadata render correctly.

### Tests for User Story 3

- [ ] T045 [P] [US3] Add unit tests for scoring evaluation and snapshot movement computation in `tests/Unit/Scoring/ScoreCompetitionServiceTest.php` and `tests/Unit/Standings/CreateSnapshotServiceTest.php`
- [ ] T046 [P] [US3] Add HTTP contract tests for public competition, result, and standings routes in `tests/Contract/PublicCompetitionRoutesTest.php`
- [ ] T047 [P] [US3] Add Playwright coverage for public landing and standings views in `tests/E2E/public-standings.spec.ts`

### Implementation for User Story 3

- [ ] T048 [P] [US3] Create result, standings, snapshot, and sub-competition tables in `database/migrations/007_create_results_and_standings_tables.php`
- [ ] T049 [P] [US3] Implement scoring evaluators and snapshot creation services in `app/Application/Scoring/ScoreCompetitionService.php`, `app/Application/Scoring/RuleEvaluators/`, `app/Application/Standings/CreateSnapshotService.php`, and `app/Application/Standings/LatestStandingsQueryService.php`
- [ ] T050 [US3] Implement bonus-question scoring evaluator in `app/Application/Scoring/RuleEvaluators/BonusQuestionEvaluator.php`
- [ ] T051 [P] [US3] Implement result, snapshot, and sub-competition repositories in `app/Infrastructure/Persistence/Pdo/PdoMatchRepository.php`, `app/Infrastructure/Persistence/Pdo/PdoMatchResultRepository.php`, `app/Infrastructure/Persistence/Pdo/PdoStandingsSnapshotRepository.php`, and `app/Infrastructure/Persistence/Pdo/PdoSubCompetitionRepository.php`
- [ ] T052 [US3] Implement public landing, competition-detail, results, and main-standings pages in `app/Http/Controllers/Public/HomeController.php`, `app/Http/Controllers/Public/CompetitionController.php`, `app/Http/Controllers/Public/StandingsController.php`, `resources/views/public/home.php`, `resources/views/public/competition-detail.php`, `resources/views/public/results.php`, and `resources/views/standings/index.php`
- [ ] T053 [US3] Render bonus-question outputs plus group and venue information on public competition/result pages in `resources/views/public/competition-detail.php` and `resources/views/public/results.php`
- [ ] T054 [US3] Implement sub-competition membership management and public sub-standings rendering in `app/Application/Standings/UpdateSubCompetitionMembershipService.php`, `app/Http/Controllers/Admin/SubCompetitionController.php`, `resources/views/admin/sub-competitions/edit.php`, and `resources/views/standings/sub-competition.php`
- [ ] T055 [US3] Wire public and standings routes to snapshot-backed query services in `bootstrap/app.php`

**Checkpoint**: Guests and participants can browse current competition information and standings rendered from the latest snapshots for both main and sub-competitions.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Finish shared documentation, operational hardening, and developer ergonomics that span multiple stories.

- [ ] T056 [P] Add startup, architecture, and Mermaid-backed data-model documentation in `docs/getting-started.md`, `docs/architecture.md`, and `docs/data-model.md`
- [ ] T057 [P] Add reusable SSR partials for validation summaries and payment badges in `resources/views/partials/validation-summary.php` and `resources/views/partials/payment-badge.php`
- [ ] T058 [P] Harden upload security, HTML escaping, and application logging in `app/Infrastructure/Storage/LogoStorage.php`, `app/Support/View/Escaper.php`, and `storage/logs/.gitkeep`
- [ ] T059 [P] Add demo data and manual validation support for quickstart flows in `database/seeders/DemoCompetitionSeeder.php` and `bin/recalculate-standings.php`
- [ ] T060 [P] Add performance validation checks for standings render, prediction submit, and recalculation in `tests/Integration/Performance/`
- [ ] T061 Configure PHPUnit, PHPStan, and Playwright command wrappers in `composer.json` and `package.json`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1: Setup** has no dependencies and starts immediately.
- **Phase 2: Foundational** depends on Phase 1 and blocks all story work.
- **Phase 3: US1** depends on Phase 2 and defines the MVP slice.
- **Phase 4: US2** depends on Phase 2; it can run after US1 or in parallel with later stories if the team uses fixtures and respects shared files.
- **Phase 5: US3** depends on Phase 2 and benefits from US2-managed match/result data, but remains independently testable with seeded fixtures and snapshots.
- **Phase 6: Polish** depends on the stories you choose to ship.

### User Story Dependencies

- **US1 (P1)**: Starts after Foundational and has no dependency on other user stories.
- **US2 (P2)**: Starts after Foundational and should remain independently testable through admin fixtures.
- **US3 (P3)**: Starts after Foundational and should remain independently testable through seeded results and snapshots, even when admin flows are exercised separately.

### Within Each User Story

- Write tests before implementation and confirm they fail for the intended reason.
- Create or update schema before repositories and services that depend on it.
- Implement domain and application services before wiring HTTP controllers and views.
- Wire routes only after the underlying services and middleware exist.

### Dependency Graph

```text
Phase 1 Setup
    -> Phase 2 Foundational
        -> Phase 3 US1 (MVP)
        -> Phase 4 US2
        -> Phase 5 US3
        -> Phase 6 Polish
```

### Parallel Opportunities

- `T002`, `T003`, `T004`, and `T005` can run in parallel after `T001`.
- `T007` through `T011` can run in parallel after `T006`.
- In **US1**, `T013`, `T014`, `T015`, `T016`, `T017`, `T018`, and `T019` are parallel-safe after Phase 2.
- In **US2**, `T025`, `T026`, `T027`, `T028`, `T029`, and `T030` are parallel-safe after Phase 2.
- In **US3**, `T045`, `T046`, `T047`, `T048`, `T049`, and `T051` are parallel-safe after Phase 2.
- `T056`, `T057`, `T058`, `T059`, and `T060` can run in parallel during the final polish phase.

---

## Parallel Example: User Story 1

```bash
# Parallel test work
Task: "T013 Add unit tests in tests/Unit/Predictions/SubmitPredictionServiceTest.php"
Task: "T014 Add route contract tests in tests/Contract/ParticipantPredictionRoutesTest.php"
Task: "T015 Add Playwright flow in tests/E2E/participant-prediction-submission.spec.ts"

# Parallel model/repository work
Task: "T016 Create prediction persistence tables in database/migrations/002_create_prediction_tables.php"
Task: "T017 Create prediction domain models in app/Domain/Prediction/"
Task: "T019 Implement prediction repositories in app/Infrastructure/Persistence/Pdo/"
```

## Parallel Example: User Story 2

```bash
# Parallel verification work
Task: "T025 Add unit tests in tests/Unit/Competitions/UpdateCompetitionServiceTest.php, tests/Unit/Competitions/EnrollParticipantServiceTest.php, and tests/Unit/Auth/AdminRoleGuardTest.php"
Task: "T026 Add contract tests in tests/Contract/AdminCompetitionRoutesTest.php, tests/Contract/AdminBonusQuestionRoutesTest.php, tests/Contract/AdminMatchManagementRoutesTest.php, and tests/Contract/CsvImportRoutesTest.php"
Task: "T027 Add Playwright flow in tests/E2E/admin-competition-management.spec.ts"

# Parallel admin infrastructure work
Task: "T028 Create management tables in database/migrations/003_create_competition_management_tables.php and database/migrations/004_create_catalog_tables.php"
Task: "T029 Create bonus-question tables in database/migrations/005_create_bonus_question_tables.php"
Task: "T030 Create match-management tables in database/migrations/006_create_match_management_tables.php"
```

## Parallel Example: User Story 3

```bash
# Parallel verification work
Task: "T045 Add scoring and snapshot tests in tests/Unit/Scoring/ScoreCompetitionServiceTest.php and tests/Unit/Standings/CreateSnapshotServiceTest.php"
Task: "T046 Add public route contract tests in tests/Contract/PublicCompetitionRoutesTest.php"
Task: "T047 Add Playwright flow in tests/E2E/public-standings.spec.ts"

# Parallel scoring infrastructure work
Task: "T048 Create result and standings tables in database/migrations/007_create_results_and_standings_tables.php"
Task: "T049 Implement scoring and snapshot services in app/Application/Scoring/ and app/Application/Standings/"
Task: "T051 Implement result and snapshot repositories in app/Infrastructure/Persistence/Pdo/"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Finish **Phase 1: Setup**.
2. Finish **Phase 2: Foundational**.
3. Finish **Phase 3: User Story 1**.
4. Validate the participant submission flow end-to-end before expanding scope.

### Incremental Delivery

1. Deliver the platform foundation.
2. Deliver **US1** as the first usable participant-facing release.
3. Deliver **US2** to let administrators configure real tournaments without code changes.
4. Deliver **US3** to publish public standings and competition information.
5. Finish cross-cutting polish and documentation before broad rollout.

### Suggested MVP Scope

- **Recommended MVP**: Phase 1 + Phase 2 + Phase 3 (User Story 1 only)
- **Why**: This delivers the minimum end-to-end value of participant registration, prediction completion, final submission, deadline enforcement, and read-only review.

---

## Notes

- Every task follows the strict checklist format `- [ ] T### [P?] [US?] Description with file path`.
- Tests are intentionally included because `spec.md` requires unit-testable domain logic and Playwright validation.
- User stories remain independently testable by using fixtures and seeded data where cross-story admin screens are not yet complete.
