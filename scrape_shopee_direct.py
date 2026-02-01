#!/usr/bin/env python3
"""
Shopee Malaysia Scraper using Playwright with better detection
Handles JavaScript-rendered content and anti-bot measures
"""

import asyncio
import json
import time
from pathlib import Path
from typing import List, Dict, Any
from datetime import datetime
import logging
import re

from playwright.async_api import async_playwright
from bs4 import BeautifulSoup

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class ShopeeDirectScraper:
    """Scraper using Playwright with robust selectors"""
    
    async def scrape(self, url: str = "https://shopee.com.my/", limit: int = 10) -> List[Dict[str, Any]]:
        """Scrape Shopee using Playwright"""
        products = []
        
        async with async_playwright() as p:
            try:
                logger.info("Launching browser...")
                browser = await p.chromium.launch(
                    headless=True,
                    args=['--disable-blink-features=AutomationControlled']
                )
                
                page = await browser.new_page(
                    user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                )
                
                # Set desktop viewport
                await page.set_viewport_size({"width": 1920, "height": 1080})
                
                logger.info(f"Loading {url}...")
                await page.goto(url, wait_until="load", timeout=60000)
                
                logger.info("Waiting for products and scrolling...")
                
                # Scroll down to load more products
                for i in range(5):  # Scroll 5 times
                    await page.evaluate("window.scrollBy(0, window.innerHeight)")
                    await asyncio.sleep(1)
                
                # Get HTML
                html = await page.content()
                logger.info(f"Got page HTML ({len(html)} bytes)")
                
                soup = BeautifulSoup(html, 'html.parser')
                
                # More specific selector for product items on homepage
                # Look for product containers that have images AND prices
                selectors = [
                    # Try product listing selectors
                    ("div[class*='col']", "Column containers"),  
                    ("a[href*='/p/']", "Direct product links"),
                ]
                
                product_elements = []
                for selector, desc in selectors:
                    try:
                        elements = soup.select(selector)
                        if len(elements) > 5:  # Need substantial results
                            logger.info(f"Found {len(elements)} elements: {desc}")
                            product_elements = elements
                            break
                    except Exception as e:
                        logger.debug(f"Error with '{selector}': {e}")
                
                if not product_elements:
                    logger.warning("No substantial product elements found")
                    await browser.close()
                    return []
                
                logger.info(f"Extracting data from {len(product_elements)} elements...")
                
                for element in product_elements[:limit * 3]:
                    try:
                        product = self._extract_product(element)
                        if product.get('name') and product.get('name') != '':
                            # Filter out category headers and banners
                            if not any(x in product.get('name', '').lower() for x in ['banner', 'category', 'collections']):
                                # Check for duplicates
                                if not any(p['name'] == product['name'] for p in products):
                                    products.append(product)
                                    if product.get('price'):
                                        logger.info(f"Extracted: {product['name'][:50]} - RM {product['price']}")
                                    else:
                                        logger.debug(f"No price for: {product['name'][:50]}")
                        
                        if len(products) >= limit:
                            break
                    except Exception as e:
                        logger.debug(f"Error extracting product: {e}")
                        continue
                
                await browser.close()
                
            except Exception as e:
                logger.error(f"Error: {e}")
                import traceback
                traceback.print_exc()
        
        return products[:limit]
    
    def _extract_product(self, element) -> Dict[str, Any]:
        """Extract product from element"""
        product = {
            'name': '',
            'price': '',
            'image_url': '',
            'link': '',
            'scraped_at': datetime.now().isoformat()
        }
        
        # Extract link first (to identify if it's a real product)
        link_elem = element
        if element.name != 'a':
            link_elem = element.select_one('a')
        
        if link_elem and link_elem.get('href'):
            href = link_elem.get('href', '')
            if '/p/' in href:
                if not href.startswith('http'):
                    href = 'https://shopee.com.my' + href
                product['link'] = href
        
        # Extract name from title attribute or alt text
        if element.get('title'):
            product['name'] = element.get('title')
        
        if not product['name']:
            # Try to find name from image alt
            img = element.select_one('img')
            if img and img.get('alt'):
                product['name'] = img.get('alt')
        
        if not product['name']:
            # Try to find name from text content
            text = element.get_text(strip=True)
            if text:
                # Filter out noise
                lines = [l.strip() for l in text.split('\n') if l.strip()]
                
                # Find the best line that looks like a product name
                for line in lines:
                    # Skip lines that look like prices, ratings, or counts
                    if re.match(r'^RM[\d,]', line):  # Starts with RM price
                        continue
                    if re.match(r'^\d+k\+\s+sold', line):  # "100k+ sold" format
                        continue
                    if re.match(r'^[\d\.]+ stars', line):  # Rating
                        continue
                    if line.startswith('flag-'):  # Flag labels
                        continue
                    if len(line) > 10:  # Minimum length for product name
                        product['name'] = line[:100]
                        break
        
        # Extract price
        price_text = element.get_text()
        price_matches = re.findall(r'RM[\s]*([\d,]+)', price_text)
        if not price_matches:
            price_matches = re.findall(r'([\d,]+)\s*(?:RM|rm)', price_text)
        
        if price_matches:
            product['price'] = price_matches[0].replace(',', '')
        
        # Extract image
        img = element.select_one('img')
        if img:
            src = img.get('src') or img.get('data-src')
            if src:
                product['image_url'] = src
        
        return product


async def main():
    """Main entry point"""
    print("\n" + "="*70)
    print("  SHOPEE SCRAPER - Playwright Direct")
    print("="*70 + "\n")
    
    scraper = ShopeeDirectScraper()
    products = await scraper.scrape(limit=10)
    
    if products:
        print(f"\nSUCCESS: Found {len(products)} products:\n")
        
        for idx, product in enumerate(products, 1):
            print(f"Product #{idx}")
            print(f"  Name:  {product.get('name', 'N/A')[:70]}")
            if product.get('price'):
                print(f"  Price: RM {product.get('price')}")
            if product.get('image_url'):
                img_url = product.get('image_url', 'N/A')
                if len(img_url) > 70:
                    img_url = img_url[:67] + "..."
                print(f"  Image: {img_url}")
            if product.get('link'):
                link = product.get('link', 'N/A')
                if len(link) > 70:
                    link = link[:67] + "..."
                print(f"  Link:  {link}")
            print()
        
        # Save
        output_file = Path("shopee_products.json")
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(products, f, indent=2, ensure_ascii=False)
        
        print(f"Saved to: {output_file}\n")
    else:
        print("No products found\n")
        print("Troubleshooting:")
        print("  1. Internet connection working?")
        print("  2. Shopee.com.my accessible?")
        print("  3. Try: playwright install chromium")
        print("  4. Try: pip install --upgrade playwright\n")


if __name__ == "__main__":
    asyncio.run(main())
