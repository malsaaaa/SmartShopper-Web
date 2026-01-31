#!/usr/bin/env python3
"""
Debug script to inspect product images on Mydin and Lotus websites
"""

import sys
import os

# Fix Windows encoding issues
if sys.platform == 'win32':
    os.environ['PYTHONIOENCODING'] = 'utf-8'
    sys.stdout.reconfigure(encoding='utf-8')

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from bs4 import BeautifulSoup
import json

# Setup Chrome options
options = Options()
options.add_argument('--headless')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')

def inspect_website(url, name):
    """Inspect website to find real image URLs"""
    print(f"\n{'='*60}")
    print(f"Inspecting {name}: {url}")
    print('='*60)
    
    driver = webdriver.Chrome(options=options)
    try:
        driver.get(url)
        print("[OK] Page loaded")
        
        # Wait for content
        WebDriverWait(driver, 10).until(
            lambda d: d.find_elements(By.TAG_NAME, "img")
        )
        
        # Scroll to load images
        for _ in range(3):
            driver.execute_script("window.scrollBy(0, window.innerHeight);")
        
        soup = BeautifulSoup(driver.page_source, 'html.parser')
        
        # Find all images
        images = soup.find_all('img', limit=20)
        print(f"Found {len(images)} images on page\n")
        
        for i, img in enumerate(images, 1):
            src = img.get('src', '')
            data_src = img.get('data-src', '')
            alt = img.get('alt', '')
            
            print(f"{i}. Image:")
            if alt:
                print(f"   Alt: {alt[:80]}")
            if src:
                print(f"   Src: {src[:100]}")
            if data_src:
                print(f"   Data-src: {data_src[:100]}")
            print()
        
        # Find product containers
        print("\nLooking for product containers...")
        selectors = [
            '[class*="product"]',
            '.product-item',
            '[data-product-id]',
            '[role="link"]'
        ]
        
        for selector in selectors:
            products = soup.select(selector)
            if products:
                print(f"[OK] Found {len(products)} with selector: {selector}")
                
                # Show first product structure
                if products:
                    print(f"\nFirst product HTML snippet:")
                    print(str(products[0])[:500])
                break
        
    except Exception as e:
        print(f"[ERROR] {e}")
    finally:
        driver.quit()

if __name__ == "__main__":
    inspect_website('https://mydin.my/search?search=Milo', 'Mydin')
    inspect_website('https://www.lotuss.com.my/en/search/Milo?sort=relevance:DESC', "Lotus's")
