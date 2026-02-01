@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Scraper Settings</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-900">← Back</a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Scraper Status Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🔄 Scraper Status</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Auto Scraping Status -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600 mb-2">Auto Scraping</p>
                    @if($settings->auto_scrape_enabled)
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <p class="font-bold text-green-600">Enabled</p>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <p class="font-bold text-red-600">Disabled</p>
                        </div>
                    @endif
                </div>

                <!-- Current Schedule -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600 mb-2">Schedule</p>
                    <p class="font-bold text-gray-900">{{ \App\Models\ScraperSetting::frequencyOptions()[$settings->scrape_frequency] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Time: {{ $settings->scrape_time }}</p>
                </div>

                <!-- Last Scrape -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600 mb-2">Last Scrape</p>
                    @if($settings->last_scrape_timestamp)
                        <p class="font-bold text-gray-900">{{ \Carbon\Carbon::createFromTimestamp($settings->last_scrape_timestamp)->format('M d, Y H:i') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::createFromTimestamp($settings->last_scrape_timestamp)->diffForHumans() }}</p>
                    @else
                        <p class="text-gray-500 italic">Never run</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">⚙️ Configuration</h2>
            <form method="POST" action="{{ route('admin.scraper.settings.update') }}">
                @csrf
                @method('PATCH')
                
                <div class="space-y-6">
                    <!-- Enable/Disable Toggle -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="auto_scrape_enabled" value="1" 
                                   {{ $settings->auto_scrape_enabled ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <div>
                                <p class="font-bold text-gray-900">Enable Automatic Scraping</p>
                                <p class="text-sm text-gray-600">When disabled, only manual scraping via "Run Scraper" button will work</p>
                            </div>
                        </label>
                    </div>

                    <!-- Scrape Frequency -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Scrape Frequency</label>
                        <select name="scrape_frequency" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(\App\Models\ScraperSetting::frequencyOptions() as $key => $label)
                                <option value="{{ $key }}" {{ $settings->scrape_frequency === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">How often the scraper should run automatically</p>
                    </div>

                    <!-- Scrape Time -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Scrape Time (for Daily schedule)</label>
                        <input type="time" name="scrape_time" value="{{ $settings->scrape_time }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">What time of day to run the daily scraper (in {{ $settings->timezone }})</p>
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Timezone</label>
                        <select name="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @php
                                $timezones = \DateTimeZone::listIdentifiers();
                            @endphp
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ $settings->timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Timezone for scheduling scraper runs</p>
                    </div>

                    <!-- Save Button -->
                    <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold transition-all">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Manual Scraper Controls -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">▶️ Manual Control</h2>
            <div class="flex gap-4">
                <button type="button" onclick="runScraperNow()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold transition-all flex items-center gap-2">
                    🔄 Run Scraper Now
                </button>
                <button type="button" onclick="testScraperNow()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold transition-all flex items-center gap-2">
                    🧪 Test Scraper
                </button>
            </div>
            <p class="text-sm text-gray-600 mt-3">Use <strong>Run Scraper Now</strong> to perform actual scraping, or <strong>Test Scraper</strong> to preview mock scraper logs</p>
        </div>

        <!-- Scraper Logs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">📋 Scraper Logs</h2>
                <form method="POST" action="{{ route('admin.scraper.logs.clear') }}" style="display: inline-block;">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-bold underline" 
                            onclick="return confirm('Clear all logs?')">Clear Logs</button>
                </form>
            </div>
            <div class="p-6 bg-gray-900 text-green-400 font-mono text-sm max-h-96 overflow-y-auto">
                @if(empty($logs))
                    <p class="text-gray-500">No logs yet. Run the scraper to generate logs.</p>
                @else
                    @foreach($logs as $line)
                        <div class="whitespace-pre-wrap break-words">{{ $line }}</div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function runScraperNow() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> <span>Running...</span>';
    
    fetch('{{ route("admin.scraper.run") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('✗ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function testScraperNow() {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> <span>Testing...</span>';
    
    fetch('{{ route("admin.scraper.run") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('✗ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endsection
