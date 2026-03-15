# Copilot Instructions

## Repository shape (high level)

This repository is a Spec‑Kit workflow package for GitHub Copilot (agents + prompts + templates + scripts). Key layers:

- `.github\agents\speckit.*.agent.md` — authoritative workflow definitions (phase rules, validation, handoffs).
- `.github\prompts\speckit.*.prompt.md` — thin wrappers that expose slash commands to users; change the agent file for behavior changes.
- `.specify\templates\*.md` — canonical templates used to generate spec/plan/tasks/checklists.
- `.specify\scripts\powershell\*.ps1` — operational entry points agents call (use PowerShell on Windows).
- `.github\skills` and `.github\instructions` — skill and instruction metadata the agents rely on.

Workflow phases (big picture): constitution → specify → clarify → plan → tasks → analyze → checklist → implement → taskstoissues → orchestrator.

## Build, test, lint commands (what exists)

- package.json contains no repo-defined `build`, `test`, or `lint` scripts. The only verified npm command:

```powershell
npm install
```

- Workflow entry points are PowerShell scripts under `.specify\scripts\powershell` (examples):

```powershell
.specify\scripts\powershell\check-prerequisites.ps1 -Json
.specify\scripts\powershell\create-new-feature.ps1 "<args>" -Json -ShortName "<short-name>" "<feature description>"
.specify\scripts\powershell\setup-plan.ps1 -Json
.specify\scripts\powershell\update-agent-context.ps1 -AgentType copilot
```

Notes:
- There are no tests configured in package.json. To run a single test after adding a test runner, add a `test` script to package.json and invoke `npm test -- <test-file-or-pattern>`.
- Use `pwsh` (PowerShell) on Windows to invoke the scripts when needed. Prefer the `-Json` flags on these scripts to get structured output parseable by agents.

## High-level architecture and responsibilities

- Agents encode the repository behavior and are the source of truth. Prompts are UI-facing and intentionally minimal.
- Templates define artifact shapes (spec.md, plan.md, tasks.md). Agents and scripts assume these templates; change templates first when altering output structure.
- PowerShell scripts perform environment checks, path resolution, and orchestrate template filling. They return JSON for downstream agent consumption.
- Skills under `.github\skills` provide reusable guidance (e.g., php-pro, front-end-developer) that agents can reference.

## Key conventions and patterns (repo-specific)

- Authoritative files and edit order:
  - Edit `.github\agents\*.agent.md` to change workflow logic.
  - Edit `.specify\templates\*.md` to change generated artifact formats.
  - Edit `.github\prompts\*.prompt.md` only for UI-level prompt changes.

- PowerShell expectations:
  - Use absolute Windows-style paths (backslashes) when calling `.specify` scripts.
  - Prefer `-Json` flags and parse JSON output instead of scraping text.
  - Run scripts in PowerShell (pwsh) to preserve cross-platform behavior.

- Task and checklist formats:
  - `speckit.tasks` uses a strict task line format (IDs, parallel marker `[P]`, story tags `[US#]`). Keep IDs sequential.
  - Checklists are requirement-quality artifacts. They validate requirements (complete/clear/measurable/consistent), not implementation status.

- Phase boundaries are enforced in agents. Keep conceptual separation: constitution (principles) → specify (what/why) → plan (how) → implement (code).

- Safety guards:
  - `speckit.taskstoissues` confirms `git remote.origin.url` matches the intended repository before creating issues.
  - Agents expect to run non-destructively; many scripts accept flags for dry-run or JSON-only outputs.

- Editing guidance:
  - When changing behavior, update agents and templates together; look for references to template names inside agent files.
  - Avoid deriving repo behavior from `node_modules` — first-party logic lives in `.github` and `.specify`.

## AI/assistant-related files present

- `.github\agents` (agent specs)
- `.github\prompts` (prompt wrappers)
- `.github\skills` (skill metadata)
- `.github\instructions` (instruction helper docs)
- `.github\copilot-instructions.md` (this file)

When adding new prompts or agents, follow existing frontmatter conventions in `.github\prompts` and `.github\agents`.

## Quick operational examples

- Install deps:

```powershell
cd C:\Github\poule_ai
npm install
```

- Run prerequisite checks (structured JSON output):

```powershell
pwsh -File .specify\scripts\powershell\check-prerequisites.ps1 -Json
```

- Create a new feature scaffold (example):

```powershell
pwsh -File .specify\scripts\powershell\create-new-feature.ps1 -Json -ShortName "myfeat" "Add X feature"
```

## What to update here when things change

- If agent behavior changes, update the corresponding `.github\agents\*.agent.md` and reflect template changes in `.specify\templates`.
- If you add test/build tooling, update this file with exact npm scripts and single‑test invocation examples.

---

Playwright MCP helper added:

- Repository MCP metadata: `.github/mcp-playwright.yml`
- Start helper: `.specify/scripts/powershell/start-playwright-mcp.ps1`

Quick start (PowerShell):

```powershell
# start playwright MCP (returns JSON with pid)
pwsh -File .specify\scripts\powershell\start-playwright-mcp.ps1 -Port 9222
n# connect agents or tools to localhost:9222
```

Update this file if you prefer a different port or want the MCP server started with a process manager (scoped systemd/service on CI).
