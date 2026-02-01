#!/usr/bin/env python3
"""
Debug script to see the actual Shopee HTML structure
"""

import asyncio
import sys
from pathlib import Path

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent))

from firecrawl_mcp_server import FirecrawlMCPServer
from bs4 import BeautifulSoup
import logging

logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

async def debug_shopee():
    """Debug Shopee HTML structure"""
    
    print("="*70)
    print("  SHOPEE HTML STRUCTURE DEBUG")
    print("="*70)
    print()
    
    # Initialize client
    client = FirecrawlMCPServer()
    
    try:
        print("[1] Fetching Shopee homepage HTML...")
        result = await client.crawl(
            url="https://shopee.com.my/",
            depth=1,
            include_markdown=False,
            timeout=60
        )
        
        if result.status_code != 200:
            print(f"❌ Failed to crawl: HTTP {result.status_code}")
            return
        
        html_content = result.body if hasattr(result, 'body') else result
        
        if not html_content:
            print("❌ No HTML content returned")
            return
        
        # Save raw HTML for inspection
        with open("shopee_debug.html", "w", encoding="utf-8") as f:
            f.write(str(html_content)[:10000])  # First 10KB
        
        print(f"✅ HTML saved to shopee_debug.html (first 10KB)")
        print()
        
        # Parse and analyze
        print("[2] Analyzing HTML structure...")
        soup = BeautifulSoup(html_content, 'html.parser')
        
        # Look for product containers
        print("\n📦 Product Container Analysis:")
        print("-" * 70)
        
        # Try various selectors
        selectors_to_try = [
            # By class patterns
            ('div[class*="product"]', 'divs with "product" in class'),
            ('div[class*="item"]', 'divs with "item" in class'),
            ('a[class*="product"]', 'links with "product" in class'),
            ('a[class*="item"]', 'links with "item" in class'),
            
            # By data attributes
            ('[data-shopee-product-id]', 'elements with data-shopee-product-id'),
            ('[data-product-id]', 'elements with data-product-id'),
            
            # Common ecommerce patterns
            ('li.shopee-', 'li with shopee- prefix'),
            ('[data-view-index]', 'elements with data-view-index'),
            
            # Generic containers
            ('div[role="link"]', 'divs with role=link'),
            ('a[href*="/p/"]', 'links with /p/ in href'),
        ]
        
        for selector, description in selectors_to_try:
            try:
                elements = soup.select(selector)
                if elements:
                    print(f"✅ Found {len(elements):3d} - {description}")
                    # Show first element's structure
                    if elements:
                        first = elements[0]
                        attrs = ' '.join([f'{k}={v[:20]}...' if isinstance(v, str) and len(str(v)) > 20 else f'{k}={v}' for k, v in first.attrs.items()])
                        print(f"   Example: <{first.name} {attrs}>")
            except Exception as e:
                print(f"❌ Error with '{selector}': {e}")
        
        # Check for specific tags
        print("\n📋 Page Structure:")
        print("-" * 70)
        print(f"Total <a> tags: {len(soup.find_all('a'))}")
        print(f"Total <div> tags: {len(soup.find_all('div'))}")
        print(f"Total <img> tags: {len(soup.find_all('img'))}")
        print(f"Total <button> tags: {len(soup.find_all('button'))}")
        
        # Check for scripts with data
        scripts = soup.find_all('script')
        print(f"\nTotal <script> tags: {len(scripts)}")
        
        # Check for JSON-LD data
        for script in scripts:
            if 'application/ld+json' in script.get('type', ''):
                print("✅ Found JSON-LD structured data")
        
        # Show all unique classes (sample)
        print("\n🏷️  Sample Classes Found:")
        print("-" * 70)
        all_classes = set()
        for element in soup.find_all(class_=True):
            classes = element.get('class', [])
            if isinstance(classes, list):
                all_classes.update(classes)
        
        # Show classes containing product/item
        relevant_classes = [c for c in all_classes if 'product' in c.lower() or 'item' in c.lower()]
        if relevant_classes:
            print(f"Classes with 'product' or 'item': {len(relevant_classes)}")
            for cls in sorted(list(relevant_classes)[:10]):
                print(f"  - {cls}")
        else:
            print("⚠️  No classes containing 'product' or 'item'")
            print("Sample of all classes found:")
            for cls in sorted(list(all_classes)[:15]):
                print(f"  - {cls}")
        
        print("\n✅ Debug complete. Check shopee_debug.html for full HTML.")
        
    except Exception as e:
        print(f"❌ Error: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    asyncio.run(debug_shopee())
