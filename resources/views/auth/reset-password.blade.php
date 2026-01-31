<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4 py-12 -m-4">
        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-8 py-10 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-black text-white tracking-tighter">Create New Password</h1>
                    <p class="text-emerald-100 text-xs font-bold uppercase tracking-widest mt-2">SmartShopper</p>
                </div>

                <!-- Content -->
                <div class="px-8 py-8">
                    <!-- Description -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900 rounded-2xl">
                        <p class="text-sm font-bold text-blue-900 dark:text-blue-200">
                            Enter your email address and create a strong new password to regain access to your account.
                        </p>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                        @csrf
                        
                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email -->
                        <div>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600">
                                    <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" placeholder="Email Address"
                                       class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-red-600 dark:text-red-400" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600">
                                    <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password" name="password" required autocomplete="new-password" placeholder="New Password"
                                       class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-red-600 dark:text-red-400" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600">
                                    <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password"
                                       class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-red-600 dark:text-red-400" />
                        </div>

                        <!-- Password Strength Hint -->
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900 rounded-2xl">
                            <p class="text-xs font-bold text-amber-900 dark:text-amber-200">
                                🔒 <strong>Password Tips:</strong> Use at least 8 characters, mix uppercase, lowercase, numbers, and symbols.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 rounded-lg transition-all uppercase tracking-widest text-sm shadow-lg hover:shadow-xl active:scale-95">
                            Reset Password
                        </button>

                        <!-- Back Link -->
                        <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('login') }}" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors uppercase tracking-wide">
                                ← Back to Login
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-8 py-4 text-center border-t border-gray-200 dark:border-gray-600">
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 font-semibold">© 2026 SmartShopper. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
