# 🚀 QUICK START GUIDE - Crawler Migration

## In 30 Seconds...

Your scraped Shopee products are now **live in the database** and searchable through the crawler system!

### What's Working Right Now

✅ **Samsung Search** → 8 products with prices  
✅ **iPhone Search** → 5 products with prices  
✅ **Gaming Laptop Search** → 6 products with prices  

---

## Getting Started

### Step 1: Verify Everything Works
```bash
php test_product_search.php
```

Expected output: All 19 products loaded, all 3 search categories working ✓

### Step 2: Start the Laravel Server
```bash
php artisan serve
```

Server runs on: `http://localhost:8000`

### Step 3: Test the API

**Option A: PowerShell (Windows)**
```powershell
# Search for Samsung
Invoke-RestMethod "http://localhost:8000/api/products/search?q=samsung" | ConvertTo-Json

# Search for iPhone  
Invoke-RestMethod "http://localhost:8000/api/products/search?q=iphone" | ConvertTo-Json

# Search for Gaming Laptop
Invoke-RestMethod "http://localhost:8000/api/products/search?q=gaming laptop" | ConvertTo-Json
```

**Option B: curl (Command Line)**
```bash
curl "http://localhost:8000/api/products/search?q=samsung"
curl "http://localhost:8000/api/products/search?q=iphone"
curl "http://localhost:8000/api/products/search?q=gaming laptop"
```

**Option C: Browser**
```
Visit: http://localhost:8000/api/products/search?q=samsung
```

---

## API Endpoints Overview

| Endpoint | Method | Returns |
|----------|--------|---------|
| `/api/products/search?q=samsung` | GET | 8 Samsung products |
| `/api/products/search?q=iphone` | GET | 5 iPhone products |
| `/api/products/search?q=gaming laptop` | GET | 6 Gaming Laptop products |
| `/api/products/category/Samsung` | GET | 8 Samsung products |
| `/api/products/category/iPhone` | GET | 5 iPhone products |
| `/api/products/category/Gaming%20Laptop` | GET | 6 Gaming Laptop products |
| `/api/products/all` | GET | All 19 products |

---

## What Was Created

### Backend
- ✅ **ProductSearchController.php** - Handles product searches
- ✅ **ShopeeProductSeeder.php** - Loads products from JSON to database
- ✅ **API Routes** - 3 new search endpoints

### Documentation (8 files)
- ✅ COMPLETION_REPORT.md - Final verification report
- ✅ MIGRATION_SUMMARY.md - Quick overview
- ✅ CRAWLER_MIGRATION_COMPLETE.md - Technical details
- ✅ CRAWLER_INTEGRATION_VISUAL_GUIDE.md - Diagrams & flows
- ✅ API_RESPONSE_EXAMPLES.json - Response examples
- ✅ FILE_INVENTORY.md - Complete file listing
- ✅ TEST_API_COMMANDS.sh - Bash test script
- ✅ TEST_API_COMMANDS.ps1 - PowerShell test script

---

## Database Status

```
Total Products: 19
├── Samsung: 8 products (RM 399 - RM 7,199)
├── iPhone: 5 products (RM 4,185 - RM 5,999)
└── Gaming Laptop: 6 products (RM 1,092 - RM 3,799)

Retailer: Shopee Malaysia
Status: ✅ All products linked to retailer with prices
```

---

## Example Response

When you search for "samsung", you get:

```json
{
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
    ... 7 more products ...
  ]
}
```

---

## Frontend Integration (JavaScript)

```javascript
// Basic search
async function searchProducts(query) {
  const response = await fetch(`/api/products/search?q=${query}`);
  const data = await response.json();
  
  if (data.success) {
    console.log(`Found ${data.count} products:`);
    data.data.forEach(product => {
      console.log(`${product.name} - RM ${product.price}`);
    });
  }
}

// Usage
await searchProducts('samsung');     // 8 results
await searchProducts('iphone');      // 5 results
await searchProducts('gaming laptop'); // 6 results
```

---

## Common Commands

### View Products in Database
```bash
php artisan tinker
> App\Models\Product::count()
19

> App\Models\Product::where('category', 'Samsung')->count()
8

> App\Models\Product::where('name', 'LIKE', '%iPhone%')->count()
5
```

### Re-seed Products (if needed)
```bash
php artisan db:seed --class=ShopeeProductSeeder
```

### Fresh Database (Warning: Clears all data)
```bash
php artisan migrate:fresh
php artisan db:seed
```

---

## Documentation Files

### For Quick Overview
- **COMPLETION_REPORT.md** - See what was done and verify it works
- **MIGRATION_SUMMARY.md** - 2-page overview of the system

### For Technical Details
- **CRAWLER_MIGRATION_COMPLETE.md** - Complete API documentation
- **CRAWLER_INTEGRATION_VISUAL_GUIDE.md** - Visual diagrams and flows

### For Examples
- **API_RESPONSE_EXAMPLES.json** - Real response examples
- **FILE_INVENTORY.md** - Complete file listing and purposes

### For Testing
- **TEST_API_COMMANDS.ps1** - PowerShell test commands
- **TEST_API_COMMANDS.sh** - Bash test commands

---

## Troubleshooting

**Q: API returns 404 error**  
A: Make sure Laravel server is running: `php artisan serve`

**Q: Products not found**  
A: Run: `php test_product_search.php` to verify seeding worked

**Q: Wrong number of results**  
A: Check database: `php artisan tinker` then `App\Models\Product::count()`

**Q: Prices showing as 0**  
A: Re-seed: `php artisan db:seed --class=ShopeeProductSeeder`

---

## Next Steps (Optional)

### Add More Products
1. Run new Shopee scraper for different search terms
2. Merge JSON files
3. Re-seed: `php artisan db:seed --class=ShopeeProductSeeder`

### Add Other Retailers
1. Create new seeder for Lazada/Tokopedia
2. Products become searchable across retailers
3. Show best prices from all sources

### Build Frontend UI
1. Create search input component
2. Call `/api/products/search?q=` endpoint
3. Display product cards with images
4. Add links to Shopee product pages

---

## Status

✅ **COMPLETE & VERIFIED**

- 19 products migrated ✓
- 3 search terms working ✓
- API endpoints live ✓
- All tests passing ✓
- Documentation complete ✓

**Ready to deploy!** 🚀

---

## Questions?

Refer to:
- **API details** → CRAWLER_MIGRATION_COMPLETE.md
- **Visual flows** → CRAWLER_INTEGRATION_VISUAL_GUIDE.md
- **Examples** → API_RESPONSE_EXAMPLES.json
- **File purposes** → FILE_INVENTORY.md
