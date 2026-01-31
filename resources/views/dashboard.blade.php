@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-blue-50 to-emerald-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen pb-12">
        <!-- Header Section with Gradient -->
        <div class="bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-600 dark:from-emerald-600 dark:via-teal-600 dark:to-blue-700 border-b-4 border-emerald-600 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row justify-between items-end gap-6">
                    <div>
                        @auth
                            <h1 class="text-4xl font-black text-white tracking-tighter drop-shadow-lg">
                                💰 Hello, {{ Auth::user()->name }}!
                            </h1>
                        @else
                            <h1 class="text-4xl font-black text-white tracking-tighter drop-shadow-lg">
                                💰 Welcome to SmartShopper!
                            </h1>
                        @endauth
                        <p class="text-sm font-bold text-emerald-100 uppercase tracking-[.25em] mt-3">🛒 Your Smart Shopping Assistant</p>
                    </div>
                    
                    @php
                        $budget_limit = optional($budget)->limit_amount ?? 1000;
                        $budget_spent = optional($budget)->current_spending ?? 0;
                        $budget_remaining = $budget_limit - $budget_spent;
                        $budget_percentage = ($budget_spent / $budget_limit) * 100;
                    @endphp

                    <!-- Budget Cards -->
                    <div class="flex gap-6 w-full md:w-auto">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 flex-1 md:flex-none border-l-4 border-emerald-500 hover:shadow-xl transition-all">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">💵 Available Budget</p>
                            <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">RM {{ number_format($budget_remaining, 2) }}</p>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full" style="width: {{ min($budget_percentage, 100) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ number_format($budget_percentage, 1) }}% used</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 flex-1 md:flex-none border-l-4 border-orange-500 hover:shadow-xl transition-all">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">🛍️ Total Spent</p>
                            <p class="text-3xl font-black text-orange-600 dark:text-orange-400 mt-2">RM {{ number_format($budget_spent, 2) }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">of RM {{ number_format($budget_limit, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-12 space-y-8">
                    <div class="w-full max-w-[1400px] mx-auto space-y-8">
                        <!-- Search Section -->
                        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-8 border-2 border-emerald-200 dark:border-emerald-700">
                            <h2 class="text-lg font-black text-gray-900 dark:text-white mb-4">🔍 Search Products</h2>
                            <form action="{{ route('products.search') }}" method="GET" class="relative flex items-center group">
                                <div style="width: 20px; height: 20px;" class="absolute left-6 text-gray-400 dark:text-gray-600 group-focus-within:text-emerald-500 transition-colors pointer-events-none">
                                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input name="query" type="text" placeholder="Search for items like Milo, Rice, Cooking Oil..." 
                                       class="w-full pl-16 pr-32 py-4 bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-full shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent font-bold text-gray-700 dark:text-gray-200 dark:placeholder-gray-400 transition-all">
                                <div class="absolute right-2 top-1.5 bottom-1.5">
                                    <button type="submit" class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white px-8 rounded-full font-black text-xs uppercase tracking-widest hover:shadow-lg hover:from-emerald-600 hover:to-teal-600 transition-all">Search</button>
                                </div>
                            </form>
                        </div>

                        <!-- Marketplace Table with Enhanced Design -->
                        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border-4 border-gray-100 dark:border-gray-700 overflow-hidden w-full">
                            <!-- Table Header -->
                            <div class="bg-gradient-to-r from-gray-900 to-gray-800 dark:from-gray-950 dark:to-gray-900 px-8 py-6 flex justify-between items-center text-white">
                                <div>
                                    <h3 class="text-lg font-black uppercase tracking-widest">🏪 Product Marketplace</h3>
                                    <p class="text-xs text-emerald-300 font-bold mt-1">Compare prices across retailers</p>
                                </div>
                                <div class="flex items-center gap-2 bg-emerald-500 px-4 py-2 rounded-full">
                                    <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                    <span class="text-xs font-black uppercase">Live Prices</span>
                                </div>
                            </div>
                            
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-gray-600 text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest border-b-2 border-gray-200 dark:border-gray-600">
                                    <tr>
                                        <th class="px-8 py-6">📦 Product Item</th>
                                        @foreach($retailers as $r)
                                            <th class="px-8 py-6 text-center">
                                                <div class="flex flex-col items-center gap-2 p-3 bg-white dark:bg-gray-800 rounded-xl">
                                                    <img src="{{ $r->logo_url ?? '/images/retailers/default.svg' }}" class="w-10 h-10 object-contain drop-shadow" alt="{{ $r->name }}">
                                                    <div class="text-xs font-black text-gray-600 dark:text-gray-300">{{ $r->name }}</div>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y-2 divide-gray-100 dark:divide-gray-700">
                                    @foreach($products as $product)
                                    <tr class="hover:bg-gradient-to-r hover:from-emerald-50 hover:to-blue-50 dark:hover:from-gray-700/50 dark:hover:to-gray-600/50 transition-colors group border-l-4 border-emerald-300 hover:border-emerald-500">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-6">
                                                <div class="w-40 h-40 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl overflow-hidden flex-shrink-0 flex items-center justify-center p-3 shadow-lg border-3 border-gray-200 dark:border-gray-600 hover:shadow-xl transition-shadow">
                                                    <img src="{{ $product->image_url ?? '/api/placeholder/160/160' }}" class="w-full h-full object-contain">
                                                </div>
                                                <div>
                                                    <a href="{{ route('product.show', $product->id) }}" class="block font-black text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors text-lg">{{ $product->name }}</a>
                                                    <span class="inline-block text-xs font-bold text-white bg-blue-500 dark:bg-blue-600 px-3 py-1 rounded-full mt-2">{{ $product->category }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    @php
                                        $bestInfo = $product->bestPriceWithRetailer();
                                        $best = $bestInfo['amount'];
                                        $bestRetailer = $bestInfo['retailer'];
                                    @endphp

                                    @foreach($retailers as $r)
                                        @php
                                            $p = $product->prices->firstWhere('retailer_id', $r->id);
                                            $amount = $p?->amount;
                                            $productUrl = $p?->product_url ?? ($r->base_url ? rtrim($r->base_url, '/') . '/search?q=' . urlencode($product->name) : 'https://www.google.com/search?q=' . urlencode($r->name . ' ' . $product->name));
                                        @endphp

                                        <td class="px-8 py-6 text-center">
                                            @if($amount && (float)$amount > 0)
                                                <div class="flex flex-col items-center gap-3">
                                                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-xl w-full justify-center">
                                                        <div class="font-black text-gray-800 dark:text-gray-100 text-lg">RM {{ number_format($amount, 2) }}</div>
                                                        @if(((float)$amount) === ((float)$best))
                                                            <span title="Lowest price" class="inline-block bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-900 text-xs font-black px-3 py-1 rounded-full shadow-md animate-pulse">⭐ BEST</span>
                                                        @endif
                                                    </div>
                                                    @if($p && $p->scraped_at)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 font-semibold bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">📅 {{ \Carbon\Carbon::parse($p->scraped_at)->format('M d, H:i') }}</div>
                                                    @endif
                                                    <div class="mt-3 flex items-center justify-center gap-2 w-full">
                                                        <form action="{{ route('budget.check') }}" method="POST" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="name" value="{{ $product->name }}">
                                                            <input type="hidden" name="retailer" value="{{ $r->name }}">
                                                            <input type="hidden" name="price" value="{{ $amount }}">
                                                            <button type="submit" class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white w-10 h-10 flex items-center justify-center rounded-full hover:shadow-lg hover:from-emerald-600 hover:to-teal-600 transition-all shadow-md font-bold" aria-label="Add {{ $product->name }} from {{ $r->name }}" title="Add to budget">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                                            </button>
                                                        </form>
                                                        <a href="{{ $productUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 px-2 py-1 rounded hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-all">View →</a>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-gray-300 dark:text-gray-600 py-6">
                                                    <div class="text-2xl">—</div>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection