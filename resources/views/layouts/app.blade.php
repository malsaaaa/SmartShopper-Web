<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SmartShopper') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { 
                display: none !important; 
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <main>
                @if (session('success'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        <div class="alert bg-emerald-500 text-white px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-emerald-100 flex items-center justify-between">
                            <span>{{ session('success') }}</span>
                            <button type="button" onclick="this.closest('.alert')?.remove()" class="opacity-50 hover:opacity-100">✕</button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        <div class="alert bg-red-500 text-white px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-red-100 flex items-center justify-between">
                            <span>{{ session('error') }}</span>
                            <button type="button" onclick="this.closest('.alert')?.remove()" class="opacity-50 hover:opacity-100">✕</button>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="bg-white border-t border-gray-100 py-10 mt-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">
                        &copy; {{ date('2025') }} SmartShopper &bull; Utility Finance Assistant
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>