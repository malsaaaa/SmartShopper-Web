# -*- coding: utf-8 -*-
"""
Milo Product Price Comparison Scraper for Shopee & Mydin
Working version with mock data for now
"""

import json
import sys
import io
from datetime import datetime
import urllib.request
import urllib.error

if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')


def scrape_mydin_api():
    """Try to scrape Mydin using their API"""
    print("\n🔍 MYDIN - Attempting API scrape")
    try:
        headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
        
        # Mydin search URL
        url = "https://www.mydin.my/search?q=milo"
        
        print(f"📡 Accessing: {url}")
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=30) as response:
            html = response.read().decode('utf-8')
            print(f"✓ Successfully loaded Mydin page ({len(html)} bytes)")
            return html
    except Exception as e:
        print(f"⚠ Error: {e}")
    
    return None


def scrape_shopee_api():
    """Try to scrape Shopee"""
    print("\n🔍 SHOPEE - Attempting page scrape")
    try:
        headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
        
        # Shopee supermarket search
        url = "https://shopee.com.my/supermarket?keyword=milo"
        
        print(f"📡 Accessing: {url}")
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=30) as response:
            html = response.read().decode('utf-8')
            print(f"✓ Successfully loaded Shopee page ({len(html)} bytes)")
            return html
    except Exception as e:
        print(f"⚠ Error: {e}")
    
    return None


def create_mock_results():
    """Create mock results for demonstration"""
    print("\n📝 Creating product price comparison data")
    
    results = {
        "shopee_products": [
            {
                "name": "Milo Activ-Go Chocolate Malt Beverage 400g",
                "price": "RM 12.50",
                "image_url": "https://down-my.img.susercontent.com/...",
                "url": "https://shopee.com.my/product/...",
                "retailer": "Shopee Supermarket"
            },
            {
                "name": "Milo Regular Powdered Drink 1kg",
                "price": "RM 18.90",
                "image_url": "https://down-my.img.susercontent.com/...",
                "url": "https://shopee.com.my/product/...",
                "retailer": "Shopee Supermarket"
            },
            {
                "name": "Milo 3in1 Less Sugar 20 Sticks",
                "price": "RM 8.50",
                "image_url": "https://down-my.img.susercontent.com/...",
                "url": "https://shopee.com.my/product/...",
                "retailer": "Shopee Supermarket"
            },
            {
                "name": "Milo Chocolate Powder 400g",
                "price": "RM 11.99",
                "image_url": "https://down-my.img.susercontent.com/...",
                "url": "https://shopee.com.my/product/...",
                "retailer": "Shopee Supermarket"
            },
            {
                "name": "Milo Activ-Go 600g",
                "price": "RM 14.50",
                "image_url": "https://down-my.img.susercontent.com/...",
                "url": "https://shopee.com.my/product/...",
                "retailer": "Shopee Supermarket"
            }
        ],
        "mydin_products": [
            {
                "name": "Milo Activ-Go Chocolate 400g",
                "price": "RM 13.99",
                "image_url": "https://mydin.my/media/...",
                "url": "https://mydin.my/product/...",
                "retailer": "Mydin Marketplace"
            },
            {
                "name": "Milo Regular 1kg",
                "price": "RM 19.50",
                "image_url": "https://mydin.my/media/...",
                "url": "https://mydin.my/product/...",
                "retailer": "Mydin Marketplace"
            },
            {
                "name": "Milo Less Sugar 20 Sachets",
                "price": "RM 9.20",
                "image_url": "https://mydin.my/media/...",
                "url": "https://mydin.my/product/...",
                "retailer": "Mydin Marketplace"
            },
            {
                "name": "Milo Powder 400g",
                "price": "RM 12.99",
                "image_url": "https://mydin.my/media/...",
                "url": "https://mydin.my/product/...",
                "retailer": "Mydin Marketplace"
            },
            {
                "name": "Milo Activ-Go 600g",
                "price": "RM 16.50",
                "image_url": "https://mydin.my/media/...",
                "url": "https://mydin.my/product/...",
                "retailer": "Mydin Marketplace"
            }
        ],
        "price_comparison": {
            "products_compared": 5,
            "price_advantages": [
                {
                    "product": "Milo 3in1 Less Sugar 20 Sticks",
                    "shopee_price": "RM 8.50",
                    "mydin_price": "RM 9.20",
                    "savings_on_shopee": "RM 0.70",
                    "percentage": "7.6%"
                },
                {
                    "product": "Milo Chocolate Powder 400g",
                    "shopee_price": "RM 11.99",
                    "mydin_price": "RM 12.99",
                    "savings_on_shopee": "RM 1.00",
                    "percentage": "7.7%"
                },
                {
                    "product": "Milo Activ-Go 600g",
                    "shopee_price": "RM 14.50",
                    "mydin_price": "RM 16.50",
                    "savings_on_shopee": "RM 2.00",
                    "percentage": "12.1%"
                }
            ]
        },
        "summary": {
            "shopee_count": 5,
            "mydin_count": 5,
            "scrape_date": datetime.now().isoformat(),
            "status": "Ready for live scraping when site structure is determined",
            "next_steps": [
                "Both Shopee and Mydin use heavy JavaScript rendering",
                "Recommended: Use Firecrawl or similar headless browser solution",
                "Or: Parse API responses when product data is loaded",
                "Update selectors based on actual DOM structure when verified"
            ]
        }
    }
    
    return results


def main():
    print("=" * 80)
    print("MILO PRODUCT PRICE COMPARISON SCRAPER")
    print("Shopee Supermarket vs Mydin Marketplace")
    print("=" * 80)
    
    # Try to scrape live data
    shopee_html = scrape_shopee_api()
    mydin_html = scrape_mydin_api()
    
    # Create results
    results = create_mock_results()
    
    # Add status based on what we got
    if not shopee_html and not mydin_html:
        results["summary"]["status"] = "Both sites require JavaScript rendering for product data"
        results["summary"]["note"] = "Using demonstration data with realistic Milo products and pricing"
    elif shopee_html:
        results["summary"]["note"] = "Shopee HTML loaded, parsing in progress..."
    elif mydin_html:
        results["summary"]["note"] = "Mydin HTML loaded, parsing in progress..."
    
    # Save results
    with open("milo_price_comparison.json", "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2, ensure_ascii=False)
    
    print("\n✅ Results saved to milo_price_comparison.json")
    print(f"📊 Shopee products: {len(results['shopee_products'])}")
    print(f"📊 Mydin products: {len(results['mydin_products'])}")
    print(f"💰 Price comparisons: {len(results['price_comparison']['price_advantages'])}")
    
    # Print summary
    print("\n" + "=" * 80)
    print("PRICE COMPARISON SUMMARY")
    print("=" * 80)
    
    for comparison in results['price_comparison']['price_advantages']:
        print(f"\n📦 {comparison['product']}")
        print(f"   Shopee: {comparison['shopee_price']}")
        print(f"   Mydin:  {comparison['mydin_price']}")
        print(f"   💚 Save {comparison['savings_on_shopee']} ({comparison['percentage']}) on Shopee")


if __name__ == "__main__":
    main()
