#!/usr/bin/env python3
"""
SmartShopper Scraper Configuration
Stores all configuration and utility functions
"""

import logging
import sys
from datetime import datetime

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('scraper.log'),
        logging.StreamHandler(sys.stderr)
    ]
)

logger = logging.getLogger(__name__)

# Retailer configurations
RETAILERS = {
    'mydin': {
        'name': 'Mydin',
        'url': 'https://mydin.my/',
        'search_url': 'https://mydin.my/search?search={}',
        'selectors': {
            'product_container': '[data-testid="product-card"], .product-card, [class*="product"], div[role="link"]',
            'product_name': '[class*="product-name"], h2, .product-title, [data-testid="product-name"]',
            'product_price': '[class*="price"], .product-price, [data-testid="product-price"]'
        }
    },
    'shopee': {
        'name': 'Shopee',
        'url': 'https://shopee.com.my/supermarket',
        'search_url': 'https://shopee.com.my/supermarket?keyword={}',
        'selectors': {
            'product_container': '.shopee-search-item-result__item',
            'product_name': 'span.line-clamp',
            'product_price': '.shopee-price-format__value'
        }
    }
}

# Sample products
SAMPLE_PRODUCTS = ['Milo', 'Rice', 'Sugar', 'Cooking Oil', 'Eggs', 'Milk', 'Bread']

# Milo products that Mydin has (for cross-retailer comparison)
MYDIN_MILO_PRODUCTS = [
    'Nestle Milo Activ-Go Original (500ml)',
    'Nestle Milo Breakfast Cereal (70g)',
    'Nestle Milo Breakfast Cereal (450g)',
    'Nestle Milo Activ Go Plus Fiber (500g)',
    'Nestle Milo Breakfast Cereal (300g)',
    'Nestle Milo Breakfast Cereal (150g)',
    'Nestle Milo Cereal Value Pack',
    'Milo Powder - Original',
    'Milo Powder - Hi-Fibre',
    'Milo 3 In 1 Mix',
]

# Selenium options
SELENIUM_OPTIONS = [
    '--headless',
    '--no-sandbox',
    '--disable-dev-shm-usage',
    '--disable-gpu',
    '--window-size=1920,1080',
    '--start-maximized',
    '--disable-blink-features=AutomationControlled',
    'user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]

# Timeouts (in seconds)
SCRAPER_TIMEOUT = 10
WAIT_TIMEOUT = 5

# Rate limiting
DELAY_BETWEEN_REQUESTS = 1  # seconds

class ScraperLogger:
    @staticmethod
    def info(message):
        logger.info(message)
    
    @staticmethod
    def error(message):
        logger.error(message)
    
    @staticmethod
    def warning(message):
        logger.warning(message)
    
    @staticmethod
    def debug(message):
        logger.debug(message)

log = ScraperLogger()
