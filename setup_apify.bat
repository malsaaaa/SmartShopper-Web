@echo off
REM Quick setup script for Apify Shopee Scraper on Windows
REM This script installs dependencies and provides setup instructions

cls
echo.
echo ========================================
echo   APIFY SHOPEE SCRAPER QUICK SETUP
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python from https://www.python.org
    pause
    exit /b 1
)

echo [1/3] Installing Python dependencies...
pip install -q apify-client beautifulsoup4 requests selenium undetected-chromedriver
if %errorlevel% neq 0 (
    echo ERROR: Failed to install dependencies
    pause
    exit /b 1
)

echo [2/3] Verifying installation...
pip show apify-client >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: apify-client installation failed
    pause
    exit /b 1
)
echo ✓ Dependencies installed successfully

echo.
echo [3/3] Setup Instructions:
echo.
echo 1. Go to https://apify.com
echo 2. Sign up (free account)
echo 3. Get your API token from: Settings ^> API tokens
echo 4. Set environment variable in PowerShell:
echo    $env:APIFY_API_TOKEN = 'your-token-here'
echo.
echo 5. Test the integration:
echo    py scrapers/shopee_crawler.py "milo" 5
echo.
echo ========================================
echo Setup Complete! Follow steps 1-5 above.
echo ========================================
echo.
pause
