@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 md:p-12">

        <!-- Header -->
        <div class="mb-12 text-center">
            <div class="w-20 h-20 bg-emerald-500 rounded-2xl flex items-center justify-center text-white mx-auto mb-6 shadow-lg shadow-emerald-100">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-4 tracking-tight">About SmartShopper</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Your intelligent marketplace companion for smart shopping decisions</p>
        </div>

        <!-- Mission -->
        <div class="mb-12">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-4 tracking-tighter">Our Mission</h2>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                SmartShopper is designed to help Malaysian shoppers make informed purchasing decisions by aggregating price data from multiple retailers. We believe that price transparency and smart budgeting are key to financial wellness.
            </p>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                Our platform connects you with the best deals across leading Malaysian retailers including Mydin and Shopee, with Lotus's and Lazada coming soon.
            </p>
        </div>

        <!-- Features -->
        <div class="mb-12">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6 tracking-tighter">Key Features</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-emerald-50 dark:bg-gray-700 rounded-xl p-6 border border-emerald-100 dark:border-gray-600">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-emerald-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2">Smart Price Comparison</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Compare prices across multiple retailers instantly and find the best deals.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-gray-700 rounded-xl p-6 border border-blue-100 dark:border-gray-600">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2">Budget Planning</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Create budgets and track your spending with our easy-to-use planning tools.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 dark:bg-gray-700 rounded-xl p-6 border border-purple-100 dark:border-gray-600">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2">Smart Recommendations</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Get intelligent product recommendations based on price and quality.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-50 dark:bg-gray-700 rounded-xl p-6 border border-orange-100 dark:border-gray-600">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2">Frequent Updates</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Prices are refreshed regularly throughout the day to ensure you always have the latest market information.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Retailers -->
        <div class="mb-12">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6 tracking-tighter text-center">Our Retail Partners</h2>
            <p class="text-gray-700 dark:text-gray-300 mb-6 text-center">We work with Malaysia's leading retailers to bring you the most comprehensive price data:</p>
            
            <!-- Active Retailers -->
            <div class="mb-8">
                <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 mb-3 uppercase tracking-widest text-center">Currently Active</h3>
                <div class="flex justify-center gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 text-center border border-gray-100 dark:border-gray-600 w-32">
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-2">🏬</div>
                        <p class="font-bold text-gray-900 dark:text-white">Mydin</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 text-center border border-gray-100 dark:border-gray-600 w-32">
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-2">🛒</div>
                        <p class="font-bold text-gray-900 dark:text-white">Shopee</p>
                    </div>
                </div>
            </div>

            <!-- Coming Soon -->
            <div>
                <h3 class="text-sm font-bold text-gray-600 dark:text-gray-400 mb-3 uppercase tracking-widest text-center">Coming Soon</h3>
                <div class="flex justify-center gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 text-center border border-blue-200 dark:border-blue-800 opacity-60 w-32">
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-2">🏪</div>
                        <p class="font-bold text-gray-900 dark:text-white">Lotus's</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Coming Soon</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 text-center border border-blue-200 dark:border-blue-800 opacity-60 w-32">
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-2">📦</div>
                        <p class="font-bold text-gray-900 dark:text-white">Lazada</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Coming Soon</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <div class="mb-12">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6 tracking-tighter">Why Choose SmartShopper?</h2>
            <ul class="space-y-4">
                <li class="flex items-start gap-4">
                    <div class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center text-white flex-shrink-0 mt-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">Transparent Pricing</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">See exactly what you'll pay at each retailer with no hidden fees.</p>
                    </div>
                </li>
                <li class="flex items-start gap-4">
                    <div class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center text-white flex-shrink-0 mt-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">Save Time & Money</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">No need to visit multiple stores. Find the best prices in seconds.</p>
                    </div>
                </li>
                <li class="flex items-start gap-4">
                    <div class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center text-white flex-shrink-0 mt-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">Easy Budget Management</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">Set spending limits and track your expenses effortlessly.</p>
                    </div>
                </li>
                <li class="flex items-start gap-4">
                    <div class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center text-white flex-shrink-0 mt-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">Made for Malaysia</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">Built with Malaysian shoppers in mind, supporting local retailers.</p>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="bg-emerald-50 dark:bg-gray-700 border border-emerald-100 dark:border-gray-600 rounded-xl p-8 text-center">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-4 tracking-tighter">Have Questions?</h2>
            <p class="text-gray-700 dark:text-gray-300 mb-6">
                We'd love to hear from you. If you have any questions or feedback about SmartShopper, please don't hesitate to reach out.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="mailto:support@smartshopper.my" class="px-8 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-sm uppercase tracking-widest rounded-xl transition-colors">
                    Contact Us
                </a>
                <a href="{{ route('dashboard') }}" class="px-8 py-3 border-2 border-emerald-500 text-emerald-600 dark:text-emerald-400 dark:border-emerald-400 font-black text-sm uppercase tracking-widest rounded-xl hover:bg-emerald-50 dark:hover:bg-gray-600 transition-colors">
                    Back to Marketplace
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
