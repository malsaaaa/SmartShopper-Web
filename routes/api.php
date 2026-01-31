<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Chat routes (protected by auth - using web auth)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/chat/get-or-create', [ChatController::class, 'getOrCreateChat']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/create', [ChatController::class, 'createChat']);
    Route::delete('/chat/{chatId}', [ChatController::class, 'deleteChat']);
});