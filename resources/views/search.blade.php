@extends('layouts.app')

@section('content')
<div class="bg-gray-50 dark:bg-gray-900 min-h-screen pb-12">
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center gap-6">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tighter shrink-0">🔍 Results for "{{ $query }}"</h1>
            <form action="{{ route('products.search') }}" method="GET" class="relative w-full group">
                <div style="width: 20px; height: 20px;" class="absolute left-5 top-4 text-gray-400 dark:text-gray-600 group-focus-within:text-emerald-500">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input name="query" type="text" value="{{ $query }}" placeholder="Search again..." class="w-full pl-14 pr-6 py-4 bg-gray-50 dark:bg-gray-800 border-transparent rounded-2xl shadow-inner focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-emerald-500 font-bold text-gray-700 dark:text-gray-200 dark:placeholder-gray-500">
            </form>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        
        <div class="grid grid-cols-1 gap-8">
            <!-- Products Grid -->
            <div>
                <!-- Shopee Results Section (Only for non-database-only searches) -->
                @if(strtolower(trim($query)) !== 'milo' && strtolower(trim($query)) !== 'maggi' && strtolower(trim($query)) !== 'coffee')
                <div class="mb-12">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        🛍️ Shopee.com.my Results
                        @if(!empty($crawledProducts))
                        <span class="text-sm font-bold bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full">{{ count($crawledProducts) }} products</span>
                        @else
                        <span class="text-sm font-bold bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-1 rounded-full">Loading...</span>
                        @endif
                    </h2>
                    @if(!empty($crawledProducts))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                        @foreach($crawledProducts as $product)
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border-2 border-blue-200 dark:border-blue-800 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all p-6 flex flex-col justify-between overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <span class="bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full text-xs font-black">{{ $product['shop_name'] ?? 'Shopee' }}</span>
                                @if($product['rating'] > 0)
                                <div class="flex items-center gap-1 bg-yellow-50 dark:bg-yellow-900/20 px-2 py-1 rounded-lg">
                                    <span class="text-yellow-500">⭐</span>
                                    <span class="text-xs font-bold text-yellow-700 dark:text-yellow-300">{{ number_format($product['rating'], 1) }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Image -->
                            <div class="h-48 bg-gray-100 dark:bg-gray-700 rounded-2xl mb-4 flex items-center justify-center overflow-hidden">
                                @if($product['image_url'])
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="max-h-full w-auto object-contain" onerror="this.src='/images/placeholder.svg'">
                                @else
                                    <div class="text-gray-400 dark:text-gray-600 text-center">
                                        <div style="font-size: 3rem;">🛒</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="mb-4">
                                <h3 class="font-black text-gray-900 dark:text-white text-base mb-2 line-clamp-2">{{ $product['name'] }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ $product['retailer'] }}</p>
                            </div>

                            <!-- Price -->
                            <div class="mb-6">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-blue-600 dark:text-blue-400">RM {{ number_format($product['price'], 2) }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <a href="{{ $product['url'] }}" target="_blank" rel="noopener noreferrer" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-black py-3 rounded-xl hover:shadow-lg transition-all text-center text-sm uppercase tracking-widest">
                                    View on Shopee
                                </a>
                                @auth
                                <form action="{{ route('budget.check') }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $product['name'] }}">
                                    <input type="hidden" name="retailer" value="{{ $product['shop_name'] ?? 'Shopee' }}">
                                    <input type="hidden" name="price" value="{{ $product['price'] }}">
                                    <button type="submit" class="w-full bg-emerald-500 text-white font-black py-3 rounded-xl hover:bg-emerald-600 transition-all text-sm uppercase tracking-widest">
                                        Add to Plan
                                    </button>
                                </form>
                                @else
                                <a href="{{ route('login') }}" class="flex-1 bg-emerald-500 text-white font-black py-3 rounded-xl hover:bg-emerald-600 transition-all text-center text-sm uppercase tracking-widest">
                                    Add to Plan
                                </a>
                                @endauth
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-2xl p-8 text-center">
                        <p class="text-blue-700 dark:text-blue-300 font-bold mb-2">🔄 Fetching Shopee results...</p>
                        <p class="text-sm text-blue-600 dark:text-blue-400">If this persists, Shopee crawler may not be available. Try searching again or use "Database Only" filter.</p>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Database Results Section - Card format for milo/maggi/coffee -->
                @if(strtolower(trim($query)) === 'milo' || strtolower(trim($query)) === 'maggi' || strtolower(trim($query)) === 'coffee')
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        📦 Results
                        <span class="text-sm font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-full">{{ count($products) }} products</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($products as $product)
                        @php $bestPrice = $product->prices->sortBy('amount')->first(); @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border-2 border-emerald-200 dark:border-emerald-800 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all p-6 flex flex-col justify-between overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <span class="bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-full text-xs font-black">{{ $product->category }}</span>
                                @if($bestPrice)
                                <div class="flex items-center gap-1 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-lg">
                                    <span class="text-emerald-600 dark:text-emerald-400">💰</span>
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Best Price</span>
                                </div>
                                @endif
                            </div>

                            <!-- Image -->
                            <div class="h-48 bg-gray-100 dark:bg-gray-700 rounded-2xl mb-4 flex items-center justify-center overflow-hidden">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="max-h-full w-auto object-contain" onerror="this.src='/images/placeholder.svg'">
                                @else
                                    <div class="text-gray-400 dark:text-gray-600 text-center">
                                        <div style="font-size: 3rem;">📦</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="mb-4">
                                <h3 class="font-black text-gray-900 dark:text-white text-base mb-2 line-clamp-2">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Multi-Retailer</p>
                            </div>

                            <!-- Price Comparison -->
                            <div class="mb-6 space-y-2">
                                @foreach($retailers as $retailer)
                                @php $price = $product->prices->where('retailer_id', $retailer->id)->first(); @endphp
                                @if($price)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400">{{ $retailer->name }}</span>
                                    <span class="font-black text-emerald-600 dark:text-emerald-400">RM {{ number_format($price->amount, 2) }}</span>
                                </div>
                                @endif
                                @endforeach
                            </div>

                            <!-- Best Price Highlight -->
                            @if($bestPrice)
                            <div class="mb-6 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-xl p-3 border border-emerald-200 dark:border-emerald-700">
                                <div class="text-center">
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase">Lowest Price</p>
                                    <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">RM {{ number_format($bestPrice->amount, 2) }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <a href="{{ route('product.show', $product->id) }}" class="flex-1 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-black py-3 rounded-xl hover:shadow-lg transition-all text-center text-sm uppercase tracking-widest">
                                    View Details
                                </a>
                                @auth
                                <form action="{{ route('budget.check') }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                    <input type="hidden" name="retailer" value="Multi">
                                    <input type="hidden" name="price" value="{{ $bestPrice?->amount ?? 0 }}">
                                    <button type="submit" class="w-full bg-gray-900 dark:bg-gray-700 text-white font-black py-3 rounded-xl hover:bg-emerald-600 transition-all text-sm uppercase tracking-widest">
                                        Add to Plan
                                    </button>
                                </form>
                                @endauth
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700">
                            <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">No products found</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @else
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        📦 Database Products
                        <span class="text-sm font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-full">{{ count($products) }} products</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($products as $product)
                            @php $bestPrice = $product->prices->sortBy('amount')->first(); @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all p-6 flex flex-col justify-between">
                                <div>
                                    <div class="h-48 bg-gray-50 dark:bg-gray-700 rounded-3xl mb-6 flex items-center justify-center text-gray-200 dark:text-gray-600 relative overflow-hidden">
                                        <img src="{{ $product->image_url ?? '/api/placeholder/300/200' }}" alt="{{ $product->name }}" class="max-h-full w-auto object-contain">
                                        <span class="absolute top-4 left-4 bg-white dark:bg-gray-800/90 px-3 py-1 rounded-full text-[9px] font-black uppercase text-emerald-600 dark:text-emerald-400 shadow-sm">{{ $product->category }}</span>
                                    </div>
                                    <h4 class="font-black text-gray-900 dark:text-white text-lg mb-2"><a href="{{ route('product.show', $product->id) }}" class="hover:text-emerald-600 transition-colors">{{ $product->name }}</a></h4>
                                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase">Compare prices in product page</p>
                                </div>
                                @auth
                                    <form action="{{ route('budget.check') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="name" value="{{ $product->name }}">
                                        <input type="hidden" name="price" value="{{ $bestPrice?->amount ?? 0 }}">
                                        <button type="submit" class="w-full bg-gray-900 dark:bg-gray-700 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 transition-all shadow-lg">Add to Plan</button>
                                    </form>
                                @endauth
                            </div>
                        @empty
                            <div class="col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700">
                                <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">No database products found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- No Results -->
                @if(empty($crawledProducts) && empty($products))
                <div class="col-span-full py-32 text-center bg-white dark:bg-gray-800 rounded-[3rem] border-2 border-dashed border-gray-100 dark:border-gray-700">
                    <div class="text-5xl mb-4">🔍</div>
                    <p class="text-gray-500 dark:text-gray-400 font-bold text-xl px-12">No products found for "{{ $query }}"@if($category) in category {{ $category }}@endif</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Try a different search term or browse our catalog</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection