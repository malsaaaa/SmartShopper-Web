#!/usr/bin/env python3
"""
Firecrawl MCP Integration with Laravel Application
Service layer for integrating Firecrawl with SmartShopper
"""

import asyncio
import json
from typing import Dict, List, Any, Optional
from datetime import datetime
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class FirecrawlService:
    """Service for integrating Firecrawl with Laravel application"""
    
    def __init__(self):
        """Initialize Firecrawl service"""
        from firecrawl_mcp_server import FirecrawlMCPServer
        self.server = FirecrawlMCPServer()
        self.cache = {}
    
    async def crawl_product_page(
        self,
        url: str,
        include_markdown: bool = True
    ) -> Dict[str, Any]:
        """
        Crawl a product page and extract structured data
        
        Args:
            url: Product page URL
            include_markdown: Whether to include markdown conversion
            
        Returns:
            Dictionary with crawl results
        """
        logger.info(f"Crawling product page: {url}")
        
        try:
            result = await self.server.client.crawl(
                url=url,
                depth=1,
                include_markdown=include_markdown,
                timeout=30
            )
            
            return {
                'success': True,
                'url': url,
                'title': result.metadata.get('title', ''),
                'description': result.metadata.get('description', ''),
                'content': result.markdown if include_markdown else result.content[:500],
                'links': result.links[:10],  # First 10 links
                'timestamp': datetime.now().isoformat()
            }
        except Exception as e:
            logger.error(f"Error crawling {url}: {str(e)}")
            return {
                'success': False,
                'url': url,
                'error': str(e),
                'timestamp': datetime.now().isoformat()
            }
    
    async def scrape_shopee_products(
        self,
        category_url: str,
        product_schema: Optional[Dict[str, str]] = None
    ) -> Dict[str, Any]:
        """
        Scrape Shopee product listings
        
        Args:
            category_url: Shopee category URL
            product_schema: Schema for extracting product data
            
        Returns:
            Dictionary with scraped products
        """
        if product_schema is None:
            product_schema = {
                'product_name': 'div.shopee-search-item-result__item',
                'price': 'span.price',
                'rating': 'span[data-testid="rating"]',
                'sold_count': 'span.sold'
            }
        
        logger.info(f"Scraping Shopee products from: {category_url}")
        
        try:
            result = await self.server.client.scrape(
                url=category_url,
                schema=product_schema,
                timeout=30
            )
            
            return {
                'success': True,
                'url': category_url,
                'products': result.get('data', {}),
                'count': len(result.get('data', {}).get('product_name', [])),
                'timestamp': datetime.now().isoformat()
            }
        except Exception as e:
            logger.error(f"Error scraping Shopee: {str(e)}")
            return {
                'success': False,
                'url': category_url,
                'error': str(e),
                'timestamp': datetime.now().isoformat()
            }
    
    async def extract_product_details(
        self,
        url: str,
        selectors: Optional[List[str]] = None
    ) -> Dict[str, Any]:
        """
        Extract specific product details using CSS selectors
        
        Args:
            url: Product URL
            selectors: CSS selectors for extraction
            
        Returns:
            Dictionary with extracted data
        """
        if selectors is None:
            selectors = [
                'h1.product-title',
                'span.price',
                'div.rating',
                'p.description',
                'button.add-to-cart'
            ]
        
        logger.info(f"Extracting details from: {url}")
        
        try:
            result = await self.server.client.extract(
                url=url,
                selectors=selectors,
                timeout=30
            )
            
            return {
                'success': True,
                'url': url,
                'data': result.get('data', {}),
                'selectors_used': selectors,
                'timestamp': datetime.now().isoformat()
            }
        except Exception as e:
            logger.error(f"Error extracting details: {str(e)}")
            return {
                'success': False,
                'url': url,
                'error': str(e),
                'timestamp': datetime.now().isoformat()
            }
    
    async def batch_crawl(
        self,
        urls: List[str],
        include_markdown: bool = True
    ) -> List[Dict[str, Any]]:
        """
        Crawl multiple URLs in batch
        
        Args:
            urls: List of URLs to crawl
            include_markdown: Whether to include markdown
            
        Returns:
            List of crawl results
        """
        logger.info(f"Batch crawling {len(urls)} URLs")
        
        tasks = [
            self.crawl_product_page(url, include_markdown)
            for url in urls
        ]
        
        results = await asyncio.gather(*tasks)
        
        return {
            'success': True,
            'total': len(urls),
            'successful': sum(1 for r in results if r.get('success')),
            'results': results,
            'timestamp': datetime.now().isoformat()
        }
    
    async def compare_products(
        self,
        product_urls: List[str],
        selectors: Optional[List[str]] = None
    ) -> Dict[str, Any]:
        """
        Extract and compare product details from multiple URLs
        
        Args:
            product_urls: List of product URLs to compare
            selectors: CSS selectors for extraction
            
        Returns:
            Comparison data
        """
        logger.info(f"Comparing {len(product_urls)} products")
        
        if selectors is None:
            selectors = [
                'h1.product-title',
                'span.price',
                'div.rating',
                'p.description'
            ]
        
        results = await self.batch_crawl(product_urls, include_markdown=False)
        
        return {
            'success': True,
            'comparison_data': results,
            'selectors': selectors,
            'timestamp': datetime.now().isoformat()
        }
    
    async def monitor_price_changes(
        self,
        product_url: str,
        price_selector: str = 'span.price'
    ) -> Dict[str, Any]:
        """
        Monitor price changes for a product
        
        Args:
            product_url: Product URL to monitor
            price_selector: CSS selector for price element
            
        Returns:
            Current price and trend data
        """
        logger.info(f"Monitoring price for: {product_url}")
        
        try:
            result = await self.server.client.extract(
                url=product_url,
                selectors=[price_selector],
                timeout=30
            )
            
            prices = result.get('data', {}).get(price_selector, [])
            current_price = prices[0] if prices else None
            
            # Store in cache for trend analysis
            if product_url not in self.cache:
                self.cache[product_url] = []
            
            self.cache[product_url].append({
                'price': current_price,
                'timestamp': datetime.now().isoformat()
            })
            
            # Keep only last 100 entries
            if len(self.cache[product_url]) > 100:
                self.cache[product_url] = self.cache[product_url][-100:]
            
            return {
                'success': True,
                'url': product_url,
                'current_price': current_price,
                'history': self.cache[product_url],
                'trend': self._calculate_trend(self.cache[product_url]),
                'timestamp': datetime.now().isoformat()
            }
        except Exception as e:
            logger.error(f"Error monitoring price: {str(e)}")
            return {
                'success': False,
                'url': product_url,
                'error': str(e),
                'timestamp': datetime.now().isoformat()
            }
    
    @staticmethod
    def _calculate_trend(prices: List[Dict[str, str]]) -> str:
        """Calculate price trend"""
        if len(prices) < 2:
            return 'stable'
        
        # Compare last price with average
        last_price = float(prices[-1].get('price', 0).replace('$', '').replace(',', ''))
        avg_price = sum(
            float(p.get('price', 0).replace('$', '').replace(',', ''))
            for p in prices[:-1]
        ) / max(len(prices) - 1, 1)
        
        if last_price > avg_price * 1.05:
            return 'increasing'
        elif last_price < avg_price * 0.95:
            return 'decreasing'
        else:
            return 'stable'
    
    def get_cache_stats(self) -> Dict[str, Any]:
        """Get cache statistics"""
        return {
            'cached_urls': len(self.cache),
            'total_entries': sum(len(v) for v in self.cache.values()),
            'urls': list(self.cache.keys())
        }
    
    def clear_cache(self, url: Optional[str] = None):
        """Clear cache"""
        if url:
            self.cache.pop(url, None)
            logger.info(f"Cleared cache for: {url}")
        else:
            self.cache.clear()
            logger.info("Cleared all cache")


