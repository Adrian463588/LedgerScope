#!/usr/bin/env python3
"""
AntigravityKit Full Verification Suite — run before deployment or major releases.

Executes all checks from checklist.py, then adds:
  - Playwright E2E
  - Lighthouse audit  (requires --url)
  - Bundle size check
  - axe accessibility (requires --url)

Usage:
    python .agent/scripts/verify_all.py [project_root] --url http://localhost:3000
    python .agent/scripts/verify_all.py . --no-e2e
"""

import sys
import subprocess
import argparse
from pathlib import Path
from datetime import datetime
from typing import Optional


# ---------------------------------------------------------------------------
# Terminal helpers
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
# Runners
# ---------------------------------------------------------------------------

def run_script(script_name: str, extra_args: list = []) -> bool:
    """Run a sibling script in this directory and return success status."""
    script_path = Path(__file__).parent / script_name
    cmd = [sys.executable, str(script_path)] + extra_args
    result = subprocess.run(cmd)
    return result.returncode == 0


def run_check(
    name: str,
    cmd: list,
    cwd: str = ".",
    timeout: int = 120,
) -> bool:
    """Run an external command, print result, return True on success."""
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
        if result.stdout:
            print(result.stdout.strip()[-400:])
        if result.stderr:
            print(result.stderr.strip()[-400:])
        return False
    except FileNotFoundError:
        _warn(f"{name} (tool not found — skipped)")
        return True   # non-blocking
    except subprocess.TimeoutExpired:
        print(f"\n{Colors.RED}TIMEOUT{Colors.ENDC}")
        return False


# ---------------------------------------------------------------------------
# Suite sections
# ---------------------------------------------------------------------------

def run_base_checklist(project_root: str, url: Optional[str]) -> bool:
    _header("Base Checklist")
    args = [project_root]
    if url:
        args += ["--url", url]
    return run_script("checklist.py", args)


def run_e2e(project_root: str) -> bool:
    _header("E2E Tests")
    return run_check(
        "Playwright E2E",
        ["npx", "playwright", "test", "--reporter=line"],
        cwd=project_root,
        timeout=300,
    )


def run_bundle(project_root: str) -> bool:
    _header("Bundle Analysis")
    return run_check(
        "Bundle size (bundlesize)",
        ["npx", "bundlesize"],
        cwd=project_root,
    )


def run_lighthouse(url: str) -> bool:
    _header("Lighthouse Audit")
    return run_check(
        "Lighthouse",
        [
            "npx", "lighthouse", url,
            "--output=json",
            "--quiet",
            "--chrome-flags=--headless",
            "--only-categories=performance,accessibility,best-practices,seo",
        ],
        timeout=180,
    )


def run_accessibility(url: str) -> bool:
    _header("Accessibility")
    return run_check(
        "axe-core",
        ["npx", "axe", url, "--exit"],
        timeout=60,
    )


# ---------------------------------------------------------------------------
# Report
# ---------------------------------------------------------------------------

def _print_summary(results: list, start: datetime) -> bool:
    duration = (datetime.now() - start).total_seconds()
    _header("Full Verification Report")

    passed_n  = sum(1 for _, p in results if p)
    failed_n  = len(results) - passed_n

    print(f"Duration : {duration:.1f}s")
    print(f"Total    : {len(results)}")
    print(f"{Colors.GREEN}Passed   : {passed_n}{Colors.ENDC}")
    print(f"{Colors.RED}Failed   : {failed_n}{Colors.ENDC}\n")

    for name, passed in results:
        status = f"{Colors.GREEN}PASS{Colors.ENDC}" if passed else f"{Colors.RED}FAIL{Colors.ENDC}"
        print(f"  [{status}] {name}")

    print()
    if failed_n:
        print(f"{Colors.RED}FAILURES FOUND — address before deployment.{Colors.ENDC}")
        return False
    print(f"{Colors.GREEN}Full verification passed! Ready for deployment.{Colors.ENDC}")
    return True


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main() -> None:
    parser = argparse.ArgumentParser(
        description="AntigravityKit full verification suite",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("project", nargs="?", default=".", help="Project root path")
    parser.add_argument("--url", help="Live URL for Lighthouse / axe / E2E")
    parser.add_argument("--no-e2e",   action="store_true", help="Skip Playwright E2E tests")
    parser.add_argument("--no-lighthouse", action="store_true", help="Skip Lighthouse audit")
    args = parser.parse_args()

    project_root = str(Path(args.project).resolve())
    start = datetime.now()

    _header("AntigravityKit — Full Verification Suite")
    print(f"Project  : {project_root}")
    print(f"URL      : {args.url or 'not provided (some checks skipped)'}")
    print(f"Started  : {start.strftime('%Y-%m-%d %H:%M:%S')}")

    results: list[tuple[str, bool]] = []

    # Always run base checklist
    results.append(("Base checklist", run_base_checklist(project_root, args.url)))

    # Bundle (no URL needed)
    results.append(("Bundle size", run_bundle(project_root)))

    # E2E (no URL required by Playwright itself)
    if not args.no_e2e:
        results.append(("Playwright E2E", run_e2e(project_root)))

    # URL-dependent checks
    if args.url:
        if not args.no_lighthouse:
            results.append(("Lighthouse", run_lighthouse(args.url)))
        results.append(("axe accessibility", run_accessibility(args.url)))

    all_passed = _print_summary(results, start)
    sys.exit(0 if all_passed else 1)


if __name__ == "__main__":
    main()
