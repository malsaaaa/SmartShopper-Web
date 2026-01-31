#!/usr/bin/env python3
"""
Shopee Scraper using Apify
Uses Apify's maintained Shopee Actor to bypass bot detection
More reliable than direct Selenium/requests approaches

Installation: pip install apify-client
API Token: https://apify.com (free tier available)
Environment: Set APIFY_API_TOKEN environment variable
"""

import json
import sys
import os
import logging
from typing import List, Dict, Optional

logger = logging.getLogger(__name__)

# Try to import apify_client, but don't fail if not installed
try:
    from apify_client import ApifyClient
    APIFY_AVAILABLE = True
except ImportError:
    APIFY_AVAILABLE = False
    logger.warning("apify-client not installed. Install with: pip install apify-client")


class ApifyShopeeScraperClient:
    """
    Uses Apify's Shopee Actor for web scraping
    Requires APIFY_API_TOKEN in environment variables
    Free tier: 50 free platform credits/month (~100-200 scrapes)
    
    Benefits:
    - Built-in bot detection bypass
    - Automatic proxy rotation
    - Official maintenance and updates
    - High success rate (95%+)
    """
    
    def __init__(self):
        if not APIFY_AVAILABLE:
            self.client = None
            logger.warning("Apify not available - install with: pip install apify-client")
            return
        
        api_token = os.getenv('APIFY_API_TOKEN')
        if not api_token:
            self.client = None
            logger.warning("APIFY_API_TOKEN environment variable not set")
            return
        
        try:
            self.client = ApifyClient(api_token)
            logger.info("Apify client initialized successfully")
            # Common Shopee scraper actors
            self.actor_ids = [
                'junglee/shopee-product-scraper',  # Primary community actor
                'apify/shopee-scraper',             # Alternative
                'metalicate/shopee-scraper',        # Another alternative
            ]
        except Exception as e:
            self.client = None
            logger.error(f"Failed to initialize Apify client: {e}")
    
    def is_available(self) -> bool:
        """Check if Apify is properly configured"""
        return self.client is not None
    
    def search(self, query: str, country: str = 'my', max_results: int = 10) -> List[Dict]:
        """
        Search Shopee using Apify Actor
        
        Args:
            query: Product search query
            country: Country code (my=Malaysia, ph=Philippines, th=Thailand, vn=Vietnam, sg=Singapore, id=Indonesia)
            max_results: Maximum number of results to return
        
        Returns:
            List of product dictionaries with normalized fields
        """
        if not self.client:
            logger.error("Apify client not initialized")
            return []
        
        try:
            logger.info(f"Starting Apify scrape for: '{query}' in {country.upper()}")
            
            # Try each actor until one works
            for actor_id in self.actor_ids:
                try:
                    return self._try_actor(actor_id, query, country, max_results)
                except Exception as e:
                    logger.debug(f"Actor {actor_id} failed: {e}")
                    continue
            
            logger.error("All Apify actors failed")
            return []
            
        except Exception as e:
            logger.error(f"Apify search error: {e}")
            return []
    
    def _try_actor(self, actor_id: str, query: str, country: str, max_results: int) -> List[Dict]:
        """Try a specific actor and return results"""
        logger.info(f"Trying actor: {actor_id}")
        
        # Prepare input for the Actor
        run_input = {
            "keyword": query,
            "country": country,
            "maxResults": max_results,
            "proxy": {
                "useApifyProxy": True
            }
        }
        
        # Call the Actor
        run = self.client.actor(actor_id).call(run_input=run_input)
        
        # Get results from the Actor's dataset
        dataset_id = run["datasetId"]
        dataset_items = self.client.dataset(dataset_id).list_items()
        
        products = []
        for item in dataset_items.items:
            try:
                product = self._normalize_product(item)
                if product:
                    products.append(product)
            except Exception as e:
                logger.debug(f"Error normalizing product: {e}")
                continue
        
        logger.info(f"Retrieved {len(products)} products from Apify (actor: {actor_id})")
        return products[:max_results]
    
    def _normalize_product(self, item: Dict) -> Optional[Dict]:
        """
        Normalize Apify response to standard product format
        Handles different field names from various Apify actors
        """
        try:
            # Extract name
            name = item.get('title') or item.get('name') or item.get('productName')
            if not name:
                return None
            
            # Extract price (handle different formats)
            price = 0
            if 'price' in item:
                price = float(str(item['price']).replace(',', '')) if item['price'] else 0
            elif 'priceValue' in item:
                price = float(item['priceValue']) if item['priceValue'] else 0
            
            if price <= 0:
                return None
            
            # Extract original price
            original_price = None
            if 'originalPrice' in item:
                op = item['originalPrice']
                original_price = float(str(op).replace(',', '')) if op else None
            elif 'priceBeforeDiscount' in item:
                op = item['priceBeforeDiscount']
                original_price = float(op) if op else None
            
            # Calculate discount if not provided
            discount = 0
            if 'discount' in item:
                discount = int(item['discount']) if item['discount'] else 0
            elif original_price and original_price > 0:
                discount = int(((original_price - price) / original_price) * 100)
            
            # Extract rating
            rating = 0
            if 'rating' in item:
                rating = float(item['rating']) if item['rating'] else 0
            elif 'ratingAverage' in item:
                rating = float(item['ratingAverage']) if item['ratingAverage'] else 0
            
            # Extract review count
            reviews = 0
            if 'reviewCount' in item:
                reviews = int(item['reviewCount']) if item['reviewCount'] else 0
            elif 'ratingCount' in item:
                reviews = int(item['ratingCount']) if item['ratingCount'] else 0
            elif 'reviews' in item:
                reviews = int(item['reviews']) if item['reviews'] else 0
            
            # Extract shop information
            shop_name = item.get('shopName') or item.get('shop') or item.get('seller') or 'Unknown'
            is_official = bool(item.get('isOfficial') or item.get('isOfficialShop') or item.get('officialShop'))
            
            # Extract image
            image_url = item.get('image') or item.get('imageUrl') or item.get('thumbnail')
            
            # Extract URL
            url = item.get('url') or item.get('productUrl') or item.get('link')
            
            product = {
                'name': str(name).strip(),
                'price': price,
                'original_price': original_price,
                'discount': discount,
                'rating': rating,
                'reviews': reviews,
                'shop_name': str(shop_name).strip(),
                'is_official_shop': is_official,
                'image_url': image_url,
                'url': url,
            }
            
            return product
            
        except Exception as e:
            logger.debug(f"Error normalizing product: {e}")
            return None


def main():
    """Test the Apify scraper from command line"""
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Usage: python apify_shopee_scraper.py "search_query" [max_results] [country]'}))
        sys.exit(1)
    
    query = sys.argv[1]
    max_results = int(sys.argv[2]) if len(sys.argv) > 2 else 10
    country = sys.argv[3] if len(sys.argv) > 3 else 'my'
    
    scraper = ApifyShopeeScraperClient()
    
    if not scraper.is_available():
        print(json.dumps({
            'error': 'Apify not available',
            'message': 'Install: pip install apify-client',
            'setup': 'Set APIFY_API_TOKEN environment variable from https://apify.com'
        }))
        sys.exit(1)
    
    try:
        products = scraper.search(query, country=country, max_results=max_results)
        output = {
            'query': query,
            'country': country,
            'count': len(products),
            'products': products
        }
        print(json.dumps(output, ensure_ascii=False, indent=2))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)


if __name__ == '__main__':
    main()
