---
name: 'speckit.constitution'
agent: speckit.constitution
model: GPT-5.4
---

# Constitution Prompt

Purpose

Create a concise, opinionated constitution for the project using the Product Requirements Document at .\prd\prd.md as the primary source of truth.

Instructions for Copilot

1. Read .\prd\prd.md fully and extract guiding principles, constraints, quality standards, and non-negotiables.
2. Produce a short, structured constitution covering:
   - Mission and philosophy
   - Constraints and non-negotiables
   - Quality standards and acceptance criteria
   - Operational/ethical guidelines relevant to the project (e.g., shared-hosting constraints, PHP 8.x, security requirements)
3. Keep implementation details out of the constitution. Focus on how decisions should be made.

Inputs

- File: .\prd\prd.md