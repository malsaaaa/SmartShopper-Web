# -*- coding: utf-8 -*-
import asyncio
import sys
import io
from playwright.async_api import async_playwright
from bs4 import BeautifulSoup
import re

async def debug_pages():
    """Debug and find correct way to scrape"""
    if sys.platform == 'win32':
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    
    print("=" * 80)
    print("DEBUGGING SHOPEE & MYDIN - FINDING PRODUCT CONTAINERS")
    print("=" * 80)
    
    # Try Shopee
    print("\n1️⃣ TESTING SHOPEE SUPERMARKET")
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            url = "https://shopee.com.my/supermarket?keyword=milo"
            print(f"📍 Loading: {url}")
            await page.goto(url, wait_until="domcontentloaded", timeout=60000)
            print("⏳ Waiting for JS rendering (5 seconds)...")
            await asyncio.sleep(5)
            
            # Try to get all text content
            body_text = await page.text_content("body")
            if "milo" in body_text.lower():
                print("✓ Found 'milo' in page content")
            
            # Check for specific data attributes
            spans = await page.query_selector_all("span")
            print(f"✓ Found {len(spans)} span elements")
            
            # Look for price patterns
            price_elements = await page.query_selector_all("[data-price], [class*='price']")
            print(f"✓ Found {len(price_elements)} price-like elements")
            
            # Get page structure
            content = await page.content()
            soup = BeautifulSoup(content, 'html.parser')
            
            # Look for links
            all_links = soup.find_all('a')
            print(f"✓ Found {len(all_links)} total links")
            
            # Find /p/ links
            product_links = [link for link in all_links if '/p/' in link.get('href', '')]
            print(f"✓ Found {len(product_links)} product links with /p/")
            
            if product_links:
                print(f"\n  First 3 product links:")
                for i, link in enumerate(product_links[:3]):
                    href = link.get('href', 'N/A')
                    text = link.text[:50] if link.text else 'No text'
                    print(f"    {i+1}. {href}")
                    print(f"       Text: {text}")
            
            await browser.close()
    except Exception as e:
        print(f"❌ Shopee error: {e}")
    
    # Try Mydin
    print("\n2️⃣ TESTING MYDIN MARKETPLACE")
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            url = "https://www.mydin.my/search?q=milo"
            print(f"📍 Loading: {url}")
            await page.goto(url, wait_until="domcontentloaded", timeout=60000)
            print("⏳ Waiting for JS rendering (5 seconds)...")
            await asyncio.sleep(5)
            
            # Check page title
            title = await page.title()
            print(f"✓ Page title: {title}")
            
            # Get all text content
            body_text = await page.text_content("body")
            if "milo" in body_text.lower():
                print("✓ Found 'milo' in page content")
            if "RM" in body_text or "product" in body_text.lower():
                print("✓ Found pricing/product indicators")
            
            # Get structure
            content = await page.content()
            soup = BeautifulSoup(content, 'html.parser')
            
            # Look for divs with product info
            divs = soup.find_all('div', class_=re.compile(r'product', re.I))
            print(f"✓ Found {len(divs)} divs with 'product' in class")
            
            # Look for article tags (common for product listings)
            articles = soup.find_all('article')
            print(f"✓ Found {len(articles)} article elements")
            
            # Look for product containers
            containers = soup.find_all(class_=re.compile(r'item|product|result', re.I))
            print(f"✓ Found {len(containers)} container-like elements")
            
            # Look for images and links
            imgs = soup.find_all('img')
            print(f"✓ Found {len(imgs)} image elements")
            
            # Look for links with href
            links = soup.find_all('a', href=True)
            print(f"✓ Found {len(links)} links")
            
            if links:
                print(f"\n  First 3 links:")
                for i, link in enumerate(links[:3]):
                    href = link.get('href', 'N/A')
                    text = link.text[:50] if link.text else 'No text'
                    print(f"    {i+1}. {href}")
                    if text:
                        print(f"       Text: {text}")
            
            await browser.close()
    except Exception as e:
        print(f"❌ Mydin error: {e}")

async def main():
    await debug_pages()

if __name__ == "__main__":
    asyncio.run(main())
