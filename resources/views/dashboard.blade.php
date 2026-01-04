<x-app-layout>
    <div class="bg-gray-50 min-h-screen pb-12">
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex flex-col md:flex-row justify-between items-end gap-6">
                    <div>
                        @auth
                            <h1 class="text-3xl font-black text-gray-900 tracking-tighter">
                                Hello, {{ Auth::user()->name }}! 👋
                            </h1>
                        @else
                            <h1 class="text-3xl font-black text-gray-900 tracking-tighter">
                                Hello! 👋
                            </h1>
                        @endauth
                        <p class="text-[11px] font-black text-emerald-500 uppercase tracking-[.25em] mt-2">Personal Finance Assistant</p>
                    </div>
                    
                    @php
                        $budget_limit = optional($budget)->limit_amount ?? 1000;
                        $budget_spent = optional($budget)->current_spending ?? 0;
                    @endphp

                    <div class="flex gap-10">
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Available Budget</p>
                            <p class="text-2xl font-black text-gray-900">RM {{ number_format($budget_limit - $budget_spent, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Total Spent</p>
                            <p class="text-2xl font-black text-emerald-500">RM {{ number_format($budget_spent, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-12 space-y-8">
                    <div class="w-full max-w-[1400px] mx-auto space-y-8">
                        <form action="{{ route('products.search') }}" method="GET" class="relative flex items-center group">
                            <div style="width: 20px; height: 20px;" class="absolute left-6 text-gray-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input name="query" type="text" placeholder="Search for items like Milo, Rice..." 
                                   class="w-full pl-16 pr-32 py-5 bg-white border-none rounded-full shadow-sm focus:ring-2 focus:ring-emerald-500 font-bold text-gray-700">
                            <div class="absolute right-2 top-1.5 bottom-1.5">
                                <button type="submit" class="h-full bg-emerald-500 text-white px-8 rounded-full font-black text-xs uppercase tracking-widest hover:bg-emerald-600 transition-all">Search</button>
                            </div>
                        </form>

                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden w-full">
                            <div class="bg-gray-900 px-8 py-5 flex justify-between items-center text-white text-[10px] font-black uppercase tracking-widest">
                                <span>Product Marketplace</span>
                                <span class="text-emerald-400">Live Price Comparison</span>
                            </div>
                            
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-8 py-5">Product Item</th>
                                        @foreach($retailers as $r)
                                            <th class="px-8 py-5 text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <img src="{{ $r->logo_url ?? '/images/retailers/default.svg' }}" class="w-8 h-8 object-contain" alt="{{ $r->name }}">
                                                    <div class="text-xs font-black text-gray-400 uppercase">{{ $r->name }}</div>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($products as $product)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-4">
                                                <div class="w-16 h-16 bg-gray-100 rounded-2xl overflow-hidden flex-shrink-0 flex items-center justify-center p-2">
                                                    <img src="{{ $product->image_url ?? '/api/placeholder/100/100' }}" class="w-full h-full object-contain">
                                                </div>
                                                <div>
                                                    <a href="{{ route('product.show', $product->id) }}" class="block font-black text-gray-900 group-hover:text-emerald-600 transition-colors">{{ $product->name }}</a>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase leading-none">{{ $product->category }}</span>
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
                                                <div class="flex flex-col items-center gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="font-bold text-gray-600">RM {{ number_format($amount, 2) }}</div>
                                                        @if(((float)$amount) === ((float)$best))
                                                            <span title="Lowest price" class="ml-2 inline-block bg-yellow-400 text-gray-900 text-xs font-black px-2 py-1 rounded-full">Best</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-3 flex items-center justify-center gap-2">
                                                        <form action="{{ route('budget.check') }}" method="POST" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="name" value="{{ $product->name }}">
                                                            <input type="hidden" name="retailer" value="{{ $r->name }}">
                                                            <input type="hidden" name="price" value="{{ $amount }}">
                                                            <button type="submit" class="bg-gray-900 text-white w-9 h-9 flex items-center justify-center rounded-full hover:bg-emerald-500 transition-all shadow" aria-label="Add {{ $product->name }} from {{ $r->name }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                                            </button>
                                                        </form>
                                                        <a href="{{ $productUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs text-gray-500">Add To Plan</a>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-gray-300">&nbsp;</div>
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
</x-app-layout>