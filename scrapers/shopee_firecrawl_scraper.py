#!/usr/bin/env python3
"""
Shopee Malaysia Scraper using Firecrawl MCP
Extracts product names, prices, and images from https://shopee.com.my/
"""

import asyncio
import json
import sys
from pathlib import Path
from typing import List, Dict, Any
from datetime import datetime
import logging

# Add parent directory to path to import firecrawl_mcp_server
sys.path.insert(0, str(Path(__file__).parent.parent))

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


class ShopeeScraper:
    """Scraper for Shopee Malaysia products using Firecrawl MCP"""
    
    def __init__(self):
        """Initialize the scraper"""
        try:
            from firecrawl_mcp_server import FirecrawlMCPServer
            self.server = FirecrawlMCPServer(debug=False)
            self.client = self.server.client
        except ImportError as e:
            logger.error(f"Failed to import FirecrawlMCPServer: {e}")
            print(f"\n❌ Import Error: {e}")
            print("\n📋 Fix Steps:")
            print("   1. Run: pip install -r requirements.txt")
            print("   2. Make sure firecrawl_mcp_server.py exists in root directory")
            print("   3. Check dependencies are installed")
            sys.exit(1)
    
    async def scrape_shopee_homepage(self) -> List[Dict[str, Any]]:
        """
        Scrape Shopee Malaysia homepage for products
        
        Returns:
            List of product dictionaries with name, price, and image
        """
        url = "https://shopee.com.my/"
        
        logger.info(f"Scraping Shopee homepage: {url}")
        
        try:
            # Define CSS selectors for product extraction
            selectors = [
                # Product containers
                "[data-testid='grid-item']",
                "[data-testid='product-item']",
                ".shopee-search-item-result__item",
                "[class*='product']",
                # Product names
                "a.product-name",
                "[class*='title']",
                "h2, h3",
                # Prices
                ".price, [class*='price']",
                "span[class*='price']",
                # Images
                "img[class*='product']",
                "img[alt*='product']",
                ".product-image img"
            ]
            
            # Crawl the page
            logger.info("Crawling page with Firecrawl MCP...")
            result = await self.client.crawl(
                url=url,
                depth=1,
                include_markdown=False,
                timeout=60
            )
            
            if result.status_code != 200:
                logger.error(f"Failed to crawl: HTTP {result.status_code}")
                return []
            
            # Extract data using BeautifulSoup
            from bs4 import BeautifulSoup
            soup = BeautifulSoup(result.content, 'html.parser')
            
            products = self._extract_products_from_html(soup)
            
            # Limit to 10 products
            products = products[:10]
            
            logger.info(f"Extracted {len(products)} products")
            return products
            
        except Exception as e:
            logger.error(f"Error scraping Shopee: {e}")
            return []
    
    def _extract_products_from_html(self, soup) -> List[Dict[str, Any]]:
        """
        Extract product information from BeautifulSoup object
        
        Args:
            soup: BeautifulSoup object of the page
            
        Returns:
            List of product dictionaries
        """
        products = []
        
        try:
            # Try multiple selector patterns to find products
            product_selectors = [
                "[data-testid='grid-item']",
                ".shopee-search-item-result__item",
                "[class*='product-item']",
                "div[class*='product']"
            ]
            
            product_elements = []
            for selector in product_selectors:
                product_elements = soup.select(selector)
                if product_elements:
                    logger.info(f"Found {len(product_elements)} products using selector: {selector}")
                    break
            
            if not product_elements:
                logger.warning("No product elements found, searching for alternative patterns...")
                # Try finding by common Shopee patterns
                product_elements = soup.find_all(['a', 'div'], class_=lambda x: bool(x and ('product' in x.lower() or 'item' in x.lower())))
            
            for element in product_elements[:10]:  # Limit to 10
                try:
                    product = self._extract_single_product(element)
                    if product and product.get('name'):
                        products.append(product)
                except Exception as e:
                    logger.debug(f"Error extracting product: {e}")
                    continue
            
            return products
            
        except Exception as e:
            logger.error(f"Error in _extract_products_from_html: {e}")
            return products
    
    def _extract_single_product(self, element) -> Dict[str, Any]:
        """
        Extract information from a single product element
        
        Args:
            element: BeautifulSoup element
            
        Returns:
            Dictionary with product info
        """
        product = {
            'name': None,
            'price': None,
            'image_url': None,
            'link': None
        }
        
        try:
            # Safety check for element
            if not element:
                return product
            
            # Extract product name
            name_selectors = [
                'a.product-name',
                'a[title]',
                'h2, h3',
                '[class*="title"]',
                'span[class*="name"]'
            ]
            
            for selector in name_selectors:
                try:
                    name_elem = element.select_one(selector)
                    if name_elem:
                        product['name'] = name_elem.get_text(strip=True)
                        if product['name']:  # Only break if we got a non-empty name
                            break
                except Exception:
                    continue
            
            # Extract product link
            try:
                link_elem = element.find('a', href=True)
                if link_elem:
                    product['link'] = link_elem['href']
                    if product['link'] and not product['link'].startswith('http'):
                        product['link'] = 'https://shopee.com.my' + product['link']
            except Exception:
                pass
            
            # Extract price
            price_selectors = [
                'span[class*="price"]',
                '[class*="price"]',
                '.price'
            ]
            
            for selector in price_selectors:
                try:
                    price_elem = element.select_one(selector)
                    if price_elem:
                        price_text = price_elem.get_text(strip=True)
                        # Extract numeric price
                        import re
                        price_match = re.search(r'[\d,]+(?:\.\d{2})?', price_text)
                        if price_match:
                            product['price'] = price_match.group()
                            break
                except Exception:
                    continue
            
            # Extract image
            img_selectors = [
                'img[class*="product"]',
                'img[alt]',
                'img',
            ]
            
            for selector in img_selectors:
                try:
                    img_elem = element.select_one(selector)
                    if img_elem:
                        src = img_elem.get('src') or img_elem.get('data-src')
                        if src:
                            product['image_url'] = src
                            break
                except Exception:
                    continue
            
            return product
            
        except Exception as e:
            logger.debug(f"Error extracting single product: {e}")
            return product
    
    async def scrape_shopee_search(self, query: str = "", limit: int = 10) -> List[Dict[str, Any]]:
        """
        Scrape Shopee search results
        
        Args:
            query: Search query (empty for homepage)
            limit: Number of products to extract
            
        Returns:
            List of products
        """
        if query:
            url = f"https://shopee.com.my/search?keyword={query}"
        else:
            url = "https://shopee.com.my/"
        
        logger.info(f"Scraping Shopee: {url}")
        
        try:
            # Crawl the page
            result = await self.client.crawl(
                url=url,
                depth=1,
                include_markdown=False,
                timeout=60
            )
            
            if result.status_code != 200:
                logger.error(f"Failed to crawl: HTTP {result.status_code}")
                return []
            
            from bs4 import BeautifulSoup
            soup = BeautifulSoup(result.content, 'html.parser')
            
            products = self._extract_products_from_html(soup)
            
            # Limit to requested amount
            products = products[:limit]
            
            logger.info(f"Extracted {len(products)} products from Shopee")
            return products
            
        except Exception as e:
            logger.error(f"Error in scrape_shopee_search: {e}")
            return []


