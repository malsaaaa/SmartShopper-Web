#!/bin/bash

# SmartShopper Crawler API Test Commands
# Use these commands to test the product search API
# Make sure your Laravel server is running: php artisan serve

echo "=========================================="
echo "SmartShopper Crawler API Test"
echo "=========================================="
echo ""
echo "Make sure to run: php artisan serve"
echo "Then run these commands in a new terminal:"
echo ""

# Test 1: Search for Samsung
echo "TEST 1: Search for Samsung products"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/search?q=samsung"'
echo ""
echo "Expected: 8 Samsung products with prices"
echo ""
echo "=========================================="
echo ""

# Test 2: Search for iPhone
echo "TEST 2: Search for iPhone products"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/search?q=iphone"'
echo ""
echo "Expected: 5 iPhone products with prices"
echo ""
echo "=========================================="
echo ""

# Test 3: Search for Gaming Laptop
echo "TEST 3: Search for Gaming Laptop products"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/search?q=gaming%20laptop"'
echo ""
echo "Expected: 6 Gaming Laptop products with prices"
echo ""
echo "=========================================="
echo ""

# Test 4: Get by Category - Samsung
echo "TEST 4: Get Samsung products by category"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/category/Samsung"'
echo ""
echo "Expected: 8 Samsung products with full details"
echo ""
echo "=========================================="
echo ""

# Test 5: Get by Category - iPhone
echo "TEST 5: Get iPhone products by category"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/category/iPhone"'
echo ""
echo "Expected: 5 iPhone products with full details"
echo ""
echo "=========================================="
echo ""

# Test 6: Get by Category - Gaming Laptop
echo "TEST 6: Get Gaming Laptop products by category"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/category/Gaming%20Laptop"'
echo ""
echo "Expected: 6 Gaming Laptop products with full details"
echo ""
echo "=========================================="
echo ""

# Test 7: Get All Products
echo "TEST 7: Get all products"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/all"'
echo ""
echo "Expected: All 19 products with prices, images, and metadata"
echo ""
echo "=========================================="
echo ""

# Test 8: Invalid Category
echo "TEST 8: Test error handling (invalid category)"
echo "Command:"
echo 'curl "http://localhost:8000/api/products/category/InvalidCategory"'
echo ""
echo "Expected: Error message with valid categories"
echo ""
echo "=========================================="
echo ""

# Pretty Print with jq (if available)
echo ""
echo "To see prettier JSON output, install jq and use:"
echo ""
echo 'curl "http://localhost:8000/api/products/search?q=samsung" | jq'
echo 'curl "http://localhost:8000/api/products/category/iPhone" | jq'
echo 'curl "http://localhost:8000/api/products/all" | jq'
echo ""
echo "=========================================="
echo ""
echo "Sample Response Format:"
echo '{
  "success": true,
  "query": "samsung",
  "count": 8,
  "data": [
    {
      "id": 1,
      "name": "Samsung Galaxy Z Fold7 [ONLINE EXCLUSIVE]",
      "category": "Samsung",
      "image_url": "https://down-my.img.susercontent.com/...",
      "price": "7199.00",
      "product_url": null,
      "retailer": "Shopee Malaysia",
      "scraped_at": "2026-02-01T22:40:24Z"
    },
    ...
  ]
}'
echo ""
