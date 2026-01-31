# SmartShopper Web Scraper

Automated price scraping system for Malaysian retailers integrated with Laravel.

## 📋 Overview

This scraping system automatically collects product prices from multiple retailers:
- 🏪 **Mydin** (mydin.com.my)
- 🏪 **Giant** (giantmall.com.my)
- 🏪 **Shopee** (shopee.com.my)
- 🏪 **Lazada** (lazada.com.my)
- 🏪 **Lotus's** (tesco.com.my)

## 🚀 Quick Start

### Prerequisites
- Python 3.7+
- Chrome/Chromium browser (for Selenium)
- Laravel application running

### Installation

1. **Install Python dependencies:**
```bash
pip install scrapy selenium beautifulsoup4 webdriver-manager scrapy-selenium
```

2. **Verify installation:**
```bash
python3 scrapers/test_scraper.py
```

## 📁 File Structure

```
scrapers/
├── scrape_products.py      # Main scraper script
├── config.py               # Configuration and retailer settings
├── test_scraper.py         # Test suite
├── marketplace_spider.py   # Scrapy spider for marketplaces
├── retail_scrapers.py      # Legacy scraper (optional)
├── scraper.log            # Scraper logs
└── README.md              # This file
```

## ⚙️ Configuration

Edit `config.py` to:
- Add/remove retailers
- Modify product keywords
- Change Selenium options
- Adjust timeouts and delays

### Adding a New Retailer

```python
RETAILERS = {
    'new_retailer': {
        'name': 'Retailer Name',
        'url': 'https://retailer.com',
        'search_url': 'https://retailer.com/search?q={}',
        'selectors': {
            'product_container': '.product-class',
            'product_name': '.product-name-class',
            'product_price': '.price-class'
        }
    }
}
```

## 🔧 Usage

### Manual Scraping

```bash
python3 scrapers/scrape_products.py > output.json
```

### Via Laravel Artisan

```bash
php artisan prices:import
```

### Scheduled Scraping

The scraper runs automatically every hour via Laravel scheduler:

```php
// app/Console/Kernel.php
$schedule->command('prices:import')->hourly();
```

## 📊 Output Format

The scraper outputs JSON with the following structure:

```json
[
  {
    "name": "Product Name",
    "retailer": "Mydin",
    "price": 24.99,
    "url": "https://retailer.com/search?q=..."
  },
  {
    "name": "Another Product",
    "retailer": "Giant",
    "price": 18.50,
    "url": "https://retailer.com/search?q=..."
  }
]
```

## 📝 Logging

Logs are stored in `scrapers/scraper.log` and displayed on stderr.

View logs:
```bash
tail -f scrapers/scraper.log
```

## 🛡️ Best Practices

1. **Respect robots.txt** - Always check retailer's robots.txt
2. **Rate Limiting** - Add delays between requests (configured: 1 second)
3. **User-Agent** - Scraper identifies itself with proper User-Agent
4. **Error Handling** - Graceful fallbacks and retry logic
5. **Data Validation** - Prices are validated before storage

## 🐛 Troubleshooting

### Chrome/Chromium Not Found
```bash
# Install Chromium
sudo apt-get install chromium-browser  # Linux
brew install chromium                   # macOS
# Windows: Download from chromium.org
```

### Selenium Timeout
- Increase `WAIT_TIMEOUT` in `config.py`
- Check retailer website for changes
- Verify internet connection

### No Data Scraped
1. Check CSS selectors match current website
2. Review logs: `tail scrapers/scraper.log`
3. Test with `python3 scrapers/test_scraper.py`
4. Verify retailers are accessible

### Rate Limited
- Increase `DELAY_BETWEEN_REQUESTS` in `config.py`
- Retailer may have blocking; use proxy (not implemented)

## 📈 Performance

- Average scrape time: 2-5 minutes per run
- Items per run: ~35 (7 products × 5 retailers)
- Database storage: ~10KB per run
- Recommended frequency: Hourly

## 🔐 Security

- No sensitive data stored
- Scraper runs locally (no external API)
- Prices are anonymized
- Standard Python security best practices

## 📚 Advanced Features

### Custom Selectors Per Retailer
Selectors are CSS-based and can be updated in `config.py` without code changes.

### Error Resilience
- Automatic retry on failure
- Fallback from Selenium to Requests
- Partial data collection (doesn't fail completely)

### Data Deduplication
Laravel's `firstOrCreate` ensures no duplicate products/retailers.

## 🤝 Contributing

To improve the scraper:
1. Update selectors for changed websites
2. Add new retailers to `config.py`
3. Improve error handling
4. Test thoroughly before deployment

## 📞 Support

Check logs for detailed error information:
```bash
grep ERROR scrapers/scraper.log
```

## 📄 License

Part of SmartShopper application.

---

**Last Updated:** 2024
**Status:** Production Ready ✅
