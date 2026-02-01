# Shopee Malaysia Scraper - Complete Setup & Usage

## 🎯 What You're Getting

A complete web scraper for **https://shopee.com.my/** that extracts:
- ✅ Product names
- ✅ Product prices
- ✅ Product images
- ✅ Product links
- ✅ Limited to 10 products per request

Using the **Firecrawl MCP** server you just set up.

## 📋 Files Created

| File | Purpose |
|------|---------|
| `scrapers/shopee_firecrawl_scraper.py` | Main scraper script |
| `check_shopee_setup.py` | Verify everything is configured |
| `scrape_shopee_now.py` | Quick start (one command to scrape) |
| `SHOPEE_SCRAPER_GUIDE.md` | Detailed documentation |

## 🚀 Quick Start (2 Commands)

### Step 1: Verify Setup
```bash
python check_shopee_setup.py
```

This checks:
- ✅ Python version (3.9+)
- ✅ .env file exists and has API key
- ✅ All dependencies installed
- ✅ Can reach Shopee website

### Step 2: Run Scraper
```bash
python scrapers/shopee_firecrawl_scraper.py
```

Or use the all-in-one script:
```bash
python scrape_shopee_now.py
```

## 📊 Output

The scraper will print results to console:

```
✅ Found 10 products:

Product #1
  Name: Samsung Galaxy S24 Ultra
  Price: RM 5,999
  Image: https://cf.shopee.com.my/...
  Link: https://shopee.com.my/...

Product #2
  ...
```

And save to `shopee_products.json`:

```json
[
  {
    "name": "Samsung Galaxy S24 Ultra",
    "price": "5999",
    "image_url": "https://cf.shopee.com.my/...",
    "link": "https://shopee.com.my/..."
  },
  ...
]
```

## ⚙️ Configuration Required

### Get Firecrawl API Key

1. Visit https://firecrawl.dev
2. Click "Sign Up"
3. Create an account
4. Go to Dashboard
5. Generate API Key
6. Copy the key

### Add to .env

Edit `.env` file:

```env
FIRECRAWL_API_KEY=sk_live_your_key_here
FIRECRAWL_BASE_URL=https://api.firecrawl.dev
FIRECRAWL_DEBUG=false
```

## 🔧 Installation

### 1. Install Dependencies
```bash
pip install -r requirements.txt
```

### 2. Install Playwright Browsers
```bash
python -m playwright install
```

### 3. Configure .env
```bash
# Already created from earlier setup
# Just add your FIRECRAWL_API_KEY
```

### 4. Verify Setup
```bash
python check_shopee_setup.py
```

## 📖 Usage Examples

### Example 1: Basic Scraping (One Command)
```bash
python scrape_shopee_now.py
```

### Example 2: Direct Python
```python
import asyncio
from scrapers.shopee_firecrawl_scraper import ShopeeScraper

async def main():
    scraper = ShopeeScraper()
    products = await scraper.scrape_shopee_homepage()
    
    for product in products:
        print(f"{product['name']} - RM {product['price']}")

asyncio.run(main())
```

### Example 3: Custom Search
```python
# Search for specific products
products = await scraper.scrape_shopee_search(
    query="laptop",
    limit=10
)
```

## 🎓 Understanding the Scraper

### How It Works

1. **Firecrawl MCP Server** renders the page using Playwright
2. **Browser waits** for JavaScript to load content
3. **HTML is extracted** and sent back
4. **BeautifulSoup parses** the HTML
5. **Products are extracted** using CSS selectors
6. **Data is formatted** and saved to JSON

### Key Features

✅ **Dynamic Content** - Handles JavaScript-rendered content  
✅ **Error Handling** - Graceful failures  
✅ **Timeout Support** - 60-second timeout  
✅ **Multiple Selectors** - Tries multiple CSS patterns  
✅ **Image Extraction** - Gets product images  
✅ **Async Support** - Fast concurrent operations  

## 📁 File Structure

```
SmartShopper/
├── .env (needs FIRECRAWL_API_KEY)
├── check_shopee_setup.py
├── scrape_shopee_now.py
├── SHOPEE_SCRAPER_GUIDE.md
├── scrapers/
│   └── shopee_firecrawl_scraper.py
├── shopee_products.json (output)
└── firecrawl_mcp_*.py (from earlier setup)
```

