#!/usr/bin/env python3
"""
Shopee.com.my Web Crawler
Real-time product search and scraping from Shopee Malaysia
Crawls search results and extracts product information

NOTE: Shopee implements sophisticated server-side bot detection that blocks all automated requests.
For production use, integrate a residential proxy service or use the official Shopee API.
This crawler includes mock data fallback for development/testing purposes.
"""

import json
import sys
import time
import re
import logging
import os
from typing import List, Dict, Optional
from urllib.parse import quote
from bs4 import BeautifulSoup
import requests
import undetected_chromedriver as uc
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException, NoSuchElementException

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Mock data for testing - Shopee blocks automated requests so we use this as fallback
MOCK_PRODUCTS_DATABASE = {
    'milo': [
        {
            'name': 'MILO 3in1 Creamy Taste 18 x 28g',
            'price': 12.90,
            'original_price': 14.90,
            'discount': 13,
            'rating': 4.5,
            'reviews': 1240,
            'shop_name': 'OFFICIAL MILO STORE',
            'image_url': 'https://cf.shopee.com.my/file/sg-11134201-7qul5-ljc23ck2fykh78',
            'url': 'https://shopee.com.my/product/123456/789012',
            'is_official_shop': True,
        },
        {
            'name': 'MILO Powder Original 400g',
            'price': 18.50,
            'original_price': 21.00,
            'discount': 12,
            'rating': 4.8,
            'reviews': 3456,
            'shop_name': 'OFFICIAL MILO STORE',
            'image_url': 'https://cf.shopee.com.my/file/sg-11134201-7qul6-ljc23ck2fykh79',
            'url': 'https://shopee.com.my/product/123456/789013',
            'is_official_shop': True,
        },
    ],
    'laptop': [
        {
            'name': 'ASUS VivoBook 15 Intel Core i5 8GB RAM 512GB SSD',
            'price': 2899.00,
            'original_price': 3499.00,
            'discount': 17,
            'rating': 4.6,
            'reviews': 567,
            'shop_name': 'ASUS Official Store',
            'image_url': 'https://cf.shopee.com.my/file/sg-11134301-7qul7-ljc23ck2fykh80',
            'url': 'https://shopee.com.my/product/234567/890123',
            'is_official_shop': True,
        },
        {
            'name': 'Lenovo ThinkBook 14 AMD Ryzen 5 16GB RAM 512GB SSD',
            'price': 2499.00,
            'original_price': 2999.00,
            'discount': 17,
            'rating': 4.7,
            'reviews': 892,
            'shop_name': 'Lenovo Official',
            'image_url': 'https://cf.shopee.com.my/file/sg-11134302-7qul8-ljc23ck2fykh81',
            'url': 'https://shopee.com.my/product/234568/890124',
            'is_official_shop': True,
        },
    ],
    'coffee': [
        {
            'name': 'Nescafe Gold Instant Coffee 200g',
            'price': 22.50,
            'original_price': 25.00,
            'discount': 10,
            'rating': 4.7,
            'reviews': 2134,
            'shop_name': 'Nestle Official',
            'image_url': 'https://cf.shopee.com.my/file/sg-11134401-7qul9-ljc23ck2fykh82',
            'url': 'https://shopee.com.my/product/345678/901234',
            'is_official_shop': True,
        },
    ],
    'samsung': [
        {
            'name': 'Samsung Galaxy Z Fold7 [ONLINE EXCLUSIVE]',
            'price': 7199.0,
            'original_price': 7799.0,
            'discount': 8,
            'rating': 5.0,
            'reviews': 20,
            'shop_name': 'Samsung Brand Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-7rasl-mbza7a0s2uh5a4',
            'url': 'https://shopee.com.my/product/471781414/27787876521',
            'is_official_shop': True,
        },
        {
            'name': 'Samsung Galaxy Z Flip7 [ONLINE EXCLUSIVE]',
            'price': 4399.0,
            'original_price': 4999.0,
            'discount': 12,
            'rating': 5.0,
            'reviews': 146,
            'shop_name': 'Samsung Brand Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-7ras8-mbzah7syi9jdc2',
            'url': 'https://shopee.com.my/product/471781414/29237881226',
            'is_official_shop': True,
        },
        {
            'name': 'Samsung Galaxy A06 5G (6GB+128GB) | 5,000mAh Battery |  6.7" HD | Android 15 | 1 YEAR WARRANTY',
            'price': 522.0,
            'original_price': 539.0,
            'discount': 3,
            'rating': 4.931818181818182,
            'reviews': 88,
            'shop_name': 'ZON GADGET MURAH',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820lb-mj0wr07aai2vee',
            'url': 'https://shopee.com.my/product/15224110/57454054278',
            'is_official_shop': True,
        },
        {
            'name': 'Samsung Galaxy A36 5G',
            'price': 1385.0,
            'original_price': 1699.0,
            'discount': 18,
            'rating': 4.978494623655914,
            'reviews': 930,
            'shop_name': 'Urban Republic Online Store',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134253-822zy-mi6avt490avc44',
            'url': 'https://shopee.com.my/product/1432004273/51702805009',
            'is_official_shop': True,
        },
        {
            'name': 'Samsung Galaxy A56 5G  (8GB+256GB) 6.7" FHD+ Super AMOLED Display/50MP OIS Camera（Used 9.9 new）',
            'price': 899.8,
            'original_price': 2069.0,
            'discount': 57,
            'rating': 5.0,
            'reviews': 2,
            'shop_name': 'POPBO Brand Direct Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820l9-mjggs7j2juo24a',
            'url': 'https://shopee.com.my/product/1665511822/53904637931',
            'is_official_shop': False,
        },
        {
            'name': 'Samsung Galaxy A07 LTE (8GB+256GB) Smartphone, 1 Year Samsung Warranty',
            'price': 525.0,
            'original_price': 699.0,
            'discount': 25,
            'rating': 4.972602739726027,
            'reviews': 146,
            'shop_name': 'Samsung Authorized Reseller - The Hopz',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820lc-me9guandclc0d1',
            'url': 'https://shopee.com.my/product/508684949/28891129561',
            'is_official_shop': False,
        },
        {
            'name': 'SAMSUNG A36 (A366) (12GB+256GB) / A26 (A266) (8GB+256GB) 100% Original Warranty Samsung Malaysia',
            'price': 1090.0,
            'original_price': 1599.0,
            'discount': 32,
            'rating': 4.90625,
            'reviews': 64,
            'shop_name': 'Ready2grab online shop',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134201-821gd-mh8kxkvp2qks2e',
            'url': 'https://shopee.com.my/product/1614598877/48601708010',
            'is_official_shop': False,
        },
        {
            'name': 'Samsung Galaxy A35 5G/A356E (8GB+256GB) Samsung Malaysia Set',
            'price': 1045.0,
            'original_price': 1045.0,
            'discount': 0,
            'rating': 5.0,
            'reviews': 102,
            'shop_name': 'A1 Distribution Online',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820l4-mjhyz4ci47b4b1',
            'url': 'https://shopee.com.my/product/12120558/48704735046',
            'is_official_shop': False,
        },
        {
            'name': 'Samsung Galaxy A17 5G (8GB+256GB) Smartphone, 1 Year Samsung Warranty',
            'price': 869.0,
            'original_price': 899.0,
            'discount': 3,
            'rating': 4.892215568862276,
            'reviews': 334,
            'shop_name': 'Samsung Authorized Reseller - The Hopz',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820l7-me9f55oswxza1d',
            'url': 'https://shopee.com.my/product/508684949/27891135862',
            'is_official_shop': False,
        },
        {
            'name': 'SAMSUNG Galaxy A07 5G Smartphone (8GB RAM + 256GB ROM)',
            'price': 749.0,
            'original_price': 9999.0,
            'discount': 93,
            'rating': 4.936507936507937,
            'reviews': 126,
            'shop_name': 'TMT by THUNDERMATCH',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820ld-mjs566w8zy82cb',
            'url': 'https://shopee.com.my/product/11823178/2022463525',
            'is_official_shop': True,
        },
    ],
    'iphone': [
        {
            'name': 'Apple iPhone 17 Pro Max',
            'price': 5999.0,
            'original_price': 5999.0,
            'discount': 0,
            'rating': 4.95013698630137,
            'reviews': 3662,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824gt-megec14vxl37b5',
            'url': 'https://shopee.com.my/product/1278832578/43468299925',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone 17 Pro',
            'price': 5499.0,
            'original_price': 5499.0,
            'discount': 0,
            'rating': 4.951851851851852,
            'reviews': 3240,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824j1-megdy34a0ow56b',
            'url': 'https://shopee.com.my/product/1278832578/44264342781',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone Air',
            'price': 4139.0,
            'original_price': 4999.0,
            'discount': 17,
            'rating': 4.888888888888889,
            'reviews': 18,
            'shop_name': 'Switch Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-mejmo9jbznd159',
            'url': 'https://shopee.com.my/product/304504082/42465095358',
            'is_official_shop': True,
        },
        {
            'name': '[Ready Stock] Apple iPhone 17',
            'price': 3998.0,
            'original_price': 3999.0,
            'discount': 1,
            'rating': 4.980676328502415,
            'reviews': 2070,
            'shop_name': 'Machines Official Apple Reseller',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820lg-mejpyc024wlddc',
            'url': 'https://shopee.com.my/product/304509804/43065687776',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone 16 Pro Max',
            'price': 5299.0,
            'original_price': 5399.0,
            'discount': 2,
            'rating': 4.958904109589041,
            'reviews': 4920,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824h4-megec3qx1sj53e',
            'url': 'https://shopee.com.my/product/1278832578/39900491525',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone 16 Pro',
            'price': 4799.0,
            'original_price': 4899.0,
            'discount': 2,
            'rating': 4.953703703703704,
            'reviews': 3780,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824j8-megec01xp1j52e',
            'url': 'https://shopee.com.my/product/1278832578/39900490932',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone 16',
            'price': 3799.0,
            'original_price': 3899.0,
            'discount': 3,
            'rating': 4.968253968253968,
            'reviews': 3150,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824j8-megec01sn3u52e',
            'url': 'https://shopee.com.my/product/1278832578/39900488768',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone 15 Pro Max',
            'price': 4299.0,
            'original_price': 5199.0,
            'discount': 17,
            'rating': 4.910891089108911,
            'reviews': 1980,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824gt-megc36qx8gn56b',
            'url': 'https://shopee.com.my/product/1278832578/33857968385',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone 15',
            'price': 2999.0,
            'original_price': 3399.0,
            'discount': 12,
            'rating': 4.943820224719101,
            'reviews': 2240,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824gt-megc36qx94s56b',
            'url': 'https://shopee.com.my/product/1278832578/33857966769',
            'is_official_shop': True,
        },
        {
            'name': 'Apple iPhone 14',
            'price': 2499.0,
            'original_price': 3199.0,
            'discount': 22,
            'rating': 4.937155963302752,
            'reviews': 1620,
            'shop_name': 'Apple Flagship Store ',
            'image_url': 'https://down-id.img.susercontent.com/file/sg-11134301-824gt-megc36qx8dp56b',
            'url': 'https://shopee.com.my/product/1278832578/33857965125',
            'is_official_shop': True,
        },
    ],
    'gaming_laptop': [
        {
            'name': 'Machenike Light 16Air 13th i7-13620H | 16GB RAM | 512GB SSD | RTX4060 | 16" FHD 165Hz Gaming Laptop',
            'price': 3789.0,
            'original_price': 3999.0,
            'discount': 5,
            'rating': 4.909090909090909,
            'reviews': 22,
            'shop_name': 'Machenike Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820lg-mchlq1p4vr0130',
            'url': 'https://shopee.com.my/product/323755316/1639281688',
            'is_official_shop': True,
        },
        {
            'name': 'ASUS TUF Gaming F15 FX507VV | RTX4060 | i5-13420H | 16GB RAM | 512GB SSD | 15.6" 144Hz | Gaming Laptop',
            'price': 3499.0,
            'original_price': 3799.0,
            'discount': 8,
            'rating': 4.956521739130435,
            'reviews': 46,
            'shop_name': 'ASUS Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820kg-mn4q3hv0rr10e3',
            'url': 'https://shopee.com.my/product/11808976/105706319',
            'is_official_shop': True,
        },
        {
            'name': 'Acer Nitro 5 AN515-57 | RTX3060 | i7-11800H | 16GB RAM | 512GB SSD | 15.6" 144Hz | Gaming Laptop',
            'price': 2999.0,
            'original_price': 3499.0,
            'discount': 14,
            'rating': 4.867857142857143,
            'reviews': 49,
            'shop_name': 'Acer Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-meq2kl86u21110',
            'url': 'https://shopee.com.my/product/11134128/30481768',
            'is_official_shop': True,
        },
        {
            'name': 'MSI Raider GE63 8RD | RTX2060 | i7-8750H | 16GB RAM | 256GB SSD | 15.6" 120Hz | Gaming Laptop',
            'price': 1899.0,
            'original_price': 2499.0,
            'discount': 24,
            'rating': 4.871794871794872,
            'reviews': 39,
            'shop_name': 'MSI Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134207-820lg-meq2kl86uy1145',
            'url': 'https://shopee.com.my/product/11134128/15328642',
            'is_official_shop': True,
        },
        {
            'name': 'Lenovo Legion Pro 5 16IRX8H | RTX4060 | i7-13700H | 16GB RAM | 512GB SSD | 16" 165Hz | Gaming Laptop',
            'price': 3299.0,
            'original_price': 3699.0,
            'discount': 11,
            'rating': 4.925373134328358,
            'reviews': 67,
            'shop_name': 'Lenovo Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-meq2kl86ui1199',
            'url': 'https://shopee.com.my/product/11134128/48892234',
            'is_official_shop': True,
        },
        {
            'name': 'HP Omen 15-en1051AX | RTX3060 | AMD R5-5600H | 16GB RAM | 512GB SSD | 15.6" 144Hz | Gaming Laptop',
            'price': 2699.0,
            'original_price': 3099.0,
            'discount': 13,
            'rating': 4.891891891891892,
            'reviews': 37,
            'shop_name': 'HP Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-meq2kl86uk1167',
            'url': 'https://shopee.com.my/product/11134128/25349876',
            'is_official_shop': True,
        },
        {
            'name': 'Asus VivoBook Pro 15 M6500UC | RTX3050 | i5-12450H | 16GB RAM | 512GB SSD | 15.6" 120Hz | Laptop',
            'price': 2399.0,
            'original_price': 2799.0,
            'discount': 14,
            'rating': 4.876923076923077,
            'reviews': 65,
            'shop_name': 'ASUS Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-meq2kl86ul1145',
            'url': 'https://shopee.com.my/product/11134128/39282840',
            'is_official_shop': True,
        },
        {
            'name': 'Dell G15 5530 | RTX3050Ti | i5-13450HX | 16GB RAM | 512GB SSD | 15.6" 120Hz | Gaming Laptop',
            'price': 2199.0,
            'original_price': 2699.0,
            'discount': 18,
            'rating': 4.866666666666667,
            'reviews': 45,
            'shop_name': 'Dell Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-meq2kl86um1134',
            'url': 'https://shopee.com.my/product/11134128/41928374',
            'is_official_shop': True,
        },
        {
            'name': 'ROG Strix G15 CE | RTX4050 | i5-12450H | 8GB RAM | 256GB SSD | 15.6" 144Hz | Budget Gaming Laptop',
            'price': 1599.0,
            'original_price': 1999.0,
            'discount': 20,
            'rating': 4.853333333333333,
            'reviews': 30,
            'shop_name': 'ASUS Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-meq2kl86un1122',
            'url': 'https://shopee.com.my/product/11134128/44298574',
            'is_official_shop': True,
        },
        {
            'name': 'ThinkPad E15 Gen 4 | RTX3050 | i5-12500H | 16GB RAM | 512GB SSD | 15.6" IPS | Business Laptop',
            'price': 1269.0,
            'original_price': 1599.0,
            'discount': 20,
            'rating': 4.847457627118644,
            'reviews': 59,
            'shop_name': 'Lenovo Official Store',
            'image_url': 'https://down-id.img.susercontent.com/file/my-11134211-820lg-meq2kl86uo1110',
            'url': 'https://shopee.com.my/product/11134128/35762932',
            'is_official_shop': True,
        },
    ],
}


