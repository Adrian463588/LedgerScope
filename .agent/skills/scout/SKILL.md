---
name: scout
description: "Fast codebase scouting using parallel agents. Use for file discovery, task context gathering, quick searches across directories."
argument-hint: "[search-target]"
version: 1.0
allowed-tools: Read, Glob, Grep, Bash
---

# Scout

Fast, token-efficient codebase scouting to find files needed for tasks.

## When to Use

- Beginning work on a feature spanning multiple directories
- User needs to "find", "locate", or "search for" files
- Starting a debugging session requiring file relationship understanding
- Before changes that might affect multiple codebase parts

## Process

### 1. Analyze Task
- Parse prompt for search targets
- Identify key directories, patterns, file types
- Estimate scale of codebase

### 2. Search Strategy
- Use wide range of Grep and Glob patterns in parallel
- Divide codebase into logical segments
- Ensure no overlap, maximize coverage

### 3. Collect Results
- Aggregate findings into concise report
- List unresolved questions at end

## Report Format

```markdown
# Scout Report

## Relevant Files
- `path/to/file.ts` - Brief description

## Key Patterns Found
- [patterns, conventions observed]

## Unresolved Questions
- [gaps in findings]
```

## Anti-Patterns

- Reading every file in full (use Grep/Glob for discovery)
- Spending more than 3 minutes on scouting
- Missing subdirectories due to shallow search
