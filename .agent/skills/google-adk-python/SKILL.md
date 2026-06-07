---
name: google-adk-python
description: "Build AI agents with Google ADK Python. Multi-agent systems, A2A protocol, MCP tools, workflow agents, state/memory, callbacks/plugins, Vertex AI deployment, evaluation."
argument-hint: "[agent or feature]"
version: 2.0
license: Apache-2.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Google ADK Python

Expert guide for Google's Agent Development Kit (ADK) Python — open-source, code-first toolkit for building, evaluating, and deploying AI agents. Optimized for Gemini, model-agnostic by design.

## When to Use

- Build single or multi-agent systems with tool integration
- Implement A2A protocol for remote agent communication
- Integrate MCP servers as agent tools
- Use workflow agents (sequential, parallel, loop) for pipelines
- Manage sessions, state, memory, and artifacts
- Deploy to Cloud Run, Vertex AI Agent Engine, or GKE

## Required Agent Structure

```
my_agent/
├── __init__.py   # MUST: from . import agent
└── agent.py      # MUST: root_agent = Agent(...) OR app = App(...)
```

## Quick Start

```bash
pip install google-adk
```

```python
from google.adk import Agent

root_agent = Agent(
    name="assistant",
    model="gemini-2.5-flash",
    instruction="You are a helpful assistant.",
    tools=[get_weather],
)
```

## Agent Types

| Type | Use Case |
|------|----------|
| `Agent` / `LlmAgent` | Dynamic routing, tool use, reasoning |
| `SequentialAgent` | Fixed-order pipeline |
| `ParallelAgent` | Concurrent execution |
| `LoopAgent` | Iterative processing |
| `RemoteA2aAgent` | Remote agent via A2A protocol |

## Key APIs

| Feature | API |
|---------|-----|
| State | `tool_context.state[key] = value` |
| Artifacts | `tool_context.save_artifact(name, part)` |
| Callbacks | `before_agent_callback`, `after_model_callback` |
| MCP Tools | `MCPToolset(connection_params=StdioConnectionParams(...))` |
| Sub-agents | `Agent(..., sub_agents=[agent1, agent2])` |

## CLI Tools

```bash
adk web <agents_dir>        # Dev UI
adk run <agent_dir>         # Interactive CLI testing
adk api_server <agents_dir> # FastAPI production server
adk eval <agent> <eval.json> # Run evaluation suite
```

## Models

- Default: `gemini-2.5-flash`
- Advanced: `gemini-2.5-pro`
- Also supported: Anthropic Claude, Ollama, LiteLLM

## Best Practices

1. Code-first — define agents in Python for version control
2. Always use `root_agent` or `app` variable in `agent.py`
3. Specialize agents per domain, compose via `sub_agents`
4. Use workflow agents for predictable, LlmAgent for dynamic
5. Callbacks for guardrails and safety

## Resources

- GitHub: https://github.com/google/adk-python
- Docs: https://google.github.io/adk-docs/

## References

- `references/agent-types-and-architecture.md`
- `references/tools-and-mcp-integration.md`
- `references/multi-agent-and-a2a-protocol.md`
- `references/sessions-state-memory-artifacts.md`
- `references/callbacks-plugins-observability.md`
- `references/evaluation-testing-cli.md`
- `references/deployment-cloud-run-vertex-gke.md`
