# Shopee Firecrawl Scraper - Usage Guide

## Overview

This guide explains how to use the Firecrawl MCP-based scraper to extract product information from Shopee Malaysia (https://shopee.com.my/).

**What it scrapes:**
- Product names
- Product prices
- Product images
- Product links
- Limited to 10 products per request

## Prerequisites

1. **Python 3.9+** installed
2. **Firecrawl API Key** from https://firecrawl.dev
3. **Internet connection**
4. All dependencies installed

## Setup (One-Time)

### Step 1: Get Firecrawl API Key

1. Visit https://firecrawl.dev
2. Sign up for an account
3. Generate an API key from your dashboard
4. Copy the key (something like: `sk_live_...`)

### Step 2: Configure .env

Edit `.env` file in the SmartShopper directory:

```env
FIRECRAWL_API_KEY=your_api_key_here
```

Replace `your_api_key_here` with your actual API key from Firecrawl.

### Step 3: Install Dependencies

```bash
pip install -r requirements.txt
python -m playwright install
```

### Step 4: Verify Setup

```bash
python check_shopee_setup.py
```

This will check:
- ✅ Python version
- ✅ .env configuration
- ✅ Dependencies installed
- ✅ Required files present
- ✅ Internet connectivity

## Usage

### Basic Usage - Scrape Homepage

```bash
python scrapers/shopee_firecrawl_scraper.py
```

This will:
1. Crawl https://shopee.com.my/
2. Extract product information (name, price, image)
3. Limit results to 10 products
4. Save results to `shopee_products.json`

### Output Example

The script will display:
```
Product #1
  Name: Example Product Name
  Price: RM 19.99
  Image: https://cf.shopee.com.my/...
  Link: https://shopee.com.my/product/...

Product #2
  ...
```

And save results to `shopee_products.json`:
```json
[
  {
    "name": "Product Name",
    "price": "19.99",
    "image_url": "https://cf.shopee.com.my/...",
    "link": "https://shopee.com.my/product/..."
  },
  ...
]
```

## Advanced Usage

### Using in Python Code

```python
import asyncio
from scrapers.shopee_firecrawl_scraper import ShopeeScraper

async def main():
    scraper = ShopeeScraper()
    
    # Scrape homepage
    products = await scraper.scrape_shopee_homepage()
    
    # Or search for specific products
    products = await scraper.scrape_shopee_search(
        query="laptop",
        limit=10
    )
    
    for product in products:
        print(f"{product['name']} - RM {product['price']}")

asyncio.run(main())
```

### Using in Laravel

```php
use App\Services\FirecrawlService;

$service = new FirecrawlService();
$result = $service->crawlProductPage('https://shopee.com.my/');

// Extract product data
$products = json_decode($result, true);
```

## Output Files

### shopee_products.json

Contains the scraped product data in JSON format:

```json
[
  {
    "name": "Product Name",
    "price": "99.99",
    "image_url": "https://...",
    "link": "https://shopee.com.my/..."
  }
]
```

## Troubleshooting

### Error: "API Key missing"

**Solution:** Make sure `FIRECRAWL_API_KEY` is set in your `.env` file.

```bash
# Check if .env exists
cat .env

# Should see: FIRECRAWL_API_KEY=your_key_here
```

### Error: "Module not found"

**Solution:** Install dependencies:

```bash
pip install -r requirements.txt
```

### Error: "Connection refused"

**Solution:** Make sure Firecrawl API is accessible:

1. Check internet connection
2. Verify API key is valid at https://firecrawl.dev
3. Try again

### Error: "Cannot reach shopee.com.my"

**Solution:** Shopee may have anti-scraping measures. Try:

1. Use a different network/VPN
2. Wait a few minutes before trying again
3. Check if Shopee website is down

### No products extracted

**Solution:** HTML structure may have changed. Try:

1. Check browser console: F12 on Shopee website
2. Find new CSS selectors for products
3. Update the selectors in `shopee_firecrawl_scraper.py`

## Rate Limiting

To avoid being rate-limited by Shopee:

- Don't scrape too frequently
- Add delays between requests
- Use different queries to vary requests
- Respect Shopee's robots.txt

Example with delays:

```python
import asyncio
import time

products_list = []
for page in range(5):
    products = await scraper.scrape_shopee_search(limit=10)
    products_list.extend(products)
    
    # Wait 5 seconds before next request
    time.sleep(5)
```

## Limits

- **Maximum products**: 10 per request (configurable in script)
- **Timeout**: 60 seconds per request
- **Daily limits**: Depends on your Firecrawl plan

## Features

✅ Extracts product names  
✅ Extracts prices  
✅ Extracts product images  
✅ Extracts product links  
✅ Handles dynamic content (JavaScript-rendered)  
✅ Automatic error handling  
✅ JSON output format  
✅ Async support for concurrent scraping  

## Performance

- Average time per crawl: 10-30 seconds
- Results saved immediately
- Can handle 10+ products per request
- Optimized for Malaysia Shopee

## Security Notes

⚠️ **Important:**
- Keep your API key secret (don't commit .env to git)
- Don't share your API key publicly
- Use rate limiting to be respectful
- Check Shopee's Terms of Service

## Support

For issues:

1. Check `.env` configuration
2. Run setup verification: `python check_shopee_setup.py`
3. Check Firecrawl status at https://firecrawl.dev
4. Review the troubleshooting section above
5. Check browser console (F12) on Shopee for HTML changes

## Next Steps

1. ✅ Verify setup with `python check_shopee_setup.py`
2. ✅ Run scraper: `python scrapers/shopee_firecrawl_scraper.py`
3. ✅ Check output: `shopee_products.json`
4. ✅ Integrate into your application

## Example Workflow

```bash
# 1. Setup
python check_shopee_setup.py

# 2. Scrape
python scrapers/shopee_firecrawl_scraper.py

# 3. View results
cat shopee_products.json | python -m json.tool
```

---

**Ready to scrape Shopee?**

Run: `python scrapers/shopee_firecrawl_scraper.py`
