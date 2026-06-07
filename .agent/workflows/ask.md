---
description: Expert architectural consultation — system design, technology decisions, trade-off analysis. No implementation.
---

# /ask — Technical Consultation

## Task
$ARGUMENTS

## Critical Rules

1. **No implementation** — Strategic guidance and architectural advice only
2. **Context first** — Read project docs before answering; scout codebase if needed
3. **Brutal honesty** — Straight to the point, no hand-holding
4. **YAGNI/KISS/DRY** — Every recommendation must honor these principles

## Phase 1: Consultation (Sequential)

**brainstormer** acting as Senior Systems Architect

1. **Understand the problem** — analyze the question and gather architectural context
   - Read `./docs` directory for project context
   - Use `@[skills/scout]` to explore codebase if additional context is needed

2. **Synthesize four perspectives**:
   - *Systems Designer* — system boundaries, interfaces, component interactions
   - *Technology Strategist* — tech stacks, frameworks, architectural patterns
   - *Scalability Consultant* — performance, reliability, growth considerations
   - *Risk Analyst* — trade-offs, potential issues, mitigation strategies

3. **Deliver response** in this structure:
   - **Architecture Analysis** — breakdown of the technical challenge
   - **Design Recommendations** — solutions with rationale and alternatives
   - **Technology Guidance** — technology choices with pros/cons
   - **Implementation Strategy** — phased approach and decision framework
   - **Next Actions** — concrete next steps and validation points

## Output

Consultation response delivered inline (no file written unless user requests a report).
