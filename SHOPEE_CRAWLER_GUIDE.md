# Shopee.com.my Web Crawler Implementation

## Overview
This document describes the real-time web crawler implementation for Shopee.com.my product searches in SmartShopper.

## Architecture

### 1. **Python Scraper** (`scrapers/shopee_crawler.py`)
- Real-time product search from Shopee.com.my
- Hybrid scraping approach:
  - **Primary:** Selenium + ChromeDriver (handles JavaScript-rendered content)
  - **Fallback:** Requests library (for static content)
- Extracts product information:
  - Product name
  - Current price (RM)
  - Original price
  - Product image
  - Product URL
  - Rating & reviews
  - Shop name
  - Discount percentage

### 2. **PHP Service** (`app/Services/ShopeeCrawlerService.php`)
- Bridges Laravel application with Python crawler
- Executes crawler script via shell
- Implements result caching (1 hour) to reduce API calls
- Formats crawler output for Laravel views
- Error handling and logging

### 3. **Controller Integration** (`app/Http/Controllers/BudgetController.php`)
- Updated `search()` method to call crawler service
- Supports three source modes:
  - `all` - Returns both database and Shopee results
  - `database` - Database products only
  - `shopee` - Shopee crawler results only
- Tracks crawl duration for performance monitoring

### 4. **Enhanced Search View** (`resources/views/search.blade.php`)
- Displays Shopee results in dedicated section with blue theme
- Database results in separate section with emerald theme
- Source filter to switch between results
- Shows crawl status and performance metrics
- Live badge indicator

## Features

✅ **Real-time Search**
- Users can search any product name
- Instant Shopee.com.my product retrieval
- 15 products per search (configurable)

✅ **Smart Caching**
- 1-hour cache per search query
- Reduces server load and API calls
- Automatic cache invalidation

✅ **Hybrid Scraping**
- Selenium for JavaScript content (more complete)
- Requests fallback for faster loading
- Graceful error handling

✅ **User-Friendly Display**
- Clear visual separation of sources
- Product cards with images, prices, ratings
- "View on Shopee" links
- "Add to Plan" integration with budget

✅ **Performance Tracking**
- Crawl duration displayed (milliseconds)
- Status indicators (LIVE badge)
- Crawl attempt logging

## Usage

### From Dashboard
1. User enters search query in marketplace search bar
2. Results automatically include Shopee crawled products
3. User can filter by source (All/Database/Shopee)
4. Click "View on Shopee" to open product on Shopee
5. Click "Add to Plan" to add to shopping budget

### From Command Line (Testing)
```bash
cd scrapers/
python shopee_crawler.py "Milo" 20
```

Output: JSON with crawled products

## Configuration

**Python Crawler Settings** (in `shopee_crawler.py`):
```python
search_url = "https://shopee.com.my/search?keyword="
max_scrolls = 3  # Number of times to scroll for more products
page_load_timeout = 30  # seconds
```

**PHP Service Settings** (in `ShopeeCrawlerService.php`):
```php
$maxResults = 15;  // Products per search
Cache::put($cacheKey, $results, now()->addHours(1));  // 1 hour cache
```

## Database Integration

Crawled products don't modify the database. Instead:
1. **Crawled products** - Displayed in search results, temporary
2. **Database products** - Persistent products with price history
3. **User can add crawled products** to their shopping plan (stored as temporary)

## Error Handling

| Scenario | Behavior |
|----------|----------|
| Selenium fails | Falls back to Requests library |
| Both methods fail | Returns empty results gracefully |
| Network timeout | Logs error, returns cached results if available |
| Invalid search | Returns empty array |
| Cache hit | Uses cached results (1 hour TTL) |

## Performance

**Expected Crawl Times:**
- Selenium (first load): 3-5 seconds
- Selenium (with scrolling): 5-8 seconds
- Requests fallback: 1-2 seconds
- Cached result: <100ms

## Future Enhancements

- [ ] Product comparison across retailers (Mydin, Shopee, Lazada)
- [ ] Price tracking history for Shopee products
- [ ] Notification on price drops
- [ ] Smart recommendations based on Shopee prices
- [ ] Batch crawling for multiple searches
- [ ] Image caching/optimization
- [ ] Pagination support for large result sets

## Troubleshooting

**No results returned:**
1. Check if Shopee URL is accessible
2. Verify ChromeDriver compatibility
3. Check Python dependencies installed
4. Review Laravel logs: `storage/logs/laravel.log`

**Slow crawl times:**
1. Reduce max_scrolls in crawler
2. Increase max_results caching TTL
3. Use database-only search if Shopee too slow
4. Monitor network conditions

**Memory issues:**
1. Reduce max_results per search
2. Clear old cache entries: `php artisan cache:clear`
3. Reduce crawl scrolls

## Testing

**Manual Test:**
```php
// In Laravel tinker
$service = new \App\Services\ShopeeCrawlerService();
$results = $service->search('Milo', 10);
dd($results);
```

## Files Modified

- ✅ Created: `scrapers/shopee_crawler.py` (340+ lines)
- ✅ Created: `app/Services/ShopeeCrawlerService.php` (70+ lines)
- ✅ Modified: `app/Http/Controllers/BudgetController.php` - Added import & updated search()
- ✅ Modified: `resources/views/search.blade.php` - Enhanced UI with Shopee results
- ✅ Modified: `routes/web.php` - No changes (existing route reused)

## Security Considerations

✅ **Input Sanitization**
- Search queries escaped with `escapeshellarg()`
- URL encoded with `quote()` in Python

✅ **Rate Limiting**
- Caching reduces requests to Shopee
- Crawl delays between requests (configurable)

✅ **Error Isolation**
- Crawler errors don't crash application
- Graceful fallbacks implemented

## Next Steps

To test the implementation:
1. Ensure ChromeDriver is in system PATH or specify path in options
2. Install Python dependencies: `pip install selenium beautifulsoup4 requests`
3. Try search from dashboard
4. Monitor logs for any issues

---

**Status:** ✅ Implementation Complete (Jan 31, 2026)
**Testing:** Ready for UAT with supervisor
