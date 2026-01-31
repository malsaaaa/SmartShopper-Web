# ParseHub Integration Guide

## Setup Steps

### 1. Create ParseHub Account
- Go to https://www.parsehub.com/
- Sign up and create account
- Get your **API Key** from Settings → Account → API Token

### 2. Create Projects for Each Retailer

#### **Mydin Project**
1. Create new project: "Mydin Maggi Search"
2. Start URL: `https://mydin.my/search?search=Maggi`
3. Add selections:
   - **Product Name**: Click on a product name (e.g., "Maggi 2-Minute Noodles")
   - **Price**: Click on the price text
   - **Image**: Click on product image
4. Click "Select Multiple" to get all products on page
5. Save and run project
6. Copy **Project Token** from URL or project page

#### **Lotus's Project**
1. Create project: "Lotus's Maggi Search"
2. Start URL: `https://www.lotuss.com.my/en/search/Maggi?sort=relevance:DESC`
3. Add same selections (Name, Price, Image)
4. Run and copy Project Token

#### **Shopee Project**
1. Create project: "Shopee Maggi Search"
2. Start URL: `https://shopee.com.my/supermarket?keyword=Maggi`
3. Add selections for product data
4. Note: May need JavaScript rendering for Shopee

#### **Lazada Project**
1. Create project: "Lazada Maggi Search"
2. Start URL: `https://www.lazada.com.my/catalog/?q=Maggi`
3. Add selections
4. Note: May need JavaScript rendering for Lazada

### 3. Add to .env File
```
PARSEHUB_API_KEY=your_api_key_here
```

## Usage

### Import from Mydin
```bash
php artisan prices:parsehub \
  --api-key=YOUR_API_KEY \
  --project-token=YOUR_MYDIN_PROJECT_TOKEN \
  --retailer=Mydin
```

### Import from Lotus's
```bash
php artisan prices:parsehub \
  --api-key=YOUR_API_KEY \
  --project-token=YOUR_LOTUS_PROJECT_TOKEN \
  --retailer="Lotus's"
```

### Import from Shopee
```bash
php artisan prices:parsehub \
  --api-key=YOUR_API_KEY \
  --project-token=YOUR_SHOPEE_PROJECT_TOKEN \
  --retailer=Shopee
```

### Import from Lazada
```bash
php artisan prices:parsehub \
  --api-key=YOUR_API_KEY \
  --project-token=YOUR_LAZADA_PROJECT_TOKEN \
  --retailer=Lazada
```

## ParseHub Project Tips

- **JavaScript Rendering**: For Shopee/Lazada, enable "JavaScript Rendering" in project settings
- **Pagination**: Use ParseHub's "click more" feature to load additional products
- **Data Formatting**: Ensure price extraction removes currency symbols (RM, etc)
- **Scheduling**: Set projects to run on schedule (e.g., daily, weekly)
- **API Rate Limit**: Free tier has limits; check documentation

## Comparison: Python Scraper vs ParseHub

| Feature | Python Scraper | ParseHub |
|---------|----------------|----------|
| Setup | Code-based | Visual point-and-click |
| Maintenance | Custom logic | ParseHub maintains selectors |
| Cost | Free | Free tier available |
| JavaScript | Selenium driver | Built-in support |
| Learning Curve | Steep | Easier |
| Speed | Slower (browser overhead) | Faster (optimized) |
| Scheduling | Manual script | Built-in scheduling |

## Hybrid Approach (Recommended)

Use **Python scraper** for:
- Mydin (most reliable static HTML)
- Development/testing

Use **ParseHub** for:
- Shopee (dynamic JavaScript)
- Lazada (dynamic JavaScript)
- Automated scheduled runs

This way you get best of both worlds!
