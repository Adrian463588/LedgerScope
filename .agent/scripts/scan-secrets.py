#!/usr/bin/env python3
import os
import re
import sys

# Color definitions
class Colors:
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    CYAN = '\033[96m'
    BOLD = '\033[1m'
    ENDC = '\033[0m'

# Patterns to scan
PATTERNS = {
    "Private Key": re.compile(r"-----BEGIN [A-Z ]+ PRIVATE KEY-----"),
    "AWS Access Key": re.compile(r"AKIA[0-9A-Z]{16}"),
    "AWS Secret Key": re.compile(r"(?i)aws_secret_access_key\s*=\s*['\"][0-9a-zA-Z/+=]{40}['\"]"),
    "Google API Key": re.compile(r"AIza[0-9A-Za-z\-_]{35}"),
    "Slack Token": re.compile(r"xox[bapr]-[0-9]{12}-[0-9]{12}-[a-zA-Z0-9]{24}"),
    "Generic High-Entropy Password/Secret": re.compile(
        r"(?i)(password|passwd|secret_key|api_key|access_token|client_secret)\s*[:=]\s*['\"](?![^'\"]*?(?:dummy|secret|password|local|test|null|key|127\.0\.0\.1|localhost|minio_secret_key))[^'\"]{10,}['\"]"
    )
}

# Folders to ignore
IGNORE_FOLDERS = {
    ".git", "node_modules", "vendor", "dist", "storage", "_bmad", "_bmad-output", 
    ".pytest_cache", ".kiro", "plans"
}

# File extensions to scan
SCAN_EXTENSIONS = {
    ".php", ".vue", ".js", ".ts", ".tsx", ".jsx", ".env", ".env.example", 
    ".json", ".yml", ".yaml", ".xml", ".md"
}

def scan_file(filepath):
    findings = []
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            for line_no, line in enumerate(f, 1):
                # Ignore comment lines to reduce false positives in documentation
                if line.strip().startswith(('#', '//', '*', '/**')):
                    continue
                
                for name, pattern in PATTERNS.items():
                    match = pattern.search(line)
                    if match:
                        findings.append((line_no, name, match.group(0).strip()))
    except Exception as e:
        print(f"{Colors.YELLOW}Warning: Could not read {filepath}: {e}{Colors.ENDC}")
    return findings

def main():
    root_dir = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    print(f"{Colors.BOLD}{Colors.CYAN}=== LedgerScope DevSecOps Secret Scanner ==={Colors.ENDC}")
    print(f"Scanning directory: {root_dir}\n")

    total_files = 0
    total_scanned = 0
    issues_found = 0

    for root, dirs, files in os.walk(root_dir):
        # Exclude ignored directories inline
        dirs[:] = [d for d in dirs if d not in IGNORE_FOLDERS]

        for file in files:
            total_files += 1
            ext = os.path.splitext(file)[1]
            if ext in SCAN_EXTENSIONS:
                total_scanned += 1
                filepath = os.path.join(root, file)
                relative_path = os.path.relpath(filepath, root_dir)
                
                # Skip the scanner file itself
                if file == "scan-secrets.py":
                    continue
                    
                findings = scan_file(filepath)
                if findings:
                    issues_found += len(findings)
                    print(f"{Colors.RED}[FAIL]{Colors.ENDC} {Colors.BOLD}{relative_path}{Colors.ENDC}")
                    for line_no, name, match_val in findings:
                        # Mask the matched value for display safety
                        masked = match_val[:6] + "..." + match_val[-6:] if len(match_val) > 12 else "********"
                        print(f"  Line {line_no:4}: {Colors.YELLOW}{name}{Colors.ENDC} detected -> Found: '{masked}'")
                    print()

    print(f"{Colors.BOLD}{Colors.CYAN}=== Summary ==={Colors.ENDC}")
    print(f"Total files in workspace: {total_files}")
    print(f"Total files scanned:      {total_scanned}")
    print(f"Total potential issues:   {issues_found}")

    if issues_found > 0:
        print(f"\n{Colors.RED}Security Scan: FAILED (Potential secrets found). Please inspect manually!{Colors.ENDC}")
        sys.exit(1)
    else:
        print(f"\n{Colors.GREEN}Security Scan: PASSED (No hardcoded production secrets found).{Colors.ENDC}")
        sys.exit(0)

if __name__ == "__main__":
    main()
