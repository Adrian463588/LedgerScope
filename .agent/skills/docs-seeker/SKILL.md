---
name: docs-seeker
description: "Search library/framework documentation via llms.txt (context7.com). Use for API docs, GitHub repository analysis, latest library features, technical documentation lookup."
argument-hint: "[library-name] [topic]"
version: 3.1
allowed-tools: Read, Glob, Grep, Bash, WebFetch
---

# Documentation Discovery

Script-first documentation discovery using the llms.txt standard via context7.com.

## When to Use

- Looking up API docs for a library or framework
- Finding latest features or breaking changes
- Analyzing a GitHub repository's documentation
- Verifying correct usage patterns before implementing

## Primary Workflow

Execute scripts in this order:

```bash
# 1. Detect query type (topic-specific vs general)
node .agent/skills/docs-seeker/scripts/detect-topic.js "<user query>"

# 2. Fetch documentation
node .agent/skills/docs-seeker/scripts/fetch-docs.js "<user query>"

# 3. Analyze if multiple URLs returned
cat llms.txt | node .agent/skills/docs-seeker/scripts/analyze-llms-txt.js -
```

Scripts handle URL construction, fallback chains, and error handling automatically.

## Scripts

| Script | Purpose |
|--------|---------|
| `detect-topic.js` | Classify query; returns `{topic, library, isTopicSpecific}` |
| `fetch-docs.js` | Fetch from context7.com; handles topic → general fallback |
| `analyze-llms-txt.js` | Categorize URLs; recommend agent distribution strategy |

## Quick Examples

**Topic query** — "How do I use date picker in shadcn?":
```bash
node scripts/detect-topic.js "<query>"   # → {isTopicSpecific: true}
node scripts/fetch-docs.js "<query>"     # → 2-3 URLs
# WebFetch those URLs
```

**General query** — "Documentation for Next.js":
```bash
node scripts/detect-topic.js "<query>"              # → {isTopicSpecific: false}
node scripts/fetch-docs.js "<query>"                # → 8+ URLs
cat llms.txt | node scripts/analyze-llms-txt.js -  # → distribution strategy
```

## Fallback Chain

1. Topic-specific context7 URL
2. General library context7 URL
3. Direct GitHub README via WebFetch
4. Official docs homepage via WebFetch

## Environment

Scripts load API keys from: `.env` → `.agent/skills/docs-seeker/.env` → `.agent/skills/.env` → `.agent/.env`

## References

- `references/context7-patterns.md` — URL patterns, known repositories
- `references/errors.md` — Error handling and fallback strategies
- `workflows/topic-search.md` — Fast topic path (10–15s)
- `workflows/library-search.md` — Comprehensive coverage (30–60s)
- `workflows/repo-analysis.md` — GitHub fallback strategy
