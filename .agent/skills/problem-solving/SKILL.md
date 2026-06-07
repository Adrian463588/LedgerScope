---
name: problem-solving
description: "Apply systematic problem-solving techniques when stuck. Use for complexity spirals, innovation blocks, recurring patterns, assumption constraints, scale uncertainty."
argument-hint: "[problem description]"
version: 2.0
allowed-tools: Read, Glob, Grep, Bash
---

# Problem-Solving Techniques

Systematic approaches for different types of stuck-ness. Each technique targets specific problem patterns.

## When to Use

- **Complexity spiraling** — multiple implementations, growing special cases
- **Innovation blocks** — conventional solutions inadequate
- **Recurring patterns** — same issue across domains
- **Assumption constraints** — forced into "only way"
- **Scale uncertainty** — production readiness unclear
- **General stuck-ness** — unsure which technique applies

## Quick Dispatch

| Stuck Symptom | Technique |
|---------------|-----------|
| Same thing implemented 5+ ways, growing special cases | Simplification Cascades |
| Conventional solutions inadequate, need breakthrough | Collision-Zone Thinking |
| Same issue in different places, reinventing wheels | Meta-Pattern Recognition |
| Solution feels forced, "must be done this way" | Inversion Exercise |
| Will this work at production? Edge cases unclear? | Scale Game |

## Core Techniques

### 1. Simplification Cascades
Find one insight eliminating multiple components. "If this is true, we don't need X, Y, Z."
**Red flag:** "Just need to add one more case..." (repeating)

### 2. Collision-Zone Thinking
Force unrelated concepts together. "What if we treated X like Y?"
**Red flag:** "I've tried everything in this domain"

### 3. Meta-Pattern Recognition
Spot patterns appearing in 3+ domains to find universal principles.
**Red flag:** "This problem is unique"

### 4. Inversion Exercise
Flip core assumptions. "What if the opposite were true?"
**Red flag:** "There's only one way to do this"

### 5. Scale Game
Test at extremes (1000x bigger/smaller) to expose fundamental truths.
**Red flag:** "Should scale fine" (without testing)

## Application Process

1. Identify stuck-type — match symptom to technique
2. Load detailed reference for that technique
3. Apply systematically
4. Document insights
5. Combine techniques if needed

## Powerful Combinations

- Simplification + Meta-pattern — find pattern, then simplify all instances
- Collision + Inversion — force metaphor, then invert its assumptions
- Scale + Simplification — extremes reveal what to eliminate

## References

- `references/simplification-cascades.md`
- `references/collision-zone-thinking.md`
- `references/meta-pattern-recognition.md`
- `references/inversion-exercise.md`
- `references/scale-game.md`
- `references/when-stuck.md` — dispatch flowchart
