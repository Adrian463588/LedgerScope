---
name: ai-multimodal
description: "Analyze images/audio/video with Gemini API. Generate images (Imagen 4), videos (Veo 3). Use for vision analysis, transcription, OCR, design extraction, multimodal AI."
argument-hint: "[file-path] [prompt]"
version: 1.0
allowed-tools: Bash, Read, Write, Edit
---

# AI Multimodal

Process audio, images, videos, documents, and generate images/videos using Google Gemini's multimodal API.

## When to Use

- Analyzing images (UI screenshots, designs, photos)
- Transcribing audio or video files
- OCR and document extraction
- Generating images with Imagen 4
- Generating videos with Veo 3
- Describing visual content for accessibility or documentation

## Setup

```bash
export GEMINI_API_KEY="your-key"  # https://aistudio.google.com/apikey
pip install google-genai python-dotenv pillow
```

Verify: `python .agent/skills/ai-multimodal/scripts/check_setup.py`

## Quick Start

```bash
# Analyze image or media file
python .agent/skills/ai-multimodal/scripts/gemini_batch_process.py \
  --files <file> --task analyze --prompt "Describe this"

# Transcribe audio/video
python .agent/skills/ai-multimodal/scripts/gemini_batch_process.py \
  --files audio.mp3 --task transcribe

# Generate image
python .agent/skills/ai-multimodal/scripts/gemini_batch_process.py \
  --task generate --prompt "A minimal logo for a tech startup"

# Generate video
python .agent/skills/ai-multimodal/scripts/gemini_batch_process.py \
  --task generate-video --prompt "A wave crashing on shore"
```

**Tip:** If `gemini` CLI is available, prefer:
```bash
echo "<prompt>" | gemini -y -m gemini-2.5-flash
```

## Models

| Task | Model |
|------|-------|
| Analysis | `gemini-2.5-flash` (fast), `gemini-2.5-pro` (quality) |
| Image generation | `imagen-4.0-generate-001` |
| Image generation (quality) | `imagen-4.0-ultra-generate-001` |
| Video generation | `veo-3.1-generate-preview` (8s clips with audio) |

## Scripts

| Script | Purpose |
|--------|---------|
| `gemini_batch_process.py` | Main CLI for all tasks |
| `media_optimizer.py` | Compress/resize media before upload |
| `document_converter.py` | Convert PDFs/Office docs to markdown |
| `check_setup.py` | Verify setup and API key |

## Format & Size Limits

- **Audio:** WAV/MP3/AAC, max 9.5h, 20MB inline / 2GB File API
- **Images:** PNG/JPEG/WEBP, 20MB inline
- **Video:** MP4/MOV, max 6h, 2GB File API
- **PDF:** up to 1000 pages

## Transcription Rules

- Audio > 15 min: split into ≤15 min chunks, transcribe each, combine
- Video > 15 min: extract audio with ffmpeg, split, transcribe all segments
- Output format: `[HH:MM:SS -> HH:MM:SS] transcript content`

## API Key Rotation

For high-volume usage, set multiple keys:
```bash
export GEMINI_API_KEY="key1"
export GEMINI_API_KEY_2="key2"
export GEMINI_API_KEY_3="key3"
```
Auto-rotates on 429/RESOURCE_EXHAUSTED with 60s cooldown per key.

## References

- `references/vision-understanding.md` — Image analysis patterns
- `references/image-generation.md` — Imagen 4 generation guide
- `references/audio-processing.md` — Transcription and audio analysis
- `references/video-analysis.md` — Video analysis patterns
- `references/video-generation.md` — Veo generation guide
