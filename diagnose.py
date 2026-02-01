#!/usr/bin/env python3
"""
Shopee Scraper - Troubleshooting & Diagnostics
"""

import sys
import os
from pathlib import Path
import subprocess

def print_header(text):
    print(f"\n{'='*70}")
    print(f"  {text}")
    print(f"{'='*70}\n")

def diagnose():
    """Run full diagnostics"""
    print_header("SHOPEE SCRAPER - DIAGNOSTICS")
    
    issues = []
    
    # Check 1: Python Version
    print("[1/8] Python Version")
    version = sys.version_info
    if version >= (3, 9):
        print(f"  ✅ Python {version.major}.{version.minor}.{version.micro}\n")
    else:
        print(f"  ❌ Python 3.9+ required (found {version.major}.{version.minor})\n")
        issues.append("Python version too old")
    
    # Check 2: Working Directory
    print("[2/8] Working Directory")
    cwd = os.getcwd()
    print(f"  Current: {cwd}\n")
    if "SmartShopper" not in cwd:
        print("  ⚠️  Not in SmartShopper directory\n")
        issues.append("Wrong working directory")
    else:
        print("  ✅ Correct directory\n")
    
    # Check 3: File Structure
    print("[3/8] File Structure")
    required = {
        'firecrawl_mcp_server.py': 'Core server',
        '.env': 'Configuration',
        'requirements.txt': 'Dependencies',
        'scrapers/shopee_firecrawl_scraper.py': 'Scraper script',
    }
    
    missing = []
    for file, desc in required.items():
        if Path(file).exists():
            print(f"  ✅ {file}")
        else:
            print(f"  ❌ {file} (missing)")
            missing.append(file)
    
    if missing:
        issues.append(f"Missing files: {', '.join(missing)}")
    print()
    
    # Check 4: .env Configuration
    print("[4/8] Environment Configuration")
    if Path('.env').exists():
        with open('.env') as f:
            content = f.read()
            if 'FIRECRAWL_API_KEY' in content:
                if 'FIRECRAWL_API_KEY=your_api_key_here' in content or 'FIRECRAWL_API_KEY=' in content and 'FIRECRAWL_API_KEY=your_api_key_here' not in content:
                    print("  ✅ FIRECRAWL_API_KEY configured\n")
                else:
                    print("  ⚠️  FIRECRAWL_API_KEY may not be set properly\n")
                    issues.append("API key not configured")
            else:
                print("  ❌ FIRECRAWL_API_KEY not found in .env\n")
                issues.append("API key missing")
    else:
        print("  ❌ .env file not found\n")
        issues.append(".env file missing")
    
    # Check 5: Python Imports
    print("[5/8] Python Imports")
    packages = [
        ('bs4', 'BeautifulSoup4'),
        ('requests', 'requests'),
        ('dotenv', 'python-dotenv'),
        ('playwright', 'Playwright'),
    ]
    
    missing_packages = []
    for package, name in packages:
        try:
            __import__(package)
            print(f"  ✅ {name}")
        except ImportError:
            print(f"  ❌ {name} (not installed)")
            missing_packages.append(name)
    
    if missing_packages:
        issues.append(f"Missing packages: {', '.join(missing_packages)}")
    print()
    
    # Check 6: Firecrawl Module Path
    print("[6/8] Firecrawl MCP Module Import")
    try:
        # Try importing from current path
        sys.path.insert(0, str(Path.cwd()))
        from firecrawl_mcp_server import FirecrawlMCPServer
        print("  ✅ FirecrawlMCPServer can be imported\n")
    except ImportError as e:
        print(f"  ❌ Cannot import FirecrawlMCPServer: {e}\n")
        issues.append("Cannot import FirecrawlMCPServer")
    
    # Check 7: Playwright Browsers
    print("[7/8] Playwright Browsers")
    try:
        from playwright.async_api import async_playwright
        print("  ✅ Playwright installed\n")
    except ImportError:
        print("  ❌ Playwright not installed\n")
        issues.append("Playwright not installed")
    
    # Check 8: Internet Connectivity
    print("[8/8] Internet Connectivity")
    try:
        import requests
        response = requests.head("https://shopee.com.my", timeout=5)
        print(f"  ✅ Can reach shopee.com.my (HTTP {response.status_code})\n")
    except Exception as e:
        print(f"  ⚠️  Cannot reach shopee.com.my: {str(e)[:50]}\n")
    
    # Summary
    print_header("DIAGNOSIS SUMMARY")
    
    if issues:
        print(f"❌ Found {len(issues)} issue(s):\n")
        for i, issue in enumerate(issues, 1):
            print(f"  {i}. {issue}")
        
        print("\n" + "="*70)
        print("  RECOMMENDED FIXES")
        print("="*70 + "\n")
        
        print("Quick Fix (Run All):")
        print("  python fix_dependencies.py\n")
        
        print("Or Fix Specific Issues:\n")
        
        if any("API key" in issue for issue in issues):
            print("  1. Add API Key:")
            print("     - Visit: https://firecrawl.dev")
            print("     - Get: API key")
            print("     - Edit: .env file")
            print("     - Add: FIRECRAWL_API_KEY=your_key_here\n")
        
        if any("missing packages" in issue.lower() for issue in issues):
            print("  2. Install Packages:")
            print(f"     python -m pip install -r requirements.txt\n")
        
        if any("playwright" in issue.lower() for issue in issues):
            print("  3. Install Playwright:")
            print("     python -m playwright install\n")
        
        if any("missing files" in issue.lower() for issue in issues):
            print("  4. Check File Existence:")
            print("     ls -la firecrawl_mcp_server.py")
            print("     ls -la requirements.txt\n")
        
        print("\n" + "="*70)
        print("  After Fixes, Run:")
        print("="*70 + "\n")
        print("  python check_shopee_setup.py")
        print("  python scrape_shopee_now.py\n")
        
        return False
    else:
        print("✅ All checks passed! Ready to scrape.\n")
        print("Run: python scrape_shopee_now.py\n")
        return True

if __name__ == '__main__':
    try:
        success = diagnose()
        sys.exit(0 if success else 1)
    except Exception as e:
        print(f"\n❌ Diagnostic error: {e}\n")
        sys.exit(1)
