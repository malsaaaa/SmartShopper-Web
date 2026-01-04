<x-guest-layout>
    <div class="min-h-screen flex overflow-hidden bg-white">
        
        <div id="auth-left" class="hidden lg:flex lg:w-1/2 bg-emerald-600 items-center justify-center relative p-12">
            <img src="/images/auth/overlay.svg" alt="" class="absolute inset-0 w-full h-full object-cover z-0 opacity-10 pointer-events-none" />

            <!-- Interactive decorative eyes (accessible: aria-hidden, presentation) -->
            <svg class="auth-eyes absolute z-5 w-44 h-24 left-1/2 top-1/3 transform -translate-x-1/2 pointer-events-none" viewBox="0 0 200 80" aria-hidden="true" role="presentation">
              <g class="eye" transform="translate(40,40)">
                <circle cx="0" cy="0" r="26" fill="#fff" />
                <circle class="pupil" cx="0" cy="0" r="8" fill="#0f172a" />
              </g>
              <g class="eye" transform="translate(120,40)">
                <circle cx="0" cy="0" r="26" fill="#fff" />
                <circle class="pupil" cx="0" cy="0" r="8" fill="#0f172a" />
              </g>
            </svg>

            <style>
                /* small, local styles for the eye effect */
                .auth-eyes { opacity: 0.12; }
                .auth-eyes .pupil {
                    transition: transform 120ms linear;
                    transform: translate(0,0);
                    transform-origin: center;
                    transform-box: fill-box; /* ensures transform origin applies to SVG shapes */
                    -webkit-transform-box: fill-box;
                    will-change: transform;
                }
                @media (prefers-reduced-motion: reduce) { .auth-eyes .pupil { transition: none; } }
            </style>
            
            <div class="relative z-10 text-center text-white">
                <div class="flex justify-center mb-8">
                    <div style="width: 80px; height: 80px;" class="bg-white/10 backdrop-blur rounded-3xl flex items-center justify-center border border-white/20 shadow-xl">
                        <svg style="width: 40px; height: 40px;" class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-5xl font-black tracking-tighter mb-2">SmartShopper</h1>
                <p class="text-emerald-100 font-bold uppercase tracking-[0.3em] text-xs">Join the Shopping Revolution</p>
                <p class="mt-8 text-sm text-emerald-100 max-w-sm mx-auto leading-relaxed opacity-80">
                    Create an account today to start saving your shopping lists, tracking your monthly budget, and comparing prices across major retailers.
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center bg-white p-8 sm:p-12 lg:p-24">
            <div class="w-full max-w-md space-y-8">
                
                <div class="lg:hidden text-center mb-10">
                    <div class="flex justify-center mb-4">
                        <div style="width: 50px; height: 50px;" class="bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-md">
                            <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tighter">SmartShopper</h2>
                </div>

                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Create Account</h2>
                    <p class="text-sm font-bold text-gray-400 mt-2">Sign up for a free utility account.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Full Name</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <div style="width: 18px; height: 18px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                            </div>
                            <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" 
                                   class="block w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label for="email" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Account Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <div style="width: 18px; height: 18px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg></div>
                            </div>
                            <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@email.com" 
                                   class="block w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <label for="password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Security Key</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <div style="width: 18px; height: 18px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" 
                                   class="block w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Confirm Key</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <div style="width: 18px; height: 18px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div>
                            </div>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" 
                                   class="block w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 text-white font-black py-4 rounded-2xl hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-3 text-lg mt-4">
                        <span>Register Utility Account</span>
                        <div style="width: 20px; height: 20px;">
                            <svg style="width: 100%; height: 100%;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                    </button>

                    <div class="pt-6 text-center">
                        <p class="text-sm font-bold text-gray-400">
                            Already a user? 
                            <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-800 font-black transition-colors ml-1">
                                Access Dashboard
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/js/auth-eyes.js" defer></script>
</x-guest-layout>