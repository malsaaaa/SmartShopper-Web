<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;

class TelegramBotController extends Controller
{
    private $botToken;
    private $apiUrl = 'https://api.telegram.org/bot';
    private $resultsPerPage = 5;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN', '');
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function handleWebhook(Request $request)
    {
        $update = $request->all();
        
        \Log::info('Telegram webhook received', $update);

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle message from user
     */
    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Handle commands
        if (strpos($text, '/') === 0) {
            $this->handleCommand($chatId, $text);
        } else {
            // Handle keyboard button presses
            switch ($text) {
                case '🔍 Search':
                    $this->sendMessage($chatId, "Type a product name to search (e.g., milo, maggi, samsung)");
                    return;
                case '📊 Trending':
                    $this->showTrendingProducts($chatId);
                    return;
                case '🛒 Shopping List':
                    $this->showShoppingList($chatId);
                    return;
                case '⚙️ Settings':
                    $this->showSettings($chatId);
                    return;
                case '❓ Help':
                    $this->sendMessage($chatId, $this->getHelpMessage(), 'Markdown');
                    return;
            }
            
            // Search for products
            $this->searchProducts($chatId, $text, 1);
        }
    }

    /**
     * Handle callback queries from inline buttons
     */
    private function handleCallbackQuery($callbackQuery)
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];

        $parts = explode(':', $data);
        $action = $parts[0] ?? '';

        try {
            switch ($action) {
                case 'search_page':
                    $query = $parts[1] ?? '';
                    $page = (int)($parts[2] ?? 1);
                    $this->searchProducts($chatId, $query, $page);
                    break;

                case 'filter_retailer':
                    $query = $parts[1] ?? '';
                    $retailer = $parts[2] ?? '';
                    $this->filterByRetailer($chatId, $query, $retailer);
                    break;

                case 'sort_price':
                    $query = $parts[1] ?? '';
                    $this->sortByPrice($chatId, $query);
                    break;

            }

            $this->answerCallbackQuery($callbackId, '✅ Done!');
        } catch (\Exception $e) {
            $this->answerCallbackQuery($callbackId, '❌ Error');
        }
    }

    /**
     * Handle commands
     */
    private function handleCommand($chatId, $command)
    {
        $parts = explode(' ', $command);
        $baseCommand = $parts[0];

        switch ($baseCommand) {
            case '/start':
                $this->sendMessage($chatId, $this->getWelcomeMessage(), 'Markdown');
                
                // Send main menu keyboard
                $this->sendMessageWithKeyboard($chatId, "Choose what you'd like to do:", [
                    ['🔍 Search', '📊 Trending'],
                    ['🛒 Shopping List', '⚙️ Settings'],
                    ['❓ Help']
                ]);
                break;

            case '/help':
                $this->sendMessage($chatId, $this->getHelpMessage(), 'Markdown');
                break;

            case '/settings':
                $this->showSettings($chatId);
                break;

            case '/list':
                $this->showShoppingList($chatId);
                break;

            case '/trending':
                $this->showTrendingProducts($chatId);
                break;

            case '/deals':
                $this->showDeals($chatId);
                break;

            case '/prices':
                if (isset($parts[1])) {
                    $query = implode(' ', array_slice($parts, 1));
                    $this->searchProducts($chatId, $query, 1);
                } else {
                    $this->sendMessage($chatId, "🔍 Please specify a product. Example: /prices milo");
                }
                break;

            case '/login':
                $this->generateLoginLink($chatId, $command);
                break;

            case '/connect':
                // Get telegram user ID from message context
                $message = request()->json()->all()['message'] ?? [];
                $telegramUserId = $message['from']['id'] ?? null;

                if (!$telegramUserId) {
                    $this->sendMessage($chatId, "❌ Unable to retrieve your Telegram ID. Please try again.");
                    return;
                }

                // Generate connection code (first 8 chars of md5)
                $code = substr(md5($chatId), 0, 8);

                // Store in cache (expires in 15 minutes)
                Cache::put(
                    "telegram_connect_{$code}",
                    [
                        'telegram_id' => $telegramUserId,
                        'chat_id' => $chatId,
                        'created_at' => now()
                    ],
                    now()->addMinutes(15)
                );

                $this->sendMessage($chatId, "🔗 *Connect Telegram to Your Account*\n\n" . 
                    "To link your existing website account to Telegram:\n\n" .
                    "1. Visit your dashboard settings\n" .
                    "2. Look for 'Connect Telegram'\n" .
                    "3. Enter this code: `" . $code . "`\n\n" .
                    "Then type /list to see your shopping items!", 'Markdown');
                break;

            default:
                $this->sendMessage($chatId, "❌ Unknown command. Type /help for available commands.");
        }
    }

    /**
     * Search products with pagination
     */
    private function searchProducts($chatId, $query, $page = 1)
    {
        if (empty(trim($query))) {
            $this->sendMessage($chatId, "Please enter a product name to search.");
            return;
        }

        $cacheKey = 'telegram_search:' . $chatId . ':' . md5($query);
        $allProducts = Cache::remember($cacheKey, 3600, function () use ($query) {
            return Product::where('name', 'LIKE', "%{$query}%")
                ->with('prices.retailer')
                ->get();
        });

        if ($allProducts->isEmpty()) {
            $this->sendMessage($chatId, "❌ No products found for '*$query*'", 'Markdown');
            return;
        }

        $total = $allProducts->count();
        $pages = ceil($total / $this->resultsPerPage);
        $offset = ($page - 1) * $this->resultsPerPage;
        $products = $allProducts->slice($offset, $this->resultsPerPage);

        foreach ($products as $product) {
            $this->sendProductWithButtons($chatId, $product, $query);
        }

        if ($pages > 1) {
            $this->sendPaginationButtons($chatId, $query, $page, $pages);
        }

        $this->sendMessage($chatId, "📊 Showing " . $products->count() . " of $total results (Page $page/$pages)");
    }

    /**
     * Send product with interactive buttons
     */
    private function sendProductWithButtons($chatId, $product, $query)
    {
        $text = "📦 *" . $product->name . "*\n";
        $text .= "📂 Category: " . $product->category . "\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━\n";

        if (!$product->prices->isEmpty()) {
            $pricesByRetailer = $product->prices->groupBy('retailer.name');
            
            foreach ($pricesByRetailer as $retailerName => $prices) {
                $emoji = $retailerName === 'Mydin' ? '🔵' : '🟠';
                $text .= "\n$emoji *" . $retailerName . "*\n";
                
                foreach ($prices as $price) {
                    $text .= "💰 RM " . number_format($price->amount, 2) . " 📅 " . $price->scraped_at->format('M d') . "\n";
                }
            }
            
            $bestPrice = $product->prices->sortBy('amount')->first();
            if ($bestPrice) {
                $text .= "\n🏆 *Best Price:* RM " . number_format($bestPrice->amount, 2) . " at " . $bestPrice->retailer->name;
            }
        } else {
            $text .= "❌ No prices available\n";
        }

        $buttons = [
            [
                ['text' => '📊 Sort Price', 'callback_data' => 'sort_price:' . $query]
            ],
            [
                ['text' => '🔵 Mydin', 'callback_data' => 'filter_retailer:' . $query . ':Mydin'],
                ['text' => '🟠 Shopee', 'callback_data' => 'filter_retailer:' . $query . ':Shopee']
            ]
        ];

        $this->sendMessageWithButtons($chatId, $text, $buttons, 'Markdown');
    }

    /**
     * Send pagination buttons
     */
    private function sendPaginationButtons($chatId, $query, $currentPage, $totalPages)
    {
        $buttons = [];
        $row = [];

        if ($currentPage > 1) {
            $row[] = ['text' => '⬅️ Prev', 'callback_data' => 'search_page:' . $query . ':' . ($currentPage - 1)];
        }

        $row[] = ['text' => $currentPage . '/' . $totalPages, 'callback_data' => 'page_info'];

        if ($currentPage < $totalPages) {
            $row[] = ['text' => 'Next ➡️', 'callback_data' => 'search_page:' . $query . ':' . ($currentPage + 1)];
        }

        $buttons[] = $row;
        $this->sendMessageWithButtons($chatId, "Page Navigation", $buttons);
    }

    /**
     * Filter products by retailer
     */
    private function filterByRetailer($chatId, $query, $retailer)
    {
        $cacheKey = 'telegram_search:' . $chatId . ':' . md5($query);
        $allProducts = Cache::get($cacheKey) ?? Product::where('name', 'LIKE', "%{$query}%")->with('prices.retailer')->get();

        $filtered = $allProducts->filter(function ($product) use ($retailer) {
            return $product->prices->contains(function ($price) use ($retailer) {
                return $price->retailer->name === $retailer;
            });
        });

        if ($filtered->isEmpty()) {
            $this->sendMessage($chatId, "❌ No products from '$retailer'");
            return;
        }

        $this->sendMessage($chatId, "🔍 *Results from $retailer:*", 'Markdown');
        foreach ($filtered->take(5) as $product) {
            $this->sendProductWithButtons($chatId, $product, $query);
        }
    }

    /**
     * Sort products by price
     */
    private function sortByPrice($chatId, $query)
    {
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->with('prices.retailer')
            ->get()
            ->sortBy(function ($product) {
                return $product->prices->min('amount') ?? PHP_INT_MAX;
            });

        if ($products->isEmpty()) {
            $this->sendMessage($chatId, "❌ No products found");
            return;
        }

        $this->sendMessage($chatId, "📊 *Sorted by Price (Lowest First):*", 'Markdown');
        foreach ($products->take(5) as $product) {
            $bestPrice = $product->prices->sortBy('amount')->first();
            $text = "💰 *" . $product->name . "* - RM " . number_format($bestPrice->amount ?? 0, 2) . " at " . ($bestPrice->retailer->name ?? 'Unknown');
            $this->sendMessage($chatId, $text, 'Markdown');
        }
    }

    /**
     * Show shopping list
     */
    private function showShoppingList($chatId)
    {
        try {
            \Log::info("Debug /list command for chat ID: $chatId");
            
            // Find user by telegram chat ID
            $user = \App\Models\User::where('telegram_chat_id', $chatId)->first();
            \Log::info("User found: " . ($user ? "ID {$user->id}" : 'null'));

            if (!$user) {
                $this->sendMessage($chatId, "❌ Please login first using /login command");
                return;
            }

            // Get user's shopping items - try multiple ways
            $shoppingItems = \App\Models\ShoppingItem::where('user_id', $user->id)
                ->with('product')
                ->get();
            
            \Log::info("Shopping items found: " . $shoppingItems->count());
            \Log::info("User shopping items: " . json_encode($shoppingItems->pluck('id')->toArray()));

            if ($shoppingItems->isEmpty()) {
                $this->sendMessage($chatId, "🛒 *Shopping List*\n\n📭 Your list is empty\n\nVisit your dashboard to add items:\n" . env('APP_URL', 'https://smartshopper.local'), 'Markdown');
                return;
            }

            $text = "🛒 *Your Shopping List*\n";
            $text .= "━━━━━━━━━━━━━━━━━━━━━\n\n";

            $total = 0;
            $itemCount = 0;
            
            foreach ($shoppingItems as $item) {
                try {
                    $product = $item->product;
                    
                    // If no product relationship, try to get from product_id or name
                    if (!$product && $item->product_id) {
                        $product = \App\Models\Product::find($item->product_id);
                    }
                    
                    if (!$product) {
                        // Fallback to name if product doesn't exist
                        $name = $item->name ?? 'Unknown Product';
                    } else {
                        $name = $product->name;
                    }
                    
                    $quantity = $item->quantity ?? 1;
                    $price = $item->estimated_cost ?? $item->price ?? 0;
                    $estimatedCost = $price * $quantity;
                    $total += $estimatedCost;
                    $itemCount++;

                    $text .= "📦 *" . $name . "*\n";
                    $text .= "   Qty: " . $quantity . " | Est: RM " . number_format($estimatedCost, 2) . "\n";
                } catch (\Exception $itemError) {
                    \Log::error("Error processing item: " . $itemError->getMessage());
                    continue;
                }
            }

            if ($itemCount === 0) {
                $this->sendMessage($chatId, "🛒 *Shopping List*\n\n📭 Your list is empty\n\nVisit your dashboard to add items:\n" . env('APP_URL', 'https://smartshopper.local'), 'Markdown');
                return;
            }

            $text .= "\n━━━━━━━━━━━━━━━━━━━━━\n";
            $text .= "💰 *Total Estimated:* RM " . number_format($total, 2) . "\n\n";
            $text .= "Visit your dashboard to manage your list:\n" . env('APP_URL', 'https://smartshopper.local');

            $this->sendMessage($chatId, $text, 'Markdown');
        } catch (\Exception $e) {
            \Log::error('Telegram /list command error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->sendMessage($chatId, "❌ Error: " . $e->getMessage());
        }
    }

    /**
     * Show trending products
     */
    private function showTrendingProducts($chatId)
    {
        $trending = Product::with('prices.retailer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($trending->isEmpty()) {
            $this->sendMessage($chatId, "❌ No trending products");
            return;
        }

        $this->sendMessage($chatId, "🔥 *Trending Products:*", 'Markdown');
        foreach ($trending as $product) {
            $bestPrice = $product->prices->sortBy('amount')->first();
            $retailerEmoji = $bestPrice && $bestPrice->retailer->name === 'Mydin' ? '🔵' : '🟠';
            $retailerName = $bestPrice ? $bestPrice->retailer->name : 'Unknown';
            $text = "🔥 *" . $product->name . "* - RM " . number_format($bestPrice->amount ?? 0, 2) . " $retailerEmoji $retailerName";
            $this->sendMessage($chatId, $text, 'Markdown');
        }
    }

    /**
     * Show deals
     */
    private function showDeals($chatId)
    {
        $this->sendMessage($chatId, "🎉 *Special Deals*\n\nCheck back soon for amazing deals! Our system tracks price changes and will notify you.", 'Markdown');
    }

    /**
     * Show settings
     */
    private function showSettings($chatId)
    {
        $text = "⚙️ *Settings*\n\n";
        $text .= "📌 Preferred Retailer: Not set\n";
        $text .= "🔔 Price Alerts: Disabled\n";
        $text .= "⭐ Favorites: View with /favorites\n\n";
        $text .= "More settings coming soon!";

        $this->sendMessage($chatId, $text, 'Markdown');
    }

    /**
     * Send message with reply keyboard
     */
    private function sendMessageWithKeyboard($chatId, $text, $buttons = [], $parseMode = null)
    {
        $url = $this->apiUrl . $this->botToken . '/sendMessage';

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => $buttons,
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]
        ];

        if ($parseMode) {
            $payload['parse_mode'] = $parseMode;
        }

        try {
            Http::withoutVerifying()->post($url, $payload);
        } catch (\Exception $e) {
            \Log::error('Telegram Bot Error: ' . $e->getMessage());
        }
    }

    /**
     * Send message with inline buttons
     */
    private function sendMessageWithButtons($chatId, $text, $buttons = [], $parseMode = null)
    {
        $url = $this->apiUrl . $this->botToken . '/sendMessage';

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => $buttons
            ]
        ];

        if ($parseMode) {
            $payload['parse_mode'] = $parseMode;
        }

        try {
            Http::withoutVerifying()->post($url, $payload);
        } catch (\Exception $e) {
            \Log::error('Telegram Bot Error: ' . $e->getMessage());
        }
    }

    /**
     * Send message via Telegram API
     */
    private function sendMessage($chatId, $text, $parseMode = null)
    {
        $url = $this->apiUrl . $this->botToken . '/sendMessage';

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($parseMode) {
            $payload['parse_mode'] = $parseMode;
        }

        try {
            Http::withoutVerifying()->post($url, $payload);
        } catch (\Exception $e) {
            \Log::error('Telegram Bot Error: ' . $e->getMessage());
        }
    }

    /**
     * Answer callback query
     */
    private function answerCallbackQuery($callbackId, $text = '', $showAlert = false)
    {
        $url = $this->apiUrl . $this->botToken . '/answerCallbackQuery';

        $payload = [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => $showAlert
        ];

        try {
            Http::withoutVerifying()->post($url, $payload);
        } catch (\Exception $e) {
            \Log::error('Telegram Bot Error: ' . $e->getMessage());
        }
    }

    /**
     * Get welcome message
     */
    private function getWelcomeMessage(): string
    {
        return "👋 *Welcome to SmartShopper Bot!*\n\n"
            . "I'm your intelligent shopping assistant helping you find the best prices across Malaysian retailers.\n\n"
            . "🛍️ *Quick Start:*\n"
            . "• Type any product name (e.g., 'milo', 'maggi', 'coffee')\n"
            . "• I'll show you prices from all retailers\n"
            . "• Use buttons to filter, sort, and compare\n\n"
            . "📝 *Popular Commands:*\n"
            . "/help - All available commands\n"
            . "/trending - Top products\n"
            . "/deals - Latest deals\n"
            . "/settings - Preferences\n\n"
            . "💡 Try: milo, maggi, coffee, samsung";
    }

    /**
     * Get help message
     */
    private function getHelpMessage(): string
    {
        return "📚 *SmartShopper Bot - Complete Guide*\n\n"
            . "*🔍 Search:*\n"
            . "Just type a product name\n\n"
            . "*📋 Commands:*\n"
            . "/start - Welcome\n"
            . "/help - This message\n"
            . "/trending - Popular products\n"
            . "/deals - Best deals\n"
            . "/settings - Preferences\n"
            . "/list - Shopping list\n\n"
            . "*💡 Features:*\n"
            . "✅ Real-time prices\n"
            . "✅ Filter by retailer\n"
            . "✅ Sort by price\n"
            . "✅ Pagination\n\n"
            . "*🏪 Retailers:*\n"
            . "🔵 Mydin\n"
            . "🟠 Shopee\n\n"
            . "Start searching now! 🚀";
    }

    /**
     * Set webhook for Telegram bot
     */
    public function setWebhook()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        
        if (!$token) {
            return response()->json(['error' => 'TELEGRAM_BOT_TOKEN not set in .env'], 500);
        }

        $url = $this->apiUrl . $token . '/setWebhook';
        $webhookUrl = env('TELEGRAM_WEBHOOK_URL') ?? route('telegram.webhook');

        try {
            $response = Http::withoutVerifying()->post($url, [
                'url' => $webhookUrl,
            ]);

            $responseData = $response->json();
            
            if ($responseData['ok'] ?? false) {
                return response()->json([
                    'message' => 'Webhook set successfully', 
                    'webhook_url' => $webhookUrl,
                    'response' => $responseData
                ]);
            } else {
                return response()->json([
                    'error' => 'Telegram API error',
                    'url_used' => $url,
                    'webhook_url' => $webhookUrl,
                    'response' => $responseData
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get bot info
     */
    public function getBotInfo()
    {
        $url = $this->apiUrl . $this->botToken . '/getMe';

        try {
            $response = Http::withoutVerifying()->get($url);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate login link for Telegram user
     */
    private function generateLoginLink($chatId, $command)
    {
        // Get telegram user ID from message context
        $message = request()->json()->all()['message'] ?? [];
        $telegramUserId = $message['from']['id'] ?? null;

        if (!$telegramUserId) {
            $this->sendMessage($chatId, "❌ Unable to retrieve your Telegram ID. Please try again.");
            return;
        }

        // Generate unique token
        $token = \Str::random(64);

        // Store in cache (expires in 15 minutes)
        Cache::put(
            "telegram_auth_{$token}",
            [
                'telegram_id' => $telegramUserId,
                'chat_id' => $chatId,
                'created_at' => now()
            ],
            now()->addMinutes(15)
        );

        // Build login URL
        $loginUrl = env('APP_URL', 'http://localhost:8000') . '/auth/telegram?token=' . $token;

        // Send login link to user
        $this->sendMessage(
            $chatId,
            "🔗 *SmartShopper Login*\n\n"
            . "Click the link below to login to your account:\n\n"
            . "[Login Now]({$loginUrl})\n\n"
            . "⏰ This link expires in 15 minutes\n"
            . "🔒 Keep this link private",
            'Markdown'
        );
    }
}