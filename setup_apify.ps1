#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Quick setup script for Apify Shopee Scraper on Windows (PowerShell)
    
.DESCRIPTION
    Installs dependencies and provides setup instructions for Apify integration
    
.EXAMPLE
    .\setup_apify.ps1
#>

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  APIFY SHOPEE SCRAPER QUICK SETUP" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Python is installed
try {
    $pythonVersion = python --version 2>&1
    Write-Host "✓ Python $pythonVersion found" -ForegroundColor Green
}
catch {
    Write-Host "ERROR: Python is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Python from https://www.python.org" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "[1/3] Installing Python dependencies..." -ForegroundColor Yellow
Write-Host "      Installing: apify-client, beautifulsoup4, requests, selenium, undetected-chromedriver"

pip install -q apify-client beautifulsoup4 requests selenium undetected-chromedriver 2>$null

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Failed to install dependencies" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "[2/3] Verifying installation..." -ForegroundColor Yellow
$apifyCheck = pip show apify-client 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: apify-client installation failed" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✓ Dependencies installed successfully" -ForegroundColor Green
$apifyVersion = ($apifyCheck | Select-String "Version:").ToString().Split(":")[1].Trim()
Write-Host "  apify-client version: $apifyVersion" -ForegroundColor Green

Write-Host ""
Write-Host "[3/3] Setup Instructions:" -ForegroundColor Yellow
Write-Host ""

Write-Host "STEP 1: Create Apify Account"
Write-Host "  Go to: https://apify.com" -ForegroundColor Cyan
Write-Host "  Sign up (free tier: 50 credits/month)" -ForegroundColor Gray
Write-Host ""

Write-Host "STEP 2: Get Your API Token"
Write-Host "  In Apify Dashboard: Settings → API tokens" -ForegroundColor Cyan
Write-Host "  Copy your personal token" -ForegroundColor Gray
Write-Host ""

Write-Host "STEP 3: Set Environment Variable"
Write-Host "  Run this command in PowerShell:" -ForegroundColor Cyan
Write-Host "  `$env:APIFY_API_TOKEN = 'your-token-here'" -ForegroundColor Green
Write-Host ""

Write-Host "STEP 4: Verify Setup"
Write-Host "  Run: py scrapers/shopee_crawler.py ""milo"" 5" -ForegroundColor Cyan
Write-Host "  You should see real Shopee products!" -ForegroundColor Gray
Write-Host ""

Write-Host "STEP 5: Make Environment Variable Permanent (Optional)"
Write-Host "  Run: [Environment]::SetEnvironmentVariable('APIFY_API_TOKEN', 'your-token', 'User')" -ForegroundColor Cyan
Write-Host "  Then restart PowerShell" -ForegroundColor Gray
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "Follow the steps above to activate Apify" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Optional: Prompt to set token now
$setNow = Read-Host "Do you want to set APIFY_API_TOKEN now? (y/n)"
if ($setNow -eq 'y') {
    $token = Read-Host "Enter your Apify API token"
    $env:APIFY_API_TOKEN = $token
    Write-Host "✓ Token set for this session" -ForegroundColor Green
    
    $permanent = Read-Host "Make it permanent for future sessions? (y/n)"
    if ($permanent -eq 'y') {
        [Environment]::SetEnvironmentVariable('APIFY_API_TOKEN', $token, 'User')
        Write-Host "✓ Token saved permanently" -ForegroundColor Green
    }
    
    Write-Host ""
    Write-Host "Testing integration..." -ForegroundColor Yellow
    py scrapers/apify_shopee_scraper.py "milo" 2 | ConvertFrom-Json | Select-Object -Property query, count, @{n='first_product'; e={$_.products[0].name}} 2>$null
}

Read-Host "Press Enter to close"
