<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists
            if ($request->user()->profile_picture) {
                Storage::disk('public')->delete($request->user()->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Connect Telegram account
     */
    public function connectTelegram(Request $request): RedirectResponse
    {
        $request->validate([
            'telegram_code' => 'required|string|max:16',
        ]);

        $code = $request->input('telegram_code');
        
        // The code format is the first 8 characters of md5(chatId)
        // We need to find the user with matching telegram_chat_id
        // For now, we'll use a cache-based approach similar to the login token
        
        $telegramData = \Illuminate\Support\Facades\Cache::get("telegram_connect_{$code}");

        if (!$telegramData) {
            return Redirect::route('profile.edit')->withErrors(['telegram_code' => 'Invalid or expired code. Please try /connect again in Telegram.']);
        }

        $telegramId = $telegramData['telegram_id'];
        $chatId = $telegramData['chat_id'];

        // If another user has this telegram_id, remove it from them (likely a temporary account from /login)
        $existingUser = \App\Models\User::where('telegram_id', $telegramId)->where('id', '!=', $request->user()->id)->first();
        
        if ($existingUser) {
            // If the existing user only has a telegram account (no real email), delete it
            if (strpos($existingUser->email, 'telegram_') === 0) {
                // Delete temporary telegram-only account
                $existingUser->delete();
            } else {
                // If they have a real account, just remove the telegram link
                $existingUser->update(['telegram_id' => null, 'telegram_chat_id' => null]);
            }
        }

        // Link Telegram to current user
        $request->user()->update([
            'telegram_id' => $telegramId,
            'telegram_chat_id' => $chatId,
        ]);

        // Remove the code from cache
        \Illuminate\Support\Facades\Cache::forget("telegram_connect_{$code}");

        return Redirect::route('profile.edit')->with('status', 'telegram-connected');
    }
}