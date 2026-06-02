# SmartShopper 🛒

**A comprehensive price comparison and product discovery platform for Malaysian e-commerce retailers.**

SmartShopper automatically scrapes product prices and availability across multiple Malaysian retailers (Shopee, Lazada, Mydin, Giant, Lotus's) and provides a unified search interface with real-time price comparisons.

---

## ✨ Features

- **🕷️ Automated Web Scraping** - Multi-retailer price scraping with intelligent bot detection bypass
- **📊 Price Comparison** - Compare prices across retailers in real-time
- **🔄 Auto-Scheduling** - Hourly automatic price updates via Laravel scheduler
- **🔍 Smart Search** - Full-text search across all products and retailers
- **📱 Responsive Design** - Mobile-friendly interface with Tailwind CSS
- **⚡ Fast API** - RESTful API for programmatic access
- **🛡️ Robust Scraping** - Multiple fallback methods (Selenium, Playwright, Apify, Firecrawl)
- **📈 Analytics** - Track price trends and product availability over time
- **🤖 Bot Detection Bypass** - Advanced techniques to avoid detection (Undetected Chrome, Apify integration)

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 10 (PHP 8.1+)
- **Database**: MySQL 8.0+
- **Task Scheduling**: Laravel Scheduler
- **API**: RESTful with JSON responses

### Frontend
- **Build Tool**: Vite
- **CSS Framework**: Tailwind CSS
- **JavaScript Framework**: Alpine.js
- **UI Components**: Tailwind Forms

### Scraping & Data Collection
- **Python 3.8+** with specialized libraries:
  - **Selenium** - JavaScript-heavy websites
  - **Playwright** - Modern browser automation
  - **BeautifulSoup** - HTML parsing
  - **Requests** - HTTP requests
  - **Undetected-chromedriver** - Anti-bot detection
  - **Apify** - Cloud-based scraping (recommended)
  - **Firecrawl** - Advanced web scraping with MCP

### Infrastructure
- **Version Control**: Git
- **Package Managers**: Composer, npm, pip
- **Development**: VS Code, Laravel Vite Plugin

---

## 📁 Project Structure

```
SmartShopper/
├── app/                           # Laravel application code
│   ├── Console/Commands/          # Artisan commands
│   │   └── ImportPrices.php      # Price scraping scheduler
│   ├── Http/Controllers/          # API controllers
│   ├── Models/                    # Database models
│   └── Services/                  # Business logic
│
├── scrapers/                      # Python scraping modules
│   ├── scrape_products.py        # Main scraper logic
│   ├── config.py                 # Retailer configurations
│   ├── manage.py                 # CLI management tool
│   ├── test_scraper.py           # Test suite
│   └── README.md                 # Scraper documentation
│
├── database/                      # Laravel migrations & seeders
│   ├── migrations/               # Database schema
│   └── seeders/                  # Sample data
│
├── resources/                     # Frontend assets
│   ├── css/                      # Tailwind styles
│   └── js/                       # Alpine.js components
│
├── routes/                        # API & web routes
│   ├── api.php                   # RESTful endpoints
│   └── web.php                   # Web routes
│
├── public/                        # Web root
├── config/                        # Laravel configuration
├── bootstrap/                     # Framework bootstrapping
├── storage/                       # Logs, cache, sessions
├── tests/                         # Test suites
├── vendor/                        # PHP dependencies (Composer)
├── node_modules/                  # JavaScript dependencies (npm)
├── .env                           # Environment variables
├── composer.json                  # PHP dependencies
├── package.json                   # Node.js dependencies
├── requirements.txt               # Python dependencies
├── tailwind.config.js             # Tailwind configuration
├── vite.config.js                 # Vite configuration
└── artisan                        # Laravel CLI tool
```

---

## 🚀 Quick Start

### Prerequisites

- **PHP 8.1+** with extensions: curl, json, mysql
- **MySQL 8.0+** (database server)
- **Python 3.8+** (for scraping)
- **Node.js 18+** (for frontend build tools)
- **Composer** (PHP package manager)
- **npm** or **yarn** (Node package manager)
- **Git** (version control)

### Setup (5 minutes)

#### 1️⃣ Clone & Install Dependencies
```bash
# Clone the repository
git clone <your-repo-url>
cd SmartShopper

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Install Python dependencies
pip install -r requirements.txt
```

#### 2️⃣ Configure Environment
```bash
# Copy environment template
cp .env.example .env

# Generate Laravel app key
php artisan key:generate

# Edit .env with your settings
# - DATABASE_* (MySQL connection)
# - APIFY_TOKEN (optional, for advanced scraping)
# - FIRECRAWL_API_KEY (optional, for web scraping)
```

#### 3️⃣ Setup Database
```bash
# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

#### 4️⃣ Build Frontend Assets
```bash
# Development mode (with hot reload)
npm run dev

# Production build
npm run build
```

#### 5️⃣ Start the Application
```bash
# In one terminal - Start Laravel server
php artisan serve
# App available at: http://localhost:8000

# In another terminal - Start frontend dev server (if needed)
npm run dev
```

#### 6️⃣ Run First Scrape
```bash
# Test the scraper
python3 scrapers/test_scraper.py

# Run manual scrape
php artisan prices:import

# Or use the Python CLI
python3 scrapers/manage.py run
```

---

## 📖 Usage Guide

### Web Interface

1. **Navigate to** `http://localhost:8000`
2. **Search** for products (e.g., "Samsung", "iPhone", "Gaming Laptop")
3. **Compare prices** across retailers
4. **View details** including availability and last update time
5. **Track history** of price changes over time

### REST API

The application provides a RESTful API for programmatic access.

#### Search Products
```bash
curl "http://localhost:8000/api/products/search?q=samsung"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Samsung Galaxy S24",
      "category": "Smartphones",
      "retailers": [
        {
          "name": "Shopee",
          "price": "3499.00",
          "currency": "MYR",
          "availability": "In Stock",
          "url": "https://shopee.com.my/..."
        },
        {
          "name": "Lazada",
          "price": "3599.00",
          "currency": "MYR",
          "availability": "Limited Stock",
          "url": "https://lazada.com.my/..."
        }
      ],
      "last_updated": "2026-06-02 14:30:00"
    }
  ]
}
```

#### Get Product Details
```bash
curl "http://localhost:8000/api/products/{id}"
```

#### Get All Products
```bash
curl "http://localhost:8000/api/products"
```

#### Filter by Retailer
```bash
curl "http://localhost:8000/api/products?retailer=shopee"
```

#### Filter by Price Range
```bash
curl "http://localhost:8000/api/products?min_price=1000&max_price=5000"
```

### Python Scraper CLI

The scraper includes a management tool for various operations:

```bash
python3 scrapers/manage.py [command]

# Available commands:
run      # Execute the scraper immediately
test     # Run test suite and validate setup
logs     # Display recent scraper logs
data     # Show currently scraped data in JSON
status   # Display system status and statistics
```

**Examples:**

```bash
# Start scraping now
python3 scrapers/manage.py run

# View scraped data
python3 scrapers/manage.py data

# Monitor logs
python3 scrapers/manage.py logs

# Check system status
python3 scrapers/manage.py status
```

### Laravel Artisan Commands

```bash
# Schedule a price import
php artisan prices:import

# View scheduled tasks
php artisan schedule:list

# Run scheduled tasks
php artisan schedule:work

# Create new product (via tinker)
php artisan tinker
>>> Product::create(['name' => '...', ...]);
```

---

## 🏗️ Architecture Overview

### Data Flow

```
┌─────────────────────────────────────────────┐
│   User Interface (Web Browser/API Client)   │
└────────────┬────────────────────────────────┘
             │ HTTP Request
             ↓
┌─────────────────────────────────────────────┐
│     Laravel HTTP Controllers/API Layer      │
│  (routes/api.php, Http/Controllers/*)      │
└────────────┬────────────────────────────────┘
             │ Data Query/Update
             ↓
┌─────────────────────────────────────────────┐
│      Laravel Models & Services              │
│  (app/Models/*, app/Services/*)            │
└────────────┬────────────────────────────────┘
             │ SQL Query
             ↓
┌─────────────────────────────────────────────┐
│        MySQL Database                       │
│  Tables: products, prices, retailers        │
└─────────────────────────────────────────────┘
```

### Scraping Flow

```
┌──────────────────────────────────────────┐
│  Laravel Scheduler (hourly trigger)      │
└────────────┬─────────────────────────────┘
             │
             ↓
┌──────────────────────────────────────────┐
│  ImportPrices Command (PHP)              │
│  app/Console/Commands/ImportPrices.php   │
└────────────┬─────────────────────────────┘
             │ Executes Python script
             ↓
┌──────────────────────────────────────────┐
│  Python Scraper (scrape_products.py)     │
│  ├─ Selenium (JS-heavy sites)            │
│  ├─ Playwright (modern browsers)         │
│  ├─ BeautifulSoup (HTML parsing)         │
│  ├─ Requests (API endpoints)             │
│  └─ Apify/Firecrawl (advanced)          │
└────────────┬─────────────────────────────┘
             │ JSON output
             ↓
┌──────────────────────────────────────────┐
│  Data Validation & Processing            │
└────────────┬─────────────────────────────┘
             │ Store prices
             ↓
┌──────────────────────────────────────────┐
│  MySQL Database (prices table updated)   │
└──────────────────────────────────────────┘
```

---

## ⚙️ Configuration

### Environment Variables (`.env`)

Key configuration options:

```bash
# Application
APP_NAME="SmartShopper"
APP_URL=http://localhost:8000
APP_ENV=local|production
APP_DEBUG=true|false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartshopper
DB_USERNAME=root
DB_PASSWORD=

# Scraping (Optional - for enhanced capabilities)
APIFY_TOKEN=your_token_here
FIRECRAWL_API_KEY=your_key_here

# Scheduler
SCHEDULE_INTERVAL=hourly  # How often to update prices
```

### Scraper Configuration (`scrapers/config.py`)

Define retailers, selectors, and rate limits:

```python
RETAILERS = {
    'shopee': {
        'name': 'Shopee Malaysia',
        'base_url': 'https://shopee.com.my',
        'product_selector': '.shopee-search-item-result',
        'rate_limit': 2,  # seconds between requests
    },
    # ... more retailers
}
```

---

## 🔧 Maintenance & Monitoring

### View Logs

```bash
# Laravel application logs
tail -f storage/logs/laravel.log

# Scraper logs
python3 scrapers/manage.py logs

# View specific scraper log file
tail -f scraper.log
```

### Database Maintenance

```bash
# Backup database
mysqldump -u root -p smartshopper > backup.sql

# Optimize tables
php artisan db:optimize

# Monitor query performance
php artisan telescope  # (if installed)
```

### Performance Tips

1. **Database Indexing** - Ensure indexes on `products.name`, `prices.product_id`
2. **Caching** - Enable query caching in `.env`
3. **Rate Limiting** - Adjust scraper delays in `scrapers/config.py`
4. **Async Processing** - Use job queues for heavy operations
5. **CDN** - Serve static assets via CDN in production

### Monitoring Scrapers

```bash
# Check scraper status
php artisan schedule:list

# View import history
php artisan tinker
>>> ImportLog::latest()->first();

# Monitor system resources
top  # or Task Manager on Windows
```

---

## 📊 Supported Retailers

Currently integrated retailers:

| Retailer | Status | Coverage | Update Frequency |
|----------|--------|----------|------------------|
| **Shopee** | ✅ Active | Electronics, Home & Living | Hourly |
| **Lazada** | ✅ Active | Gadgets, Fashion | Hourly |
| **Mydin** | ✅ Active | Grocery, FMCG | Hourly |
| **Giant** | ✅ Active | Grocery, FMCG | Hourly |
| **Lotus's** | ✅ Active | Pharmacy, Health | Daily |

To add a new retailer:

1. Add configuration to `scrapers/config.py`
2. Implement scraping logic in `scrape_products.py`
3. Test with `python3 scrapers/test_scraper.py`
4. Update documentation

---

## 🐛 Troubleshooting

### Common Issues & Solutions

**Problem: Scraper fails with bot detection**
```bash
# Solution: Use Apify integration (recommended)
1. Get token: https://apify.com
2. Set APIFY_TOKEN in .env
3. Run: python3 scrapers/manage.py run
```

**Problem: Database connection error**
```bash
# Check MySQL is running
mysql -u root -p

# Verify .env settings
php artisan config:show | grep DB_

# Recreate database
php artisan migrate:fresh
```

**Problem: Python dependencies not installing**
```bash
# Update pip
python -m pip install --upgrade pip

# Clear cache and reinstall
pip install --no-cache-dir -r requirements.txt

# For specific issues with Selenium
pip install --upgrade undetected-chromedriver
```

**Problem: Frontend assets not compiling**
```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Rebuild
npm run build
```

For more detailed troubleshooting, see [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

---

## 📚 Additional Documentation

- **[QUICK_START.md](QUICK_START.md)** - 30-second setup guide
- **[QUICK_START_SCRAPING.md](QUICK_START_SCRAPING.md)** - Scraper quick reference
- **[SCRAPING_IMPLEMENTATION.md](SCRAPING_IMPLEMENTATION.md)** - Technical scraping guide
- **[APIFY_QUICK_START.md](APIFY_QUICK_START.md)** - Cloud scraping with Apify
- **[FIRECRAWL_MCP_QUICK_REFERENCE.md](FIRECRAWL_MCP_QUICK_REFERENCE.md)** - Advanced web scraping
- **[VALIDATION_CHECKLIST.md](VALIDATION_CHECKLIST.md)** - Pre-deployment checklist
- **[scrapers/README.md](scrapers/README.md)** - Scraper documentation

---

## 🧪 Testing

### Run Test Suite

```bash
# Python tests
python3 scrapers/test_scraper.py

# Laravel tests
php artisan test

# Specific test file
php artisan test tests/Feature/ProductSearchTest.php

# With coverage
php artisan test --coverage
```

### Manual API Testing

**PowerShell:**
```powershell
# Search products
Invoke-RestMethod "http://localhost:8000/api/products/search?q=samsung" | ConvertTo-Json

# Get all products
Invoke-RestMethod "http://localhost:8000/api/products" | ConvertTo-Json
```

**Bash/Shell:**
```bash
# Search products
curl "http://localhost:8000/api/products/search?q=samsung"

# Get all products with filter
curl "http://localhost:8000/api/products?min_price=1000&max_price=5000"

# Get single product
curl "http://localhost:8000/api/products/1"
```

---

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Run database migrations: `php artisan migrate --force`
- [ ] Build frontend assets: `npm run build`
- [ ] Clear all caches: `php artisan cache:clear`
- [ ] Set up SSL certificate (HTTPS)
- [ ] Configure scheduler in crontab:
  ```bash
  * * * * * cd /path/to/smartshopper && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Enable log rotation
- [ ] Set up monitoring and alerting
- [ ] Test all scraping endpoints
- [ ] Backup database regularly

### Docker Deployment

Coming soon! We're preparing Docker Compose configuration.

---

## 📝 License

This project is licensed under the **MIT License**. See [LICENSE](LICENSE) file for details.

---

## 👥 Contributing

Contributions are welcome! Here's how to contribute:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Commit** changes: `git commit -m 'Add amazing feature'`
4. **Push** to branch: `git push origin feature/amazing-feature`
5. **Open** a Pull Request

### Development Guidelines

- Follow PSR-12 (PHP coding standards)
- Use meaningful commit messages
- Add tests for new features
- Update documentation
- Keep dependencies updated

---

## 📞 Support & Contact

For issues, questions, or suggestions:

1. **Check documentation** - See links above
2. **Search issues** - Look for existing solutions
3. **Create issue** - Use GitHub Issues with detailed description
4. **Contact** - Reach out to the development team

---

## 🙏 Acknowledgments

- **Laravel** framework and community
- **Playwright** for modern browser automation
- **Apify** for cloud-based scraping services
- **Tailwind CSS** for styling
- All contributors and testers

---

## 📈 Roadmap

Planned features for future releases:

- [ ] Price trend analytics and charts
- [ ] Price drop notifications
- [ ] Wishlist functionality
- [ ] Mobile app (React Native)
- [ ] Advanced filtering and sorting
- [ ] Telegram/Email notifications
- [ ] Docker containerization
- [ ] Multi-language support
- [ ] Web scraping with Firecrawl integration
- [ ] Machine learning price predictions

---

**Last Updated:** June 2, 2026  
**Version:** 1.0.0

Happy price comparing! 🎉

---

Made with ❤️ for smart shopping in Malaysia
