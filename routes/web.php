<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BudgetController;
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

// Route to view specific product details
Route::get('/product/{id}', [BudgetController::class, 'show'])->name('product.show');

// Route to handle price alert subscriptions
Route::post('/product/{id}/alert', [BudgetController::class, 'toggleAlert'])->name('product.alert');

// Route to report incorrect price (any user can report)
Route::post('/product/{id}/report-price', [\App\Http\Controllers\PriceReportController::class, 'store'])->name('product.report');

// 3. Authenticated SmartShopper Routes
// These routes require the user to be logged in to manage their personal budget
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- Navigation & View Routes ---
    // Displays the main Budget Planning and Active Shopping List page
    Route::get('/budget-plan', [BudgetController::class, 'showBudget'])->name('budget.index');

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

    // --- Profile Management (Default Laravel Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. Include Authentication Routes (Login, Register, Logout)
require __DIR__.'/auth.php';