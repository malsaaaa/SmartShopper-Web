# ✅ SmartShopper Web Scraping - Validation Checklist

Use this checklist to verify the web scraping implementation is working correctly.

## 🔍 Pre-Deployment Verification

### Python Environment
- [ ] Python 3.7+ installed: `python3 --version`
- [ ] Chrome/Chromium browser available
- [ ] Dependencies installed: `pip install scrapy selenium beautifulsoup4 webdriver-manager`

### File Structure
- [ ] `scrapers/scrape_products.py` exists
- [ ] `scrapers/config.py` exists
- [ ] `scrapers/manage.py` exists
- [ ] `scrapers/test_scraper.py` exists
- [ ] `app/Console/Commands/ImportPrices.php` modified

### Documentation
- [ ] `SCRAPING_IMPLEMENTATION.md` created
- [ ] `QUICK_START_SCRAPING.md` created
- [ ] `IMPLEMENTATION_SUMMARY.md` created
- [ ] `scrapers/README.md` created

## 🧪 Testing

### Test 1: Python Imports
```bash
python3 scrapers/test_scraper.py
```
**Expected:** All ✓ marks
- [ ] requests ✓
- [ ] beautifulsoup4 ✓
- [ ] selenium ✓
- [ ] webdriver-manager ✓
- [ ] Scraper initialized ✓
- [ ] Scraper ran successfully ✓

**Pass:** ☐ YES ☐ NO

### Test 2: Manual Scraper Run
```bash
python3 scrapers/scrape_products.py
```
**Expected:** JSON output with items
**Example:**
```json
[
  {"name": "Product", "retailer": "Mydin", "price": 24.99, "url": "..."}
]
```
**Pass:** ☐ YES ☐ NO

### Test 3: Laravel Command
```bash
php artisan prices:import
```
**Expected Output:**
```
🔄 Starting price scraping...
✅ Successfully imported X prices
```
**Pass:** ☐ YES ☐ NO

### Test 4: Database Check
```php
php artisan tinker
>>> Price::latest()->first();
```
**Expected:** Price record with `scraped_at` timestamp
```
Price {#3856
  id: 1,
  product_id: 1,
  retailer_id: 1,
  amount: 24.99,
  scraped_at: "2024-01-05 14:30:00",
}
```
**Pass:** ☐ YES ☐ NO

### Test 5: Dashboard Display
- [ ] Open dashboard
- [ ] Look for prices with timestamps
- [ ] Verify format: "📅 Jan 05, 14:30"
- [ ] Multiple retailers visible
**Pass:** ☐ YES ☐ NO

## 🔧 Configuration Check

### scrapers/config.py
- [ ] Retailers defined (5 total)
- [ ] CSS selectors present
- [ ] Sample products listed
- [ ] Logging configured
- [ ] Rate limits set (1 second)

**Pass:** ☐ YES ☐ NO

### app/Console/Commands/ImportPrices.php
- [ ] Imports Price, Product, Retailer models
- [ ] runScraper() method exists
- [ ] Error handling in place
- [ ] Data validation implemented
- [ ] scraped_at timestamp saved

**Pass:** ☐ YES ☐ NO

### app/Console/Kernel.php
- [ ] Scheduler enabled
- [ ] Command runs hourly: `$schedule->command('prices:import')->hourly();`
- [ ] No syntax errors

**Pass:** ☐ YES ☐ NO

## 🚀 Deployment Verification

### Step 1: Install Dependencies
```bash
pip install -r requirements.txt  # If available
# OR
pip install scrapy selenium beautifulsoup4 webdriver-manager
```
**Status:** ☐ Complete ☐ Error ☐ Skip

### Step 2: Run Test Suite
```bash
python3 scrapers/test_scraper.py
```
**Status:** ☐ PASS ☐ FAIL

**Errors Found:**
```
[List any errors here]
```

### Step 3: Test Manual Scrape
```bash
php artisan prices:import
```
**Output:**
```
[Paste output here]
```
**Status:** ☐ PASS ☐ FAIL

### Step 4: Verify Database
```bash
php artisan tinker
>>> Price::count();
```
**Count:** _____ prices

