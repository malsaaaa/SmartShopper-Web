#!/usr/bin/env python3
"""
QUICK START: Scrape Shopee.com.my with Firecrawl MCP

This is the fastest way to get started scraping Shopee.
Just run this file and follow the instructions.
"""

import subprocess
import sys
from pathlib import Path

def print_header(text):
    print(f"\n{'='*70}")
    print(f"  {text}")
    print(f"{'='*70}\n")

def print_step(num, text):
    print(f"[Step {num}] {text}\n")

def main():
    print_header("SHOPEE SCRAPER - Quick Start")
    
    # Step 1: Check setup
    print_step(1, "Verifying setup...")
    result = subprocess.run(
        [sys.executable, "check_shopee_setup.py"],
        capture_output=False
    )
    
    if result.returncode != 0:
        print("\n❌ Setup verification failed!")
        print("\nPlease fix the issues above and try again:")
        print("  1. Make sure .env file exists with FIRECRAWL_API_KEY")
        print("  2. Get your API key from: https://firecrawl.dev")
        print("  3. Run: pip install -r requirements.txt")
        sys.exit(1)
    
    # Step 2: Run scraper
    print_step(2, "Starting Shopee scraper...")
    print("Connecting to https://shopee.com.my/...\n")
    
    result = subprocess.run(
        [sys.executable, "scrapers/shopee_firecrawl_scraper.py"],
        capture_output=False
    )
    
    if result.returncode == 0:
        print_header("✅ Success!")
        print("""
Scraped products have been saved to: shopee_products.json

Next steps:
  1. View results: cat shopee_products.json
  2. Or import into database
  3. Or integrate into your app

For more options:
  Read: SHOPEE_SCRAPER_GUIDE.md
""")
    else:
        print_header("❌ Scraper Error")
        print("""
There was an error during scraping. Check:
  1. Is your FIRECRAWL_API_KEY valid?
  2. Do you have internet connection?
  3. Is Shopee website accessible?
  
For help, read: SHOPEE_SCRAPER_GUIDE.md
""")
        sys.exit(1)

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nCancelled by user")
        sys.exit(0)
