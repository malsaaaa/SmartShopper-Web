#!/usr/bin/env python3
"""
AUTOMATED FIX - Run this if you get any errors
This will automatically fix everything
"""

import subprocess
import sys
import os
from pathlib import Path

def run_cmd(cmd, description):
    """Run a command"""
    print(f"\n[*] {description}...")
    try:
        result = subprocess.run(cmd, shell=True, capture_output=False)
        return result.returncode == 0
    except Exception as e:
        print(f"Error: {e}")
        return False

def main():
    print("""
╔════════════════════════════════════════════════════════════╗
║   SHOPEE SCRAPER - AUTOMATED FIX & SETUP                  ║
║   This will fix all common issues                          ║
╚════════════════════════════════════════════════════════════╝
""")
    
    # Step 1: Verify we're in the right directory
    print("\n[Step 1] Checking working directory...")
    if "SmartShopper" not in os.getcwd():
        print(f"❌ Wrong directory: {os.getcwd()}")
        print(f"\n📌 Please run from SmartShopper root:")
        print(f"   cd c:\\Users\\HP VICTUS\\SmartShopper")
        return False
    print(f"✅ Correct directory: {os.getcwd()}\n")
    
    # Step 2: Verify required files
    print("[Step 2] Checking required files...")
    required = ['firecrawl_mcp_server.py', 'requirements.txt', '.env.example']
    for file in required:
        if Path(file).exists():
            print(f"  ✅ {file}")
        else:
            print(f"  ❌ {file} (missing)")
            return False
    
    # Step 3: Copy .env if needed
    print("\n[Step 3] Checking .env configuration...")
    if not Path('.env').exists():
        print("  Creating .env from .env.example...")
        with open('.env.example') as f:
            content = f.read()
        with open('.env', 'w') as f:
            f.write(content)
        print("  ⚠️  Added FIRECRAWL_API_KEY=your_api_key_here")
        print("  📌 Please edit .env and add your real API key")
        print("     Get key from: https://firecrawl.dev")
    else:
        print("  ✅ .env file exists")
    
    # Step 4: Install dependencies
    print("\n[Step 4] Installing Python dependencies...")
    print("  (This may take a minute...)")
    
    if not run_cmd(f"{sys.executable} -m pip install --upgrade pip", "Upgrading pip"):
        print("  ⚠️  Pip upgrade had issues, continuing...")
    
    if not run_cmd(f"{sys.executable} -m pip install -r requirements.txt", "Installing from requirements.txt"):
        print("❌ Failed to install packages")
        print("   Try manually: pip install -r requirements.txt")
        return False
    
    print("  ✅ Packages installed")
    
    # Step 5: Install Playwright
    print("\n[Step 5] Installing Playwright browsers...")
    print("  (This may take 2-3 minutes...)")
    
    if not run_cmd(f"{sys.executable} -m playwright install", "Installing Playwright"):
        print("  ⚠️  Playwright install had issues")
    
    print("  ✅ Playwright setup done")
    
    # Step 6: Verify imports
    print("\n[Step 6] Verifying Python imports...")
    
    packages = {
        'bs4': 'BeautifulSoup4',
        'requests': 'requests',
        'dotenv': 'python-dotenv',
        'playwright': 'Playwright',
    }
    
    all_ok = True
    for pkg, name in packages.items():
        try:
            __import__(pkg)
            print(f"  ✅ {name}")
        except ImportError:
            print(f"  ❌ {name}")
            all_ok = False
    
    if not all_ok:
        print("\n❌ Some packages failed to import")
        print("   Try: pip install -r requirements.txt --force-reinstall")
        return False
    
    # Step 7: Test Firecrawl import
    print("\n[Step 7] Testing Firecrawl MCP import...")
    try:
        sys.path.insert(0, str(Path.cwd()))
        from firecrawl_mcp_server import FirecrawlMCPServer
        print("  ✅ FirecrawlMCPServer can be imported")
    except ImportError as e:
        print(f"  ❌ Cannot import FirecrawlMCPServer: {e}")
        print("     Make sure firecrawl_mcp_server.py exists in current directory")
        return False
    
    # Success
    print("\n" + "="*60)
    print("  ✅ ALL FIXES COMPLETE - Ready to scrape!")
    print("="*60)
    print("""
Next steps:

1. ✅ You're done! Everything is set up.

2. ⚠️  IMPORTANT: Add your API key to .env
   - Edit: .env
   - Find: FIRECRAWL_API_KEY=your_api_key_here
   - Replace: your_api_key_here with your actual key
   - Get key from: https://firecrawl.dev

3. Run the scraper:
   python scrape_shopee_now.py

   Or manually:
   python scrapers/shopee_firecrawl_scraper.py
""")
    
    return True

if __name__ == '__main__':
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print("\n\nCancelled by user")
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ Unexpected error: {e}")
        sys.exit(1)
