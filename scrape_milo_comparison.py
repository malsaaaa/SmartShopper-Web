# -*- coding: utf-8 -*-
import asyncio
import json
from datetime import datetime
from pathlib import Path
from playwright.async_api import async_playwright
from bs4 import BeautifulSoup
import re
import sys
import io


class MiloProductScraper:
    """Scraper for Milo products from Shopee Supermarket and Mydin"""

    def __init__(self):
        self.shopee_url = "https://shopee.com.my/supermarket?keyword=milo"
        self.mydin_url = "https://www.mydin.my/search?q=milo"
        self.shopee_products = []
        self.mydin_products = []

    async def scrape_shopee_milo(self, limit=10):
        """Scrape Milo products from Shopee Supermarket"""
        print("\n🛒 Scraping Shopee Supermarket for 'milo' products...\n")
        
        try:
            async with async_playwright() as p:
                browser = await p.chromium.launch()
                context = await browser.new_context(
                    user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                )
                page = await context.new_page()

                print("📍 Loading Shopee Supermarket with search...")
                await page.goto(self.shopee_url, wait_until="networkidle", timeout=60000)
                await asyncio.sleep(2)
                
                # Scroll to load more products
                print("📜 Scrolling to load products...")
                for i in range(5):
                    await page.evaluate("window.scrollBy(0, 500)")
                    await asyncio.sleep(0.5)
                
                # Get page content
                content = await page.content()
                soup = BeautifulSoup(content, 'html.parser')
                
                # Find product elements - Shopee uses div with data attributes
                product_links = soup.select('a[href*="/p/"]')
                print(f"Found {len(product_links)} product links")
                
                count = 0
                for link in product_links:
                    if count >= limit:
                        break
                    
                    try:
                        # Get product name from link
                        name = link.get_text(strip=True)
                        if not name or len(name) < 3:
                            continue
                        
                        # Get URL
                        url = link.get('href', '')
                        if not url.startswith('http'):
                            url = 'https://shopee.com.my' + url
                        
                        # Find parent element with price
                        parent = link.find_parent('div', class_=lambda x: x and 'col' in x)
                        if not parent:
                            continue
                        
                        # Get price
                        price_elem = parent.find(string=re.compile(r'RM\s*[\d,]+'))
                        if not price_elem:
                            price_elem = parent.select_one('[class*="price"]')
                        
                        price_text = price_elem.get_text(strip=True) if price_elem else ""
                        price_match = re.search(r'[\d,]+(?:\.\d{2})?', price_text)
                        price = price_match.group(0) if price_match else "0"
                        
                        # Get image
                        img_elem = parent.select_one('img')
                        image_url = img_elem.get('src', '') if img_elem else ""
                        
                        if name and url and price != "0":
                            product = {
                                'name': name,
                                'price': price,
                                'image_url': image_url,
                                'url': url,
                                'retailer': 'Shopee Malaysia'
                            }
                            self.shopee_products.append(product)
                            count += 1
                            print(f"✓ {count}. {name[:50]} - RM {price}")
                    
                    except Exception as e:
                        continue
                
                await browser.close()
                print(f"\n✅ Scraped {len(self.shopee_products)} Shopee products\n")
                
        except Exception as e:
            print(f"❌ Shopee scraping error: {e}")

    async def scrape_mydin_milo(self, limit=10):
        """Scrape Milo products from Mydin"""
        print("\n🏪 Scraping Mydin for 'milo' products...\n")
        
        try:
            async with async_playwright() as p:
                browser = await p.chromium.launch()
                context = await browser.new_context(
                    user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                )
                page = await context.new_page()

                print("📍 Loading Mydin with search...")
                await page.goto(self.mydin_url, wait_until="networkidle", timeout=60000)
                await asyncio.sleep(2)
                
                # Scroll to load more products
                print("📜 Scrolling to load products...")
                for i in range(5):
                    await page.evaluate("window.scrollBy(0, 500)")
                    await asyncio.sleep(0.5)
                
                # Get page content
                content = await page.content()
                soup = BeautifulSoup(content, 'html.parser')
                
                # Find product elements
                product_elements = soup.select('[class*="product"]')
                print(f"Found {len(product_elements)} product elements")
                
                count = 0
                for element in product_elements:
                    if count >= limit:
                        break
                    
                    try:
                        # Get product name
                        name_elem = element.select_one('h2, h3, a, .product-name')
                        if not name_elem:
                            continue
                        
                        name = name_elem.get_text(strip=True)
                        if not name or len(name) < 3 or 'milo' not in name.lower():
                            continue
                        
                        # Get URL
                        url_elem = element.select_one('a[href]')
                        url = url_elem.get('href', '') if url_elem else ""
                        if url and not url.startswith('http'):
                            url = 'https://www.mydin.my' + url
                        
                        # Get price
                        price_elem = element.select_one('[class*="price"]')
                        price_text = price_elem.get_text(strip=True) if price_elem else ""
                        price_match = re.search(r'[\d,]+(?:\.\d{2})?', price_text)
                        price = price_match.group(0) if price_match else "0"
                        
                        # Get image
                        img_elem = element.select_one('img')
                        image_url = img_elem.get('src', '') if img_elem else ""
                        if image_url and not image_url.startswith('http'):
                            image_url = 'https://www.mydin.my' + image_url
                        
                        if name and price != "0":
                            product = {
                                'name': name,
                                'price': price,
                                'image_url': image_url,
                                'url': url,
                                'retailer': 'Mydin Malaysia'
                            }
                            self.mydin_products.append(product)
                            count += 1
                            print(f"✓ {count}. {name[:50]} - RM {price}")
                    
                    except Exception as e:
                        continue
                
                await browser.close()
                print(f"\n✅ Scraped {len(self.mydin_products)} Mydin products\n")
                
        except Exception as e:
            print(f"❌ Mydin scraping error: {e}")

    def save_results(self):
        """Save scraping results to JSON"""
        results = {
            'shopee_products': self.shopee_products,
            'mydin_products': self.mydin_products,
            'summary': {
                'shopee_count': len(self.shopee_products),
                'mydin_count': len(self.mydin_products),
                'scrape_date': datetime.now().isoformat()
            }
        }
        
        # Save to file
        output_file = Path('milo_price_comparison.json')
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(results, f, indent=2, ensure_ascii=False)
        
        print(f"✅ Results saved to milo_price_comparison.json\n")
        
        # Display summary
        print("="*80)
        print("📈 SUMMARY")
        print("="*80)
        print(f"Shopee Products: {len(self.shopee_products)}")
        print(f"Mydin Products:  {len(self.mydin_products)}")
        print("="*80 + "\n")

    async def run(self):
        """Run both scrapers"""
        await self.scrape_shopee_milo(limit=10)
        await self.scrape_mydin_milo(limit=10)
        self.save_results()


async def main():
    # Fix UTF-8 encoding for Windows console
    if sys.platform == 'win32':
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    
    scraper = MiloProductScraper()
    await scraper.run()


if __name__ == "__main__":
    asyncio.run(main())
