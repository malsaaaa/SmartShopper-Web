<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle Telegram login
     */
    public function telegramLogin(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('/login')->with('error', 'No authentication token provided');
        }

        // Retrieve from cache
        $auth = Cache::pull("telegram_auth_{$token}");

        if (!$auth) {
            return redirect('/login')->with('error', 'Link expired or invalid. Please try again.');
        }

        $telegramId = $auth['telegram_id'];
        $chatId = $auth['chat_id'];

        // Find or create user
        $user = User::firstOrCreate(
            ['telegram_id' => $telegramId],
            [
                'name' => "Telegram User {$telegramId}",
                'email' => "telegram_{$telegramId}@smartshopper.local",
                'password' => bcrypt(Str::random(32)),
                'telegram_chat_id' => $chatId,
                'email_verified_at' => now(),
            ]
        );

        // Update chat ID if it changed
        if ($user->telegram_chat_id !== $chatId) {
            $user->update(['telegram_chat_id' => $chatId]);
        }

        // Login user
        Auth::login($user, remember: true);

        return redirect('/budget-plan')->with('success', 'Successfully logged in via Telegram!');
    }
}
