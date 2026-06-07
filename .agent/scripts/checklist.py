#!/usr/bin/env python3
"""
AntigravityKit Checklist — Run after every implementation.

Orchestrates validation in priority order:
  P0: Security (secrets, vulnerabilities)
  P1: Lint / type check
  P2: Tests (pytest, npm test)

Usage:
    python .agent/scripts/checklist.py [project_root] [--url URL]
"""

import sys
import subprocess
import argparse
from pathlib import Path
from typing import Optional


# ---------------------------------------------------------------------------
# Terminal output helpers
# ---------------------------------------------------------------------------

class Colors:
    GREEN  = '\033[92m'
    YELLOW = '\033[93m'
    RED    = '\033[91m'
    CYAN   = '\033[96m'
    BOLD   = '\033[1m'
    ENDC   = '\033[0m'


def _header(text: str) -> None:
    print(f"\n{Colors.BOLD}{Colors.CYAN}{'=' * 60}{Colors.ENDC}")
    print(f"{Colors.BOLD}{Colors.CYAN}{text.center(60)}{Colors.ENDC}")
    print(f"{Colors.BOLD}{Colors.CYAN}{'=' * 60}{Colors.ENDC}\n")


def _ok(text: str)   -> None: print(f"{Colors.GREEN}  PASS  {Colors.ENDC}{text}")
def _warn(text: str) -> None: print(f"{Colors.YELLOW}  SKIP  {Colors.ENDC}{text}")
def _fail(text: str) -> None: print(f"{Colors.RED}  FAIL  {Colors.ENDC}{text}")


# ---------------------------------------------------------------------------
# Core runner
# ---------------------------------------------------------------------------

def run_check(
    name: str,
    cmd: list,
    cwd: str = ".",
    timeout: int = 60,
) -> bool:
    """
    Execute cmd, print result, return True on success.
    Returns True (skip) when the tool is not installed.
    """
    print(f"\n[{name}]", end=" ", flush=True)
    try:
        result = subprocess.run(
            cmd,
            cwd=cwd,
            capture_output=True,
            text=True,
            timeout=timeout,
        )
        if result.returncode == 0:
            _ok(name)
            return True
        _fail(name)
        # Show tail of output to keep noise low
        for stream in (result.stdout, result.stderr):
            if stream:
                print(stream.strip()[-500:])
        return False
    except FileNotFoundError:
        _warn(f"{name} (tool not found — skipped)")
        return True  # non-blocking skip
    except subprocess.TimeoutExpired:
        print(f"\n{Colors.RED}TIMEOUT{Colors.ENDC}")
        return False


# ---------------------------------------------------------------------------
# Check definitions
# ---------------------------------------------------------------------------

def _security_check(project_root: str) -> bool:
    """Grep for hardcoded secrets in source files."""
    patterns = "password=|api_key=|secret_key=|private_key=|access_token="
    result = subprocess.run(
        [
            "grep", "-r", "-i", "-l",
            "--include=*.py", "--include=*.js", "--include=*.ts",
            "--include=*.env", "--include=*.json",
            "-E", patterns,
            project_root,
        ],
        capture_output=True,
        text=True,
    )
    # grep exits 0 when matches found (= secrets present = FAIL)
    if result.returncode == 0 and result.stdout.strip():
        _fail("Security: potential secrets found in:")
        print(result.stdout.strip())
        return False
    _ok("Security: no hardcoded secrets detected")
    return True


def build_checks(project_root: str, url: Optional[str]) -> list:
    """Return ordered list of (name, callable_or_cmd) tuples."""
    root = project_root
    checks = [
        # P0 — Security
        ("Security scan",        lambda: _security_check(root)),
        # P1 — Lint
        ("Lint: Python (ruff)",  lambda: run_check("ruff", ["ruff", "check", root])),
        ("Lint: JS/TS (eslint)",  lambda: run_check(
            "eslint",
            ["npx", "--yes", "eslint", root, "--ext", ".js,.ts,.tsx", "--max-warnings=0"],
        )),
        ("Types: tsc",           lambda: run_check("tsc", ["npx", "tsc", "--noEmit"], cwd=root)),
        # P2 — Tests
        ("Tests: pytest",        lambda: run_check(
            "pytest", ["python", "-m", "pytest", "--tb=short", "-q"], cwd=root,
        )),
        ("Tests: npm test",      lambda: run_check(
            "npm test", ["npm", "test", "--", "--passWithNoTests"], cwd=root, timeout=120,
        )),
    ]
    return checks


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main() -> None:
    parser = argparse.ArgumentParser(
        description="AntigravityKit validation checklist",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("project", nargs="?", default=".", help="Project root path")
    parser.add_argument("--url", help="Live URL (reserved for future performance checks)")
    args = parser.parse_args()

    project_root = str(Path(args.project).resolve())

    _header("AntigravityKit — Validation Checklist")
    print(f"Project: {project_root}")

    checks = build_checks(project_root, args.url)
    results = []

    for name, fn in checks:
        try:
            passed = fn()
        except Exception as exc:
            _fail(f"{name}: unexpected error — {exc}")
            passed = False
        results.append((name, passed))

    # Summary
    _header("Results")
    passed_n = sum(1 for _, p in results if p)
    total_n  = len(results)
    for name, passed in results:
        status = f"{Colors.GREEN}PASS{Colors.ENDC}" if passed else f"{Colors.RED}FAIL{Colors.ENDC}"
        print(f"  [{status}] {name}")

    print(f"\n{passed_n}/{total_n} checks passed")

    if passed_n < total_n:
        print(f"\n{Colors.RED}ACTION REQUIRED: fix failures before continuing.{Colors.ENDC}")
        sys.exit(1)
    else:
        print(f"\n{Colors.GREEN}All checks passed!{Colors.ENDC}")


if __name__ == "__main__":
    main()
