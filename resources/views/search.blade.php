<x-app-layout>
    <div class="bg-gray-50 min-h-screen pb-12">
        <div class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center gap-6">
                <h1 class="text-2xl font-black text-gray-900 tracking-tighter shrink-0">Results for "{{ $query }}"</h1>
                <form action="{{ route('products.search') }}" method="GET" class="relative w-full group">
                    <div style="width: 20px; height: 20px;" class="absolute left-5 top-4 text-gray-400 group-focus-within:text-emerald-500">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input name="query" type="text" value="{{ $query }}" placeholder="Search again..." class="w-full pl-14 pr-6 py-4 bg-gray-50 border-transparent rounded-2xl shadow-inner focus:bg-white focus:ring-2 focus:ring-emerald-500 font-bold text-gray-700">
                </form>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Filters Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-6 sticky top-32">
                        <h3 class="font-black text-gray-900 text-lg mb-6">Filters</h3>
                        
                        <form action="{{ route('products.search') }}" method="GET" class="space-y-6">
                            <!-- Search Query (Hidden) -->
                            <input type="hidden" name="query" value="{{ $query }}">
                            
                            <!-- Category Filter -->
                            <div>
                                <label class="block text-xs font-black uppercase text-gray-600 mb-3">Category</label>
                                <select name="category" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    <option value="">All Categories</option>
                                    @foreach($allCategories as $cat)
                                        <option value="{{ $cat }}" @if($category === $cat) selected @endif>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Apply Filters Button -->
                            <button type="submit" class="w-full bg-emerald-600 text-white font-black py-3 rounded-2xl hover:bg-emerald-700 transition-all shadow-md">
                                Apply Filters
                            </button>
                            
                            <!-- Clear Filters Button -->
                            @if($category)
                                <a href="{{ route('products.search', ['query' => $query]) }}" class="w-full block text-center bg-gray-200 text-gray-700 font-black py-3 rounded-2xl hover:bg-gray-300 transition-all">
                                    Clear Filters
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="lg:col-span-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @forelse($products as $product)
                    @php $bestPrice = $product->prices->sortBy('amount')->first(); @endphp
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all p-6 flex flex-col justify-between">
                        <div>
                            <div class="h-48 bg-gray-50 rounded-3xl mb-6 flex items-center justify-center text-gray-200 relative overflow-hidden">
                                <img src="{{ $product->image_url ?? '/api/placeholder/300/200' }}" alt="{{ $product->name }}" class="max-h-full w-auto object-contain">
                                <span class="absolute top-4 left-4 bg-white/90 px-3 py-1 rounded-full text-[9px] font-black uppercase text-emerald-600 shadow-sm">{{ $product->category }}</span>
                            </div>
                            <h4 class="font-black text-gray-900 text-lg mb-2"><a href="{{ route('product.show', $product->id) }}">{{ $product->name }}</a></h4>
                            <p class="text-[10px] font-black text-gray-400 uppercase">Compare prices in product page</p>
                        </div>
                        @auth
                            <form action="{{ route('budget.check') }}" method="POST">
                                @csrf
                                <input type="hidden" name="name" value="{{ $product->name }}">
                                <input type="hidden" name="price" value="{{ $bestPrice?->amount ?? 0 }}">
                                <button type="submit" class="w-full bg-gray-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 transition-all shadow-lg">Add to Plan</button>
                            </form>
                        @endauth
                    </div>
                @empty
                    <div class="col-span-full py-32 text-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                        <p class="text-gray-300 font-bold text-xl px-12">No products found for "{{ $query }}"@if($category) in category {{ $category }}@endif</p>
                    </div>
                @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>