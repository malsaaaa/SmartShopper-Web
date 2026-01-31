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

        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <style>
            [x-cloak] { 
                display: none !important; 
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
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

                @yield('content')
            </main>

            <footer class="bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 py-10 mt-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em]">
                        &copy; {{ date('2025') }} SmartShopper &bull; Utility Finance Assistant
                    </p>
                </div>
            </footer>
        </div>

        <!-- Floating Chat Widget -->
        @auth
        <button id="chat-toggle-btn" class="fixed bottom-6 right-6 z-40 w-14 h-14 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all flex items-center justify-center">
            <svg id="chat-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div id="chat-widget" class="fixed bottom-24 right-6 z-40 w-96 max-h-[600px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden hidden flex flex-col">
            <div class="bg-emerald-600 text-white p-4 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-lg">SmartShopper AI</h3>
                    <p class="text-xs text-emerald-100">Your shopping assistant</p>
                </div>
                <button id="close-widget-btn" class="text-white hover:text-emerald-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900/50">
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 font-semibold text-sm">Hi! How can I help?</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Ask about products or budget</p>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 p-3 bg-white dark:bg-gray-800">
                <form id="chat-form" class="flex gap-2">
                    <input id="message-input" type="text" placeholder="Type message..." class="flex-1 px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:focus:ring-emerald-500" disabled>
                    <button type="submit" id="send-btn" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm" disabled>Send</button>
                </form>
            </div>
        </div>

        <script>
            let chatWidgetOpen = false, currentChatId = null;
            const toggleBtn = document.getElementById('chat-toggle-btn');
            const chatWidget = document.getElementById('chat-widget');
            const closeWidgetBtn = document.getElementById('close-widget-btn');
            const messagesContainer = document.getElementById('chat-messages');
            const messageInput = document.getElementById('message-input');
            const sendBtn = document.getElementById('send-btn');
            const chatForm = document.getElementById('chat-form');
            const chatIcon = document.getElementById('chat-icon');
            const closeIcon = document.getElementById('close-icon');

            toggleBtn.addEventListener('click', () => {
                chatWidgetOpen = !chatWidgetOpen;
                if (chatWidgetOpen) {
                    chatWidget.classList.remove('hidden');
                    chatIcon.classList.add('hidden');
                    closeIcon.classList.remove('hidden');
                    messageInput.focus();
                    if (!currentChatId) initChat();
                } else {
                    chatWidget.classList.add('hidden');
                    chatIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                }
            });

            closeWidgetBtn.addEventListener('click', () => {
                chatWidgetOpen = false;
                chatWidget.classList.add('hidden');
                chatIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            });

            async function initChat() {
                try {
                    const response = await fetch('/api/chat/get-or-create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        },
                    });
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType?.includes('application/json')) {
                        console.error('Invalid response type:', contentType);
                        console.error('Response text:', await response.text());
                        showError('Server error - invalid response type');
                        return;
                    }
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        console.error('API Error:', data);
                        showError(data.error || 'Failed to start chat');
                        return;
                    }
                    
                    currentChatId = data.chat_id;
                    messageInput.disabled = false;
                    sendBtn.disabled = false;
                    if (data.messages && data.messages.length > 0) {
                        messagesContainer.innerHTML = '';
                        data.messages.forEach(msg => displayMessage(msg.content, msg.role));
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                } catch (error) {
                    console.error('Chat Init Error:', error);
                    console.error('Error message:', error.message);
                    showError('Failed to start chat: ' + error.message);
                }
            }

            chatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const message = messageInput.value.trim();
                if (!message) return;
                displayMessage(message, 'user');
                messageInput.value = '';
                messageInput.focus();
                sendBtn.disabled = true;
                try {
                    const response = await fetch('/api/chat/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        },
                        body: JSON.stringify({ chat_id: currentChatId, message: message }),
                    });
                    const data = await response.json();
                    if (data.success) displayMessage(data.message, 'assistant');
                    else showError(data.error || 'Failed to get response');
                } catch (error) {
                    console.error('Error:', error);
                    showError('Error sending message');
                } finally {
                    sendBtn.disabled = false;
                    messageInput.focus();
                }
            });

            function displayMessage(content, role) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;
                const bubble = document.createElement('div');
                bubble.className = role === 'user' 
                    ? 'bg-emerald-600 text-white rounded-xl rounded-tr-none px-3 py-2 max-w-xs text-sm'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl rounded-tl-none px-3 py-2 max-w-xs text-sm';
                bubble.textContent = content;
                messageDiv.appendChild(bubble);
                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            function showError(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-3 py-2 rounded-lg text-sm';
                errorDiv.textContent = message;
                messagesContainer.appendChild(errorDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        </script>
        @endauth
    </body>
</html>