# Telegram Bot Setup Guide for SmartShopper

## Overview
SmartShopper now includes an interactive Telegram bot that allows users to search for products and compare prices directly from Telegram!

## Setup Steps

### 1. Create a Telegram Bot (One-time Setup)

1. Open Telegram and search for **@BotFather**
2. Start the conversation with `/start`
3. Type `/newbot`
4. Follow the prompts:
   - Choose a name for your bot (e.g., "SmartShopper Bot")
   - Choose a username (e.g., "smartshopper_bot")
5. BotFather will give you a **TOKEN** - save this!

### 2. Configure Bot Token

Add the bot token to your `.env` file:

```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
```

Replace `your_bot_token_here` with the actual token from BotFather.

### 3. Set Up Webhook

Once your application is deployed and accessible via HTTPS, run:

```bash
php artisan tinker
```

Then in Tinker:
```php
app('TelegramBotController')->setWebhook()
```

Or visit: `https://your-domain.com/telegram/set-webhook`

**Note:** Telegram requires HTTPS for webhooks. For local development, you can use:
- ngrok (tunnel local server to HTTPS)
- Laravel Valet with custom SSL
- Deploy to staging first

### 4. Test the Bot

1. Search for your bot on Telegram (using the username)
2. Start the bot with `/start`
3. Type a product name (e.g., "milo", "maggi", "coffee")
4. The bot will show prices from all available retailers

## Bot Features

### Commands
- **`/start`** - Shows welcome message and instructions
- **`/help`** - Shows available commands and how to use
- **`/prices`** - Help for searching prices

### Search Functionality
- Simply type any product name
- Bot searches database and returns:
  - Product name and category
  - Prices from all retailers
  - Best price indicator
  - Last updated timestamp

### Supported Products
- **Milo** - Chocolate malt beverages
- **Maggi** - Instant noodles and seasonings
- **Coffee** - Various instant coffee brands
- **Samsung** - Electronics
- Any other product in the database

## Database Requirements

The bot queries the `products` table and joins with `prices` and `retailers` tables. Make sure:

1. Products are in the database with categories
2. Prices are linked to retailers
3. Tables are properly migrated:

```bash
php artisan migrate
```

## API Endpoints

### Webhook (Handles messages)
```
POST /telegram/webhook
```
This is where Telegram sends user messages. Configured automatically when you set the webhook.

### Set Webhook
```
GET /telegram/set-webhook
```
Manually sets the webhook URL on Telegram servers.

### Get Bot Info
```
GET /telegram/bot-info
```
Returns bot information (name, username, etc.)

## Bot Response Examples

### Search for "milo"
```
📦 Milo Activ-Go Chocolate Malt Beverage 400g
📂 Category: Grocery
━━━━━━━━━━━━━━━━━━━━━

🏪 Mydin
💰 RM 13.99
📅 Feb 01, 15:40

🏪 Shopee
💰 RM 12.50
📅 Feb 01, 15:40

✅ Best Price: RM 12.50 at Shopee
```

## Troubleshooting

### Bot Not Responding
1. Check if `TELEGRAM_BOT_TOKEN` is set in `.env`
2. Verify webhook is set: Visit `/telegram/set-webhook`
3. Check Laravel logs: `storage/logs/laravel.log`

### Webhook Not Working
- Ensure domain is publicly accessible via HTTPS
- Check firewall/network restrictions
- Run: `php artisan tinker` → `app('TelegramBotController')->getBotInfo()`

### No Products Found
- Check if products exist in database: `php artisan tinker` → `App\Models\Product::count()`
- Verify products have prices and retailer relationships
- Check search query formatting

## Security Considerations

1. **Webhook Verification**: Currently not implemented. Add webhook token verification for production:
```php
// In TelegramBotController.php
$webhookToken = env('TELEGRAM_WEBHOOK_TOKEN');
if ($request->header('X-Telegram-Bot-Api-Secret-Token') !== $webhookToken) {
    return response()->json(['error' => 'Invalid token'], 403);
}
```

2. **Rate Limiting**: Add rate limiting to prevent abuse
3. **Message Validation**: Validate all incoming messages

## Future Enhancements

- [ ] Add price alerts via Telegram
- [ ] Show retailer-specific deals
- [ ] User preferences and favorites
- [ ] Budget tracking via bot
- [ ] Shopping list sharing
- [ ] Inline buttons for product actions
- [ ] Image search for products
- [ ] Multi-language support

## Local Testing with ngrok

For local development testing:

```bash
# Install ngrok if not installed
brew install ngrok

# Start ngrok tunnel
ngrok http 8000

# Your HTTPS URL will be like: https://xxxx-xx-xxx-xxx-xx.ngrok.io

# Update .env
TELEGRAM_WEBHOOK_URL=https://xxxx-xx-xxx-xxx-xx.ngrok.io/telegram/webhook

# Set webhook
php artisan tinker
app('TelegramBotController')->setWebhook()
```

## File Structure

```
SmartShopper/
├── app/Http/Controllers/
│   └── TelegramBotController.php       # Bot logic
├── routes/
│   └── web.php                         # Bot routes
├── .env                                # Bot token configuration
└── storage/logs/
    └── laravel.log                     # Debug logs
```

## Command History

Set up bot with:
```bash
# Already done - controller and routes added
php artisan cache:clear
php artisan config:cache
```

Verify setup:
```bash
php artisan tinker
app('TelegramBotController')->getBotInfo()
```

## Support

For issues or improvements:
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Test endpoints manually
3. Review Telegram Bot API docs: https://core.telegram.org/bots/api
