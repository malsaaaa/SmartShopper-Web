#!/usr/bin/env python3
"""
Simple test to see what the Firecrawl client returns
"""

import sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent))

# Try to get Shopee using requests + BeautifulSoup (no API key needed)
import requests
from bs4 import BeautifulSoup

print("="*70)
print("  SHOPEE HTML SCRAPE TEST (Using Requests + BeautifulSoup)")
print("="*70)
print()

try:
    print("[1] Fetching https://shopee.com.my/...")
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    }
    
    response = requests.get('https://shopee.com.my/', headers=headers, timeout=10)
    
    print(f"Status Code: {response.status_code}")
    
    if response.status_code == 200:
        print(f"Content Length: {len(response.text)} bytes")
        print()
        
        # Save the raw HTML
        with open("shopee_raw.html", "w", encoding="utf-8") as f:
            f.write(response.text[:20000])  # First 20KB
        
        print("[2] Saved raw HTML to shopee_raw.html")
        
        # Parse it
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # Look for products
        print()
        print("[3] Searching for product patterns...")
        
        selectors = [
            ('a[href*="/p/"]', 'Product links'),
            ('[data-testid="grid-item"]', 'Grid items'),
            ('[class*="product"]', 'Product class elements'),
            ('div[role="link"]', 'Link role divs'),
        ]
        
        for selector, desc in selectors:
            try:
                elements = soup.select(selector)
                print(f"  {desc:30} ({selector:30}): {len(elements):3d} found")
            except Exception as e:
                print(f"  {desc:30} - Error: {e}")
        
        # Show page structure
        print()
        print("[4] Page Structure:")
        print(f"  Total links: {len(soup.find_all('a'))}")
        print(f"  Total divs: {len(soup.find_all('div'))}")
        print(f"  Total images: {len(soup.find_all('img'))}")
        
        # Look for interesting patterns
        print()
        print("[5] Looking for data attributes...")
        
        found_data_attrs = set()
        for element in soup.find_all(True):  # Find all elements
            for attr in element.attrs.keys():
                if attr.startswith('data-'):
                    found_data_attrs.add(attr)
        
        if found_data_attrs:
            print(f"  Found {len(found_data_attrs)} data attributes:")
            for attr in sorted(list(found_data_attrs)[:10]):
                print(f"    - {attr}")
        
        # Look for specific content
        print()
        print("[6] Looking for product indicators...")
        
        # Check if we can find text patterns
        text_lower = response.text.lower()
        
        indicators = [
            'rm', 'price', 'product', 'item', 'add to cart', 'buy', 'sold',
            'shopee', 'category', 'search'
        ]
        
        found = [ind for ind in indicators if ind in text_lower]
        print(f"  Found keywords: {', '.join(found)}")
        
    else:
        print(f"ERROR: Got status code {response.status_code}")
        
except requests.exceptions.RequestException as e:
    print(f"REQUEST ERROR: {e}")
except Exception as e:
    print(f"ERROR: {e}")
    import traceback
    traceback.print_exc()

print()
print("="*70)
