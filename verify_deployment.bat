@echo off
REM SmartShopper Web Scraping - Deployment Verification Script (Windows)
REM Run this to verify everything is set up correctly

setlocal enabledelayedexpansion

echo.
echo ====================================================================
echo   SmartShopper Web Scraping - Deployment Verification
echo ====================================================================
echo.

set total=0
set passed=0

REM Check Python files
echo Checking file structure...
echo.

if exist "scrapers\scrape_products.py" (
    echo [OK] Main scraper script
    set /a passed+=1
) else (
    echo [FAIL] Main scraper script (Missing: scrapers\scrape_products.py)
)
set /a total+=1

if exist "scrapers\config.py" (
    echo [OK] Configuration file
    set /a passed+=1
) else (
    echo [FAIL] Configuration file (Missing: scrapers\config.py)
)
set /a total+=1

if exist "scrapers\manage.py" (
    echo [OK] Management CLI tool
    set /a passed+=1
) else (
    echo [FAIL] Management CLI tool (Missing: scrapers\manage.py)
)
set /a total+=1

if exist "scrapers\test_scraper.py" (
    echo [OK] Test suite
    set /a passed+=1
) else (
    echo [FAIL] Test suite (Missing: scrapers\test_scraper.py)
)
set /a total+=1

if exist "scrapers\README.md" (
    echo [OK] Scraper documentation
    set /a passed+=1
) else (
    echo [FAIL] Scraper documentation (Missing: scrapers\README.md)
)
set /a total+=1

if exist "app\Console\Commands\ImportPrices.php" (
    echo [OK] Laravel command
    set /a passed+=1
) else (
    echo [FAIL] Laravel command (Missing: app\Console\Commands\ImportPrices.php)
)
set /a total+=1

echo.
echo Checking documentation...
echo.

if exist "QUICK_START_SCRAPING.md" (
    echo [OK] Quick start guide
    set /a passed+=1
) else (
    echo [FAIL] Quick start guide
)
set /a total+=1

if exist "SCRAPING_IMPLEMENTATION.md" (
    echo [OK] Technical documentation
    set /a passed+=1
) else (
    echo [FAIL] Technical documentation
)
set /a total+=1

if exist "TROUBLESHOOTING.md" (
    echo [OK] Troubleshooting guide
    set /a passed+=1
) else (
    echo [FAIL] Troubleshooting guide
)
set /a total+=1

if exist "VALIDATION_CHECKLIST.md" (
    echo [OK] Validation checklist
    set /a passed+=1
) else (
    echo [FAIL] Validation checklist
)
set /a total+=1

echo.
echo Checking system requirements...
echo.

python3 --version >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Python 3 installed
    set /a passed+=1
) else (
    echo [FAIL] Python 3 not found
)
set /a total+=1

php --version >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] PHP installed
    set /a passed+=1
) else (
    echo [FAIL] PHP not found
)
set /a total+=1

echo.
echo ====================================================================
echo                        VERIFICATION RESULTS
echo ====================================================================
echo.
echo Checks Passed: %passed% / %total%
echo.

if %passed% equ %total% (
    echo RESULT: All checks passed! System is ready to deploy.
    echo.
    echo Next Steps:
    echo 1. Read: QUICK_START_SCRAPING.md
    echo 2. Run: python3 scrapers\test_scraper.py
    echo 3. Test: php artisan prices:import
    echo 4. Check: Dashboard for prices
    exit /b 0
) else (
    echo RESULT: Some checks failed. Review the items above.
    echo.
    echo Installation Help:
    echo 1. Install Python: https://python.org
    echo 2. Install PHP: From your Laravel setup
    echo 3. Install Python packages: pip install selenium beautifulsoup4 requests webdriver-manager
    exit /b 1
)