# Example usage functions for Laravel integration

async def example_crawl():
    """Example: Crawl a product page"""
    service = FirecrawlService()
    result = await service.crawl_product_page('https://example.com/product')
    return result

async def example_batch_crawl():
    """Example: Crawl multiple products"""
    service = FirecrawlService()
    urls = [
        'https://example.com/product/1',
        'https://example.com/product/2',
        'https://example.com/product/3'
    ]
    results = await service.batch_crawl(urls)
    return results

async def example_price_monitoring():
    """Example: Monitor product prices"""
    service = FirecrawlService()
    result = await service.monitor_price_changes(
        'https://example.com/product',
        'span.price'
    )
    return result

# Integration with Laravel
"""
# In your Laravel Controller:
use App\\Services\\FirecrawlService;

class ProductController extends Controller
{
    private $firecrawlService;
    
    public function __construct()
    {
        $this->firecrawlService = new FirecrawlService();
    }
    
    public function crawlProduct(Request $request)
    {
        $url = $request->input('url');
        $result = $this->firecrawlService->crawlProductPage($url);
        return response()->json($result);
    }
    
    public function batchCrawl(Request $request)
    {
        $urls = $request->input('urls', []);
        $results = $this->firecrawlService->batchCrawl($urls);
        return response()->json($results);
    }
}
"""
