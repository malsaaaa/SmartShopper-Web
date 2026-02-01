<div class="max-w-xl">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                🤖 Connect Telegram Bot
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Link your Telegram account to sync your shopping list
            </p>
        </div>
    </div>

    @if(auth()->user()->telegram_id)
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-800 dark:text-green-300">
                ✅ Your Telegram account is connected!
            </p>
            <p class="text-xs text-green-700 dark:text-green-400 mt-2">
                Telegram ID: <code class="bg-white dark:bg-gray-800 px-2 py-1 rounded">{{ auth()->user()->telegram_id }}</code>
            </p>
        </div>
    @else
        <form method="POST" action="{{ route('profile.connect-telegram') }}" class="mt-6 space-y-6">
            @csrf

            <div>
                <label for="telegram_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Telegram Connection Code
                </label>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Get the code by typing <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/connect</code> in the Telegram bot
                </p>
                <input 
                    type="text" 
                    name="telegram_code" 
                    id="telegram_code"
                    placeholder="e.g., f756928f"
                    class="mt-2 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"
                    required
                />
                @error('telegram_code')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-emerald-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    Connect Telegram
                </button>
                @if ($errors->any())
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
                @endif
            </div>
        </form>
    @endif

    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <h3 class="text-sm font-medium text-blue-900 dark:text-blue-300">
            💡 How it works:
        </h3>
        <ul class="mt-3 space-y-2 text-sm text-blue-800 dark:text-blue-400">
            <li>1. Open Telegram and message <strong>@SmartShopper_MY_Bot</strong></li>
            <li>2. Type <code>/connect</code> to get a connection code</li>
            <li>3. Paste the code above and click "Connect Telegram"</li>
            <li>4. Your shopping list will now sync with Telegram!</li>
        </ul>
    </div>
</div>
