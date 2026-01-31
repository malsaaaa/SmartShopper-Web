#!/usr/bin/env python3
"""
Scrape retailer logos from websites
"""

import sys
import os
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from bs4 import BeautifulSoup
import time

# Setup Chrome options
options = Options()
options.add_argument('--headless')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')

retailers = {
    'mydin': {
        'url': 'https://mydin.my/',
        'selectors': ['img[alt*="logo"]', 'img[src*="logo"]', '.navbar img', 'header img']
    },
    'lotus': {
        'url': 'https://www.lotuss.com.my/en',
        'selectors': ['img[alt*="logo"]', 'img[alt*="Lotus"]', 'img[src*="logo"]', '.navbar img']
    },
    'giant': {
        'url': 'https://www.giantmall.com.my',
        'selectors': ['img[alt*="logo"]', 'img[src*="logo"]', '.navbar img']
    }
}

def scrape_logo(retailer_name, config):
    """Scrape logo from retailer website"""
    print(f"\n{'='*60}")
    print(f"Scraping logo for: {retailer_name}")
    print(f"URL: {config['url']}")
    print('='*60)
    
    driver = webdriver.Chrome(options=options)
    try:
        driver.get(config['url'])
        time.sleep(2)
        
        soup = BeautifulSoup(driver.page_source, 'html.parser')
        
        # Try each selector
        for selector in config['selectors']:
            imgs = soup.select(selector)
            if imgs:
                print(f"Found {len(imgs)} images with selector: {selector}")
                
                for i, img in enumerate(imgs[:5], 1):
                    src = img.get('src', '')
                    alt = img.get('alt', '')
                    
                    if src and ('logo' in src.lower() or 'logo' in alt.lower() or 'icon' in src.lower()):
                        # Handle relative URLs
                        if src.startswith('http'):
                            logo_url = src
                        elif src.startswith('/'):
                            logo_url = config['url'].rstrip('/') + src
                        else:
                            logo_url = config['url'].rstrip('/') + '/' + src
                        
                        print(f"  [{i}] Logo: {logo_url[:80]}")
                        if i == 1:
                            return logo_url
                
                break
        
        print("[ERROR] No logo found")
        return None
        
    except Exception as e:
        print(f"[ERROR] {e}")
        return None
    finally:
        driver.quit()

if __name__ == "__main__":
    print("Retailer Logo Scraper")
    print("=" * 60)
    
    logos = {}
    for retailer_name, config in retailers.items():
        logo = scrape_logo(retailer_name, config)
        if logo:
            logos[retailer_name] = logo
    
    print(f"\n{'='*60}")
    print("Summary:")
    print('='*60)
    for retailer_name, logo in logos.items():
        print(f"{retailer_name}: {logo[:80]}")
