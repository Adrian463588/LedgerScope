---
description: Socratic ideation — asks strategic questions before proposing solutions. Use for architecture decisions and feature design.
---

# /brainstorm — Structured Ideation

## Task
$ARGUMENTS

## Critical Rules

1. **Questions first, always** — Ask 3–5 strategic questions BEFORE proposing any solution
2. **Wait for answers** — Do not generate options until user responds to questions
3. **Minimum 3 approaches** — Present at least 3 meaningfully distinct options
4. **No implementation** — This workflow is ideas only; no code changes

## Phase 1: Socratic Gate (MANDATORY)

**brainstormer agent** — Before generating any options:

Analyze `$ARGUMENTS` and ask 3–5 targeted questions to understand constraints. Example question types:

- **Scale**: How many users / requests / records at launch vs. 1 year?
- **Team**: Who will maintain this? What's the team's familiarity with the tech?
- **Constraints**: Any existing tech stack lock-in, budget, or timeline limits?
- **Priority**: Optimize for speed-to-market, long-term maintainability, or performance?
- **Integration**: What systems must this connect to?

Format:
```
Before I propose solutions, I need to understand a few things:

1. [Question 1]
2. [Question 2]
3. [Question 3]
4. [Question 4 — if relevant]
5. [Question 5 — if relevant]
```

**STOP here. Wait for user to answer questions.**

## Phase 2: Option Generation (after user answers)

Generate 3+ distinct approaches. Each option must cover:

```markdown
### Option [A/B/C]: [Name]

[2–3 sentence description]

**Pros:**
- [benefit 1]
- [benefit 2]

**Cons:**
- [drawback 1]
- [drawback 2]

**Best when:** [scenario where this is optimal]
**Effort:** Low | Medium | High
**Risk:** Low | Medium | High
```

Options must be genuinely different — not minor variations of the same approach.

## Phase 3: Recommendation

After presenting all options:

```markdown
## Recommendation

Given [key constraints from user answers], **Option [X]** is most appropriate because:
- [Reason 1 tied to user's constraints]
- [Reason 2]

**Alternative:** Option [Y] if [specific condition].

What direction would you like to explore further?
```

## Output

Inline in conversation — no file written unless user requests it.
If user requests file: `plans/reports/brainstorm-YYMMDD-HHMM-{slug}.md`
