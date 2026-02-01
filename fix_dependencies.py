#!/usr/bin/env python3
"""
Fix Dependencies for Shopee Scraper
Installs all required packages
"""

import subprocess
import sys
import os

def print_header(text):
    print(f"\n{'='*70}")
    print(f"  {text}")
    print(f"{'='*70}\n")

def run_command(cmd, description):
    """Run a command and report status"""
    print(f"[*] {description}...")
    try:
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
        if result.returncode == 0:
            print(f"✅ {description} - Success\n")
            return True
        else:
            print(f"❌ {description} - Failed")
            if result.stderr:
                print(f"Error: {result.stderr[:200]}\n")
            return False
    except Exception as e:
        print(f"❌ {description} - Error: {e}\n")
        return False

def main():
    print_header("SHOPEE SCRAPER - Dependency Fix")
    
    print("This script will install all required dependencies.\n")
    
    # Step 1: Upgrade pip
    print("[Step 1/4] Upgrading pip...")
    run_command(f"{sys.executable} -m pip install --upgrade pip", "Pip upgrade")
    
    # Step 2: Install from requirements.txt
    print("[Step 2/4] Installing from requirements.txt...")
    if os.path.exists('requirements.txt'):
        run_command(f"{sys.executable} -m pip install -r requirements.txt", "Pip install from requirements.txt")
    else:
        print("❌ requirements.txt not found\n")
        return False
    
    # Step 3: Install Playwright browsers
    print("[Step 3/4] Installing Playwright browsers...")
    run_command(f"{sys.executable} -m playwright install", "Playwright browser installation")
    
    # Step 4: Verify imports
    print("[Step 4/4] Verifying installations...")
    required_packages = [
        ('bs4', 'BeautifulSoup4'),
        ('requests', 'requests'),
        ('dotenv', 'python-dotenv'),
        ('playwright', 'Playwright'),
    ]
    
    all_ok = True
    for package, name in required_packages:
        try:
            __import__(package)
            print(f"  ✅ {name}")
        except ImportError:
            print(f"  ❌ {name}")
            all_ok = False
    
    print()
    
    if all_ok:
        print_header("✅ All Dependencies Installed!")
        print("""
Ready to scrape! Run:
  python scrape_shopee_now.py
""")
        return True
    else:
        print_header("❌ Some Dependencies Failed")
        print("""
Try these fixes:
  1. python -m pip install --upgrade pip
  2. python -m pip install -r requirements.txt --force-reinstall
  3. python -m playwright install
""")
        return False

if __name__ == '__main__':
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print("\n\nInterrupted by user")
        sys.exit(1)
