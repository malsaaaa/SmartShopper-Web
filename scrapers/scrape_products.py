#!/usr/bin/env python3
"""
SmartShopper Web Scraper
Scrapes product prices from Malaysian retailers with error handling and retries
"""

import json
import sys
import time
import re
from typing import List, Dict, Optional
from bs4 import BeautifulSoup
import requests
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException

try:
    from config import RETAILERS, SAMPLE_PRODUCTS, SELENIUM_OPTIONS, DELAY_BETWEEN_REQUESTS, MYDIN_MILO_PRODUCTS, log
except ImportError:
    # Fallback if config.py not found
    RETAILERS = {}
    SAMPLE_PRODUCTS = ['Milo']
    SELENIUM_OPTIONS = ['--headless', '--no-sandbox', '--disable-dev-shm-usage']
    DELAY_BETWEEN_REQUESTS = 1
    MYDIN_MILO_PRODUCTS = []
    class FakeLog:
        @staticmethod
        def info(msg): print(msg, file=sys.stderr)
        @staticmethod
        def error(msg): print(f"ERROR: {msg}", file=sys.stderr)
        @staticmethod
        def warning(msg): print(f"WARNING: {msg}", file=sys.stderr)
    log = FakeLog()


class RetailerScraper:
    def __init__(self, max_retries=2):
        self.results: List[Dict] = []
        self.max_retries = max_retries
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        })
        self.chrome_options = self._setup_chrome_options()

    def _setup_chrome_options(self) -> Options:
        """Setup Chrome options for Selenium"""
        options = Options()
        for opt in SELENIUM_OPTIONS:
            options.add_argument(opt)
        return options

    def _scrape_with_requests(self, url: str, selectors: Dict) -> List[Dict]:
        """Scrape using Requests library as fallback when Selenium fails"""
        results = []
        try:
            log.info(f"Attempting Requests library scrape from {url}")
            
            try:
                response = self.session.get(url, timeout=10)
                if response.status_code == 403:
                    log.warning(f"HTTP 403 Forbidden - Server blocked Requests library")
                    return []
                elif response.status_code == 404:
                    log.warning(f"HTTP 404 Not Found - URL doesn't exist")
                    return []
                elif response.status_code != 200:
                    log.warning(f"HTTP {response.status_code} - Unexpected status code")
                    return []
            except requests.Timeout:
                log.warning(f"Requests timeout after 10 seconds")
                return []
            except requests.ConnectionError as e:
                log.warning(f"Connection error: {e}")
                return []
            except requests.RequestException as e:
                log.warning(f"Requests error: {e}")
                return []
            
            soup = BeautifulSoup(response.content, 'html.parser')
            products = []
            selectors_list = selectors['product_container'].split(', ')
            
            for selector in selectors_list:
                if not selector.strip():
                    continue
                try:
                    products = soup.select(selector)
                    if products:
                        log.info(f"Requests: Found {len(products)} products")
                        break
                except Exception as e:
                    log.debug(f"Requests selector error: {e}")
                    continue
            
            if not products:
                log.warning(f"Requests: No products found")
                return []
            
            for idx, product in enumerate(products[:20]):
                try:
                    name_elem = None
                    for name_selector in selectors['product_name'].split(', '):
                        if not name_selector.strip():
                            continue
                        try:
                            name_elem = product.select_one(name_selector)
                            if name_elem:
                                break
                        except Exception as e:
                            log.debug(f"Name selector error: {e}")
                            continue
                    
                    price_elem = None
                    for price_selector in selectors['product_price'].split(', '):
                        if not price_selector.strip():
                            continue
                        try:
                            price_elem = product.select_one(price_selector)
                            if price_elem:
                                break
                        except Exception as e:
                            log.debug(f"Price selector error: {e}")
                            continue
                    
                    if not name_elem or not price_elem:
                        continue
                    
                    name = name_elem.get_text(strip=True)[:100]
                    price = self._extract_price(price_elem.get_text())
                    
                    if not self._validate_product_data(name, price):
                        continue
                    
                    results.append({
                        'name': name,
                        'price': price,
                        'source': 'requests_library'
                    })
                    log.debug(f"Requests: Extracted {name} - RM {price}")
                    
                except Exception as e:
                    log.debug(f"Product parsing error: {e}")
                    continue
            
            if results:
                log.info(f"✓ Requests succeeded: {len(results)} products")
            
            return results
            
        except Exception as e:
            log.error(f"Requests scraping error: {e}")
            return []

    def _scrape_with_hybrid_approach(self, url: str, selectors: Dict) -> List[Dict]:
        """Try Selenium first, fallback to Requests if Selenium fails"""
        log.info(f"Hybrid scraping (Selenium → Requests) for {url}")
        
        # Step 1: Try Selenium
        try:
            log.info("Step 1: Attempting Selenium...")
            selenium_results = self._scrape_with_selenium(url, selectors)
            
            if selenium_results:
                log.info(f"✓ Selenium succeeded: {len(selenium_results)} products")
                for result in selenium_results:
                    result['source'] = 'selenium'
                return selenium_results
        except Exception as e:
            log.warning(f"Selenium failed: {e}")
        
        # Step 2: Fallback to Requests
        try:
            log.info("Step 2: Fallback - Attempting Requests...")
            requests_results = self._scrape_with_requests(url, selectors)
            
            if requests_results:
                log.info(f"✓ Requests succeeded: {len(requests_results)} products")
                return requests_results
        except Exception as e:
            log.warning(f"Requests fallback failed: {e}")
        
        # Step 3: Both failed
        log.error(f"Both Selenium and Requests failed for {url}")
        return []

    def _extract_price(self, text: str) -> float:
        """Extract numeric price from text with validation"""
        if not text:
            log.warning("Empty price text provided")
            return 0.0
        
        if not isinstance(text, str):
            log.warning(f"Price text is not string, type: {type(text)}")
            return 0.0
        
        price_str = re.sub(r'[^\d.]', '', text)
        
        if not price_str:
            log.warning(f"No numeric value found in price text: {text}")
            return 0.0
        
        try:
            price = float(price_str)
            if price < 0.01:
                log.warning(f"Price too low: {price}")
                return 0.0
            elif price > 9999.99:
                log.warning(f"Price suspiciously high: {price}")
                return 0.0
            return price
        except ValueError as e:
            log.error(f"Failed to convert price '{price_str}' to float: {e}")
            return 0.0

    def _validate_product_data(self, name: str, price: float) -> bool:
        """Validate extracted product data with comprehensive checks"""
        if not name or not name.strip():
            log.warning("Product name is empty")
            return False
        
        if len(name) > 150:
            log.warning(f"Product name too long: {len(name)} chars")
            return False
        
        if price <= 0:
            log.warning("Product price is zero or negative")
            return False
        
        invalid_patterns = ['javascript', 'error', 'null', 'undefined', 'nan', 'script']
        if any(pattern in name.lower() for pattern in invalid_patterns):
            log.warning(f"Invalid product name contains suspicious content: {name}")
            return False
        
        return True

    def _get_chrome_driver(self):
        """Get a Chrome WebDriver instance"""
        try:
            return webdriver.Chrome(options=self.chrome_options)
        except WebDriverException as e:
            log.error(f"Failed to initialize Chrome driver: {e}")
            return None

    def _scrape_with_selenium(self, url: str, selectors: Dict) -> List[Dict]:
        """Scrape using Selenium with comprehensive error handling"""
        driver = self._get_chrome_driver()
        if not driver:
            log.error("Failed to initialize Chrome driver")
            return []

        results = []
        try:
            log.info(f"Opening URL: {url}")
            driver.get(url)
            
            try:
                WebDriverWait(driver, 15).until(
                    lambda d: d.find_elements(By.CSS_SELECTOR, "[class*='product']")
                )
            except TimeoutException:
                log.warning(f"Timeout waiting for products to load at {url}")
                return []
            
            for i in range(3):
                try:
                    driver.execute_script("window.scrollBy(0, window.innerHeight);")
                    time.sleep(1)
                except Exception as e:
                    log.warning(f"Error during scroll iteration {i}: {e}")
                    break
            
            soup = BeautifulSoup(driver.page_source, 'html.parser')
            
            products = []
            selectors_list = selectors['product_container'].split(', ')
            
            for selector in selectors_list:
                if not selector.strip():
                    continue
                
                try:
                    products = soup.select(selector)
                    if products:
                        log.info(f"Found {len(products)} products with selector: {selector}")
                        break
                except Exception as e:
                    log.warning(f"Error with selector '{selector}': {e}")
                    continue
            
            if not products:
                log.error(f"No products found with any selector for {url}")
                return []
            
            log.info(f"Total products found: {len(products)}")
            
            for idx, product in enumerate(products[:20]):
                try:
                    name_elem = None
                    name_selectors = selectors['product_name'].split(', ')
                    
                    for name_selector in name_selectors:
                        if not name_selector.strip():
                            continue
                        try:
                            name_elem = product.select_one(name_selector)
                            if name_elem:
                                break
                        except Exception as e:
                            log.debug(f"Error with name selector '{name_selector}': {e}")
                            continue
                    
                    price_elem = None
                    price_selectors = selectors['product_price'].split(', ')
                    
                    for price_selector in price_selectors:
                        if not price_selector.strip():
                            continue
                        try:
                            price_elem = product.select_one(price_selector)
                            if price_elem:
                                break
                        except Exception as e:
                            log.debug(f"Error with price selector '{price_selector}': {e}")
                            continue
                    
                    if not name_elem:
                        log.debug(f"Product {idx}: No name element found")
                        continue
                    
                    if not price_elem:
                        log.debug(f"Product {idx}: No price element found")
                        continue
                    
                    name = name_elem.get_text(strip=True)[:100]
                    price = self._extract_price(price_elem.get_text())
                    
                    if not self._validate_product_data(name, price):
                        log.debug(f"Product {idx}: Failed validation - name: {name}, price: {price}")
                        continue
                    
                    results.append({
                        'name': name,
                        'price': price
                    })
                    log.debug(f"Extracted: {name} - RM {price}")
                    
                except Exception as e:
                    log.warning(f"Error parsing product {idx}: {e}")
                    continue
                    
        except TimeoutException:
            log.error(f"Timeout waiting for content at {url}")
        except WebDriverException as e:
            log.error(f"WebDriver error at {url}: {e}")
        except Exception as e:
            log.error(f"Selenium scraping error: {e}")
        finally:
            try:
                driver.quit()
            except Exception as e:
                log.warning(f"Error closing driver: {e}")
        
        return results

    def _extract_image_url(self, product_element) -> str:
        """Extract image URL from a product element"""
        try:
            # Try multiple image selectors
            selectors = [
                'img[src*="product"]',
                'img[alt*="product"]',
                'img',
                'picture img',
                '[role="img"] img'
            ]
            
            for selector in selectors:
                img = product_element.select_one(selector)
                if img:
                    # Try src attribute first
                    src = img.get('src')
                    if src and ('http' in src or '/' in src):
                        return src
                    # Try data-src for lazy loading
                    data_src = img.get('data-src')
                    if data_src and ('http' in data_src or '/' in data_src):
                        return data_src
            
            return ""
        except Exception as e:
            log.warning(f"Error extracting image: {e}")
            return ""

    def _scrape_with_selenium_images(self, url: str, selectors: Dict) -> List[Dict]:
        """Scrape products with images using Selenium"""
        driver = self._get_chrome_driver()
        if not driver:
            return []

        results = []
        try:
            driver.get(url)
            # Wait for products to load
            WebDriverWait(driver, 15).until(
                lambda d: d.find_elements(By.CSS_SELECTOR, "[class*='product']")
            )
            
            # Scroll to load more products
            for _ in range(3):
                driver.execute_script("window.scrollBy(0, window.innerHeight);")
                time.sleep(0.5)
            
            soup = BeautifulSoup(driver.page_source, 'html.parser')
            
            # Find product containers
            products = []
            for selector in selectors['product_container'].split(', '):
                products = soup.select(selector)
                if products:
                    log.info(f"Found {len(products)} products with selector: {selector}")
                    break
            
            for product in products[:10]:  # Limit to 10 products
                try:
                    # Extract product name
                    name_elem = None
                    for selector in selectors['product_name'].split(', '):
                        name_elem = product.select_one(selector)
                        if name_elem:
                            break
                    
                    # Extract price
                    price_elem = None
                    for selector in selectors['product_price'].split(', '):
                        price_elem = product.select_one(selector)
                        if price_elem:
                            break
                    
                    # Extract image
                    image_url = self._extract_image_url(product)
                    
                    if name_elem and price_elem and image_url:
                        results.append({
                            'name': name_elem.get_text(strip=True)[:100],
                            'price': self._extract_price(price_elem.get_text()),
                            'image_url': image_url
                        })
                except Exception as e:
                    log.warning(f"Error parsing product: {e}")
                    continue
                    
        except TimeoutException:
            log.warning(f"Timeout loading {url}")
        except Exception as e:
            log.error(f"Error scraping {url}: {e}")
        finally:
            driver.quit()
        
        return results

    def scrape_retailer(self, retailer_key: str, retailer_config: Dict) -> None:
        """Scrape products from a retailer with comprehensive fallback logic"""
        if 'name' not in retailer_config:
            log.error("Retailer config missing 'name' field")
            return
        
        retailer_name = retailer_config['name']
        log.info(f"Starting scrape for {retailer_name}...")
        
        products_to_scrape = [
            ('Maggi', 'Maggi Instant Noodles'),
            ('Cooking Oil', 'Cooking Oil 1L'),
            ('Condensed Milk', 'Condensed Milk')
        ]
        
        for search_term, product_category in products_to_scrape:
            try:
                if 'search_url' not in retailer_config:
                    log.error(f"Retailer {retailer_name} has no search_url configured")
                    continue
                
                if 'selectors' not in retailer_config:
                    log.error(f"Retailer {retailer_name} has no selectors configured")
                    continue
                
                search_url = retailer_config['search_url'].format(search_term)
                log.info(f"Searching for {product_category} in {retailer_name}")
                
                scraped_products = None
                scrape_error = None
                
                try:
                    log.info(f"Attempting real scrape from {search_url}")
                    scraped_products = self._scrape_with_hybrid_approach(search_url, retailer_config['selectors'])
                    
                    if scraped_products:
                        log.info(f"Successfully scraped {len(scraped_products)} real products")
                    elif scraped_products is not None:
                        log.warning(f"Scrape completed but found 0 products from {retailer_name}")
                        scrape_error = "No products found"
                        
                except TimeoutException as e:
                    log.warning(f"Timeout scraping {retailer_name}: {e}")
                    scrape_error = "Timeout"
                except WebDriverException as e:
                    log.warning(f"WebDriver error scraping {retailer_name}: {e}")
                    scrape_error = "WebDriver error"
                except Exception as e:
                    log.warning(f"General error scraping real data: {e}")
                    scrape_error = str(e)
                
                if scraped_products:
                    for product in scraped_products[:3]:
                        self.results.append({
                            'name': product['name'],
                            'retailer': retailer_name,
                            'price': product['price'],
                            'category': product_category,
                            'url': search_url,
                            'image_url': product.get('image_url', ''),
                            'source': 'real'
                        })
                    continue
                
                if scrape_error:
                    log.info(f"Falling back to test data due to: {scrape_error}")
                else:
                    log.warning(f"No real products found, using test data as fallback")
                
                test_data = self._get_test_products(retailer_key, search_term, product_category)
                
                if test_data:
                    log.info(f"Adding {len(test_data)} test products for {product_category}")
                    for item in test_data:
                        self.results.append({
                            'name': item['name'],
                            'retailer': retailer_name,
                            'price': item['price'],
                            'category': product_category,
                            'url': search_url,
                            'image_url': item.get('image_url', ''),
                            'source': 'fallback_test_data'
                        })
                else:
                    log.error(f"No test data available for fallback")
                    
            except KeyError as e:
                log.error(f"Missing configuration key: {e}")
                continue
            except Exception as e:
                log.error(f"Unexpected error scraping {product_category} from {retailer_name}: {e}")
                continue
    
    def _get_test_products(self, retailer_key: str, search_term: str, category: str) -> List[Dict]:
        """Get test product data for fallback"""
        test_products = {
            'maggi': {
                'mydin': [
                    {'name': 'Maggi 2-Minute Noodles Original', 'price': 0.65, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Original'},
                    {'name': 'Maggi 2-Minute Noodles Chicken', 'price': 0.65, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Chicken'},
                    {'name': 'Maggi Curry Flavor', 'price': 0.70, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Curry'},
                ],
                'lotus': [
                    {'name': 'Maggi 2-Minute Noodles Original', 'price': 0.70, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Original'},
                    {'name': 'Maggi 2-Minute Noodles Chicken', 'price': 0.70, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Chicken'},
                    {'name': 'Maggi Curry Flavor', 'price': 0.75, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Curry'},
                ],
                'shopee': [
                    {'name': 'Maggi 2-Minute Noodles Original (Box 30)', 'price': 18.50, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Box'},
                    {'name': 'Maggi 2-Minute Noodles Chicken', 'price': 0.60, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Chicken'},
                    {'name': 'Maggi Curry Flavor', 'price': 0.65, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Curry'},
                ],
                'lazada': [
                    {'name': 'Maggi 2-Minute Noodles Original (Box 30)', 'price': 19.99, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Box'},
                    {'name': 'Maggi 2-Minute Noodles Chicken', 'price': 0.68, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Chicken'},
                    {'name': 'Maggi Curry Flavor', 'price': 0.72, 'image_url': 'https://via.placeholder.com/300?text=Maggi+Curry'},
                ],
            },
            'cooking oil': {
                'mydin': [
                    {'name': 'Saji Cooking Oil 1L', 'price': 6.80, 'image_url': 'https://via.placeholder.com/300?text=Saji+Oil'},
                    {'name': 'Aluanna Cooking Oil 1L', 'price': 6.50, 'image_url': 'https://via.placeholder.com/300?text=Aluanna+Oil'},
                    {'name': 'Knife Cooking Oil 1L', 'price': 6.20, 'image_url': 'https://via.placeholder.com/300?text=Knife+Oil'},
                ],
                'lotus': [
                    {'name': 'Saji Cooking Oil 1L', 'price': 6.90, 'image_url': 'https://via.placeholder.com/300?text=Saji+Oil'},
                    {'name': 'Aluanna Cooking Oil 1L', 'price': 6.60, 'image_url': 'https://via.placeholder.com/300?text=Aluanna+Oil'},
                    {'name': 'Knife Cooking Oil 1L', 'price': 6.30, 'image_url': 'https://via.placeholder.com/300?text=Knife+Oil'},
                ],
                'shopee': [
                    {'name': 'Saji Cooking Oil 1L', 'price': 6.50, 'image_url': 'https://via.placeholder.com/300?text=Saji+Oil'},
                    {'name': 'Aluanna Cooking Oil 1L', 'price': 6.20, 'image_url': 'https://via.placeholder.com/300?text=Aluanna+Oil'},
                    {'name': 'Knife Cooking Oil 1L', 'price': 5.99, 'image_url': 'https://via.placeholder.com/300?text=Knife+Oil'},
                ],
                'lazada': [
                    {'name': 'Saji Cooking Oil 1L', 'price': 6.75, 'image_url': 'https://via.placeholder.com/300?text=Saji+Oil'},
                    {'name': 'Aluanna Cooking Oil 1L', 'price': 6.45, 'image_url': 'https://via.placeholder.com/300?text=Aluanna+Oil'},
                    {'name': 'Knife Cooking Oil 1L', 'price': 6.15, 'image_url': 'https://via.placeholder.com/300?text=Knife+Oil'},
                ],
            },
            'condensed milk': {
                'mydin': [
                    {'name': 'Longleng Condensed Milk 397g', 'price': 3.20, 'image_url': 'https://via.placeholder.com/300?text=Longleng+Milk'},
                    {'name': 'F&N Condensed Milk 397g', 'price': 3.50, 'image_url': 'https://via.placeholder.com/300?text=FN+Milk'},
                    {'name': 'Susu Cap Bendera 397g', 'price': 3.40, 'image_url': 'https://via.placeholder.com/300?text=Cap+Bendera'},
                ],
                'lotus': [
                    {'name': 'Longleng Condensed Milk 397g', 'price': 3.30, 'image_url': 'https://via.placeholder.com/300?text=Longleng+Milk'},
                    {'name': 'F&N Condensed Milk 397g', 'price': 3.60, 'image_url': 'https://via.placeholder.com/300?text=FN+Milk'},
                    {'name': 'Susu Cap Bendera 397g', 'price': 3.50, 'image_url': 'https://via.placeholder.com/300?text=Cap+Bendera'},
                ],
                'shopee': [
                    {'name': 'Longleng Condensed Milk 397g', 'price': 3.10, 'image_url': 'https://via.placeholder.com/300?text=Longleng+Milk'},
                    {'name': 'F&N Condensed Milk 397g', 'price': 3.35, 'image_url': 'https://via.placeholder.com/300?text=FN+Milk'},
                    {'name': 'Susu Cap Bendera 397g', 'price': 3.25, 'image_url': 'https://via.placeholder.com/300?text=Cap+Bendera'},
                ],
                'lazada': [
                    {'name': 'Longleng Condensed Milk 397g', 'price': 3.25, 'image_url': 'https://via.placeholder.com/300?text=Longleng+Milk'},
                    {'name': 'F&N Condensed Milk 397g', 'price': 3.55, 'image_url': 'https://via.placeholder.com/300?text=FN+Milk'},
                    {'name': 'Susu Cap Bendera 397g', 'price': 3.45, 'image_url': 'https://via.placeholder.com/300?text=Cap+Bendera'},
                ],
            }
        }
        
        search_key = search_term.lower()
        retailer_map = {
            'mydin': 'mydin',
            'lotus': 'lotus',
            'shopee': 'shopee',
            'lazada': 'lazada'
        }
        
        retailer_name = retailer_map.get(retailer_key, retailer_key)
        return test_products.get(search_key, {}).get(retailer_name, [])

    def run(self) -> str:
        """Run all scrapers and return JSON output"""
        log.info("Starting price scraping from Mydin, Lotus's, Shopee, and Lazada...")
        
        try:
            # Use predefined retailers if available, otherwise use defaults
            retailers_to_scrape = RETAILERS if RETAILERS else {
                'mydin': {'name': 'Mydin', 'search_url': 'https://mydin.my/search?search={}', 'selectors': {}},
                'lotus': {'name': "Lotus's", 'search_url': 'https://www.lotuss.com.my/en/search/{}?sort=relevance:DESC', 'selectors': {}},
                'shopee': {'name': 'Shopee', 'search_url': 'https://shopee.com.my/search?keyword={}', 'selectors': {}},
                'lazada': {'name': 'Lazada', 'search_url': 'https://www.lazada.com.my/catalog/?q={}', 'selectors': {}},
            }
            
            # Scrape only these 4 retailers (exclude Giant)
            for retailer_key in ['mydin', 'lotus', 'shopee', 'lazada']:
                if retailer_key in retailers_to_scrape:
                    retailer_config = retailers_to_scrape[retailer_key]
                    try:
                        self.scrape_retailer(retailer_key, retailer_config)
                    except Exception as e:
                        log.error(f"Failed to scrape {retailer_config['name']}: {e}")
                        continue
            
            log.info(f"Scraping complete. Found {len(self.results)} items")
            return json.dumps(self.results)
            
        except Exception as e:
            log.error(f"Fatal error during scraping: {e}")
            return json.dumps(self.results)


if __name__ == "__main__":
    scraper = RetailerScraper()
    output = scraper.run()
    # Print JSON to stdout for Laravel to consume
    print(output)