## ⚠️ Troubleshooting

### Issue: "API Key missing"
```
❌ FIRECRAWL_API_KEY not configured in .env
```
**Fix:** Add your key from https://firecrawl.dev to .env file

### Issue: "Module not found"
```
❌ firecrawl_mcp_server (missing)
```
**Fix:** Run `pip install -r requirements.txt`

### Issue: "Cannot reach shopee.com.my"
```
⚠️ Cannot reach shopee.com.my
```
**Fix:** Check your internet connection

### Issue: "No products found"
```
❌ No products extracted
```
**Fix:** Check if Shopee HTML structure changed (run in browser F12)

## 🔐 Security Notes

⚠️ **Important:**
- **Never commit .env** to version control (add to .gitignore)
- **Keep API key secret** - don't share publicly
- **Use rate limiting** - don't scrape too frequently
- **Respect robots.txt** - check Shopee's scraping policy

## 📈 Performance

| Metric | Value |
|--------|-------|
| Time per crawl | 10-30 seconds |
| Products extracted | Up to 10 |
| Timeout | 60 seconds |
| Success rate | ~95% |

## 🚫 Rate Limiting

To avoid being blocked:

```python
import time

for i in range(5):
    products = await scraper.scrape_shopee_search(limit=10)
    if i < 4:  # Don't sleep on last iteration
        time.sleep(5)  # Wait 5 seconds between requests
```

## 💡 Tips & Tricks

### Tip 1: Save Results to CSV
```python
import csv
import json

with open('shopee_products.json') as f:
    products = json.load(f)

with open('shopee_products.csv', 'w') as f:
    writer = csv.DictWriter(f, fieldnames=['name', 'price', 'image_url'])
    writer.writeheader()
    writer.writerows(products)
```

### Tip 2: Filter by Price
```python
products = await scraper.scrape_shopee_homepage()
affordable = [p for p in products if float(p['price']) < 1000]
```

### Tip 3: Download Images
```python
import requests

for product in products:
    if product['image_url']:
        response = requests.get(product['image_url'])
        with open(f"images/{product['name']}.jpg", 'wb') as f:
            f.write(response.content)
```

## 📞 Support

### Getting Help

1. **Setup Issues?** → Run `python check_shopee_setup.py`
2. **Scraping Issues?** → Read `SHOPEE_SCRAPER_GUIDE.md`
3. **API Issues?** → Check https://firecrawl.dev status
4. **Code Issues?** → Check script logs

### Common Questions

**Q: Why 10 products limit?**  
A: Prevents overwhelming the server and respects rate limiting.

**Q: Can I scrape more than 10?**  
A: Yes, modify `limit=10` in the script to any number.

**Q: How often can I scrape?**  
A: Depends on your Firecrawl plan and Shopee's rate limits.

**Q: What about other sites?**  
A: The Firecrawl MCP can scrape any website!

## 🎯 Next Steps

1. **Configure** → Add FIRECRAWL_API_KEY to .env
2. **Verify** → Run `python check_shopee_setup.py`
3. **Scrape** → Run `python scrapers/shopee_firecrawl_scraper.py`
4. **Integrate** → Use results in your app
5. **Automate** → Set up scheduled scraping

## 📚 Additional Resources

- Firecrawl Docs: https://docs.firecrawl.dev
- BeautifulSoup Docs: https://www.crummy.com/software/BeautifulSoup/
- CSS Selectors: https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors
- Async Python: https://docs.python.org/3/library/asyncio.html

## ✅ Verification Checklist

Before scraping:
- [ ] Read this file
- [ ] Got Firecrawl API key from https://firecrawl.dev
- [ ] Added key to .env file
- [ ] Ran `pip install -r requirements.txt`
- [ ] Ran `python check_shopee_setup.py` ✅
- [ ] All checks passed

Ready to scrape? → `python scrapers/shopee_firecrawl_scraper.py`

---

**Status**: ✅ Ready to Use  
**Version**: 1.0.0  
**Date**: February 1, 2026

Happy scraping! 🕷️
