#!/usr/bin/env python3
"""
Test script for SmartShopper scraper
Validates that all dependencies are installed and scraper works
"""

import sys
import json
from datetime import datetime

def test_imports():
    """Test that all required imports work"""
    print("🧪 Testing imports...")
    try:
        import requests
        print("  ✓ requests")
    except ImportError:
        print("  ✗ requests - install with: pip install requests")
        return False
    
    try:
        from bs4 import BeautifulSoup
        print("  ✓ beautifulsoup4")
    except ImportError:
        print("  ✗ beautifulsoup4 - install with: pip install beautifulsoup4")
        return False
    
    try:
        from selenium import webdriver
        print("  ✓ selenium")
    except ImportError:
        print("  ✗ selenium - install with: pip install selenium")
        return False
    
    try:
        from webdriver_manager.chrome import ChromeDriverManager
        print("  ✓ webdriver-manager")
    except ImportError:
        print("  ✗ webdriver-manager - install with: pip install webdriver-manager")
        return False
    
    return True

def test_scraper():
    """Test that the scraper runs without errors"""
    print("\n🧪 Testing scraper...")
    try:
        from scrape_products import RetailerScraper
        
        scraper = RetailerScraper()
        print("  ✓ Scraper initialized")
        
        # Test JSON output
        output = scraper.run()
        data = json.loads(output)
        print(f"  ✓ Scraper ran successfully, found {len(data)} items")
        
        if data:
            print(f"  ✓ Sample item: {data[0]}")
        
        return True
    except Exception as e:
        print(f"  ✗ Scraper error: {e}")
        return False

def main():
    """Run all tests"""
    print("=" * 50)
    print("SmartShopper Scraper Test Suite")
    print("=" * 50)
    print(f"Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
    
    results = []
    
    # Test imports
    if test_imports():
        results.append(("Imports", "PASS"))
    else:
        results.append(("Imports", "FAIL"))
        print("\n❌ Failed: Please install missing dependencies")
        return 1
    
    # Test scraper
    if test_scraper():
        results.append(("Scraper", "PASS"))
    else:
        results.append(("Scraper", "FAIL"))
    
    # Print summary
    print("\n" + "=" * 50)
    print("Test Summary")
    print("=" * 50)
    for test_name, status in results:
        symbol = "✓" if status == "PASS" else "✗"
        print(f"{symbol} {test_name}: {status}")
    
    all_passed = all(status == "PASS" for _, status in results)
    
    print("\n" + "=" * 50)
    if all_passed:
        print("✅ All tests passed! Scraper is ready to use.")
        return 0
    else:
        print("❌ Some tests failed. Please check the errors above.")
        return 1

if __name__ == "__main__":
    sys.exit(main())