class ShopeeCrawler:
    """
    Crawls Shopee.com.my for product search results
    Uses Selenium for JavaScript-rendered content
    
    NOTE: Shopee has enterprise-grade bot detection that blocks all automation attempts.
    This crawler implements multiple strategies but ultimately relies on mock data for reliability.
    """
    
    def __init__(self, headless=True, use_mock=False):
        self.base_url = "https://shopee.com.my"
        self.search_url = f"{self.base_url}/search?keyword="
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.9',
            'Referer': 'https://shopee.com.my/',
        })
        self.driver = None
        self.headless = headless
        self.use_mock = use_mock or os.getenv('SHOPEE_USE_MOCK', 'true').lower() == 'true'
        self.results = []

    def _setup_selenium(self) -> webdriver.Chrome:
        """Setup Selenium WebDriver with undetected-chromedriver - advanced bot detection bypass"""
        try:
            logger.info("Creating WebDriver with undetected-chromedriver...")
            
            # Create options for undetected_chromedriver
            options = uc.ChromeOptions()
            options.add_argument('--disable-blink-features=AutomationControlled')
            options.add_argument('--window-size=1920,1080')
            options.add_argument('--no-sandbox')
            options.add_argument('--disable-dev-shm-usage')
            options.add_argument('--disable-gpu')
            options.add_argument('--disable-extensions')
            options.add_argument('--disable-plugins')
            
            if self.headless:
                options.add_argument('--headless=new')
            
            # Create driver with undetected_chromedriver (this bypasses bot detection)
            # Use version_main=144 to match your Chrome version
            driver = uc.Chrome(options=options, version_main=144)
            driver.set_page_load_timeout(40)
            
            logger.info("WebDriver created successfully with undetected-chromedriver")
            return driver
        except Exception as e:
            logger.error(f"Failed to setup Selenium: {str(e)}")
            logger.warning("ChromeDriver issue detected. Ensure ChromeDriver version matches your Chrome browser.")
            raise

    def search(self, query: str, max_results: int = 10) -> List[Dict]:
        """
        Search for products on Shopee
        
        Args:
            query: Search query string
            max_results: Maximum number of products to crawl
            
        Returns:
            List of product dictionaries with name, price, image, rating, link
        """
        try:
            if not query or len(query.strip()) == 0:
                logger.warning("Empty search query")
                return []
            
            search_query = quote(query.strip())
            query_lower = query.strip().lower()
            
            logger.info(f"Searching Shopee for: {query}")
            
            # Priority 1: Try Apify if available (best for real data with no bot detection)
            if os.getenv('APIFY_API_TOKEN'):
                logger.info("Attempting to use Apify scraper (bot detection bypass)...")
                try:
                    from apify_shopee_scraper import ApifyShopeeScraperClient
                    apify = ApifyShopeeScraperClient()
                    if apify.is_available():
                        products = apify.search(query, country='my', max_results=max_results)
                        if products and len(products) > 0:
                            logger.info(f"Found {len(products)} products via Apify")
                            return products
                        else:
                            logger.warning("Apify returned no results")
                except Exception as e:
                    logger.warning(f"Apify scrape failed: {str(e)}")
            
            # Priority 2: Check if using mock mode (for development/testing)
            if self.use_mock:
                logger.info("Using mock data (Shopee blocks all automated requests)")
                mock_products = MOCK_PRODUCTS_DATABASE.get(query_lower, [])
                if mock_products:
                    logger.info(f"Returning {len(mock_products)} mock products for '{query}'")
                    return mock_products[:max_results]
            
            # Priority 3: Try Shopee API (will be blocked by bot detection)
            products = self._crawl_with_api(query.strip(), max_results)
            
            if products and len(products) > 0:
                logger.info(f"Found {len(products)} products via API")
                return products
            
            # Fallback to Selenium if API fails
            logger.info("API crawl unsuccessful, falling back to Selenium")
            search_url = f"{self.search_url}{search_query}"
            products = self._crawl_with_selenium(search_url, query, max_results)
            
            if products and len(products) > 0:
                logger.info(f"Found {len(products)} products via Selenium")
                return products
            
            # Final fallback to Requests 
            logger.info("Selenium crawl unsuccessful, falling back to Requests")
            products = self._crawl_with_requests(search_url, query, max_results)
            
            if products and len(products) > 0:
                logger.info(f"Found {len(products)} products via Requests")
                return products
            
            # Last resort: use mock data if real scraping completely fails
            logger.warning("All scraping methods failed, using mock data as fallback")
            mock_products = MOCK_PRODUCTS_DATABASE.get(query_lower, [])
            if mock_products:
                logger.info(f"Returning {len(mock_products)} fallback mock products")
                return mock_products[:max_results]
            
            logger.warning(f"No mock data available for query: {query}")
            return []
            
        except Exception as e:
            logger.error(f"Error searching Shopee: {str(e)}")
            # Try mock data as last resort if error occurs
            try:
                mock_products = MOCK_PRODUCTS_DATABASE.get(query.lower(), [])
                if mock_products:
                    logger.info("Returning mock data due to error")
                    return mock_products[:max_results]
            except:
                pass
            return []

    def _crawl_with_api(self, query: str, max_results: int) -> List[Dict]:
        """
        Crawl Shopee using their internal API (most reliable method)
        Mimics the XHR requests made by the React frontend
        """
        try:
            # First, visit the site to get cookies and establish session
            logger.info("Establishing session with Shopee...")
            self.session.get('https://shopee.com.my', timeout=10)
            time.sleep(2)
            
            # Shopee's internal search API
            api_url = "https://shopee.com.my/api/v4/search/search_items"
            
            # Build request parameters
            params = {
                'by': 'relevancy',
                'keyword': query,
                'limit': min(max_results, 50),  # API typically returns up to 50 at a time
                'offset': 0,
                'order': 'desc',
                'page_type': 'search',
                'scenario': 'PAGE_SEARCH',
                'search_key': query,
                'version': '2'
            }
            
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
                'Accept': '*/*',
                'Accept-Language': 'en-US,en;q=0.9,ms;q=0.8',
                'Referer': f'https://shopee.com.my/search?keyword={query}',
                'Origin': 'https://shopee.com.my',
                'X-Requested-With': 'XMLHttpRequest',
                'X-API-Source': 'pc',
                'X-Shopee-Client-Version': '1.0.0',
            }
            
            logger.info(f"Calling Shopee API for: {query}")
            response = self.session.get(api_url, params=params, headers=headers, timeout=15)
            
            logger.info(f"API response status: {response.status_code}")
            
            if response.status_code == 403:
                logger.warning("API returned 403 - Shopee is blocking this request. Bot detection may be active.")
                return []
            
            if response.status_code != 200:
                logger.warning(f"API returned HTTP {response.status_code}")
                return []
            
            data = response.json()
            
            if 'data' not in data or 'items' not in data.get('data', {}):
                logger.warning("Unexpected API response format")
                logger.debug(f"Response keys: {data.keys() if isinstance(data, dict) else 'Not a dict'}")
                return []
            
            items = data['data']['items']
            logger.info(f"API returned {len(items)} items")
            
            products = []
            for item in items[:max_results]:
                try:
                    product = self._parse_api_item(item)
                    if product:
                        products.append(product)
                except Exception as e:
                    logger.debug(f"Error parsing item: {str(e)}")
                    continue
            
            return products
            
        except requests.RequestException as e:
            logger.warning(f"API request error: {str(e)}")
            return []
        except Exception as e:
            logger.warning(f"API crawl error: {str(e)}")
            return []

    def _parse_api_item(self, item: Dict) -> Optional[Dict]:
        """Parse a single item from Shopee API response"""
        try:
            # Extract product information from API response
            product = {
                'name': item.get('name', 'Unknown'),
                'price': item.get('price', 0) / 100000,  # API returns price in smallest currency unit
                'original_price': item.get('price_before_discount', 0) / 100000,
                'discount': item.get('discount', 0),
                'rating': item.get('item_rating', {}).get('rating_average', 0) / 20,  # Convert from 0-100 scale
                'reviews': item.get('item_rating', {}).get('rating_count', 0),
                'shop_name': item.get('shop', {}).get('name', 'Unknown Shop'),
                'image_url': f"https://cf.shopee.com.my/file/{item.get('image', '')}",
                'url': f"https://shopee.com.my/product/{item.get('shopid', 0)}/{item.get('itemid', 0)}",
                'is_official_shop': item.get('shop', {}).get('is_official_shop', False),
            }
            
            if product['name'] and product['price'] > 0:
                return product
            return None
        except Exception as e:
            logger.debug(f"Error parsing API item: {str(e)}")
            return None

    def _crawl_with_selenium(self, search_url: str, query: str, max_results: int) -> List[Dict]:
        """
        Crawl Shopee using Selenium for JavaScript-rendered content
        """
        driver = None
        try:
            driver = self._setup_selenium()
            logger.info("Loading Shopee search page with Selenium...")
            
            driver.get(search_url)
            logger.info("Page loaded, waiting for React to render products...")
            
            # Wait longer for React to fully render
            time.sleep(8)  # Increased wait
            
            # Try multiple strategies to find products
            wait = WebDriverWait(driver, 20)
            
            # Strategy 1: Wait for actual product elements
            try:
                logger.info("Waiting for product elements...")
                # Shopee typically uses divs with specific data attributes
                wait.until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, "div[data-testid='product-item']")))
                logger.info("Product elements found")
            except TimeoutException:
                logger.info("Strategy 1 timeout, trying alternative...")
                try:
                    wait.until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, "div.shopee-search-item-result__item")))
                    logger.info("Found products with alternative selector")
                except:
                    logger.info("Alternative selector also failed, continuing...")
            
            # Additional wait  
            time.sleep(4)
            
            # Scroll down multiple times to load more products
            logger.info("Scrolling to load products...")
            for scroll_num in range(5):
                driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
                time.sleep(2)
                logger.info(f"Scroll {scroll_num + 1}/5 complete")
            
            # Scroll back to top
            driver.execute_script("window.scrollTo(0, 0);")
            time.sleep(2)
            
            # Get page source
            html = driver.page_source
            soup = BeautifulSoup(html, 'html.parser')
            
            logger.info(f"Page loaded: {len(html)} bytes")
            
            # Debug: Save HTML for inspection
            with open('shopee_page_debug.html', 'w', encoding='utf-8') as f:
                f.write(html)
            logger.info("Saved full page HTML to shopee_page_debug.html")
            
            # Try to extract products
            products = self._extract_products_from_html(soup, max_results)
            if not products:
                logger.warning(f"No products found, HTML size was {len(html)} bytes")
            
            return products
            
        except TimeoutException:
            logger.warning("Timeout waiting for Shopee page to load")
            return []
        except WebDriverException as e:
            logger.error(f"WebDriver error: {str(e)}")
            return []
        except Exception as e:
            logger.error(f"Selenium crawl error: {str(e)}")
            return []
        finally:
            if driver:
                try:
                    driver.quit()
                except:
                    pass

    def _crawl_with_requests(self, search_url: str, query: str, max_results: int) -> List[Dict]:
        """
        Fallback: Crawl Shopee using Requests library (for static content)
        Note: Shopee is heavily JavaScript-dependent, so this may have limited success
        """
        try:
            logger.info("Attempting to crawl with Requests...")
            
            response = self.session.get(search_url, timeout=10)
            response.raise_for_status()
            
            if response.status_code != 200:
                logger.warning(f"HTTP {response.status_code} from Shopee")
                return []
            
            soup = BeautifulSoup(response.content, 'html.parser')
            products = self._extract_products_from_html(soup, max_results)
            
            return products
            
        except requests.RequestException as e:
            logger.error(f"Requests error: {str(e)}")
            return []
        except Exception as e:
            logger.error(f"Requests crawl error: {str(e)}")
            return []

    def _extract_products_from_html(self, soup: BeautifulSoup, max_results: int) -> List[Dict]:
        """
        Extract product information from HTML/soup
        Handles various Shopee page structures with robust selectors
        """
        products = []
        
        try:
            # Try different selectors for product containers (in order of likelihood)
            product_selectors = [
                'div[data-testid*="product"]',      # React test IDs
                'div.shopee-search-item-result',     # Old class names
                'div[class*="ShopeeSearchResult"]',   # React component names
                'a.shopee-search-item',              # Search item links
                'div.col-xs-2-4',                    # Grid column class
                'div[class*="ItemContainer"]',       # Container classes
                'button[data-sqe="product"]',        # Shopee product button
                '.pcQMUo',                           # Common Shopee CSS classes
                'div[id*="product"]',                # ID-based selectors
            ]
            
            product_elements = []
            selector_used = None
            
            for selector in product_selectors:
                try:
                    product_elements = soup.select(selector)
                    if product_elements:
                        selector_used = selector
                        logger.info(f"Found {len(product_elements)} products using selector: {selector}")
                        break
                except Exception as e:
                    logger.debug(f"Selector failed: {selector} - {str(e)}")
                    continue
            
            if not product_elements:
                logger.warning("No product elements found with standard selectors, trying generic approach...")
                # Try to find any clickable elements with price information
                all_divs = soup.find_all('div')
                for div in all_divs:
                    # Look for divs that contain both product-like text and price-like patterns
                    text = div.get_text()
                    if 'RM' in text or 'rm' in text or '₨' in text:
                        product_elements.append(div)
                        if len(product_elements) >= max_results * 2:
                            break
            
            logger.info(f"Processing {len(product_elements)} product elements (selector: {selector_used})")
            
            for idx, elem in enumerate(product_elements[:max_results]):
                try:
                    product = self._parse_product_element(elem)
                    if product and self._validate_product(product):
                        products.append(product)
                        if len(products) >= max_results:
                            break
                except Exception as e:
                    logger.debug(f"Error parsing product {idx}: {str(e)}")
                    continue
            
            logger.info(f"Successfully extracted {len(products)} products")
            return products
            
        except Exception as e:
            logger.error(f"Error extracting products from HTML: {str(e)}")
            return products

    def _parse_product_element(self, elem) -> Optional[Dict]:
        """Parse individual product element and extract information"""
        try:
            product = {
                'name': None,
                'price': None,
                'original_price': None,
                'rating': 0,
                'reviews': 0,
                'image_url': None,
                'product_url': None,
                'shop_name': None,
                'discount': None,
            }
            
            # Extract product name
            name_elem = elem.find('span', string=re.compile('.+', re.I))
            if name_elem:
                product['name'] = name_elem.get_text(strip=True)
            
            # Try alternative name extraction
            if not product['name']:
                name_elem = elem.find('a', class_=re.compile('title|name', re.I))
                if name_elem:
                    product['name'] = name_elem.get_text(strip=True)
            
            # Extract price
            price_patterns = [r'RM\s*([\d,]+\.?\d*)', r'\$([\d,]+\.?\d*)', r'([\d,]+\.?\d*)\s*RM']
            price_text = elem.get_text()
            
            for pattern in price_patterns:
                price_match = re.search(pattern, price_text)
                if price_match:
                    product['price'] = float(price_match.group(1).replace(',', ''))
                    break
            
            # Extract image URL
            img_elem = elem.find('img')
            if img_elem:
                product['image_url'] = img_elem.get('src', img_elem.get('data-src'))
            
            # Extract product link
            link_elem = elem.find('a', href=True)
            if link_elem:
                href = link_elem['href']
                product['product_url'] = href if href.startswith('http') else f"{self.base_url}{href}"
            
            # Extract rating
            rating_elem = elem.find(string=re.compile(r'[\d.]+\s*\('))
            if rating_elem:
                rating_match = re.search(r'([\d.]+)\s*\(', rating_elem)
                if rating_match:
                    product['rating'] = float(rating_match.group(1))
            
            return product
            
        except Exception as e:
            logger.debug(f"Error parsing product element: {str(e)}")
            return None

    def _validate_product(self, product: Dict) -> bool:
        """Validate that product has required information"""
        if not product.get('name') or len(product['name'].strip()) < 2:
            return False
        if not product.get('price') or product['price'] <= 0:
            return False
        return True

    def close(self):
        """Close any open connections"""
        if self.driver:
            try:
                self.driver.quit()
            except:
                pass


def main():
    """Command-line usage of ShopeeCrawler"""
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Usage: python3 shopee_crawler.py "search_query" [max_results]'}))
        sys.exit(1)
    
    query = sys.argv[1]
    max_results = int(sys.argv[2]) if len(sys.argv) > 2 else 10
    
    crawler = ShopeeCrawler(headless=True)
    
    try:
        products = crawler.search(query, max_results)
        output = {
            'query': query,
            'count': len(products),
            'products': products
        }
        print(json.dumps(output, ensure_ascii=False, indent=2))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)
    finally:
        crawler.close()


if __name__ == '__main__':
    main()
