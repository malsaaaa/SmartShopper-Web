# SmartShopper Web

**A Laravel web app for comparing product and grocery prices across Malaysian retailers, with shopping lists, a Gemini chatbot, a Telegram bot and an admin dashboard.**

SmartShopper collects prices from retailer websites with Python scrapers, stores them in MySQL, and lets shoppers search, compare prices and plan a shopping budget. This is the web version of the project. The Flutter mobile app is in [smartshopper-mobile](https://github.com/malsaaaa/smartshopper-mobile).

<!-- Add screenshots here, for example:
<p align="center">
  <img src="docs/screenshots/dashboard.png" alt="Dashboard" width="700">
</p>
-->

---

## Features

### For shoppers
- **Price comparison.** Search a product and see its price at each retailer, with links to the retailer's page.
- **Shopping and budget lists.** Add items with quantities, notes, wishlist marks and photos, set a spending limit, and watch the running total. Lists can be shared with other users.
- **Price reports.** Report a wrong or outdated price. Admins review the reports.
- **AI shopping assistant.** A chat page powered by Google Gemini (`gemini-2.0-flash`) with saved conversations.
- **Telegram bot.** Log in with a Telegram link, check your list and prices, see trending products and deals, and receive a warning when your spending goes over your budget limit.
- **Accounts.** Registration, email verification, password reset, profile page and profile picture (Laravel Breeze).

### For admins
- Manage **products**, **retailers** (with logos), and **prices**.
- Review **price reports** and update their status.
- Manage **users**, including who is an admin.
- **Scraper settings:** turn automatic scraping on or off, choose the frequency and time, and see the last run's status.

### Data collection
- Python scrapers for **Mydin**, **Lotus's**, **Shopee** and **Lazada**, run by the Laravel scheduler through `php artisan scraper:run`.
- Several scraping back ends for hard-to-scrape pages: Selenium and undetected Chrome, Playwright, [Apify](https://apify.com/) and [Firecrawl](https://www.firecrawl.dev/).
- Import and clean-up commands for prices (see [Artisan commands](#artisan-commands)).
- Sample product data in the JSON files at the project root, loaded by the database seeders.

### Public JSON API

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/products/search?q=...` | Search products |
| `GET` | `/api/products/all` | All products |
| `GET` | `/api/products/category/{category}` | Products in a category |

## Tech stack

| Layer | Technology |
| :--- | :--- |
| Backend | PHP 8.1+, Laravel 10, Laravel Breeze, Sanctum |
| Database | MySQL |
| Front end | Blade templates, Tailwind CSS, Alpine.js, Vite |
| Scraping | Python 3.8+: Selenium, undetected-chromedriver, Playwright, BeautifulSoup, Apify client, Firecrawl |
| AI | Google Gemini API |
| Bot | Telegram Bot API (webhook) |
| Tests | PHPUnit |

## Project structure

```
SmartShopper/
├── app/
│   ├── Console/Commands/      # scraper:run, prices:import and other Artisan commands
│   ├── Http/Controllers/      # Budget, Chat, Telegram bot, Admin, Price report, Product search
│   ├── Models/                # Product, Retailer, Price, ShoppingList, Chat, ScraperSetting ...
│   └── Services/              # Scraping helper services
├── database/
│   ├── migrations/            # Schema
│   └── seeders/               # Users, retailers, products and sample prices
├── resources/views/           # Blade views: dashboard, search, budget list, chat, admin
├── routes/                    # web.php and api.php
├── scrapers/                  # Python scrapers (see scrapers/README.md)
├── docs/                      # Setup notes for the chatbot, scheduler and Telegram bot
├── tests/                     # PHPUnit feature and unit tests
├── firecrawl_mcp_server.py    # Firecrawl helper used by the Shopee scraper
├── *.json                     # Sample scrape results used as seed data
└── requirements.txt           # Python dependencies
```

## Getting started

### Requirements
- PHP 8.1 or newer with the usual Laravel extensions, and [Composer](https://getcomposer.org/)
- MySQL 8
- Node.js 18+ and npm
- Python 3.8+ (only for scraping)
- Google Chrome (for the Selenium scrapers)

### Install

```bash
git clone https://github.com/malsaaaa/SmartShopper.git
cd SmartShopper

composer install
npm install
pip install -r requirements.txt
```

### Configure

Copy the environment file and create an app key:

```bash
cp .env.example .env          # on Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

Edit `.env`: set the `DB_*` values for your MySQL database and `APP_NAME`. Optional keys, all of which you should keep out of version control:

```env
GEMINI_API_KEY=            # AI chat
TELEGRAM_BOT_TOKEN=        # Telegram bot
TELEGRAM_WEBHOOK_URL=
TELEGRAM_WEBHOOK_TOKEN=
APIFY_API_TOKEN=           # Apify scraping (optional)
FIRECRAWL_API_KEY=         # Firecrawl scraping (optional)
```

### Set up the database and run

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Open http://localhost:8000. For front-end development, run `npm run dev` in a second terminal.

> **The seeders create two demo accounts** (an admin and a student) with simple passwords that appear in `database/seeders/UserSeeder.php`. Change those passwords, or remove the accounts, before putting the app on a public server.

### Run the scraper

```bash
php artisan scraper:run
```

For automatic scraping, run the Laravel scheduler (`php artisan schedule:work` in development, or a cron entry in production) and set the frequency in the admin **Scraper settings** page. If the database is not ready yet, it defaults to a daily run at 02:00 (Asia/Kuala_Lumpur). More details are in [`scrapers/README.md`](scrapers/README.md) and [`docs/SCHEDULER_SETUP.md`](docs/SCHEDULER_SETUP.md).

### Artisan commands

| Command | Purpose |
| :--- | :--- |
| `scraper:run` | Run the Python scraper and import the results |
| `prices:import` | Import scraped prices |
| `prices:parsehub` | Import prices from a ParseHub project |
| `prices:populate-quick` | Quickly fill sample prices |
| `populate:products-prices` | Fill sample products and prices |
| `cleanup:duplicate-prices` | Remove duplicate price rows |

## Documentation

- [Chatbot setup](docs/CHATBOT_SETUP.md)
- [Telegram bot setup](docs/TELEGRAM_BOT_SETUP.md)
- [Scheduler setup](docs/SCHEDULER_SETUP.md)
- [Troubleshooting](docs/TROUBLESHOOTING.md)
- [Scrapers](scrapers/README.md)

## Tests

```bash
php artisan test
```

The suite covers the budget list, price reports and profile pages, plus a product unit test.

## Limitations

- **Scraping is fragile.** Retailer sites change their layout and use bot detection, so scrapers need maintenance. Shopee in particular is difficult to scrape reliably.
- **The "price alert" button is a placeholder** and does not yet send notifications. Telegram budget warnings do work.
- The sample JSON files are old snapshots, so their prices are not current.
- Built for Malaysian retailers and Malaysian ringgit.

## Data and scraping notice

Prices are collected from public retailer websites for **academic and demonstration purposes**. This project is not affiliated with or endorsed by Mydin, Lotus's, Shopee or Lazada. Store names and logos belong to their owners. Check each retailer's terms of use before scraping any site, and confirm prices in store or on the retailer's site before buying.

## License

Copyright (c) 2026 malsaaaa. All rights reserved.

This repository is public for viewing and portfolio purposes. No license is granted to copy, modify, distribute or use the code without the author's written permission.
