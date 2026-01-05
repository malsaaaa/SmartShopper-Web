<x-app-layout>
    <div class="bg-gray-50 min-h-screen pb-12" x-data="{ openModal: false, showClearConfirm: false, shareModal: false, editNotes: null, sortBy: 'name', filterWishlist: false, printMode: false }">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex justify-between items-center flex-wrap gap-4">
            <h1 class="text-3xl font-black text-gray-900 tracking-tighter">Budget Planning</h1>
            <div class="flex gap-4 flex-wrap">
                <button @click="printMode = true; window.print();" class="p-2 bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-purple-500 transition-colors" title="Print list">
                    <div style="width:20px; height:20px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></div>
                </button>
                <button @click="shareModal = true" class="p-2 bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-blue-500 transition-colors" title="Share list">
                    <div style="width:20px; height:20px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.589 12.43 10.647 11.5 12 11.5c1.353 0 2.411.93 3.316 1.842m0 0a3 3 0 11-4.248-4.248m4.248 4.248L9.75 21H5.25V5.25h5.892m0 0a3 3 0 11-4.248-4.248"/></svg></div>
                </button>
                <button @click="openModal = true" class="p-2 bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-emerald-500 transition-colors">
                    <div style="width:20px; height:20px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></div>
                </button>
            </div>
        </div>

        <!-- Budget Alert Notifications -->
        @php
            $percentSpent = $budget->limit_amount > 0 ? min(100, ($budget->current_spending / $budget->limit_amount) * 100) : 0;
            $isHigh = ($percentSpent >= 80 && $percentSpent < 100);
            $isCritical = ($percentSpent >= 100);
        @endphp

        @if($isCritical)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm">
                    <p class="text-sm font-black text-red-600">🚨 CRITICAL: You have exceeded your budget by RM{{ number_format($budget->current_spending - $budget->limit_amount, 2) }}</p>
                </div>
            </div>
        @elseif($isHigh)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg shadow-sm">
                    <p class="text-sm font-black text-amber-600">⚠️ WARNING: You're at {{ round($percentSpent) }}% of your budget. Remaining: RM{{ number_format($budget->limit_amount - $budget->current_spending, 2) }}</p>
                </div>
            </div>
        @elseif($percentSpent >= 50)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg shadow-sm">
                    <p class="text-sm font-black text-blue-600">ℹ️ INFO: You're using {{ round($percentSpent) }}% of your budget. Keep tracking!</p>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-5 bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100 h-fit">
                <!-- Progress ring with threshold animation -->
                @php
                    $ringColor = $isCritical ? '#EF4444' : ($isHigh ? '#F59E0B' : '#10B981');
                @endphp

                <style>
                    @keyframes ringPulse {
                        from { transform: scale(1); }
                        to { transform: scale(1.06); }
                    }
                    .ring-pulse { animation: ringPulse 1s infinite alternate; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>

                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Monthly Limit</h3>
                        <span class="text-5xl font-black text-gray-900 leading-none">RM {{ number_format($budget->limit_amount, 2) }}</span>
                    </div>

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
                </div>

                <div class="mt-6">
                    <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 transition-all duration-700" 
                             style="width: {{ $budget->limit_amount > 0 ? ($budget->current_spending / $budget->limit_amount) * 100 : 0 }}%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mt-3">
                        <span class="text-emerald-500">Spent: RM {{ number_format($budget->current_spending, 2) }}</span>
                        <span class="text-blue-500">Remaining: RM {{ number_format(max(0, $budget->limit_amount - $budget->current_spending), 2) }}</span>
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
                <div class="bg-gray-900 px-10 py-6 flex justify-between items-center flex-wrap gap-4 no-print">
                    <span class="text-white text-[10px] font-black uppercase tracking-widest">Active Shopping List</span>
                    <div class="flex gap-2 flex-wrap">
                        <select x-model="sortBy" class="px-3 py-2 bg-gray-800 text-white rounded-lg text-xs font-black border-0 focus:ring-2 focus:ring-emerald-500">
                            <option value="name">Sort by Name</option>
                            <option value="price">Sort by Price</option>
                            <option value="retailer">Sort by Retailer</option>
                            <option value="quantity">Sort by Quantity</option>
                        </select>
                        <button @click="filterWishlist = !filterWishlist" class="px-3 py-2 text-xs font-black uppercase bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <span x-show="!filterWishlist">⭐ Show Wishlist</span>
                            <span x-show="filterWishlist">❌ Hide Wishlist</span>
                        </button>
                        <button @click="showClearConfirm = true" class="text-[10px] font-black text-emerald-400 uppercase hover:text-red-400 transition-colors">
                            Clear List
                        </button>
                    </div>
                </div>

                <div class="p-8 space-y-4">
                    @forelse($items as $item)
                        <div x-show="!filterWishlist || !{{ $item->is_wishlist ? 'true' : 'false' }}" class="bg-gray-50 rounded-3xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between group hover:bg-white transition-all border border-transparent hover:border-emerald-100 gap-4">
                            <div class="flex items-center gap-6 flex-1 w-full">
                                <!-- Product Image -->
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-12 h-12 rounded-2xl object-cover shadow-sm flex-shrink-0">
                                @else
                                    <div style="width:52px; height:52px;" class="bg-white rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm flex-shrink-0">
                                        <svg style="width:26px; height:26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-black text-gray-900 leading-none mb-1">{{ $item->name }}</h4>
                                        @if($item->is_wishlist)
                                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded font-bold">⭐ Wishlist</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $item->retailer }}</p>
                                    @if($item->notes)
                                        <p class="text-[10px] text-blue-600 font-semibold mt-1 italic">📝 {{ $item->notes }}</p>
                                    @else
                                        <button @click="editNotes = {{ $item->id }}" class="text-[10px] text-gray-400 hover:text-gray-600 font-semibold mt-1 italic no-print">+ Add note</button>
                                    @endif
                                    <p class="text-emerald-600 font-bold text-sm mt-2">RM {{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap no-print">
                                <div class="flex items-center bg-white rounded-xl px-2 py-1 shadow-sm border border-gray-100">
                                    <form action="{{ route('budget.update', [$item->id, 'sub']) }}" method="POST">@csrf
                                        <button class="p-2 text-gray-300 hover:text-red-500 transition-colors"><svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M20 12H4"/></svg></button>
                                    </form>
                                    <span class="px-4 font-black text-gray-900">{{ $item->quantity }}</span>
                                    <form action="{{ route('budget.update', [$item->id, 'add']) }}" method="POST">@csrf
                                        <button class="p-2 text-gray-300 hover:text-emerald-500 transition-colors"><svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M12 4v16m8-8H4"/></svg></button>
                                    </form>
                                </div>

                                <!-- Wishlist Toggle -->
                                <form action="{{ route('budget.wishlist', $item->id) }}" method="POST" class="inline">@csrf
                                    <button type="submit" class="p-2 text-gray-300 hover:text-purple-500 transition-colors" title="Add to wishlist">
                                        <svg style="width:16px; height:16px;" fill="{{ $item->is_wishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </button>
                                </form>

                                <form action="{{ route('budget.remove', $item->id) }}" method="POST" class="ml-2 border-l pl-2 border-gray-100">@csrf
                                    <button class="p-2 text-red-300 hover:text-red-500 transition-colors"><svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </form>
                            </div>

                            @if($item->notes)
                            <button @click="editNotes = {{ $item->id }}" class="text-[10px] text-gray-400 hover:text-blue-600 font-semibold uppercase tracking-widest mt-2 sm:mt-0 self-end sm:self-auto no-print">Edit note</button>
                            @endif

                            @if(isset($editNotes) && $editNotes === $item->id)
                            <div class="w-full sm:col-span-2 mt-4 p-4 bg-blue-50 rounded-2xl border border-blue-100">
                                <form action="{{ route('item.note', $item->id) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <textarea name="notes" placeholder="Add a note (e.g., buy organic, check expiry)" class="w-full px-4 py-2 bg-white border-none rounded-lg text-sm font-semibold focus:ring-2 focus:ring-blue-500" rows="2">{{ $item->notes }}</textarea>
                                    <div class="flex gap-2">
                                        <button type="button" @click="editNotes = null" class="flex-1 py-2 text-gray-600 font-black text-xs uppercase tracking-widest hover:text-gray-800">Cancel</button>
                                        <button type="submit" class="flex-1 py-2 bg-blue-500 text-white rounded-lg font-black text-xs uppercase tracking-widest">Save Note</button>
                                    </div>
                                </form>
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-20 text-gray-300 font-bold italic border-2 border-dashed border-gray-100 rounded-[2.5rem]">
                            Your list is currently empty. Start adding items!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Shopping Tips Section -->
        @if(!empty($shoppingTips))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
            <h2 class="text-2xl font-black text-gray-900 tracking-tighter mb-6">💡 Shopping Tips</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($shoppingTips as $tip)
                <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-sm">
                    <div class="text-3xl mb-3">{{ $tip['icon'] }}</div>
                    <h3 class="font-black text-gray-900 mb-2">{{ $tip['title'] }}</h3>
                    <p class="text-sm text-gray-600 font-semibold">{{ $tip['message'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Budget History Section -->
        @if(!empty($budgetHistory) && $budgetHistory->count() > 0)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
            <h2 class="text-2xl font-black text-gray-900 tracking-tighter mb-6">📊 Budget History (Last 30 Days)</h2>
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Budget Limit</th>
                                <th class="px-6 py-4">Spent</th>
                                <th class="px-6 py-4">Remaining</th>
                                <th class="px-6 py-4">Used</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($budgetHistory as $history)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-black text-gray-900">{{ $history->recorded_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 font-bold text-gray-600">RM {{ number_format($history->limit_amount, 2) }}</td>
                                <td class="px-6 py-4 font-bold text-emerald-600">RM {{ number_format($history->current_spending, 2) }}</td>
                                <td class="px-6 py-4 font-bold text-blue-600">RM {{ number_format($history->limit_amount - $history->current_spending, 2) }}</td>
                                <td class="px-6 py-4 font-bold">
                                    @php $percent = $history->limit_amount > 0 ? ($history->current_spending / $history->limit_amount) * 100 : 0; @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 h-2 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500" style="width: {{ min(100, $percent) }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-black">{{ round($percent) }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Modals -->
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

        <div x-show="shareModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm px-4">
            <div class="bg-white rounded-[3rem] p-10 w-full max-w-md shadow-2xl" @click.away="shareModal = false">
                <h2 class="text-2xl font-black text-gray-900 mb-2 tracking-tighter">Share Shopping List</h2>
                <p class="text-gray-500 text-sm mb-6 font-semibold">Share your current shopping list with a family member or friend.</p>
                
                <form action="{{ route('budget.share') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="email" name="email" placeholder="Enter their email address" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl font-semibold text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-emerald-500">
                    <div class="flex gap-4">
                        <button type="button" @click="shareModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs tracking-widest">Cancel</button>
                        <button type="submit" class="flex-1 py-4 bg-blue-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-blue-100">Share</button>
                    </div>
                </form>

                @if($sharedWith && $sharedWith->count() > 0)
                <div class="mt-8 pt-8 border-t border-gray-100">
                    <h3 class="font-black text-gray-900 mb-4 text-sm uppercase tracking-widest">Shared With:</h3>
                    @foreach($sharedWith as $share)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-xl mb-2">
                        <span class="font-semibold text-gray-900 text-sm">{{ $share->sharedWithUser->name }}</span>
                        <span class="text-[10px] font-black text-gray-400">{{ $share->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>