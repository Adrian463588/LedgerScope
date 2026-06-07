---
name: sequential-thinking
description: "Apply step-by-step analysis for complex problems with revision capability. Use for multi-step reasoning, hypothesis verification, adaptive planning, problem decomposition."
argument-hint: "[problem to analyze step-by-step]"
version: 1.0
allowed-tools: Read, Glob, Grep, Bash
---

# Sequential Thinking

Structured problem-solving via manageable, reflective thought sequences with dynamic adjustment.

## When to Apply

- Complex problem decomposition
- Adaptive planning with revision capability
- Analysis needing course correction
- Problems with unclear or emerging scope
- Multi-step solutions requiring context maintenance
- Hypothesis-driven investigation or debugging

## Core Process

### 1. Start with Loose Estimate
```
Thought 1/5: [Initial analysis]
```
Adjust total dynamically as understanding evolves.

### 2. Structure Each Thought
- Build on previous context explicitly
- Address one aspect per thought
- State assumptions, uncertainties, realizations

### 3. Dynamic Adjustment
- **Expand** — more complexity discovered → increase total
- **Contract** — simpler than expected → decrease total
- **Revise** — new insight invalidates previous → mark revision
- **Branch** — multiple approaches → explore alternatives

### 4. Revision Format
```
Thought 5/8 [REVISION of Thought 2]: [Corrected understanding]
- Original: [What was stated]
- Why revised: [New insight]
- Impact: [What changes]
```

### 5. Branching
```
Thought 4/7 [BRANCH A from Thought 2]: [Approach A]
Thought 4/7 [BRANCH B from Thought 2]: [Approach B]
```
Compare explicitly, converge with decision rationale.

### 6. Hypothesis Testing
```
Thought 6/9 [HYPOTHESIS]: [Proposed solution]
Thought 7/9 [VERIFICATION]: [Test results]
```

### 7. Completion
Mark final: `Thought N/N [FINAL]`

Complete only when: solution verified, all critical aspects addressed, no outstanding uncertainties.

## Application Modes

- **Explicit** — use visible thought markers when user requests breakdown or complexity warrants it
- **Implicit** — apply methodology internally for routine problem-solving

## References

- `references/core-patterns.md` — Revision and branching patterns
- `references/advanced-techniques.md` — Spiral refinement, convergence
