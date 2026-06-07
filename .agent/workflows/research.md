---
description: Multi-source technical research with parallel agents. Produces structured report with recommendations.
---

# /research — Technical Research

## Task
$ARGUMENTS

## Critical Rules

1. **Parallel agents** — Split topic into sub-topics, run up to 3 researcher agents simultaneously
2. **No implementation** — Research and report only; no code changes
3. **Cite sources** — Reference documentation, GitHub issues, RFCs, benchmarks where applicable
4. **Unresolved questions** — Always list open questions at end of report

## Phase 1: Topic Decomposition (Sequential)

**project-planner** or lead agent:
- Analyze `$ARGUMENTS` and decompose into 2–3 distinct research sub-topics
- Assign one sub-topic per researcher agent

## Phase 2: Parallel Research

Spawn up to 3 researcher agents simultaneously, each covering one sub-topic:

- **researcher-1**: Core concept, fundamentals, official documentation
- **researcher-2**: Implementation patterns, libraries, tradeoffs, real-world usage
- **researcher-3**: Security, performance, edge cases, migration considerations

Each researcher:
1. Uses `docs-seeker` skill (context7) to find latest docs
2. Searches GitHub for examples and issues
3. Synthesizes findings into structured notes

## Phase 3: Synthesis (Sequential)

Lead agent consolidates all researcher outputs into a single report:

**Report structure:**
```markdown
# Research Report: [Topic]

## Executive Summary
[3–5 bullet points of key findings]

## Findings

### Sub-topic 1: [Name]
[Detailed findings]

### Sub-topic 2: [Name]
[Detailed findings]

### Sub-topic 3: [Name]
[Detailed findings]

## Recommendations
1. [Most recommended approach + rationale]
2. [Alternative if constraints apply]

## Tradeoffs Matrix
| Option | Pros | Cons | Effort |
|--------|------|------|--------|

## Unresolved Questions
- [Question 1]
- [Question 2]
```

## Output

| Deliverable | Location |
|-------------|----------|
| Final research report | `plans/reports/researcher-YYMMDD-HHMM-{slug}.md` |
| Sub-topic notes (optional) | `plans/reports/researcher-YYMMDD-HHMM-{subtopic}.md` |
