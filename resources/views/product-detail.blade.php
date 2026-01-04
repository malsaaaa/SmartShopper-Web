<x-app-layout>
    <div class="bg-gray-50 min-h-screen pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm font-black text-gray-400 hover:text-emerald-500 uppercase tracking-widest transition-colors">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                Back to Marketplace
            </a>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <div class="lg:col-span-7 space-y-8">
                <div class="bg-white rounded-[3rem] p-12 shadow-sm border border-gray-100 flex items-center justify-center aspect-video relative">
                    <img src="{{ $product->image_url ?? '/api/placeholder/600/400' }}" class="max-h-full w-auto object-contain transition-transform hover:scale-105 duration-500">
                </div>
                
                <div class="bg-white rounded-[3rem] p-10 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-10">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 tracking-tighter">Price Trends</h3>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Last 30 Days Market Activity</p>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 px-4 py-1.5 rounded-full">Live Data</span>
                    </div>
                    @php
                        $fallback = 'https://quickchart.io/chart?c=' . rawurlencode(json_encode([
                            'type' => 'line',
                            'data' => [
                                'labels' => [2,6,11,16,21,26,31],
                                'datasets' => [
                                    [
                                        'label' => 'Price',
                                        'data' => [750,830,780,850,810,740,875],
                                        'fill' => false,
                                        'borderColor' => 'rgb(16,185,129)',
                                        'tension' => 0.4,
                                    ],
                                ],
                            ],
                        ]));
                    @endphp
                    <div class="h-64 w-full bg-gray-50 rounded-3xl overflow-hidden">
                        <img src="{{ $chartUrl ?? $fallback }}" class="w-full h-full object-cover opacity-80" />
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-8 sticky top-28">
                <div class="bg-white rounded-[3rem] p-10 shadow-sm border border-gray-100">
                    <div class="mb-10">
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em]">{{ $product->category }}</span>
                        <h1 class="text-4xl font-black text-gray-900 tracking-tighter mt-2 leading-none">{{ $product->name }}</h1>
                    </div>

                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Compare Store Prices</h3>
                    
                    <div class="space-y-4">
                        @foreach($product->prices->sortBy('amount') as $price)
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-3xl border border-transparent hover:border-emerald-100 hover:bg-white transition-all group relative">
                            @if($loop->first)
                                <span class="absolute -top-2 -right-2 bg-yellow-400 text-gray-900 text-[9px] font-black px-3 py-1 rounded-full shadow-sm uppercase tracking-widest z-10">Best Value</span>
                            @endif
                            <div>
                                <div class="flex flex-col items-start">
                                    <img src="{{ $price->retailer->logo_url ?? '/images/retailers/default.svg' }}" class="w-8 h-8 object-contain mb-2" alt="{{ $price->retailer->name }}">
                                    <p class="text-[10px] font-black text-gray-400 uppercase mb-1">{{ $price->retailer->name ?? 'Retailer' }}</p>
                                </div>
                                <p class="text-2xl font-black text-gray-900">RM {{ number_format($price->amount, 2) }}</p>
                            </div>
                            <div x-data="{ showReport: false }" class="flex items-center gap-3">
                                <button class="p-3 bg-white border border-gray-100 rounded-2xl text-gray-400 hover:text-emerald-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </button>

                                <form action="{{ route('budget.check') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                    <input type="hidden" name="retailer" value="{{ $price->retailer->name }}">
                                    <input type="hidden" name="price" value="{{ $price->amount }}">
                                    <button type="submit" class="bg-emerald-500 text-white px-6 py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg active:scale-95">Add to Plan</button>
                                </form>

                                @php
                                    // Prefer the per-price product URL when available, otherwise fall back to retailer base URL (search) or Google search
                                    $storeUrl = $price->product_url
                                        ?? ($price->retailer->base_url ? rtrim($price->retailer->base_url, '/') . '/search?q=' . urlencode($product->name) : 'https://www.google.com/search?q=' . urlencode($price->retailer->name . ' ' . $product->name));
                                @endphp
                                <a href="{{ $storeUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs px-3 py-2 rounded-md border bg-white">Visit Store</a>

                                <!-- Report price UI -->
                                <div class="relative">
                                    <button type="button" @click.prevent="showReport = !showReport" class="text-xs font-black border border-gray-100 px-3 py-2 rounded-full bg-white hover:bg-gray-50">Report</button>

                                    <div x-show="showReport" x-cloak class="absolute right-0 mt-2 w-80 bg-white border rounded-xl p-4 shadow-lg z-20">
                                        <form action="{{ route('product.report', $product->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="retailer_id" value="{{ $price->retailer->id }}">
                                            <input type="hidden" name="reported_price" value="{{ $price->amount }}">
                                            <textarea name="comment" class="w-full p-2 border rounded-md text-sm" rows="3" placeholder="Describe the issue (optional)"></textarea>
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button type="button" @click="showReport = false" class="text-xs px-3 py-2 rounded-md border">Cancel</button>
                                                <button type="submit" class="text-xs px-3 py-2 rounded-md bg-emerald-500 text-white font-black">Send</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-12 pt-10 border-t border-gray-100">
                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Available Budget</p>
                                <p class="text-xl font-black text-gray-900 mt-1">RM {{ number_format($budget->limit_amount - $budget->current_spending, 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Plan Total</p>
                                <p class="text-xl font-black text-emerald-500 mt-1">RM {{ number_format($budget->current_spending, 2) }}</p>
                            </div>
                        </div>
                        <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 transition-all duration-700" style="width: {{ ($budget->current_spending / $budget->limit_amount) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>