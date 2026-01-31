@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Edit Retailer</h1>
            <a href="{{ route('admin.retailers') }}" class="text-blue-600 hover:text-blue-900">← Back</a>
        </div>
    </div>

    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.retailers.update', $retailer) }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Retailer Name</label>
                    <input type="text" name="name" value="{{ $retailer->name }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Base URL</label>
                    <input type="url" name="base_url" value="{{ $retailer->base_url }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo URL</label>
                    <input type="url" name="logo_url" value="{{ $retailer->logo_url }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @if($retailer->logo_url)
                        <img src="{{ $retailer->logo_url }}" alt="{{ $retailer->name }}" class="mt-2 h-20 w-auto">
                    @endif
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.retailers') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
