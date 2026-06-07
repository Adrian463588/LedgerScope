---
description: Generate visual explanations, architecture diagrams, slides, or ASCII art for code and concepts.
---

# /preview — Visual Explanation

## Task
$ARGUMENTS

## Flags

| Flag | Output |
|------|--------|
| `--explain` | Visual explanation with ASCII + Mermaid diagram (default) |
| `--diagram` | Architecture or data flow diagram (Mermaid) |
| `--slides` | Step-by-step walkthrough as slide deck |
| `--ascii` | Terminal-friendly ASCII diagram only (no browser needed) |

## Critical Rules

1. **Flag determines format** — Default to `--explain` if no flag provided
2. **Save output** — Write visual to `plans/visuals/` (or active plan dir if set)
3. **Mermaid v11 syntax** — Use `@[skills/mermaidjs-v11]` for diagram syntax rules
4. **Serve when possible** — Open result in browser via markdown-novel-viewer if available

## Phase 1: Generate (Sequential)

**docs-manager**

1. Parse the topic and flag from task arguments
2. Load `@[skills/mermaidjs-v11]` for Mermaid v11 syntax rules if producing diagrams
3. Generate the appropriate visual format:
   - `--explain`: ASCII overview + Mermaid component diagram + narrative
   - `--diagram`: Mermaid flowchart/sequence/ER/architecture diagram
   - `--slides`: Numbered markdown sections formatted as slides
   - `--ascii`: Pure ASCII box-and-arrow diagram
4. Save output to `plans/visuals/{slug}.md`
5. Open in browser if markdown-novel-viewer is available

## Output

| Deliverable | Location |
|-------------|----------|
| Visual file | `plans/visuals/{slug}.md` |
