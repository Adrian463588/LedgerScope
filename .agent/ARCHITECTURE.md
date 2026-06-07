# AntigravityKit Architecture
15 agents | 68 skills | 16 workflows

## Agents

| Agent | Description |
|-------|-------------|
| project-planner | Analyze requirements and create phased implementation plans with todos |
| researcher | Multi-source research on technical topics, produces structured reports |
| debugger | Systematic root-cause analysis, bug isolation, and fix verification |
| code-reviewer | Code quality audit, edge case detection, security review |
| test-engineer | Write and run tests, coverage reports, validate fixes |
| docs-manager | Create and maintain project documentation in docs/ |
| git-manager | Conventional commits, branch management, push workflows |
| fullstack-developer | Implement frontend/backend features, component development |
| brainstormer | Socratic ideation, ask 3+ questions first, explore solution space |
| code-simplifier | Reduce complexity, refactor for readability, apply DRY/KISS |
| journal-writer | Session summaries, decision logs, progress notes |
| project-manager | Milestone tracking, roadmap updates, changelog maintenance |
| ui-ux-designer | Interface design, UX critique, design system compliance |
| orchestrator | Coordinate multi-agent parallel execution (min 3 agents) |
| explorer-agent | Codebase mapping, file discovery, dependency analysis |

---

## Skills

| Skill | Category | Description |
|-------|----------|-------------|
| plan | Core | Create phased implementation plans with todos |
| research | Core | Multi-source research with structured reports |
| scout | Core | Quick codebase scan and dependency mapping |
| debug | Core | Systematic debugging and root-cause analysis |
| cook | Core | Step-by-step task execution framework |
| fix | Core | Targeted bug fixes with minimal side effects |
| ask | Core | Socratic questioning before complex tasks |
| brainstorm | Core | Divergent thinking and idea exploration |
| code-review | Quality | Static analysis, quality gates, anti-pattern detection |
| simplify | Quality | Complexity reduction and refactoring |
| sequential-thinking | Quality | Break problems into ordered reasoning steps |
| problem-solving | Quality | Structured approach to complex technical problems |
| frontend-development | Dev | React/Vue/HTML/CSS implementation patterns |
| backend-development | Dev | API design, server logic, REST/GraphQL patterns |
| databases | Dev | Schema design, queries, migrations, ORMs |
| mobile-development | Dev | React Native / mobile app development |
| devops | Dev | CI/CD, Docker, deployment configuration |
| ui-styling | UI/UX | CSS, Tailwind, component styling patterns |
| ui-ux-pro-max | UI/UX | Full UX design process with accessibility |
| frontend-design | UI/UX | Visual design system implementation |
| test | Testing | Unit/integration test authoring and coverage |
| web-testing | Testing | E2E and browser-based test automation |
| docs | Docs | Technical documentation writing and structure |
| docs-seeker | Docs | Find latest external documentation and references |
| mermaidjs-v11 | Docs | Mermaid v11 diagram syntax rules |
| preview | Docs | Generate visual explanations and ASCII diagrams |
| google-adk-python | AI | Google ADK Python SDK usage patterns |
| ai-api | AI | AI API integration (OpenAI, Anthropic, Gemini) |
| ai-multimodal | AI | Multimodal input handling (images, audio, video) |
| payment-integration | Domain | Stripe and payment provider integration |
| better-auth | Domain | Authentication with Better Auth library |
| bootstrap | Core | Bootstrap new projects — research, tech stack, plan, scaffold |
| media-processing | Utils | Image/video processing with ImageMagick/FFmpeg |
| repomix | Utils | Pack repository for AI context with repomix |
| git | Utils | Git operations, branch strategies, conflict resolution |
| kanban | Utils | Task board management and sprint planning |
| agent-browser | Utils | AI-optimized browser automation and web scraping |
| ai-artist | Creative | AI image generation with curated style prompts |
| chrome-devtools | Dev | Browser automation with Puppeteer, screenshots, network monitoring |
| ck-help | Core | ClaudeKit/AntigravityKit usage guide and command discovery |
| coding-level | Core | Set coding experience level for tailored explanations |
| context-engineering | Core | Monitor context usage, optimize token consumption |
| copywriting | Creative | Conversion copywriting, headlines, email campaigns, landing pages |
| common | Core | Shared utilities and common patterns |
| document-skills | Docs | Document analysis and extraction workflows |
| find-skills | Core | Discover and install agent skills |
| gkg | Dev | Semantic code analysis with GitLab Knowledge Graph |
| journal | Docs | Session summaries and decision logging |
| markdown-novel-viewer | Docs | View markdown with book-like reading experience |
| mcp-builder | Dev | Build MCP servers for LLM-external service integration |
| mcp-management | Dev | Manage MCP servers, discover and execute tools |
| mintlify | Docs | Build and deploy documentation sites with Mintlify |
| plans-kanban | Core | Plans dashboard with progress tracking |
| project-management | Core | Track progress, manage tasks, generate reports |
| react-best-practices | Dev | React and Next.js performance optimization patterns |
| remotion | Dev | Video creation in React with Remotion |
| shader | Dev | GLSL fragment shaders for procedural graphics |
| shopify | Dev | Shopify apps, extensions, themes with Shopify CLI |
| skill-creator | Core | Create or update Claude/Antigravity skills |
| tanstack | Dev | Build with TanStack Start, Form, and AI |
| team | Core | Orchestrate Agent Teams for parallel multi-session collaboration |
| template-skill | Core | Template for creating new skills |
| threejs | Dev | 3D web apps with Three.js (WebGL/WebGPU) |
| use-mcp | Dev | Utilize MCP server tools with intelligent discovery |
| watzup | Core | Review recent changes and wrap up work sessions |
| web-design-guidelines | UI/UX | Review UI code for web interface guidelines compliance |
| web-frameworks | Dev | Build with Next.js App Router, Turborepo monorepos |
| worktree | Dev | Create isolated git worktrees for parallel development |

---

## Workflows

| Command | File | Description | Turbo |
|---------|------|-------------|-------|
| /plan | plan.md | Create phased implementation plan (no code) | No |
| /research | research.md | Multi-source research with structured report | No |
| /orchestrate | orchestrate.md | Parallel multi-agent execution (min 3 agents) | No |
| /debug | debug.md | Systematic debugging + root cause analysis | No |
| /review | review.md | Code quality review + edge case detection | No |
| /test | test.md | Run tests and generate coverage report | Yes |
| /deploy | deploy.md | Deployment workflow | No |
| /docs | docs.md | Update project documentation | No |
| /brainstorm | brainstorm.md | Socratic ideation (asks 3+ questions first) | No |
| /commit | commit.md | Conventional commit + push | Yes |
| /cook | cook.md | Step-by-step task execution framework | No |
| /fix | fix.md | Targeted bug fix with root cause analysis | Yes |
| /bootstrap | bootstrap.md | Bootstrap new project from scratch | No |
| /simplify | simplify.md | Reduce code complexity, apply DRY/KISS | Yes |
| /preview | preview.md | Generate visual explanations and diagrams | No |
| /ask | ask.md | Expert architectural consultation, no implementation | No |

---

## Scripts

| Script | Purpose |
|--------|---------|
| checklist.py | Security + lint + schema + tests + UX gate after every implementation |
| verify_all.py | Full validation suite including Lighthouse and E2E checks |
