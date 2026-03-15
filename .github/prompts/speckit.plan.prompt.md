---
name: 'speckit.plan'
agent: speckit.plan
model: GPT-5.4
---

# Plan Prompt

Purpose

Create a technical plan (how) using .\prd\prd.md, the Constitution, and Specification documents.

Instructions for Copilot

1. Read .\prd\prd.md, Constitution, and Specification.
2. Recommend tech stack, system architecture, key components, integration points, risks/tradeoffs, and an implementation roadmap.
3. Avoid repeating principles or problem statements verbatim; reference them instead.

Inputs

- File: .\prd\prd.md
