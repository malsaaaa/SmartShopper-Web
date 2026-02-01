<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PriceReportController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Landing Page: Redirects to the Marketplace Dashboard
Route::get('/', [BudgetController::class, 'index']);

// 2. Public Marketplace & Search
// Users can browse products and search without being logged in
Route::get('/dashboard', [BudgetController::class, 'index'])->name('dashboard');
Route::get('/search', [BudgetController::class, 'search'])->name('products.search');
Route::get('/about', function () {
    return view('about');
})->name('about');

// Route to view specific product details
Route::get('/product/{id}', [BudgetController::class, 'show'])->name('product.show');

// Route to handle price alert subscriptions
Route::post('/product/{id}/alert', [BudgetController::class, 'toggleAlert'])->name('product.alert');

// Route to report incorrect price (any user can report)
Route::post('/product/{id}/report-price', [PriceReportController::class, 'store'])->name('product.report');

// 3. Authenticated SmartShopper Routes
// These routes require the user to be logged in to manage their personal budget
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- Navigation & View Routes ---
    // Displays the main Budget Planning and Active Shopping List page
    Route::get('/budget-plan', [BudgetController::class, 'showBudget'])->name('budget.index');

    // Chat with AI assistant
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    // --- Shopping List Actions (Must use POST) ---
    
    // Adds a product from the marketplace to the user's shopping list
    Route::post('/add-item', [BudgetController::class, 'check'])->name('budget.check');
    
    // Updates quantity (+ or -) for a specific item in the list
    Route::post('/budget/update/{id}/{action}', [BudgetController::class, 'updateQuantity'])->name('budget.update');
    
    // Removes a single specific item from the shopping list
    Route::post('/budget/remove/{id}', [BudgetController::class, 'removeItem'])->name('budget.remove');
    
    // Clears all items currently in the shopping list
    Route::post('/clear-list', [BudgetController::class, 'clearList'])->name('budget.clear');

    // --- Budget Management ---
    // Updates the monthly RM spending limit
    Route::post('/update-limit', [BudgetController::class, 'updateLimit'])->name('budget.limit');

    // --- Item Notes ---
    // Update item notes
    Route::post('/item/{id}/note', [BudgetController::class, 'updateItemNote'])->name('item.note');

    // --- Wishlist Feature ---
    // Toggle item wishlist status
    Route::post('/item/{id}/wishlist', [BudgetController::class, 'toggleWishlist'])->name('budget.wishlist');

    // --- Item Images ---
    // Add image to item
    Route::post('/item/{id}/image', [BudgetController::class, 'addItemImage'])->name('budget.image');

    // --- List Sharing ---
    // Share shopping list with another user
    Route::post('/share-list', [BudgetController::class, 'shareList'])->name('budget.share');

    // --- Chat API Routes ---
    Route::post('/api/chat/get-or-create', [ChatController::class, 'getOrCreateChat']);
    Route::post('/api/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/api/chat/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/api/chat/create', [ChatController::class, 'createChat']);
    Route::delete('/api/chat/{chatId}', [ChatController::class, 'deleteChat']);

    // --- Profile Management (Default Laravel Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Connect Telegram Account
    Route::post('/profile/connect-telegram', [ProfileController::class, 'connectTelegram'])->name('profile.connect-telegram');
    
    // User profile view
    Route::get('/my-profile', [BudgetController::class, 'showProfile'])->name('profile');
});

// 4. Admin Routes (Protected by IsAdmin middleware)
Route::middleware(['auth', 'verified', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Products Management
    Route::get('/products', [\App\Http\Controllers\AdminDashboardController::class, 'products'])->name('products');
    Route::get('/products/create', [\App\Http\Controllers\AdminDashboardController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [\App\Http\Controllers\AdminDashboardController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\AdminDashboardController::class, 'editProduct'])->name('products.edit');
    Route::patch('/products/{product}', [\App\Http\Controllers\AdminDashboardController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\AdminDashboardController::class, 'deleteProduct'])->name('products.delete');
    
    // Retailers Management
    Route::get('/retailers', [\App\Http\Controllers\AdminDashboardController::class, 'retailers'])->name('retailers');
    Route::get('/retailers/create', [\App\Http\Controllers\AdminDashboardController::class, 'createRetailer'])->name('retailers.create');
    Route::post('/retailers', [\App\Http\Controllers\AdminDashboardController::class, 'storeRetailer'])->name('retailers.store');
    Route::get('/retailers/{retailer}/edit', [\App\Http\Controllers\AdminDashboardController::class, 'editRetailer'])->name('retailers.edit');
    Route::patch('/retailers/{retailer}', [\App\Http\Controllers\AdminDashboardController::class, 'updateRetailer'])->name('retailers.update');
    
    // Prices Management
    Route::get('/prices', [\App\Http\Controllers\AdminDashboardController::class, 'prices'])->name('prices');
    Route::patch('/prices/{price}', [\App\Http\Controllers\AdminDashboardController::class, 'updatePrice'])->name('prices.update');
    Route::delete('/prices/{price}', [\App\Http\Controllers\AdminDashboardController::class, 'deletePrice'])->name('prices.delete');
    
    // Users Management
    Route::get('/users', [\App\Http\Controllers\AdminDashboardController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle-admin', [\App\Http\Controllers\AdminDashboardController::class, 'toggleAdmin'])->name('users.toggleAdmin');
    
    // Price Reports
    Route::get('/reports', [\App\Http\Controllers\AdminDashboardController::class, 'reports'])->name('reports');
    Route::patch('/reports/{report}', [\App\Http\Controllers\AdminDashboardController::class, 'updateReportStatus'])->name('reports.update');
    
    // Scraper Management
    Route::post('/scraper/run', [\App\Http\Controllers\AdminDashboardController::class, 'runScraper'])->name('scraper.run');
    Route::get('/scraper/settings', [\App\Http\Controllers\AdminDashboardController::class, 'scraperSettings'])->name('scraper.settings');
    Route::patch('/scraper/settings', [\App\Http\Controllers\AdminDashboardController::class, 'updateScraperSettings'])->name('scraper.settings.update');
    Route::post('/scraper/logs/clear', [\App\Http\Controllers\AdminDashboardController::class, 'clearScraperLogs'])->name('scraper.logs.clear');
});

// 6. Telegram Bot Routes (Public - No authentication required)
Route::post('/telegram/webhook', [TelegramBotController::class, 'handleWebhook'])->name('telegram.webhook');
Route::get('/telegram/set-webhook', [TelegramBotController::class, 'setWebhook'])->name('telegram.set-webhook');
Route::get('/telegram/bot-info', [TelegramBotController::class, 'getBotInfo'])->name('telegram.bot-info');

// 7. Telegram Authentication Route
Route::get('/auth/telegram', [AuthController::class, 'telegramLogin'])->name('auth.telegram');

// 5. Include Authentication Routes (Login, Register, Logout)
require __DIR__.'/auth.php';