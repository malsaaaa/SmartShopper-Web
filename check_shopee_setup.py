#!/usr/bin/env python3
"""
Quick setup and test for Shopee Firecrawl Scraper
"""

import sys
import os
from pathlib import Path

def check_setup():
    """Check if everything is set up correctly"""
    print("="*70)
    print("  SHOPEE FIRECRAWL SCRAPER - SETUP CHECK")
    print("="*70)
    print()
    
    # Check Python version
    print("[1/5] Checking Python version...")
    if sys.version_info >= (3, 9):
        print(f"  ✅ Python {sys.version_info.major}.{sys.version_info.minor} (OK)\n")
    else:
        print(f"  ❌ Python 3.9+ required (found {sys.version_info.major}.{sys.version_info.minor})\n")
        return False
    
    # Check .env file
    print("[2/5] Checking .env configuration...")
    if Path('.env').exists():
        with open('.env') as f:
            content = f.read()
            if 'FIRECRAWL_API_KEY' in content and 'FIRECRAWL_API_KEY=your_api_key_here' not in content:
                print("  ✅ FIRECRAWL_API_KEY is configured\n")
            else:
                print("  ⚠️  FIRECRAWL_API_KEY needs to be configured in .env")
                print("     Get your key from: https://firecrawl.dev\n")
                return False
    else:
        print("  ⚠️  .env file not found")
        print("     Creating from .env.example...\n")
        if Path('.env.example').exists():
            with open('.env.example') as f:
                content = f.read()
            with open('.env', 'w') as f:
                f.write(content)
            print("  ⚠️  Please edit .env and add your FIRECRAWL_API_KEY\n")
            return False
        else:
            print("  ❌ .env.example not found\n")
            return False
    
    # Check dependencies
    print("[3/5] Checking Python dependencies...")
    required_packages = ['firecrawl_mcp_server', 'bs4', 'requests', 'playwright']
    missing = []
    
    for package in required_packages:
        try:
            if package == 'firecrawl_mcp_server':
                __import__('firecrawl_mcp_server')
            elif package == 'bs4':
                __import__('bs4')
            else:
                __import__(package)
            print(f"  ✅ {package}")
        except ImportError:
            print(f"  ❌ {package} (missing)")
            missing.append(package)
    
    if missing:
        print(f"\n  Install missing packages with:")
        print(f"  pip install -r requirements.txt\n")
        return False
    
    print()
    
    # Check Firecrawl MCP server files
    print("[4/5] Checking Firecrawl MCP files...")
    required_files = [
        'firecrawl_mcp_server.py',
        'firecrawl_mcp_config.json',
        'scrapers/shopee_firecrawl_scraper.py'
    ]
    
    missing_files = []
    for file in required_files:
        if Path(file).exists():
            print(f"  ✅ {file}")
        else:
            print(f"  ❌ {file} (missing)")
            missing_files.append(file)
    
    if missing_files:
        print(f"\n  Missing files: {', '.join(missing_files)}")
        return False
    
    print()
    
    # Check connectivity
    print("[5/5] Checking internet connectivity...")
    try:
        import requests
        response = requests.head("https://shopee.com.my", timeout=5)
        print(f"  ✅ Can reach shopee.com.my\n")
    except Exception as e:
        print(f"  ⚠️  Cannot reach shopee.com.my: {e}")
        print(f"     Make sure you have internet connection\n")
    
    return True

def main():
    print()
    if check_setup():
        print("="*70)
        print("  ✅ SETUP VERIFIED - Ready to scrape!")
        print("="*70)
        print()
        print("To scrape Shopee, run:")
        print("  python scrapers/shopee_firecrawl_scraper.py")
        print()
        print("The scraper will:")
        print("  • Connect to https://shopee.com.my/")
        print("  • Extract product information")
        print("  • Save results to shopee_products.json")
        print()
    else:
        print("="*70)
        print("  ❌ SETUP INCOMPLETE - Fix issues above")
        print("="*70)
        print()
        sys.exit(1)

if __name__ == '__main__':
    main()
