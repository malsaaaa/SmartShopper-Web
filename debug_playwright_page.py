#!/usr/bin/env python3
"""
Debug the actual Shopee page structure with Playwright
"""

import asyncio
import re
from playwright.async_api import async_playwright
from bs4 import BeautifulSoup


async def debug_page():
    """Debug what's actually on the Shopee page"""
    
    print("Debugging Shopee page structure...")
    print()
    
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        # Set mobile viewport
        await page.set_viewport_size({"width": 375, "height": 812})
        
        try:
            print("[1] Loading page...")
            await page.goto("https://shopee.com.my/", wait_until="networkidle", timeout=60000)
            
            print("[2] Waiting for content...")
            # Wait for any content to appear
            await page.wait_for_timeout(3000)
            
            print("[3] Getting page HTML...")
            html = await page.content()
            
            # Save raw HTML
            with open("shopee_page_debug.html", "w", encoding="utf-8") as f:
                f.write(html)
            
            print(f"    HTML saved (size: {len(html)} bytes)")
            
            soup = BeautifulSoup(html, 'html.parser')
            
            # Find all elements with common patterns
            print()
            print("[4] Analyzing page structure...")
            
            selectors_info = [
                ("[data-testid]", "elements with data-testid"),
                ("a[href*='/p/']", "product links"),
                ("button", "buttons"),
                ("[class*='item']", "elements with 'item' in class"),
                ("[class*='product']", "elements with 'product' in class"),
                ("[class*='grid']", "elements with 'grid' in class"),
                ("img", "images"),
                ("[class*='card']", "card elements"),
            ]
            
            for selector, desc in selectors_info:
                try:
                    elements = soup.select(selector)
                    if elements:
                        print(f"    {desc:35} ({selector:30}): {len(elements):3d}")
                except Exception as e:
                    print(f"    {desc:35} - Error: {e}")
            
            # Look at unique data-testid values
            print()
            print("[5] All data-testid values found:")
            testids = set()
            for elem in soup.find_all(attrs={"data-testid": True}):
                testids.add(elem.get("data-testid"))
            
            if testids:
                for testid in sorted(list(testids)[:15]):
                    count = len(soup.select(f'[data-testid="{testid}"]'))
                    print(f"    - {testid:40} ({count} elements)")
            else:
                print("    No data-testid attributes found")
            
            # Look at all classes
            print()
            print("[6] Sample class names:")
            all_classes = set()
            for elem in soup.find_all(class_=True):
                classes = elem.get("class", [])
                if isinstance(classes, list):
                    all_classes.update(classes)
            
            interesting = [c for c in all_classes if any(x in c.lower() for x in ['item', 'product', 'card', 'grid', 'shop'])]
            
            if interesting:
                print(f"    Found {len(interesting)} relevant classes:")
                for cls in sorted(list(interesting)[:20]):
                    print(f"      - {cls}")
            
            # Check for script tags with data
            print()
            print("[7] Checking for JSON data in scripts...")
            scripts = soup.find_all("script")
            json_scripts = [s for s in scripts if "data" in s.string.lower()[:100] if s.string]
            print(f"    Found {len(json_scripts)} scripts with potential data")
            
            # Look for the actual product container
            print()
            print("[8] Looking for common container patterns...")
            
            # Print some divs to understand structure
            divs = soup.find_all("div", limit=20)
            print(f"    First 5 divs on page:")
            for i, div in enumerate(divs[:5]):
                classes = ' '.join(div.get("class", []))[:50]
                testid = div.get("data-testid", "")
                print(f"      {i+1}. <div class='{classes}' data-testid='{testid}'>")
            
        except Exception as e:
            print(f"Error: {e}")
            import traceback
            traceback.print_exc()
        
        await browser.close()


if __name__ == "__main__":
    asyncio.run(debug_page())
