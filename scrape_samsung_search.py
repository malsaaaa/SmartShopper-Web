#!/usr/bin/env python3
"""
Shopee Samsung Search Scraper
Searches for Samsung products and scrapes the first 10 results
"""

import asyncio
import json
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


class ShopeeSearchScraper:
    """Scraper for Shopee search results"""
    
    async def search(self, query: str = "samsung", limit: int = 10) -> List[Dict[str, Any]]:
        """Search and scrape products from Shopee"""
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
                
                # Search URL
                search_url = f"https://shopee.com.my/search?keyword={query}"
                logger.info(f"Loading search: {search_url}")
                await page.goto(search_url, wait_until="load", timeout=60000)
                
                # Wait for products
                logger.info("Waiting for search results...")
                try:
                    await page.wait_for_selector("a[href*='/p/']", timeout=15000)
                except:
                    logger.warning("Search results not found in 15s, continuing anyway...")
                
                # Wait for rendering
                await asyncio.sleep(2)
                
                # Scroll to load more results
                logger.info("Scrolling to load more products...")
                for i in range(5):
                    await page.evaluate("window.scrollBy(0, 500)")
                    await asyncio.sleep(1)
                
                # Get HTML
                html = await page.content()
                logger.info(f"Got page HTML ({len(html)} bytes)")
                
                soup = BeautifulSoup(html, 'html.parser')
                
                # Find product containers
                product_elements = []
                
                selectors = [
                    ("div[class*='col']", "Column containers"),
                    ("a[href*='/p/']", "Direct product links"),
                ]
                
                for selector, desc in selectors:
                    try:
                        elements = soup.select(selector)
                        if len(elements) > 5:
                            logger.info(f"Found {len(elements)} elements: {desc}")
                            product_elements = elements
                            break
                    except Exception as e:
                        logger.debug(f"Error with '{selector}': {e}")
                
                if not product_elements:
                    logger.warning("No products found")
                    await browser.close()
                    return []
                
                # Extract data
                logger.info(f"Extracting data from {len(product_elements)} product elements...")
                
                for element in product_elements[:limit * 3]:
                    try:
                        product = self._extract_product(element)
                        if product.get('name'):
                            # Avoid duplicates
                            if not any(p['name'] == product['name'] for p in products):
                                products.append(product)
                                logger.info(f"Extracted: {product['name'][:60]}")
                        
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
            'url': '',
            'scraped_at': datetime.now().isoformat()
        }
        
        # Extract link first
        link_elem = element
        if element.name != 'a':
            link_elem = element.select_one('a')
        
        if link_elem and link_elem.get('href'):
            href = link_elem.get('href', '')
            if '/p/' in href:
                if not href.startswith('http'):
                    href = 'https://shopee.com.my' + href
                product['url'] = href
        
        # Extract name
        if element.get('title'):
            product['name'] = element.get('title')
        
        if not product['name']:
            img = element.select_one('img')
            if img and img.get('alt'):
                product['name'] = img.get('alt')
        
        if not product['name']:
            text = element.get_text(strip=True)
            if text:
                lines = [l.strip() for l in text.split('\n') if l.strip()]
                
                for line in lines:
                    # Filter out noise and UI elements
                    if re.match(r'^RM[\d,]', line):
                        continue
                    if re.match(r'^\d+k\+\s+sold', line):
                        continue
                    if re.match(r'^[\d\.]+ stars', line):
                        continue
                    if line.startswith('flag-'):
                        continue
                    if 'rating-star' in line.lower():
                        continue
                    if line.lower() in ['view more', 'banner', 'promotion']:
                        continue
                    if len(line) > 10 and 'samsung' in element.get_text().lower():
                        product['name'] = line[:150]
                        break
        
        # Skip if no valid name or looks like UI element
        if not product['name'] or 'flag' in product['name'].lower() or 'rating' in product['name'].lower():
            return {}
        
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
            if src and 'susercontent' in src:  # Real product image
                product['image_url'] = src
        
        # Only return if it has both name and valid image or URL
        if product['name'] and (product['image_url'] or product['url']):
            return product
        
        return {}


async def main():
    """Main entry point"""
    print("\n" + "="*70)
    print("  SHOPEE SAMSUNG SEARCH SCRAPER")
    print("="*70 + "\n")
    
    scraper = ShopeeSearchScraper()
    products = await scraper.search(query="samsung", limit=10)
    
    if products:
        print(f"\nSUCCESS: Found {len(products)} Samsung products:\n")
        
        for idx, product in enumerate(products, 1):
            print(f"Product #{idx}")
            print(f"  Name:  {product.get('name', 'N/A')[:70]}")
            if product.get('price'):
                print(f"  Price: RM {product.get('price')}")
            if product.get('image_url'):
                print(f"  Image: {product.get('image_url', 'N/A')[:70]}")
            if product.get('url'):
                print(f"  URL:   {product.get('url', 'N/A')[:70]}")
            print()
        
        # Save to JSON
        output_file = Path("shopee_products.json")
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(products, f, indent=2, ensure_ascii=False)
        
        print(f"Saved to: {output_file}\n")
    else:
        print("No products found\n")


if __name__ == "__main__":
    asyncio.run(main())
