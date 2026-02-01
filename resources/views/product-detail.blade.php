@extends('layouts.app')

@section('content')
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

                    <!-- Report Price Button -->
                    <div class="mb-8">
                        <button type="button" onclick="document.getElementById('reportModal').classList.remove('hidden')" class="inline-flex items-center gap-2 text-[10px] font-black text-red-600 uppercase tracking-widest hover:text-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-12H9m6 0h-3m6 0H9m6 0h3m-6 0v2m0-4v-2m0 12v2m0-4v-2"/></svg>
                            Report Incorrect Price
                        </button>
                    </div>
                    
                    @php
                        $pricesByRetailer = $product->prices->groupBy('retailer_id');
                    @endphp

                    @foreach($pricesByRetailer as $retailerId => $prices)
                        @php
                            $retailer = $prices->first()->retailer;
                            $bgColor = $retailer->name === 'Mydin' 
                                ? 'bg-blue-600' 
                                : ($retailer->name === 'Shopee' 
                                    ? 'bg-orange-600' 
                                    : 'bg-gray-600');
                            $boxColor = $retailer->name === 'Mydin' 
                                ? 'bg-blue-50 border-blue-200 hover:bg-blue-100' 
                                : ($retailer->name === 'Shopee' 
                                    ? 'bg-orange-50 border-orange-200 hover:bg-orange-100' 
                                    : 'bg-gray-50 border-gray-200 hover:bg-gray-100');
                        @endphp
                        
                        <!-- Retailer Header -->
                        <div class="{{ $bgColor }} rounded-t-3xl px-8 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $retailer->logo_url ?? '/images/retailers/default.svg' }}" class="w-10 h-10 object-contain" alt="{{ $retailer->name }}">
                                <p class="text-lg font-black text-white uppercase tracking-widest">{{ $retailer->name }}</p>
                            </div>
                        </div>

                        <!-- Prices Grid -->
                        <div class="bg-gray-50 rounded-b-3xl px-8 py-6 mb-6">
                            <div class="space-y-4">
                                @foreach($prices->sortBy('amount') as $price)
                                    @php
                                        $isLowest = $price->amount === $product->prices->min('amount');
                                    @endphp
                                    <div class="relative">
                                        @if($isLowest)
                                            <span class="absolute -top-3 left-4 bg-yellow-400 text-gray-900 text-[9px] font-black px-3 py-1 rounded-full shadow-sm uppercase tracking-widest z-10">BEST</span>
                                        @endif
                                        <div class="flex flex-col items-center justify-center p-6 {{ $boxColor }} rounded-2xl border transition-all group hover:shadow-lg">
                                            <p class="text-4xl font-black text-gray-900 mb-2">RM {{ number_format($price->amount, 2) }}</p>
                                            <p class="text-[10px] font-bold text-gray-500 mb-4">{{ $price->scraped_at->format('M d, H:i') }}</p>
                                            
                                            <div class="flex flex-col gap-2 w-full">
                                                <form action="{{ route('budget.check') }}" method="POST" class="w-full">
                                                    @csrf
                                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                                    <input type="hidden" name="retailer" value="{{ $price->retailer->name }}">
                                                    <input type="hidden" name="price" value="{{ $price->amount }}">
                                                    <button type="submit" class="w-full bg-emerald-500 text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-600 transition-all text-center">+ Add</button>
                                                </form>

                                                @php
                                                    $storeUrl = $price->product_url
                                                        ?? ($price->retailer->base_url ? rtrim($price->retailer->base_url, '/') . '/search?q=' . urlencode($product->name) : 'https://www.google.com/search?q=' . urlencode($price->retailer->name . ' ' . $product->name));
                                                @endphp
                                                <a href="{{ $storeUrl }}" target="_blank" rel="noopener noreferrer" class="w-full text-emerald-600 px-4 py-3 rounded-xl border border-emerald-200 font-black text-xs uppercase tracking-widest hover:bg-emerald-50 transition-all text-center">View →</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

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

        <!-- Report Price Modal -->
        <div id="reportModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-lg font-black text-gray-900">Report Incorrect Price</h3>
                </div>
                
                <p class="text-sm text-gray-600 mb-6">Help us keep prices accurate by reporting incorrect information.</p>
                
                <form action="{{ route('product.report', $product->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Select Retailer</label>
                        <select name="retailer_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Choose Retailer --</option>
                            @foreach($product->prices->groupBy('retailer_id') as $retailerId => $prices)
                                @php $retailer = $prices->first()->retailer; @endphp
                                <option value="{{ $retailerId }}">{{ $retailer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Issue Type</label>
                        <select name="issue_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Select Issue --</option>
                            <option value="price_wrong">Price is incorrect</option>
                            <option value="price_outdated">Price is outdated</option>
                            <option value="product_unavailable">Product unavailable</option>
                            <option value="other">Other issue</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" rows="3" placeholder="Please tell us more about the issue..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('reportModal').classList.add('hidden')" class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg font-black text-sm hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 text-white bg-emerald-600 rounded-lg font-black text-sm hover:bg-emerald-700 transition-colors">
                            Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
@endsection