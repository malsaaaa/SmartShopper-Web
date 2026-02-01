# SmartShopper Crawler API Test Commands (PowerShell)
# Use these commands to test the product search API
# Make sure your Laravel server is running: php artisan serve

Write-Host "==========================================" -ForegroundColor Green
Write-Host "SmartShopper Crawler API Test" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Make sure to run: php artisan serve" -ForegroundColor Yellow
Write-Host "Then run these commands in a new terminal:" -ForegroundColor Yellow
Write-Host ""

# Test 1: Search for Samsung
Write-Host "TEST 1: Search for Samsung products" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/search?q=samsung" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: 8 Samsung products with prices" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Test 2: Search for iPhone
Write-Host "TEST 2: Search for iPhone products" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/search?q=iphone" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: 5 iPhone products with prices" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Test 3: Search for Gaming Laptop
Write-Host "TEST 3: Search for Gaming Laptop products" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/search?q=gaming laptop" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: 6 Gaming Laptop products with prices" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Test 4: Get by Category - Samsung
Write-Host "TEST 4: Get Samsung products by category" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/category/Samsung" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: 8 Samsung products with full details" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Test 5: Get by Category - iPhone
Write-Host "TEST 5: Get iPhone products by category" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/category/iPhone" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: 5 iPhone products with full details" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Test 6: Get by Category - Gaming Laptop
Write-Host "TEST 6: Get Gaming Laptop products by category" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/category/Gaming%20Laptop" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: 6 Gaming Laptop products with full details" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Test 7: Get All Products
Write-Host "TEST 7: Get all products" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/all" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: All 19 products with prices, images, and metadata" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Test 8: Invalid Category
Write-Host "TEST 8: Test error handling (invalid category)" -ForegroundColor Cyan
Write-Host "Command:" -ForegroundColor White
Write-Host 'Invoke-RestMethod "http://localhost:8000/api/products/category/InvalidCategory" -Method Get | ConvertTo-Json | Write-Host' -ForegroundColor Yellow
Write-Host ""
Write-Host "Expected: Error message with valid categories" -ForegroundColor Green
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""

# Sample Response
Write-Host ""
Write-Host "Sample Response Format:" -ForegroundColor Magenta
$sample = @{
    success = $true
    query = "samsung"
    count = 8
    data = @(
        @{
            id = 1
            name = "Samsung Galaxy Z Fold7 [ONLINE EXCLUSIVE]"
            category = "Samsung"
            image_url = "https://down-my.img.susercontent.com/..."
            price = "7199.00"
            product_url = $null
            retailer = "Shopee Malaysia"
            scraped_at = "2026-02-01T22:40:24Z"
        }
    )
}
$sample | ConvertTo-Json | Write-Host -ForegroundColor Gray
Write-Host ""

Write-Host "==========================================" -ForegroundColor Green
Write-Host "To test, copy and paste the commands above" -ForegroundColor Yellow
Write-Host "==========================================" -ForegroundColor Green
