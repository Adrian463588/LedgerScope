---
name: preview
description: "View files/directories OR generate visual explanations, slides, diagrams. Use for rendering markdown, creating ASCII/Mermaid diagrams, visual walkthroughs."
argument-hint: "[path] OR --explain|--slides|--diagram|--ascii [topic]"
version: 1.0
allowed-tools: Read, Write, Bash
---

# Preview

Universal viewer and visual generator. View existing content OR generate new visual explanations.

## Operations

| Operation | Description |
|-----------|-------------|
| `(view)` | View a file or directory in browser |
| `--explain <topic>` | Generate visual explanation (ASCII + Mermaid + prose) |
| `--slides <topic>` | Generate presentation slides |
| `--diagram <topic>` | Generate focused architecture diagram |
| `--ascii <topic>` | Terminal-friendly ASCII-only diagram |
| `--stop` | Stop preview server |

## Usage

```
@[skills/preview] <file.md>           # View markdown file
@[skills/preview] <directory/>        # Browse directory
@[skills/preview] --explain <topic>   # Visual explanation
@[skills/preview] --slides <topic>    # Presentation slides
@[skills/preview] --diagram <topic>   # Architecture diagram
@[skills/preview] --ascii <topic>     # ASCII diagram (no browser needed)
@[skills/preview] --stop              # Stop running server
```

## Argument Resolution Priority

1. `--stop` → stop server
2. Generation flags (`--explain`, `--slides`, `--diagram`, `--ascii`) → generation mode
3. Explicit file path → view mode
4. Contextual reference → resolve from recent conversation
5. Unresolvable → ask user to clarify

## Generation Output

Visual outputs saved to `plans/visuals/` by default.

**Topic-to-slug conversion:**
- Lowercase, spaces → hyphens, strip special chars
- Max 80 chars, truncate at word boundary

## Error Handling

| Error | Action |
|-------|--------|
| Flag without topic | Ask: "Please provide a topic" |
| File not found | Ask user to clarify path |
| Server already running | Reuse existing instance |
| Parent dir missing | Create directories recursively |

## References

- `references/generation-modes.md` — Generation mode details and templates
- `references/view-mode.md` — View mode server setup
