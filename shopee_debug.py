# -*- coding: utf-8 -*-
import asyncio
import sys
import io
import json
from playwright.async_api import async_playwright
from bs4 import BeautifulSoup

async def deep_debug():
    """Deep dive into finding products"""
    if sys.platform == 'win32':
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    
    print("=" * 80)
    print("DEEP DEBUG - Looking for product data in page")
    print("=" * 80)
    
    # Try Mydin first since we see API calls
    print("\n1️⃣ MYDIN - Deep inspection")
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            url = "https://www.mydin.my/search?q=milo"
            print(f"📍 Loading: {url}")
            await page.goto(url, wait_until="networkidle", timeout=60000)
            await asyncio.sleep(3)
            
            # Get full HTML content
            content = await page.content()
            soup = BeautifulSoup(content, 'html.parser')
            
            # Check page text for "milo" and "RM"
            text = soup.get_text()
            milo_count = text.lower().count('milo')
            rm_count = text.count('RM')
            print(f"✓ Text contains 'milo': {milo_count} times")
            print(f"✓ Text contains 'RM': {rm_count} times")
            
            # Look for images with alt text containing "milo"
            imgs = soup.find_all('img')
            milo_imgs = [img for img in imgs if 'milo' in img.get('alt', '').lower()]
            print(f"✓ Images with 'milo' in alt: {len(milo_imgs)}")
            
            # Look for all possible product containers
            divs = soup.find_all('div')
            print(f"✓ Total divs: {len(divs)}")
            
            # Look for links
            links = soup.find_all('a', href=True)
            product_links = [l for l in links if '/product' in l.get('href', '').lower() or l.get('href', '').startswith('/')]
            print(f"✓ Found {len(links)} links total, {len(product_links)} potential product links")
            
            # Save HTML for manual inspection
            with open("mydin_full.html", "w", encoding="utf-8") as f:
                f.write(content[:50000])
            print("✓ Saved first 50000 chars to mydin_full.html")
            
            await browser.close()
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Try Shopee
    print("\n2️⃣ SHOPEE - Deep inspection")
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            url = "https://shopee.com.my/supermarket?keyword=milo"
            print(f"📍 Loading: {url}")
            await page.goto(url, wait_until="networkidle", timeout=60000)
            await asyncio.sleep(3)
            
            # Scroll multiple times to load products
            print("📜 Scrolling to load more products...")
            for i in range(10):
                await page.evaluate("window.scrollBy(0, 1000)")
                await asyncio.sleep(0.3)
            
            # Get full HTML content
            content = await page.content()
            soup = BeautifulSoup(content, 'html.parser')
            
            # Check page text for "milo" and "RM"
            text = soup.get_text()
            milo_count = text.lower().count('milo')
            rm_count = text.count('RM')
            print(f"✓ Text contains 'milo': {milo_count} times")
            print(f"✓ Text contains 'RM': {rm_count} times")
            
            # Check for product links
            all_links = soup.find_all('a', href=True)
            product_links = [a for a in all_links if '/p/' in a.get('href', '')]
            print(f"✓ Found {len(all_links)} total links")
            print(f"✓ Product links with /p/: {len(product_links)}")
            
            if product_links:
                print("\n  First 3 product links:")
                for i, link in enumerate(product_links[:3]):
                    href = link.get('href', '')
                    text_content = link.get_text(strip=True)[:50]
                    print(f"    {i+1}. {href}")
                    if text_content:
                        print(f"       Text: {text_content}")
            
            # Look for price patterns
            spans = soup.find_all('span')
            price_spans = [s for s in spans if 'RM' in s.get_text()]
            print(f"\n✓ Spans containing 'RM': {len(price_spans)}")
            
            # Save HTML for manual inspection
            with open("shopee_full.html", "w", encoding="utf-8") as f:
                f.write(content[:50000])
            print("✓ Saved first 50000 chars to shopee_full.html")
            
            await browser.close()
    except Exception as e:
        print(f"❌ Error: {e}")

async def main():
    await deep_debug()

if __name__ == "__main__":
    asyncio.run(main())
