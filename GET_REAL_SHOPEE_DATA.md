# Getting Real Shopee Products (Instead of Demo Data)

## Current Status
The Shopee crawler is showing **demo products** because ChromeDriver is not properly configured. The crawler uses Selenium with Chrome WebDriver to load JavaScript content from Shopee.com.my, which requires ChromeDriver.

## Why Demo Data?
- ✅ Search limit: **10 products per search** (as requested)
- ⚠️ **Demo data only**: Real products need ChromeDriver
- 🔍 **Status indicator**: The search results will show "DEMO" badge if using fallback data

## How to Get Real Shopee Products

### Option 1: Download ChromeDriver (Recommended)

1. **Check Your Chrome Version**
   - Open Chrome → Click ⋮ (menu) → About Google Chrome
   - Note your version number (e.g., 120.0.6099.129)

2. **Download ChromeDriver**
   - Visit: https://googlechromelabs.github.io/chrome-for-testing/
   - Download the ChromeDriver version matching your Chrome version
   - Extract the `chromedriver.exe` file

3. **Add to PATH (Windows)**

   **Option A: Add to Program Files**
   - Create folder: `C:\tools\chromedriver\`
   - Move `chromedriver.exe` there
   - Add `C:\tools\chromedriver\` to Windows PATH environment variable

   **Option B: Set Environment Variable**
   - Set `CHROMEDRIVER_PATH=C:\path\to\chromedriver.exe`
   - The scraper will automatically detect this

   **Option C: Copy to Project**
   - Copy `chromedriver.exe` to: `c:\Users\HP VICTUS\SmartShopper\`
   - The crawler will find it in the working directory

4. **Test ChromeDriver**
   ```powershell
   chromedriver --version
   ```
   If successful, you'll see: `ChromeDriver 120.x.x.x ...`

### Option 2: Disable Headless Mode (For Debugging)

Edit [scrapers/shopee_crawler.py](scrapers/shopee_crawler.py#L42):
```python
def _setup_selenium(self):
    options = Options()
    # Change to: options.add_argument("--start-maximized")  # Show browser window
    # Leave headless=False in ShopeeCrawler constructor
```

### Option 3: Use API Alternative

Modify [app/Services/ShopeeCrawlerService.php](app/Services/ShopeeCrawlerService.php) to use Shopee's official API (if available) instead of web scraping.

## Expected Results After Setup

**Before (Demo Data):**
```
🛒 Shopee Search Results
Found 10 products (demo data - real crawler currently unavailable)
[DEMO badge]
```

**After (Real Data):**
```
🛒 Shopee Search Results
Found 10 products from Shopee.com.my (crawled in 2534ms)
[LIVE badge] ← animated pulse
```

## Troubleshooting

### "ChromeDriver not found in PATH"
- Run: `Get-Command chromedriver` in PowerShell
- If no results, ChromeDriver is not in PATH
- Follow "Add to PATH" steps above

### Selenium crashes or timeout
- Ensure Chrome browser is installed
- Update Chrome to latest version
- Re-download matching ChromeDriver version
- Check `storage/logs/laravel.log` for detailed errors

### Want to Change Product Limit?
Edit [app/Http/Controllers/BudgetController.php](app/Http/Controllers/BudgetController.php#L186):
```php
$crawledData = $crawlerService->search(trim($query), 10); // Change 10 to desired limit
```

## How It Works

1. **Search Query** → User enters search term in marketplace
2. **Controller** → Calls `ShopeeCrawlerService->search()`
3. **Python Crawler** → Runs `py scrapers/shopee_crawler.py "query" 10`
4. **Selenium/Requests** → Loads Shopee.com.my and extracts products
5. **Results Cache** → Caches results for 1 hour (avoid rate limiting)
6. **Fallback** → If crawler fails, shows realistic demo products

## Architecture

```
┌─ Search Request ─┐
│  "samsung"       │
└─────────┬────────┘
          │
          ↓
┌─────────────────────────────────────┐
│  ShopeeCrawlerService (PHP)         │
│  - Caches results (1 hour TTL)      │
│  - Detects Python executable        │
│  - Executes crawler command         │
│  - Returns formatted products       │
└─────────┬───────────────────────────┘
          │
          ↓
┌─────────────────────────────────────┐
│  shopee_crawler.py (Python)         │
│  - Selenium: JS-rendered content    │
│  - Requests: Static HTML fallback   │
│  - BeautifulSoup: HTML parsing      │
│  - Returns JSON products            │
└─────────┬───────────────────────────┘
          │
    ┌─────┴─────┐
    ↓           ↓
 Success    Fallback
 (Real)     (Demo 10)
    │           │
    └─────┬─────┘
          ↓
┌─────────────────────────────────────┐
│  View: resources/views/search.blade │
│  - Display with LIVE or DEMO badge  │
│  - Show crawl duration (real vs 0ms)│
│  - Product cards with ratings       │
│  - "View on Shopee" links           │
│  - "Add to Plan" buttons            │
└─────────────────────────────────────┘
```

## Performance Notes

- **Real crawl**: 2-5 seconds (shows LIVE badge)
- **Demo data**: <100ms (shows DEMO badge)
- **Caching**: 1 hour for same query (skips crawling)
- **10 products**: Per search (configurable)

## Next Steps

1. Download ChromeDriver for your Chrome version
2. Add to PATH or set `CHROMEDRIVER_PATH`
3. Search for any product on Marketplace
4. You'll now see "LIVE" badge instead of "DEMO"
5. Products will be real Shopee listings (limited to 10)

---

For more details, see [SHOPEE_CRAWLER_GUIDE.md](SHOPEE_CRAWLER_GUIDE.md)
