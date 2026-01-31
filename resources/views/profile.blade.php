@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 md:p-12">
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row items-start md:items-center gap-6 pb-8 border-b border-gray-200 dark:border-gray-700">
            <div class="w-24 h-24 bg-emerald-500 rounded-2xl flex items-center justify-center text-white shadow-lg overflow-hidden flex-shrink-0">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                @endif
            </div>
            <div>
                <h1 class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{ $user->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.15em] mt-3">
                    @if($user->is_admin)
                        ⭐ Administrator
                    @else
                        👤 Regular User
                    @endif
                </p>
            </div>
        </div>

        <!-- Account Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white mb-6 tracking-tight">Account Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Full Name</p>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Email Address</p>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Account Type</p>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold">
                            @if($user->is_admin)
                                <span class="inline-block bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-3 py-1 rounded-full text-xs font-bold">Administrator</span>
                            @else
                                <span class="inline-block bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-3 py-1 rounded-full text-xs font-bold">Regular User</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Member Since</p>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $user->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Account Statistics -->
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white mb-6 tracking-tight">Activity</h2>
                <div class="space-y-4">
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800">
                        <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Shopping Lists</p>
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ $shoppingListCount }}</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
                        <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Budget Updates</p>
                        <p class="text-2xl font-black text-blue-700 dark:text-blue-300">{{ $budgetHistoryCount }}</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 border border-purple-100 dark:border-purple-800">
                        <p class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-widest mb-1">Price Reports</p>
                        <p class="text-2xl font-black text-purple-700 dark:text-purple-300">{{ $reportCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Overview -->
        @if($currentBudget)
        <div class="mb-8 p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-800/30 rounded-xl border border-emerald-200 dark:border-emerald-700">
            <h2 class="text-lg font-black text-emerald-900 dark:text-emerald-300 mb-4 tracking-tight">Current Budget Status</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-1">Budget Limit</p>
                    <p class="text-xl font-black text-emerald-900 dark:text-emerald-200">RM {{ number_format($currentBudget->limit_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-1">Spent</p>
                    <p class="text-xl font-black text-red-600 dark:text-red-400">RM {{ number_format($currentBudget->current_spending, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-1">Remaining</p>
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400">RM {{ number_format($currentBudget->limit_amount - $currentBudget->current_spending, 2) }}</p>
                </div>
            </div>
            @php
                $percentSpent = $currentBudget->limit_amount > 0 ? ($currentBudget->current_spending / $currentBudget->limit_amount) * 100 : 0;
            @endphp
            <div class="mt-4">
                <div class="w-full bg-gray-300 dark:bg-gray-600 rounded-full h-3 overflow-hidden">
                    <div class="bg-emerald-500 h-full transition-all" style="width: {{ min(100, $percentSpent) }}%"></div>
                </div>
                <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-2 font-semibold">{{ round($percentSpent) }}% of budget used</p>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('profile.edit') }}" class="px-8 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-sm uppercase tracking-widest rounded-xl transition-colors">
                Edit Profile
            </a>
            <a href="{{ route('dashboard') }}" class="px-8 py-3 border-2 border-emerald-500 text-emerald-600 dark:text-emerald-400 dark:border-emerald-400 font-black text-sm uppercase tracking-widest rounded-xl hover:bg-emerald-50 dark:hover:bg-gray-700 transition-colors">
                Back to Marketplace
            </a>
        </div>
    </div>
</div>
@endsection
