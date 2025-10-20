@extends('layouts.app')

@section('title', 'Asset List')

@section('content')

<!-- Breadcrumb -->
<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-saipem-primary">
                <i data-lucide="home" class="w-4 h-4"></i>
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                <span class="ml-1 text-sm font-medium text-gray-700">Assets</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="package" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Asset Management
            </h1>
            <p class="text-gray-600 mt-1">Track and manage all company assets</p>
            
            @if(request()->hasAny(['search', 'asset_type_id', 'status']))
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="text-xs text-gray-500 flex items-center">
                    <i data-lucide="filter" class="w-3 h-3 mr-1"></i>
                    Active Filters:
                </span>
                @if(request('search'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Search: {{ request('search') }}
                </span>
                @endif
                @if(request('asset_type_id'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Type: {{ $assetTypes->find(request('asset_type_id'))->name ?? 'N/A' }}
                </span>
                @endif
                @if(request('status'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Status: {{ request('status') }}
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Main Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- LEFT SIDEBAR - Filters -->
    <div class="xl:col-span-3">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="filter" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Filter Assets
            </h3>
            
            <form method="GET" action="{{ route('assets.index') }}" class="space-y-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Asset Tag, SN, Owner..."
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                </div>

                <!-- Asset Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Asset Type</label>
                    <select name="asset_type_id" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Types</option>
                        @foreach($assetTypes as $type)
                            <option value="{{ $type->id }}" {{ request('asset_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Status</option>
                        <option value="In Stock" {{ request('status') == 'In Stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="In Use" {{ request('status') == 'In Use' ? 'selected' : '' }}>In Use</option>
                        <option value="Broken" {{ request('status') == 'Broken' ? 'selected' : '' }}>Broken</option>
                        <option value="Retired" {{ request('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
                        <option value="Taken" {{ request('status') == 'Taken' ? 'selected' : '' }}>Taken</option>
                    </select>
                </div>

                <!-- Apply Button -->
                <div>
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-saipem-primary to-saipem-accent hover:from-saipem-accent hover:to-saipem-primary text-white px-4 py-2.5 rounded-lg font-medium transition-all duration-300 shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Apply Filters
                    </button>
                </div>

                <!-- Clear Filters -->
                @if(request()->hasAny(['search', 'asset_type_id', 'status']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('assets.index') }}" 
                       class="text-sm text-saipem-primary hover:text-saipem-accent flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-4 h-4 mr-1"></i>
                        Clear All Filters
                    </a>
                </div>
                @endif
            </form>

            <!-- Export Button -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <button type="button" 
                        data-action="open-export-modal" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    Export Excel
                </button>
            </div>

            <!-- Filter Info -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 leading-relaxed">
                    <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                    Use filters to quickly find specific assets by type or status.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Stats & Table -->
    <div class="xl:col-span-9 space-y-6">
       
        <!-- Assets Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center">
                        <i data-lucide="table" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Asset Records
                    </h2>
                    <span class="text-sm text-gray-600 bg-gray-50 px-3 py-1 rounded-full">
                        {{ $assets->total() }} assets found
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Employee ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $asset->assetType->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $asset->asset_tag }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($asset->assignedEmployee)
                                <div class="flex items-center">
                                    <div class="ml-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $asset->assignedEmployee->name }}</div>
                                    </div>
                                </div>
                                @else
                                <span class="text-sm text-gray-500 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 hidden lg:table-cell">
                                {{ $asset->assignedEmployee->employee_id ?? '-' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 hidden sm:table-cell">
                                {{ $asset->assignedEmployee->department ?? '-' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'In Stock' => 'bg-blue-100 text-blue-800',
                                        'In Use' => 'bg-green-100 text-green-800',
                                        'Broken' => 'bg-red-100 text-red-800',
                                        'Retired' => 'bg-gray-100 text-gray-800',
                                        'Taken' => 'bg-orange-100 text-orange-800',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColors[$asset->status] ?? 'bg-gray-100 text-gray-800' }} inline-flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ str_replace('bg-', 'bg-', str_replace('-100', '-600', $statusColors[$asset->status] ?? 'bg-gray-600')) }}"></span>
                                    {{ $asset->status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('assets.show', $asset) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors"
                                       title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('assets.edit', $asset) }}" 
                                       class="text-saipem-primary hover:text-saipem-accent transition-colors"
                                       title="Edit">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-gray-500 text-lg">No assets found</p>
                                @if(request()->hasAny(['search', 'asset_type_id', 'status']))
                                <a href="{{ route('assets.index') }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
                                    Clear filters to see all assets
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($assets->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                {{ $assets->onEachSide(1)->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 m-4">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center">
                <i data-lucide="download" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Export Asset Data
            </h2>
            <button type="button" data-action="close-export-modal" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('assets.export') }}" method="GET" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range (Status Date)</label>
                <div class="grid grid-cols-2 gap-3">
                    <input type="date" 
                           name="start_date" 
                           class="border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                    <input type="date" 
                           name="end_date" 
                           class="border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asset Type</label>
                <select name="asset_type_id" 
                        class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                    <option value="">All Types</option>
                    @foreach($assetTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" 
                        class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                    <option value="">All Status</option>
                    <option value="In Stock">In Stock</option>
                    <option value="In Use">In Use</option>
                    <option value="Broken">Broken</option>
                    <option value="Retired">Retired</option>
                    <option value="Taken">Taken</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" 
                        data-action="close-export-modal" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition-all shadow-lg hover:shadow-xl inline-flex items-center">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    Export
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
console.log('📄 Asset Index page script loaded');

// Wait for ICTAssetApp
function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForApp, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initAssetIndex !== 'function') {
        console.error('❌ initAssetIndex not found in ICTAssetApp');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }
    
    console.log('✅ ICTAssetApp ready, calling initAssetIndex()');
    window.ICTAssetApp.initAssetIndex();
}

// Start waiting
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForApp);
} else {
    waitForApp();
}
</script>
@endpush