<nav class="bg-white border-b border-gray-100 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-100 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="text-xl font-black text-gray-900 tracking-tighter">SmartShopper</span>
                </a>
            </div>

            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-400 hover:text-gray-900' }} transition-all">
                    Marketplace
                </a>
                <a href="{{ route('budget.index') }}" class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ request()->routeIs('budget.index') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-400 hover:text-gray-900' }} transition-all">
                    Budget Plan
                </a>
            </div>

            <div class="flex items-center gap-8">
                @auth
                    <div class="text-right border-r border-gray-100 pr-8 hidden sm:block">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Account</p>
                        <p class="text-sm font-black text-gray-900 leading-none">{{ Auth::user()->name }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-[10px] font-black text-red-400 uppercase tracking-[0.2em] hover:text-red-600 transition-colors">
                            Logout
                        </button>
                    </form>
                @else
                    <div class="hidden sm:flex items-center gap-4">
                        <a href="{{ route('login') }}" class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-gray-900 transition-colors">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-gray-900 transition-colors">Register</a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>