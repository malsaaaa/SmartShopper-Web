@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen pb-12">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex justify-between items-start gap-8">
            <div class="flex-1">
                <a href="{{ route('dashboard') }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-bold mb-4 inline-block">← Back to Marketplace</a>
                <div class="flex items-start gap-6">
                    <div class="w-32 h-32 bg-gray-100 rounded-2xl overflow-hidden flex items-center justify-center p-4">
                        <img src="{{ $product->image_url ?? '/api/placeholder/128/128' }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest">{{ $product->category }}</p>
                        <h1 class="text-4xl font-black text-gray-900 mt-2 tracking-tight">{{ $product->name }}</h1>
                        @if($product->description)
                            <p class="text-gray-600 mt-3 text-sm">{{ $product->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Available Budget</p>
                <p class="text-2xl font-black text-gray-900">RM {{ number_format(($budget->limit_amount ?? 1000) - ($budget->current_spending ?? 0), 2) }}</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Price Comparison Table -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-900 px-8 py-5 flex justify-between items-center text-white text-[10px] font-black uppercase tracking-widest">
                        <span>Compare Prices Across Retailers</span>
                        <span class="text-emerald-400">Live Prices</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                <tr>
                                    <th class="px-8 py-5">Retailer</th>
                                    <th class="px-8 py-5 text-center">Price</th>
                                    <th class="px-8 py-5 text-center">Last Updated</th>
                                    <th class="px-8 py-5 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @php
                                    $bestPrice = $product->bestPriceWithRetailer();
                                    $best = $bestPrice['amount'];
                                @endphp
                                
                                @forelse($product->prices as $price)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-4">
                                                <img src="{{ $price->retailer->logo_url ?? '/images/retailers/default.svg' }}" alt="{{ $price->retailer->name }}" class="h-10 w-10 object-contain">
                                                <span class="font-bold text-gray-900">{{ $price->retailer->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <span class="font-black text-lg text-gray-900">RM {{ number_format($price->amount, 2) }}</span>
                                                @if(((float)$price->amount) === ((float)$best))
                                                    <span class="bg-yellow-400 text-gray-900 text-xs font-black px-2 py-1 rounded-full">Best</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center text-sm text-gray-600">
                                            @if($price->scraped_at)
                                                {{ $price->scraped_at->format('M d, Y H:i') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <form action="{{ route('budget.check') }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                                    <input type="hidden" name="retailer" value="{{ $price->retailer->name }}">
                                                    <input type="hidden" name="price" value="{{ $price->amount }}">
                                                    <button type="submit" class="bg-gray-900 text-white w-10 h-10 flex items-center justify-center rounded-full hover:bg-emerald-500 transition-all shadow">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                    </button>
                                                </form>
                                                <a href="{{ $price->product_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold">Visit Site</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-8 text-center text-gray-600">No prices available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Price Trend -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Price Trend</h3>
                    <div style="position: relative; width: 100%; height: 200px;">
                        <canvas id="priceChart"></canvas>
                    </div>
                </div>

                <!-- Best Deal -->
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white">
                    <p class="text-xs font-bold uppercase tracking-widest opacity-80">Best Deal</p>
                    <p class="text-4xl font-black mt-2 tracking-tight">RM {{ number_format($best, 2) }}</p>
                    <p class="text-sm mt-2 opacity-90">at {{ $bestPrice['retailer'] ?? 'Unknown' }}</p>
                    <form action="{{ route('budget.check') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="name" value="{{ $product->name }}">
                        <input type="hidden" name="retailer" value="{{ $bestPrice['retailer'] ?? '' }}">
                        <input type="hidden" name="price" value="{{ $best }}">
                        <button type="submit" class="w-full bg-white text-emerald-600 font-black py-3 rounded-lg hover:bg-gray-100 transition-all uppercase tracking-widest text-sm">
                            Add Best Deal to Plan
                        </button>
                    </form>
                </div>

                <!-- Report Price Issue -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Issue with Price?</h3>
                    <form action="{{ route('product.report', $product->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="issue" placeholder="Report incorrect price or missing retailer..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" rows="3"></textarea>
                        <button type="submit" class="w-full bg-red-600 text-white font-bold py-2 rounded-lg hover:bg-red-700 transition-all">
                            Report Issue
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    const ctx = document.getElementById('priceChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Best Price (RM)',
                data: @json($data),
                fill: true,
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderColor: '#10B981',
                borderWidth: 3,
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
