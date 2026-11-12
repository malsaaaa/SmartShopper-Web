# 🔧 SmartShopper Web Scraping - Troubleshooting Guide

## 🆘 Common Issues & Solutions

### 1. "ModuleNotFoundError: No module named 'selenium'"

**Symptom:**
```
ModuleNotFoundError: No module named 'selenium'
```

**Causes:**
- Python dependencies not installed
- Wrong Python version
- Virtual environment not activated

**Solutions:**
```bash
# Install missing package
pip install selenium

# Or install all requirements
pip install scrapy selenium beautifulsoup4 webdriver-manager

# Verify Python version (need 3.7+)
python3 --version

# If using virtual environment, activate it first
source venv/bin/activate  # Linux/Mac
venv\Scripts\activate     # Windows
```

---

### 2. "ChromeDriver version mismatch"

**Symptom:**
```
selenium.common.exceptions.WebDriverException: unknown error: 
  Chrome failed to start: got handshake failure
```

**Causes:**
- Chrome/Chromium browser outdated
- WebDriver version mismatch
- Missing Chrome installation

**Solutions:**
```bash
# Update webdriver-manager
pip install --upgrade webdriver-manager

# Install Chrome (if missing)
# Ubuntu/Debian
sudo apt-get install chromium-browser

# macOS
brew install chromium

# For Windows: Download from chromium.org or use:
choco install chromium

# Clear WebDriver cache
rm -rf ~/.wdm/  # Linux/Mac
del %APPDATA%\wdm\  # Windows
```

---

### 3. "TimeoutException: Message: Timeout waiting for element"

**Symptom:**
```
TimeoutException: Message: Timeout waiting for element to be located
```

**Causes:**
- CSS selectors changed on website
- Website taking too long to load
- Network connectivity issues
- Website blocking the scraper

**Solutions:**

1. **Check CSS selectors:**
   - Visit retailer website in browser
   - Right-click on product name → Inspect
   - Find CSS class (e.g., `.product-name`)
   - Update `scrapers/config.py` with new selector

2. **Increase timeout:**
   ```python
   # In config.py
   WAIT_TIMEOUT = 10  # Increase from 5
   ```

3. **Check network:**
   ```bash
   # Test connectivity
   ping google.com
   
   # Test retailer website
   curl -I https://www.mydin.com.my
   ```

4. **Test individual retailer:**
   ```bash
   # Edit config.py to test only one product
   SAMPLE_PRODUCTS = ['Milo']
   
   # Run scraper
   python3 scrapers/scrape_products.py
   ```

---

### 4. "No module named 'config'"

**Symptom:**
```
ModuleNotFoundError: No module named 'config'
```

**Causes:**
- `config.py` not in scrapers folder
- Wrong working directory
- Python path issue

**Solutions:**
```bash
# Verify file exists
ls scrapers/config.py  # Linux/Mac
dir scrapers\config.py # Windows

# Run from project root
cd /path/to/SmartShopper
python3 scrapers/scrape_products.py

# OR run from scrapers folder
cd scrapers
python3 scrape_products.py
```

---

### 5. "JSON decode error"

**Symptom:**
```
JSONDecodeError: Expecting value: line 1 column 1
```

**Causes:**
- Scraper output not valid JSON
- Scraper crashed or returned error message
- Empty output from scraper

**Solutions:**
```bash
# Check scraper output directly
python3 scrapers/scrape_products.py 2>&1

# View scraper logs
cat scrapers/scraper.log

# Test with simpler config
python3 scrapers/manage.py run

# Check stderr for errors
python3 scrapers/scrape_products.py 2>&1 | grep ERROR
```

---

### 6. "php artisan prices:import shows no output"

**Symptom:**
```
No output when running prices:import
```

**Causes:**
- Python script path incorrect
- Python not installed or not in PATH
- Permission issues

**Solutions:**
```bash
# Verify Python is accessible
which python3  # Linux/Mac
where python3  # Windows

# Check file permissions
chmod +x scrapers/scrape_products.py

# Test Python directly
python3 scrapers/scrape_products.py

# Check Laravel logs
tail storage/logs/laravel.log

# Run with debug output
php artisan prices:import --verbose
```

---

### 7. "Database error: No such table"

**Symptom:**
```
SQLSTATE[42S02]: Table 'smartshopper.prices' doesn't exist
```

**Causes:**
- Database migrations not run
- Wrong database name
- Database connection error

**Solutions:**
```bash
# Run migrations
php artisan migrate

# Check migration status
php artisan migrate:status

# Run specific migration
php artisan migrate --path=database/migrations/2025_12_16_144052_create_prices_table.php

# Verify database connection
php artisan tinker
>>> DB::connection()->getPDO()
```

---

### 8. "Website blocks scraper (403 Forbidden)"

**Symptom:**
```
HTTP Error 403: Forbidden
```

**Causes:**
- Rate limiting (too many requests)
- Missing/invalid User-Agent
- IP blocked by website

**Solutions:**
1. **Increase delays:**
   ```python
   # In config.py
   DELAY_BETWEEN_REQUESTS = 3  # Increase from 1
   ```

2. **Update User-Agent:**
   ```python
   # In scrape_products.py - already has proper User-Agent
   # But you can change it if needed
   'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
   ```

3. **Use proxy (advanced):**
   ```python
   # Not implemented by default
   # Would need: pip install selenium-wire
   # And configure proxy in Selenium options
   ```

