<x-app-layout>
    <div class="bg-gray-50 min-h-screen pb-12" x-data="{ openModal: false, showClearConfirm: false }">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex justify-between items-center">
            <h1 class="text-3xl font-black text-gray-900 tracking-tighter">Budget Planning</h1>
            <button @click="openModal = true" class="p-2 bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-emerald-500 transition-colors">
                <div style="width:20px; height:20px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></div>
            </button>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-5 bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100 h-fit">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Monthly Limit</h3>
                        <span class="text-5xl font-black text-gray-900 leading-none">RM {{ number_format($budget->limit_amount, 2) }}</span>
                    </div>

                    <!-- Progress ring with threshold animation -->
                    @php
                        $isHigh = ($percentSpent >= 80 && $percentSpent < 100);
                        $isCritical = ($percentSpent >= 100);
                        $ringColor = $isCritical ? '#EF4444' : ($isHigh ? '#F59E0B' : '#10B981');
                    @endphp

                    <style>
                        @keyframes ringPulse {
                            from { transform: scale(1); }
                            to { transform: scale(1.06); }
                        }
                        .ring-pulse { animation: ringPulse 1s infinite alternate; }
                    </style>

                    <div class="flex items-center gap-4">
                        <div class="relative w-20 h-20 {{ $isHigh || $isCritical ? 'ring-pulse' : '' }} {{ $isCritical ? 'shadow-lg rounded-full' : '' }}" style="transform-origin: center;">
                            <svg viewBox="0 0 80 80" class="w-20 h-20" style="color: {{ $ringColor }};">
                                <circle cx="40" cy="40" r="36" stroke="#E5E7EB" stroke-width="6" fill="none"></circle>
                                <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="6" fill="none" stroke-linecap="round"
                                    stroke-dasharray="226.19467105846508"
                                    stroke-dashoffset="{{ 226.19467105846508 * (1 - ($percentSpent / 100)) }}"
                                    transform="rotate(-90 40 40)" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-sm font-black text-gray-900">{{ round($percentSpent) }}%</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Spent</div>
                            <div class="font-black" style="color: {{ $ringColor }}">RM {{ number_format($budget->current_spending, 2) }}</div>
                        </div>
                    </div>

                    @if($isCritical)
                        <div class="mt-3 text-sm font-black text-red-600">You have exceeded your budget limit! Review your items or increase the limit.</div>
                    @elseif($isHigh)
                        <div class="mt-3 text-sm font-black text-amber-500">Warning: You're nearing your budget limit.</div>
                    @endif
                </div>

                <div class="mt-6">
                    <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 transition-all duration-700" 
                             style="width: {{ $budget->limit_amount > 0 ? ($budget->current_spending / $budget->limit_amount) * 100 : 0 }}%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mt-3">
                        <span class="text-emerald-500">Spent: RM {{ number_format($budget->current_spending, 2) }}</span>
                        <span class="text-blue-500">Remaining: RM {{ number_format($budget->limit_amount - $budget->current_spending, 2) }}</span>
                    </div>
                </div>

                <!-- Category donut chart -->
                <div class="mt-8">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Spending by Category</h4>
                    <div class="flex items-center gap-6">
                        <div class="w-40">
                            <img src="{{ $categoryChartUrl }}" alt="Spending by category" class="w-full h-auto object-contain rounded-lg shadow-sm" />
                        </div>
                        <div class="flex-1">
                            @if(!empty($labels) && is_array($labels))
                                @foreach($labels as $i => $lbl)
                                    <div class="flex items-center justify-between text-sm py-1">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-block w-3 h-3 rounded-full" style="background: {{ $colors[$i] ?? '#E5E7EB' }}"></span>
                                            <span class="text-gray-600 font-black">{{ $lbl }}</span>
                                        </div>
                                        <div class="text-gray-900 font-black">RM {{ number_format($data[$i] ?? 0, 2) }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-gray-400">No spending yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7 bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-900 px-10 py-6 flex justify-between items-center">
                    <span class="text-white text-[10px] font-black uppercase tracking-widest">Active Shopping List</span>
                    <button @click="showClearConfirm = true" class="text-[10px] font-black text-emerald-400 uppercase hover:text-red-400 transition-colors">
                        Clear List
                    </button>
                </div>

                <div class="p-8 space-y-4">
                    @forelse($items as $item)
                        <div class="bg-gray-50 rounded-3xl p-6 flex items-center justify-between group hover:bg-white transition-all border border-transparent hover:border-emerald-100">
                            <div class="flex items-center gap-6">
                                <div style="width:52px; height:52px;" class="bg-white rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm">
                                    <svg style="width:26px; height:26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-black text-gray-900 leading-none mb-1">{{ $item->name }}</h4>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $item->retailer }}</p>
                                    <p class="text-emerald-600 font-bold text-sm mt-2">RM {{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center bg-white rounded-xl px-2 py-1 shadow-sm border border-gray-100">
                                <form action="{{ route('budget.update', [$item->id, 'sub']) }}" method="POST">@csrf
                                    <button class="p-2 text-gray-300 hover:text-red-500 transition-colors"><svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M20 12H4"/></svg></button>
                                </form>
                                <span class="px-4 font-black text-gray-900">{{ $item->quantity }}</span>
                                <form action="{{ route('budget.update', [$item->id, 'add']) }}" method="POST">@csrf
                                    <button class="p-2 text-gray-300 hover:text-emerald-500 transition-colors"><svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"/></svg></button>
                                </form>
                                <form action="{{ route('budget.remove', $item->id) }}" method="POST" class="ml-2 border-l pl-2 border-gray-100">@csrf
                                    <button class="p-2 text-red-300 hover:text-red-500 transition-colors"><svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-20 text-gray-300 font-bold italic border-2 border-dashed border-gray-100 rounded-[2.5rem]">
                            Your list is currently empty.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm px-4">
            <div class="bg-white rounded-[3rem] p-10 w-full max-w-md shadow-2xl" @click.away="openModal = false">
                <h2 class="text-2xl font-black text-gray-900 mb-6 tracking-tighter">Update Monthly Limit</h2>
                <form action="{{ route('budget.limit') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="number" name="limit" value="{{ $budget->limit_amount }}" class="w-full px-8 py-5 bg-gray-50 border-none rounded-3xl font-black text-xl focus:ring-2 focus:ring-emerald-500">
                    <div class="flex gap-4">
                        <button type="button" @click="openModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs tracking-widest">Cancel</button>
                        <button type="submit" class="flex-1 py-4 bg-emerald-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-emerald-100">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showClearConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm px-4">
            <div class="bg-white rounded-[3rem] p-10 w-full max-w-md shadow-2xl text-center" @click.away="showClearConfirm = false">
                <div style="width: 80px; height: 80px;" class="bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg style="width: 40px; height: 40px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-2 tracking-tighter">Clear Shopping List?</h2>
                <p class="text-gray-500 font-bold text-sm mb-10 px-4">This will permanently remove all items from your plan. This action cannot be undone.</p>
                <form action="{{ route('budget.clear') }}" method="POST" class="flex gap-4">
                    @csrf
                    <button type="button" @click="showClearConfirm = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs tracking-widest">Cancel</button>
                    <button type="submit" class="flex-1 py-4 bg-red-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-red-100 hover:bg-red-600 transition-colors">Clear All</button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>