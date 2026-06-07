#!/usr/bin/env python3
"""
AntigravityKit Catalog Generator — lists all agents, skills, and workflows.

Reads YAML frontmatter from .agent/{agents,skills,workflows} directories
and prints a formatted catalog. Useful for discovery and documentation.

Usage:
    python .agent/scripts/generate_catalogs.py [--agents] [--skills] [--workflows] [--all]
"""

import sys
import re
import argparse
from pathlib import Path


# ---------------------------------------------------------------------------
# Paths — relative to this script's parent (.agent/)
# ---------------------------------------------------------------------------

AGENT_DIR    = Path(__file__).parent.parent / "agents"
SKILL_DIR    = Path(__file__).parent.parent / "skills"
WORKFLOW_DIR = Path(__file__).parent.parent / "workflows"


# ---------------------------------------------------------------------------
# Terminal helpers
# ---------------------------------------------------------------------------

class Colors:
    CYAN  = '\033[96m'
    BOLD  = '\033[1m'
    ENDC  = '\033[0m'


def _header(text: str) -> None:
    print(f"\n{Colors.BOLD}{Colors.CYAN}{'=' * 55}{Colors.ENDC}")
    print(f"{Colors.BOLD}{Colors.CYAN}  {text}{Colors.ENDC}")
    print(f"{Colors.BOLD}{Colors.CYAN}{'=' * 55}{Colors.ENDC}")


# ---------------------------------------------------------------------------
# Frontmatter helpers
# ---------------------------------------------------------------------------

def _frontmatter_field(content: str, field: str) -> str:
    """Extract a scalar value from YAML frontmatter (--- block)."""
    # Match inside the frontmatter block only
    fm_match = re.match(r"^---\n(.*?)\n---", content, re.DOTALL)
    if not fm_match:
        return ""
    block = fm_match.group(1)
    match = re.search(rf"^{re.escape(field)}:\s*(.+)$", block, re.MULTILINE)
    return match.group(1).strip().strip('"').strip("'") if match else ""


def _has_turbo(content: str) -> bool:
    """Return True if the file contains the // turbo annotation."""
    return "// turbo" in content


# ---------------------------------------------------------------------------
# Catalog builders
# ---------------------------------------------------------------------------

def catalog_agents() -> list[dict]:
    """Read agent markdown files and extract metadata."""
    agents = []
    if not AGENT_DIR.exists():
        return agents
    for f in sorted(AGENT_DIR.glob("*.md")):
        try:
            content = f.read_text(encoding="utf-8")
        except OSError:
            continue
        agents.append({
            "name":        _frontmatter_field(content, "name") or f.stem,
            "description": _frontmatter_field(content, "description"),
            "skills":      _frontmatter_field(content, "skills"),
        })
    return agents


def catalog_skills() -> list[dict]:
    """Read SKILL.md files inside each skill sub-directory."""
    skills = []
    if not SKILL_DIR.exists():
        return skills
    for skill_dir in sorted(SKILL_DIR.iterdir()):
        if not skill_dir.is_dir():
            continue
        skill_file = skill_dir / "SKILL.md"
        if not skill_file.exists():
            continue
        try:
            content = skill_file.read_text(encoding="utf-8")
        except OSError:
            continue
        skills.append({
            "name":        _frontmatter_field(content, "name") or skill_dir.name,
            "description": _frontmatter_field(content, "description"),
            "priority":    _frontmatter_field(content, "priority"),
        })
    return skills


def catalog_workflows() -> list[dict]:
    """Read workflow markdown files and extract metadata."""
    workflows = []
    if not WORKFLOW_DIR.exists():
        return workflows
    for f in sorted(WORKFLOW_DIR.glob("*.md")):
        try:
            content = f.read_text(encoding="utf-8")
        except OSError:
            continue
        workflows.append({
            "command":     f"/{f.stem}",
            "description": _frontmatter_field(content, "description"),
            "turbo":       _has_turbo(content),
        })
    return workflows


# ---------------------------------------------------------------------------
# Display
# ---------------------------------------------------------------------------

def _truncate(text: str, width: int) -> str:
    return text[:width - 1] + "…" if len(text) > width else text


def print_catalog(
    agents: list[dict],
    skills: list[dict],
    workflows: list[dict],
) -> None:
    if agents:
        _header(f"Agents ({len(agents)})")
        for a in agents:
            desc = _truncate(a["description"], 55)
            print(f"  {a['name']:<28} {desc}")

    if skills:
        _header(f"Skills ({len(skills)})")
        for s in skills:
            priority = f"[{s['priority']}]" if s["priority"] else ""
            desc     = _truncate(s["description"], 50)
            print(f"  {s['name']:<28} {desc:<50} {priority}")

    if workflows:
        _header(f"Workflows ({len(workflows)})")
        for w in workflows:
            turbo = " [turbo]" if w["turbo"] else ""
            desc  = _truncate(w["description"], 52)
            print(f"  {w['command']:<20} {desc}{turbo}")

    total = len(agents) + len(skills) + len(workflows)
    print(f"\nTotal: {len(agents)} agents · {len(skills)} skills · {len(workflows)} workflows  ({total} items)\n")


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main() -> None:
    parser = argparse.ArgumentParser(
        description="Generate AntigravityKit component catalog",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("--agents",    action="store_true", help="Show agents only")
    parser.add_argument("--skills",    action="store_true", help="Show skills only")
    parser.add_argument("--workflows", action="store_true", help="Show workflows only")
    parser.add_argument("--all",       action="store_true", help="Show everything (default)")
    args = parser.parse_args()

    # Default to --all when no filter given
    show_all       = args.all or not (args.agents or args.skills or args.workflows)
    show_agents    = args.agents    or show_all
    show_skills    = args.skills    or show_all
    show_workflows = args.workflows or show_all

    agents    = catalog_agents()    if show_agents    else []
    skills    = catalog_skills()    if show_skills    else []
    workflows = catalog_workflows() if show_workflows else []

    print_catalog(agents, skills, workflows)


if __name__ == "__main__":
    main()