---

### 9. "Database full or disk space error"

**Symptom:**
```
Error: Disk quota exceeded
```

**Causes:**
- Database growing too large
- Log files consuming space
- Old backups not deleted

**Solutions:**
```bash
# Check disk space
df -h  # Linux/Mac
dir    # Windows

# Clear old logs
python3 scrapers/manage.py clear-logs

# Check database size
du -sh storage/database/  # If using SQLite

# Run Laravel cleanup
php artisan cache:clear
php artisan config:clear
```

---

### 10. "Scraper runs but collects no data"

**Symptom:**
```
✅ Successfully imported 0 prices
```

**Causes:**
- CSS selectors don't match website HTML
- Website changed structure
- JavaScript content not loading properly
- Scraper blocked before rendering

**Solutions:**

1. **Inspect website manually:**
   ```bash
   # Open browser, visit retailer
   # Right-click product → Inspect Element
   # Find CSS class for product name/price
   ```

2. **Update selectors in config.py:**
   ```python
   'selectors': {
       'product_container': '.NEW-CLASS',  # Update this
       'product_name': '.NEW-NAME-CLASS',
       'product_price': '.NEW-PRICE-CLASS'
   }
   ```

3. **Test updated config:**
   ```bash
   python3 scrapers/scrape_products.py | python3 -m json.tool
   ```

4. **Check scraper logs:**
   ```bash
   tail -f scrapers/scraper.log
   grep ERROR scrapers/scraper.log
   ```

---

### 11. "Memory usage too high"

**Symptom:**
```
MemoryError or system slowdown during scraping
```

**Causes:**
- Too many browser instances
- Selenium driver not quitting properly
- Large data accumulation

**Solutions:**
```python
# In scrape_products.py - already has driver.quit()
# But verify it's called in finally block:

try:
    # scraping logic
except:
    pass
finally:
    driver.quit()  # IMPORTANT

# Reduce concurrent operations
SAMPLE_PRODUCTS = ['Milo', 'Rice']  # Reduce test scope
```

---

### 12. "Laravel command timeout"

**Symptom:**
```
Max execution time exceeded
```

**Causes:**
- Scraper taking too long
- Database operations slow
- Website responding slowly

**Solutions:**
```bash
# Increase PHP timeout in php.ini
max_execution_time = 300  # 5 minutes

# Or run with specific timeout
timeout 600 php artisan prices:import

# Run scraper directly to test speed
time python3 scrapers/scrape_products.py
```

---

## 🔍 Debugging Steps

### Enable Verbose Logging
1. Edit `scrapers/config.py`:
   ```python
   logging.basicConfig(level=logging.DEBUG)
   ```

2. Run scraper:
   ```bash
   python3 scrapers/scrape_products.py 2>&1 | grep DEBUG
   ```

### Test Individual Retailers
Edit `scrapers/config.py`:
```python
RETAILERS = {
    'mydin': {...}  # Only test Mydin
}
SAMPLE_PRODUCTS = ['Milo']  # Only test Milo
```

Then run:
```bash
python3 scrapers/scrape_products.py
```

### Check Selector Validity
```python
from bs4 import BeautifulSoup
html = "<div class='product'><span class='price'>$10</span></div>"
soup = BeautifulSoup(html, 'html.parser')
print(soup.select('.product'))  # Should return element
```

### Monitor Database Growth
```php
// Laravel Tinker
php artisan tinker
>>> Price::count();
>>> Price::latest()->get();
>>> DB::table('prices')->groupBy('retailer_id')->count();
```

---

## 📊 Performance Analysis

### Check Scraper Speed
```bash
time python3 scrapers/scrape_products.py
```

Expected: 2-5 minutes

### Monitor Database Impact
```bash
# Before scrape
php artisan tinker
>>> Price::count();

# Wait for scrape to complete

# After scrape
>>> Price::count();
>>> Price::where('created_at', '>', now()->subMinutes(10))->count();
```

### Check Resource Usage
```bash
# During scrape (in another terminal)
top  # Linux/Mac
tasklist  # Windows

# Look for: python3 process using CPU/memory
```

---

## 📝 When to Contact Support

If you've tried all solutions above, collect this info:

1. **Error message** - Full error text
2. **Logs** - `cat scrapers/scraper.log`
3. **System info** - `python3 --version`, `php --version`
4. **Environment** - Linux/Mac/Windows, Python version, Chrome version
5. **Steps to reproduce** - Exact commands run
6. **Output of tests:**
   ```bash
   python3 scrapers/test_scraper.py
   python3 scrapers/manage.py status
   ```

---

## ✅ Quick Health Check

Run this to diagnose issues:

```bash
#!/bin/bash
echo "=== SmartShopper Scraper Health Check ==="
echo ""
echo "1. Python version:"
python3 --version
echo ""
echo "2. Required packages:"
python3 -c "import requests, bs4, selenium; print('✓ All packages installed')"
echo ""
echo "3. Scraper test:"
python3 scrapers/test_scraper.py | head -5
echo ""
echo "4. File structure:"
ls -la scrapers/
echo ""
echo "5. Database check:"
php artisan tinker << 'EOF'
echo("Prices in DB: " . Price::count());
exit;
EOF
```

---

**Last Updated:** 2024
**Status:** Complete & Tested
**Suggestions:** Report any new issues to dev team
