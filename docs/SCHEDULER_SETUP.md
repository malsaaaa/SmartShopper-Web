# Laravel Scheduler Setup for Automatic Web Scraping

SmartShopper now has automatic web scraping enabled using Laravel Scheduler!

## How It Works

The Laravel Scheduler runs the web scraper automatically without any manual intervention.

**Current Schedule:**
- ⏰ **Daily at 2:00 AM** (Malaysia Time)
- ⏭️ Prevents overlapping runs (won't run twice simultaneously)
- 📝 Logs all output to `storage/logs/scraper.log`

## Requirements

To enable the scheduler, you need to add a **cron job** on your server.

### Option 1: Hosting with cPanel/SSH Access

Add this single cron job that runs every minute:

```bash
* * * * * cd /path/to/smartshopper && php artisan schedule:run >> /dev/null 2>&1
```

**Steps:**
1. Log into cPanel or your hosting control panel
2. Go to **Cron Jobs**
3. Add a new cron job with the command above
4. Set it to run **Every minute** (most hosting providers support this)
5. Save

### Option 2: Linux/Unix Server

SSH into your server and edit crontab:

```bash
crontab -e
```

Add this line:

```bash
* * * * * cd /path/to/smartshopper && php artisan schedule:run >> /dev/null 2>&1
```

Save and exit (`:wq` in vim).

### Option 3: Windows Task Scheduler (Local Development)

Not recommended for production, but for testing:

1. Open **Task Scheduler**
2. Create a new basic task
3. Set trigger to **Every minute**
4. Set action to run: `php "C:\path\to\artisan" schedule:run`

## Configuration

Edit the schedule in `app/Console/Kernel.php`:

### Change Scrape Time

```php
$schedule->command('scraper:run')
    ->dailyAt('06:00')  // Change to 6:00 AM
    ->timezone('Asia/Kuala_Lumpur');
```

### Run Every 6 Hours Instead

```php
$schedule->command('scraper:run')
    ->everyThreeHours()  // Or everyTwoHours(), everySixHours()
    ->withoutOverlapping();
```

### Run Every Hour

```php
$schedule->command('scraper:run')
    ->hourly()
    ->withoutOverlapping();
```

### Available Frequencies

- `->everyMinute()` - Every minute
- `->everyFiveMinutes()` - Every 5 minutes
- `->everyTenMinutes()` - Every 10 minutes
- `->everyThirtyMinutes()` - Every 30 minutes
- `->hourly()` - Every hour
- `->everyThreeHours()` - Every 3 hours
- `->everySixHours()` - Every 6 hours
- `->dailyAt('02:00')` - Specific time daily
- `->weekly()` - Once per week
- `->monthly()` - Once per month

## Monitoring

### View Scraper Logs

```bash
tail -f storage/logs/scraper.log
```

Or check the file: `storage/logs/scraper.log`

### Manual Test

Run the scraper manually to test:

```bash
php artisan scraper:run
```

### Verify Scheduler is Working

```bash
# List all scheduled commands
php artisan schedule:list

# Run scheduler in debug mode
php artisan schedule:work
```

## What Gets Scraped

Each scheduled run:
1. ✅ Scrapes prices from all 4 retailers:
   - Lotus's
   - Mydin
   - Shopee
   - Lazada

2. ✅ Updates 9 products across 3 categories:
   - Maggi Noodles
   - Cooking Oil
   - Condensed Milk

3. ✅ Stores prices in database
4. ✅ Updates "Last Scrape" time on admin dashboard
5. ✅ Logs all activity to `storage/logs/scraper.log`

## Troubleshooting

### Scraper Not Running

1. **Check cron job exists:**
   ```bash
   crontab -l
   ```

2. **Verify scheduler is working:**
   ```bash
   php artisan schedule:list
   ```

3. **Run scheduler in foreground for debugging:**
   ```bash
   php artisan schedule:work
   ```

4. **Check logs:**
   ```bash
   tail -f storage/logs/scraper.log
   ```

### Common Issues

**Error: "Command not found"**
- Make sure `php artisan` path is correct in cron job

**Error: "Python not found"**
- Python may not be in cron's PATH
- Use full path to python: `/usr/bin/python3 scraper.py`

**Overlapping executions**
- Already prevented with `->withoutOverlapping()`
- If still occurring, check `storage/framework/schedule-*` lock files

## Dashboard Integration

The admin dashboard shows:
- **Last Scrape Time** - When the scraper last ran
- **Data Freshness** - How old current prices are (green = fresh)
- **Price Coverage** - % of products covered per retailer

The "Run Scraper" button on the admin dashboard still works for manual triggering anytime!

## Disabling Scheduler

To temporarily disable auto-scraping:

1. Edit `app/Console/Kernel.php`
2. Comment out the scraper schedule:
   ```php
   // $schedule->command('scraper:run')
   //     ->dailyAt('02:00');
   ```
3. Manual scraping via dashboard button will still work

## Next Steps

- Monitor logs regularly
- Adjust scrape frequency as needed
- Consider adding email notifications on errors
- Track scraping success rates in database
