@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Prices</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-900">← Back</a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('admin.prices') }}" class="flex gap-4">
                <select name="product_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Products</option>
                    @foreach($products as $id => $name)
                        <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                
                <select name="retailer_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Retailers</option>
                    @foreach($retailers as $id => $name)
                        <option value="{{ $id }}" {{ request('retailer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Filter
                </button>
                
                <a href="{{ route('admin.prices') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 font-medium">
                    Reset
                </a>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retailer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scraped</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($prices as $price)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $price->product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $price->retailer->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">RM {{ number_format($price->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($price->product_url)
                                    <a href="{{ $price->product_url }}" target="_blank" class="text-blue-600 hover:text-blue-900 break-all line-clamp-2 text-xs">{{ Str::limit($price->product_url, 40) }}</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $price->scraped_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button onclick="openEditModal({{ $price->id }}, '{{ str_replace("'", "\\'", $price->retailer->name) }}', {{ $price->amount }}, '{{ addslashes($price->product_url) }}')" class="text-blue-600 hover:text-blue-900 font-medium">Edit</button>
                                <form action="{{ route('admin.prices.delete', $price) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Remove this product from {{ str_replace("'", "\\'", $price->retailer->name) }}?')" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-600">No prices found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @php
            $totalPages = ceil($prices->total() / $prices->perPage());
            $currentPage = $prices->currentPage();
        @endphp
        <div class="mt-8 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Page <span class="font-bold">{{ $currentPage }}</span> of 
                <span class="font-bold">{{ $totalPages }}</span>
            </div>
            <div class="flex gap-2">
                @if($prices->onFirstPage())
                    <button disabled class="px-4 py-2 bg-gray-200 text-gray-400 rounded-lg cursor-not-allowed">← Previous</button>
                @else
                    <a href="{{ $prices->previousPageUrl() }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">← Previous</a>
                @endif

                @for($i = 1; $i <= $totalPages; $i++)
                    @if($i == $currentPage)
                        <button disabled class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold">{{ $i }}</button>
                    @else
                        <a href="{{ $prices->url($i) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">{{ $i }}</a>
                    @endif
                @endfor

                @if($prices->hasMorePages())
                    <a href="{{ $prices->nextPageUrl() }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Next →</a>
                @else
                    <button disabled class="px-4 py-2 bg-gray-200 text-gray-400 rounded-lg cursor-not-allowed">Next →</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Price Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4">
        <h2 class="text-xl font-bold mb-4 text-gray-900">Edit Price & Product URL</h2>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price (RM)</label>
                <input type="number" id="priceInput" name="amount" step="0.01" min="0" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product URL</label>
                <input type="url" id="urlInput" name="product_url" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs"
                       placeholder="https://retailer.com/search?q=product">
                <p class="text-xs text-gray-500 mt-1">Link where users will find this product on the retailer's website</p>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium transition-colors">Save</button>
                <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400 font-medium transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(priceId, retailer, amount, productUrl) {
    document.getElementById('editForm').action = `/admin/prices/${priceId}`;
    document.getElementById('priceInput').value = amount;
    document.getElementById('urlInput').value = productUrl;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('priceInput').focus();
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>
@endsection
