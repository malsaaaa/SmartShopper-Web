# -*- coding: utf-8 -*-
import asyncio
import sys
import io
from playwright.async_api import async_playwright

async def debug_shopee():
    """Debug Shopee page structure"""
    if sys.platform == 'win32':
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    
    print("=" * 80)
    print("DEBUGGING SHOPEE SUPERMARKET STRUCTURE")
    print("=" * 80)
    
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            url = "https://shopee.com.my/supermarket?keyword=milo"
            print(f"\n📍 Loading: {url}")
            await page.goto(url, wait_until="networkidle", timeout=60000)
            await asyncio.sleep(2)
            
            # Scroll to load more
            for i in range(3):
                await page.evaluate("window.scrollBy(0, 500)")
                await asyncio.sleep(0.5)
            
            content = await page.content()
            
            # Save first 10000 chars
            with open("shopee_page_structure.html", "w", encoding="utf-8") as f:
                f.write(content[:10000])
            
            print("✅ Saved first 10000 chars to shopee_page_structure.html")
            
            # Try different selectors
            selectors_to_try = [
                'a[href*="/p/"]',
                '[data-testid*="product"]',
                '.shopee-product-item',
                'div.product-item',
                'a.product-item-link',
                '[class*="product"]',
                'li[data-testid]',
                'div[data-testid*="item"]',
            ]
            
            for selector in selectors_to_try:
                try:
                    elements = await page.query_selector_all(selector)
                    if elements:
                        print(f"✓ Found {len(elements)} elements with selector: {selector}")
                        # Get text from first element
                        if elements:
                            text = await elements[0].text_content()
                            print(f"  First element text: {text[:100]}")
                except:
                    pass
            
            await browser.close()
    
    except Exception as e:
        print(f"❌ Error: {e}")

async def debug_mydin():
    """Debug Mydin page structure"""
    if sys.platform == 'win32':
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    
    print("\n" + "=" * 80)
    print("DEBUGGING MYDIN STRUCTURE")
    print("=" * 80)
    
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            url = "https://www.mydin.my/search?q=milo"
            print(f"\n📍 Loading: {url}")
            await page.goto(url, wait_until="networkidle", timeout=60000)
            await asyncio.sleep(2)
            
            # Scroll to load more
            for i in range(3):
                await page.evaluate("window.scrollBy(0, 500)")
                await asyncio.sleep(0.5)
            
            content = await page.content()
            
            # Save first 10000 chars
            with open("mydin_page_structure.html", "w", encoding="utf-8") as f:
                f.write(content[:10000])
            
            print("✅ Saved first 10000 chars to mydin_page_structure.html")
            
            # Try different selectors
            selectors_to_try = [
                '[class*="product"]',
                'div.product-item',
                'a.product-link',
                '[data-testid*="product"]',
                '.product',
                'li.product',
                '[class*="Product"]',
                'article',
            ]
            
            for selector in selectors_to_try:
                try:
                    elements = await page.query_selector_all(selector)
                    if elements:
                        print(f"✓ Found {len(elements)} elements with selector: {selector}")
                        # Get text from first element
                        if elements:
                            text = await elements[0].text_content()
                            print(f"  First element text: {text[:100]}")
                except:
                    pass
            
            await browser.close()
    
    except Exception as e:
        print(f"❌ Error: {e}")

async def main():
    await debug_shopee()
    await debug_mydin()

if __name__ == "__main__":
    asyncio.run(main())