**Status:** ☐ Records created ☐ No records ☐ Error

### Step 5: Check Dashboard
- [ ] Prices displaying
- [ ] Timestamps showing
- [ ] Multiple retailers visible
- [ ] No error messages

**Status:** ☐ PASS ☐ FAIL

## 📊 Performance Check

### Before Deployment
- [ ] Note current database size: _____ MB
- [ ] Check disk space: _____ GB available
- [ ] Monitor CPU during first run

### After First Run
- [ ] Database size: _____ MB (Δ _____ KB)
- [ ] Execution time: _____ seconds
- [ ] Items collected: _____
- [ ] No errors in logs

**Status:** ☐ ACCEPTABLE ☐ NEEDS OPTIMIZATION

## 🐛 Troubleshooting Completed

### Issue: No data scraped
- [ ] Checked website selectors
- [ ] Verified network connectivity
- [ ] Reviewed scraper logs
- [ ] Ran test suite

### Issue: Chrome driver error
- [ ] Updated webdriver-manager
- [ ] Verified Chrome/Chromium installed
- [ ] Checked system PATH

### Issue: Timeout errors
- [ ] Increased WAIT_TIMEOUT in config.py
- [ ] Verified website availability
- [ ] Checked network speed

### Issue: Database errors
- [ ] Verified migrations ran
- [ ] Checked Model imports
- [ ] Verified field types

**Status:** ☐ ALL ISSUES RESOLVED ☐ ISSUES REMAIN

## 📋 Documentation Review

- [ ] SCRAPING_IMPLEMENTATION.md reviewed
- [ ] QUICK_START_SCRAPING.md reviewed
- [ ] README.md in scrapers/ reviewed
- [ ] All links valid and working
- [ ] Examples tested and working

**Status:** ☐ COMPLETE ☐ NEEDS UPDATES

## 🔐 Security Check

- [ ] No hardcoded credentials in scripts
- [ ] Rate limiting enabled (1 second delay)
- [ ] User-Agent headers set properly
- [ ] No sensitive data logged
- [ ] Error messages don't expose paths
- [ ] File permissions correct (755 for scripts)

**Status:** ☐ SECURE ☐ NEEDS FIXES

## 🔄 Scheduler Verification

### Hourly Schedule
```bash
php artisan schedule:run
```
**Expected:** Command runs without errors

- [ ] Scheduler enabled in Kernel.php
- [ ] Command executes without error
- [ ] Output shows completion
- [ ] Database updated with new data

**Status:** ☐ WORKING ☐ ERROR

### Test Next Run
- [ ] Wait 1 hour for scheduler to run
- [ ] Check database for new records: `Price::latest()->first()->created_at`
- [ ] Verify timestamp is current

**Status:** ☐ PASSED ☐ FAILED ☐ PENDING

## 📊 Data Quality Check

### Sample Data Verification
```php
$prices = Price::with('product', 'retailer')
    ->latest('scraped_at')
    ->limit(10)
    ->get();
```

- [ ] All prices have valid amounts
- [ ] All retailers found in database
- [ ] All products have names
- [ ] scraped_at timestamps are recent
- [ ] No duplicate entries

**Status:** ☐ PASS ☐ FAIL

**Issues Found:**
```
[List any data quality issues]
```

## ✅ Final Approval

### Ready for Production?

**All Tests Passed:** ☐ YES ☐ NO

**Issues Remaining:** 
```
[List any remaining issues]
```

**Approval Sign-off:**
- Developer: _________________ Date: _______
- QA: _________________ Date: _______

### Deployment Notes
```
[Any special notes or configurations for production]
```

## 🎉 Post-Deployment

- [ ] Monitor first 24 hours of scraping
- [ ] Check logs daily for errors
- [ ] Verify prices updating on dashboard
- [ ] Monitor database growth
- [ ] Set up alerts for scraper failures
- [ ] Document any website selector changes

**Status:** ☐ MONITORED ☐ IN PROGRESS ☐ COMPLETE

---

**Checklist Date:** _______________
**Completed By:** _______________
**Status:** ✅ APPROVED / ❌ REJECTED

**Sign-off:** _____________________
