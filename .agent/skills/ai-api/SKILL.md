---
name: ai-api
description: "Integrate AI APIs — Anthropic Claude, OpenAI, Gemini. Use for chat completions, streaming, tool use, vision, embeddings, structured output, prompt engineering."
argument-hint: "[provider] [task]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# AI API Integration

Integrate and work with AI provider APIs: Anthropic Claude, OpenAI, and Google Gemini.

## When to Use

- Integrating Claude/OpenAI/Gemini into applications
- Implementing streaming chat responses
- Tool use / function calling patterns
- Vision and multimodal inputs
- Embeddings for search or similarity
- Structured output / JSON mode
- Prompt engineering and optimization

## Provider Quick Reference

### Anthropic Claude

```typescript
import Anthropic from "@anthropic-ai/sdk";

const client = new Anthropic({ apiKey: process.env.ANTHROPIC_API_KEY });

const message = await client.messages.create({
  model: "claude-opus-4-5",
  max_tokens: 1024,
  messages: [{ role: "user", content: "Hello" }],
});
```

**Streaming:**
```typescript
const stream = client.messages.stream({ model: "claude-opus-4-5", ... });
for await (const chunk of stream) { /* process */ }
```

### OpenAI

```typescript
import OpenAI from "openai";
const openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });

const completion = await openai.chat.completions.create({
  model: "gpt-4o",
  messages: [{ role: "user", content: "Hello" }],
});
```

### Google Gemini

```python
from google import genai
client = genai.Client(api_key=os.environ["GEMINI_API_KEY"])
response = client.models.generate_content(
    model="gemini-2.5-flash", contents="Hello"
)
```

## Tool Use / Function Calling

Define tools with JSON schema, pass to API, handle tool_use blocks in response, return results, continue conversation. All three providers support this pattern.

## Structured Output

- **Claude:** Use `<json>` tags in prompt or `tool_use` pattern
- **OpenAI:** `response_format: { type: "json_schema", json_schema: {...} }`
- **Gemini:** `response_mime_type: "application/json"` with schema

## Best Practices

- Store API keys in environment variables — never in code
- Implement retry logic with exponential backoff for rate limits
- Set `max_tokens` explicitly to avoid runaway costs
- Use streaming for user-facing responses > 1 sentence
- Log token usage for cost monitoring
- Cache identical prompts where possible

## Anti-Patterns

- Hardcoding API keys or model names
- No error handling for rate limits (429) or server errors (500/529)
- Blocking the main thread with synchronous calls in web servers
- Sending entire conversation history without trimming

## References

- `references/claude-api.md` — Anthropic SDK patterns, tool use, vision
- `references/openai-api.md` — OpenAI SDK patterns, assistants, embeddings
- `references/gemini-api.md` — Gemini SDK, multimodal, grounding
- `references/prompt-engineering.md` — Prompt patterns and optimization
- `references/streaming.md` — Streaming implementation patterns
