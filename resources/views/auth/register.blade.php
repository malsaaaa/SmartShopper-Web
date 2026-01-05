<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url('https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&q=80');">
        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Register Card -->
        <div class="relative z-10 w-full max-w-md mx-4">
            <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                <!-- Logo and Name -->
                <div class="px-8 pt-8 pb-4 text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                    </div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">SmartShopper</h2>
                </div>

                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-8 py-8 text-center">
                    <h1 class="text-xl font-black text-white tracking-widest uppercase">Register</h1>
                    <p class="text-emerald-100 text-xs font-bold mt-2 tracking-[0.2em]">Join SmartShopper</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('register') }}" class="px-8 py-8 space-y-4">
                    @csrf

                    <!-- Name -->
                    <div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600">
                                <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <input type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Full Name" 
                                   class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs text-red-600" />
                    </div>

                    <!-- Email -->
                    <div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600">
                                <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                            </div>
                            <input type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Email Address" 
                                   class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-600" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600">
                                <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input type="password" name="password" required autocomplete="new-password" placeholder="Password" 
                                   class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-600" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600">
                                <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password" 
                                   class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-red-600" />
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 rounded-lg transition-all uppercase tracking-widest text-sm shadow-lg hover:shadow-xl active:scale-95">
                        Register
                    </button>

                    <!-- Login Link -->
                    <div class="text-center pt-4 border-t border-gray-200">
                        <p class="text-sm font-bold text-gray-700">
                            Already have an account? <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 transition-colors">Login</a>
                        </p>
                    </div>
                </form>

                <!-- Footer -->
                <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-200">
                    <p class="text-[10px] text-gray-500 font-semibold">© 2026 SmartShopper. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>