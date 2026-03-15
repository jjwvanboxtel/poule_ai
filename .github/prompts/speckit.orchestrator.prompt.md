---
name: 'Generate Spec-Kit prompts'
description: 'Create Spec-Kit prompt files from the repository PRD'
agent: speckit.orchestrator
model: GPT-5.4
---

# Generate Spec-Kit prompt files from the repository PRD

Use the product requirements document at `.\prd\prd.md` as the input for this workflow.

Read `.\prd\prd.md` first, then use its contents as the project description for the `speckit.orchestrator` agent.