async def main():
    """Main function"""
    
    print("\n" + "="*70)
    print("  SHOPEE MALAYSIA SCRAPER - Using Firecrawl MCP")
    print("="*70 + "\n")
    
    # Initialize scraper
    scraper = ShopeeScraper()
    
    # Scrape homepage for featured products
    logger.info("Starting Shopee scraping...")
    products = await scraper.scrape_shopee_homepage()
    
    if not products:
        logger.warning("No products found on homepage, trying search...")
        products = await scraper.scrape_shopee_search(limit=10)
    
    # Display results
    if products:
        print(f"\n✅ Found {len(products)} products:\n")
        
        for idx, product in enumerate(products, 1):
            print(f"Product #{idx}")
            print(f"  Name: {product.get('name', 'N/A')}")
            print(f"  Price: RM {product.get('price', 'N/A')}")
            print(f"  Image: {product.get('image_url', 'N/A')[:60]}..." if product.get('image_url') else "  Image: N/A")
            if product.get('link'):
                print(f"  Link: {product.get('link')[:60]}...")
            print()
        
        # Save to JSON
        output_file = Path("shopee_products.json")
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(products, f, indent=2, ensure_ascii=False)
        
        print(f"✅ Results saved to: {output_file}\n")
        
    else:
        print("❌ No products found. Please check:")
        print("  1. Internet connection is working")
        print("  2. Firecrawl API key is configured in .env")
        print("  3. Shopee website is accessible")
        print()


if __name__ == "__main__":
    asyncio.run(main())
