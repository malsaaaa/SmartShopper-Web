<nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-100 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="text-xl font-black text-gray-900 dark:text-white tracking-tighter">SmartShopper</span>
                </a>
            </div>

            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ request()->routeIs('dashboard') ? 'bg-emerald-50 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-gray-100' }} transition-all">
                    Marketplace
                </a>
                <a href="{{ route('budget.index') }}" class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ request()->routeIs('budget.index') ? 'bg-emerald-50 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-gray-100' }} transition-all">
                    Budget Plan
                </a>
                <a href="{{ route('about') }}" class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ request()->routeIs('about') ? 'bg-emerald-50 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-gray-100' }} transition-all">
                    About
                </a>
            </div>

            <div class="flex items-center gap-4">
                <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg id="sun-icon" class="w-5 h-5 text-yellow-500 hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v2a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l-2.12-2.12a1 1 0 111.414-1.414l2.12 2.12a1 1 0 11-1.414 1.414zM2.05 6.464l2.12-2.12a1 1 0 011.414 1.414l-2.12 2.12a1 1 0 01-1.414-1.414zM17.657 16.97l-2.12-2.12a1 1 0 111.414-1.414l2.12 2.12a1 1 0 11-1.414 1.414zM5.464 5.465a1 1 0 111.414-1.414l2.12 2.12a1 1 0 11-1.414 1.414l-2.12-2.12zM5.464 15.536a1 1 0 11-1.414-1.414l2.12-2.12a1 1 0 111.414 1.414l-2.12 2.12zM12 20a1 1 0 01-1-1v-2a1 1 0 112 0v2a1 1 0 01-1 1z" clip-rule="evenodd"/></svg>
                    <svg id="moon-icon" class="w-5 h-5 text-gray-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-8">
                @auth
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 border-r border-gray-100 dark:border-gray-700 pr-8 hidden sm:flex hover:opacity-80 transition-opacity">
                        <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if(Auth::user()->profile_picture)
                                <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-1">Account</p>
                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 leading-none">{{ Auth::user()->name }}</p>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display: inline;">
                        @csrf
                        <button type="submit" class="text-[10px] font-black text-red-400 dark:text-red-500 uppercase tracking-[0.2em] hover:text-red-600 dark:hover:text-red-400 transition-colors" onclick="return confirm('Are you sure you want to logout?')">
                            Logout
                        </button>
                    </form>
                @else
                    <div class="hidden sm:flex items-center gap-4">
                        <a href="{{ route('login') }}" class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] hover:text-gray-900 dark:hover:text-gray-100 transition-colors">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] hover:text-gray-900 dark:hover:text-gray-100 transition-colors">Register</a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    const themeToggle = document.getElementById('theme-toggle');
    const sunIcon = document.getElementById('sun-icon');
    const moonIcon = document.getElementById('moon-icon');

    themeToggle.addEventListener('click', () => {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
            sunIcon.classList.remove('hidden');
            moonIcon.classList.add('hidden');
        }
    });

    if (document.documentElement.classList.contains('dark')) {
        sunIcon.classList.remove('hidden');
        moonIcon.classList.add('hidden');
    }
</script>