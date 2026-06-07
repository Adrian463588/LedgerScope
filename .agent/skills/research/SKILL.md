---
name: research
description: "Research technical solutions, analyze architectures, gather requirements thoroughly. Use for technology evaluation, best practices research, solution design, scalability/security/maintainability analysis."
argument-hint: "[topic]"
version: 1.0
allowed-tools: Read, Write, Glob, Grep, Bash, WebFetch
---

# Research

## When to Use

- Technology evaluation and comparison
- Best practices investigation
- Solution design research
- Security and performance analysis
- Library/framework selection

## Process

### Phase 1: Scope Definition
- Identify key terms and concepts
- Determine recency requirements
- Establish evaluation criteria
- Set research depth boundaries

### Phase 2: Information Gathering

- Use `WebFetch` for official documentation, GitHub repos, authoritative blogs
- Max 5 research tool calls; think carefully before each
- When GitHub repo URL found, use `@[skills/docs-seeker]` to read it
- Cross-reference across multiple independent sources

### Phase 3: Analysis and Synthesis
- Identify common patterns and best practices
- Evaluate pros/cons of different approaches
- Assess maturity and stability
- Note security implications and performance considerations

### Phase 4: Report Generation

Save report to `plans/reports/` directory. Structure:

```markdown
# Research Report: [Topic]

## Executive Summary
## Key Findings
### 1. Technology Overview
### 2. Current State & Trends
### 3. Best Practices
### 4. Security Considerations
### 5. Performance Insights
## Comparative Analysis
## Implementation Recommendations
## Resources & References
## Unresolved Questions
```

## Quality Standards

- Accuracy: Verified across multiple sources
- Currency: Prioritize last 12 months
- Completeness: Cover all requested aspects
- Actionability: Practical, implementable recommendations
- Attribution: Always cite sources

## Anti-Patterns

- Researching more than 5 topics per session
- Using outdated docs without noting deprecation
- Skipping cross-reference validation
