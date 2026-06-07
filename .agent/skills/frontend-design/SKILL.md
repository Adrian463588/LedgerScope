---
name: frontend-design
description: "Create polished frontend interfaces from designs, screenshots, or videos. Use for UI replication, 3D experiences, prototypes, immersive interfaces, avoiding AI slop."
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Frontend Design

Create distinctive, production-grade frontend interfaces with exceptional aesthetic attention.

## When to Use

- Replicating a design from screenshot or video
- Building immersive or award-quality UIs
- Creating 3D/WebGL experiences
- Rapid prototyping from scratch
- Avoiding generic "AI slop" aesthetics

## Workflow Selection

| Input | Workflow |
|-------|----------|
| Screenshot | Replicate exactly — `references/workflow-screenshot.md` |
| Video | Replicate with animations — `references/workflow-video.md` |
| 3D/WebGL request | Three.js immersive — `references/workflow-3d.md` |
| Quick task | Rapid implementation — `references/workflow-quick.md` |
| Complex/award-quality | Full immersive — `references/workflow-immersive.md` |
| From scratch | Design Thinking below |

**All workflows:** Activate `@[skills/ui-ux-pro-max]` FIRST for design intelligence.

## Screenshot/Video Replication

1. **Analyze** with `@[skills/ai-multimodal]` — extract colors, fonts, spacing, effects
2. **Plan** — create phased implementation
3. **Implement** — match source precisely
4. **Verify** — compare to original side-by-side
5. **Document** — update `./docs/design-guidelines.md` if approved

## Design Thinking (From Scratch)

Before coding, commit to a BOLD aesthetic direction:

- **Purpose** — what problem does this interface solve? Who uses it?
- **Tone** — pick an extreme: brutally minimal, maximalist, retro-futuristic, editorial, luxury, brutalist, etc.
- **Differentiation** — what makes this unforgettable?

**CRITICAL:** Execute with precision. Intentionality is everything.

## Aesthetics Guidelines

- **Typography** — avoid generic fonts (Arial, Inter); use distinctive characterful fonts
- **Color** — cohesive palette with dominant + sharp accent; use CSS variables
- **Motion** — CSS-first; anime.js for complex sequences; orchestrated > scattered
- **Spatial** — unexpected layouts, asymmetry, overlap, negative space
- **Backgrounds** — atmosphere over solid colors: gradients, noise, grain
- **Assets** — generate with `@[skills/ai-multimodal]`

## Anti-Patterns

- Generic centered card layout with Inter font
- Flat blue/white corporate palette without personality
- Scattered micro-animations with no coherent motion story
- Copying Bootstrap/Material patterns without intent

## References

- `references/workflow-screenshot.md` — Pixel-perfect replication
- `references/workflow-immersive.md` — Award-quality full build
- `references/workflow-3d.md` — Three.js and WebGL
- `references/animejs.md` — Animation orchestration
- `references/asset-generation.md` — AI asset generation
- `references/visual-analysis-overview.md` — Design quality analysis
