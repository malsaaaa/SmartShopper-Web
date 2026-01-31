@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100" x-data="{ expandedReport: null }">
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Price Reports</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-900">← Back</a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('admin.reports') }}" class="flex gap-4">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $report->product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $report->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($report->comment)
                                    <div class="max-w-xs">
                                        <p class="truncate">{{ $report->comment }}</p>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">No description</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($report->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($report->status === 'resolved') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $report->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button type="button" @click="expandedReport === {{ $report->id }} ? expandedReport = null : expandedReport = {{ $report->id }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                    {{ $report->comment ? 'View Full' : 'Details' }}
                                </button>
                            </td>
                        </tr>
                        <!-- Expanded Details Row -->
                        <tr x-show="expandedReport === {{ $report->id }}" class="bg-blue-50">
                            <td colspan="6" class="px-6 py-4">
                                <div class="space-y-3">
                                    @if($report->comment)
                                    <div>
                                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">User's Report</p>
                                        <p class="text-sm text-gray-700 bg-white p-3 rounded border border-gray-200">{{ $report->comment }}</p>
                                    </div>
                                    @endif
                                    @if($report->reported_price)
                                    <div>
                                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Reported Price</p>
                                        <p class="text-lg font-bold text-red-600">RM {{ number_format($report->reported_price, 2) }}</p>
                                    </div>
                                    @endif
                                    @if($report->status === 'pending')
                                    <div class="flex gap-2 mt-4">
                                        <form method="POST" action="{{ route('admin.reports.update', $report) }}" style="display: inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium text-sm">✓ Resolve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.reports.update', $report) }}" style="display: inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium text-sm">✗ Reject</button>
                                        </form>
                                    </div>
                                    @else
                                    <div class="mt-4 p-3 bg-gray-100 rounded-lg">
                                        <p class="text-sm font-medium text-gray-600">Status: <span class="font-bold">{{ ucfirst($report->status) }}</span></p>
                                        @if($report->admin_notes)
                                        <p class="text-sm text-gray-600 mt-2"><strong>Admin Notes:</strong> {{ $report->admin_notes }}</p>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-600">No reports found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>
</div>
@endsection
