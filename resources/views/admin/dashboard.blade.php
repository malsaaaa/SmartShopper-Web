@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="text-gray-600 mt-2">Welcome back, {{ Auth::user()->name }}!</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.scraper.settings') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-all flex items-center gap-2 shadow-lg hover:shadow-xl">
                    <span>⚙️</span>
                    <span>Scraper Settings</span>
                </a>
                <button id="runScraperBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all flex items-center gap-2 shadow-lg hover:shadow-xl">
                    <span>🔄</span>
                    <span>Run Scraper</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <!-- Total Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Products</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_products'] }}</p>
                    </div>
                    <div class="text-blue-500 text-3xl">📦</div>
                </div>
                <a href="{{ route('admin.products') }}" class="text-blue-600 text-sm font-medium mt-4 block hover:text-blue-700">
                    View all →
                </a>
            </div>

            <!-- Total Retailers -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Retailers</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_retailers'] }}</p>
                    </div>
                    <div class="text-green-500 text-3xl">🏪</div>
                </div>
                <a href="{{ route('admin.retailers') }}" class="text-blue-600 text-sm font-medium mt-4 block hover:text-blue-700">
                    View all →
                </a>
            </div>

            <!-- Total Prices -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Price Records</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_prices'] }}</p>
                    </div>
                    <div class="text-purple-500 text-3xl">💰</div>
                </div>
                <a href="{{ route('admin.prices') }}" class="text-blue-600 text-sm font-medium mt-4 block hover:text-blue-700">
                    View all →
                </a>
            </div>

            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_users'] }}</p>
                    </div>
                    <div class="text-orange-500 text-3xl">👥</div>
                </div>
                <a href="{{ route('admin.users') }}" class="text-blue-600 text-sm font-medium mt-4 block hover:text-blue-700">
                    View all →
                </a>
            </div>

            <!-- Total Admins -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Admins</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_admins'] }}</p>
                    </div>
                    <div class="text-red-500 text-3xl">👨‍💼</div>
                </div>
                <a href="{{ route('admin.users') }}" class="text-blue-600 text-sm font-medium mt-4 block hover:text-blue-700">
                    View all →
                </a>
            </div>

            <!-- Pending Reports -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Pending Price Reports</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['price_reports'] }}</p>
                    </div>
                    <div class="text-yellow-500 text-3xl">⚠️</div>
                </div>
                <a href="{{ route('admin.reports') }}" class="text-blue-600 text-sm font-medium mt-4 block hover:text-blue-700">
                    View all →
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.products') }}" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition">
                    <p class="font-medium text-blue-900">Manage Products</p>
                    <p class="text-sm text-blue-700 mt-1">Add, edit, or delete products</p>
                </a>
                <a href="{{ route('admin.users') }}" class="bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition">
                    <p class="font-medium text-green-900">Manage Users</p>
                    <p class="text-sm text-green-700 mt-1">View users and manage admin roles</p>
                </a>
                <a href="{{ route('admin.reports') }}" class="bg-orange-50 border border-orange-200 rounded-lg p-4 hover:bg-orange-100 transition">
                    <p class="font-medium text-orange-900">View Price Reports</p>
                    <p class="text-sm text-orange-700 mt-1">Handle customer price reports</p>
                </a>
            </div>
        </div>

        <!-- Scraping Status -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">🔄 Scraping Status</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Last Scrape Info -->
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-2">Last Scrape</p>
                        @if($scrapingStats['last_scrape'])
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <div>
                                    <p class="text-lg font-bold text-gray-900">{{ $scrapingStats['last_scrape']->format('M d, Y H:i') }}</p>
                                    <p class="text-xs text-gray-500">{{ $scrapingStats['last_scrape']->diffForHumans() }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <p class="text-gray-600 font-medium">No price data yet</p>
                            </div>
                        @endif
                    </div>
                    <!-- Data Coverage -->
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-2">Price Coverage</p>
                        <p class="text-lg font-bold text-gray-900">{{ $scrapingStats['products_with_prices'] }} / {{ $stats['total_products'] }} Products</p>
                        <p class="text-xs text-gray-500">{{ $stats['total_products'] > 0 ? round(($scrapingStats['products_with_prices'] / $stats['total_products']) * 100, 1) : 0 }}% coverage</p>
                    </div>
                </div>

                <!-- Price Coverage by Retailer -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm font-bold text-gray-900 mb-4">Coverage by Retailer</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($scrapingStats['price_coverage'] as $coverage)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-900">{{ $coverage['retailer'] }}</p>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <p class="text-2xl font-bold text-gray-900">{{ $coverage['count'] }}</p>
                                    <p class="text-xs text-gray-600">/ {{ $stats['total_products'] }}</p>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $coverage['percentage'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">{{ $coverage['percentage'] }}% coverage</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Prices -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Latest Prices Updated</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retailer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($scrapingStats['latest_prices'] as $price)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $price->product->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $price->retailer->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">RM {{ number_format($price->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $price->updated_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-600">No prices yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Products -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Recently Added Products</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recent_products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $product->category }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $product->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-600">No products yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Price Reports -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Pending Price Reports</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recent_reports as $report)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $report->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $report->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $report->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('admin.reports') }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-600">No pending reports</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.getElementById('runScraperBtn').addEventListener('click', async function() {
    if (!confirm('Are you sure you want to run the scraper? This may take a few minutes.')) {
        return;
    }
    
    const btn = this;
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span><span>Running...</span>';
    
    try {
        const response = await fetch('{{ route("admin.scraper.run") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✓ ' + data.message);
            // Reload the page after 1 second to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('✗ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    } catch (error) {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});
</script>
