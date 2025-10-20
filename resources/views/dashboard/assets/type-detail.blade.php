@extends('layouts.app')

@section('title', 'Asset Type Detail - ' . $assetType->name)

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
                <a href="{{ route('dashboard.assets.analytics') }}" class="ml-1 text-sm font-medium text-gray-600 hover:text-saipem-primary">Asset Details</a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                <span class="ml-1 text-sm font-medium text-gray-700">{{ $assetType->name }}</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="box" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                {{ $assetType->name }} Details
            </h1>
            <p class="text-gray-600 mt-1">Category: <span class="font-semibold">{{ $assetType->category }}</span></p>
            
            @if(request()->hasAny(['status', 'search']))
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="text-xs text-gray-500 flex items-center">
                    <i data-lucide="filter" class="w-3 h-3 mr-1"></i>
                    Active Filters:
                </span>
                @if(request('status') && request('status') != 'all')
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Status: {{ request('status') }}
                </span>
                @endif
                @if(request('search'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Search: {{ request('search') }}
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Main Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- LEFT SIDEBAR - Chart & Filters -->
    <div class="xl:col-span-3">
        <!-- Status Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Status Distribution
            </h3>
            <div class="relative h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="filter" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Filter Assets
            </h3>
            
            <form method="GET" action="" class="space-y-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="In Stock" {{ request('status') == 'In Stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="In Use" {{ request('status') == 'In Use' ? 'selected' : '' }}>In Use</option>
                        <option value="Broken" {{ request('status') == 'Broken' ? 'selected' : '' }}>Broken</option>
                        <option value="Retired" {{ request('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
                        <option value="Taken" {{ request('status') == 'Taken' ? 'selected' : '' }}>Taken</option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Asset tag, serial..."
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
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
                @if(request()->hasAny(['status', 'search']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('dashboard.assets.type.detail', $assetType->id) }}" 
                       class="text-sm text-saipem-primary hover:text-saipem-accent flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-4 h-4 mr-1"></i>
                        Clear All Filters
                    </a>
                </div>
                @endif
            </form>

            <!-- Filter Info -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 leading-relaxed">
                    <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                    Filters will update the asset list and statistics.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Stats & Table -->
    <div class="xl:col-span-9 space-y-6">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100 text-center hover:shadow-xl transition-all duration-300 group">
                <p class="text-gray-500 text-xs mb-1">Total</p>
                <p class="text-2xl font-bold text-gray-800 group-hover:text-saipem-primary transition-colors">{{ $totalAssets }}</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-xl shadow-lg border border-blue-100 text-center hover:shadow-xl transition-all duration-300 group">
                <p class="text-blue-600 text-xs mb-1">In Stock</p>
                <p class="text-2xl font-bold text-blue-700 group-hover:scale-110 transition-transform">{{ $statusBreakdown['in_stock'] }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-xl shadow-lg border border-green-100 text-center hover:shadow-xl transition-all duration-300 group">
                <p class="text-green-600 text-xs mb-1">In Use</p>
                <p class="text-2xl font-bold text-green-700 group-hover:scale-110 transition-transform">{{ $statusBreakdown['in_use'] }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded-xl shadow-lg border border-red-100 text-center hover:shadow-xl transition-all duration-300 group">
                <p class="text-red-600 text-xs mb-1">Broken</p>
                <p class="text-2xl font-bold text-red-700 group-hover:scale-110 transition-transform">{{ $statusBreakdown['broken'] }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl shadow-lg border border-gray-100 text-center hover:shadow-xl transition-all duration-300 group">
                <p class="text-gray-600 text-xs mb-1">Retired</p>
                <p class="text-2xl font-bold text-gray-700 group-hover:scale-110 transition-transform">{{ $statusBreakdown['retired'] }}</p>
            </div>
            <div class="bg-orange-50 p-4 rounded-xl shadow-lg border border-orange-100 text-center hover:shadow-xl transition-all duration-300 group">
                <p class="text-orange-600 text-xs mb-1">Taken</p>
                <p class="text-2xl font-bold text-orange-700 group-hover:scale-110 transition-transform">{{ $statusBreakdown['taken'] }}</p>
            </div>
        </div>

        <!-- Assets Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center">
                        <i data-lucide="table" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Asset List
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Tag</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Serial Number</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Department</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assets as $asset)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $asset->asset_tag }}</span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">
                                <span class="text-sm text-gray-600">{{ $asset->serial_number ?? 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'In Stock' => 'bg-blue-100 text-blue-800',
                                        'In Use' => 'bg-green-100 text-green-800',
                                        'Broken' => 'bg-red-100 text-red-800',
                                        'Retired' => 'bg-gray-100 text-gray-800',
                                        'Taken' => 'bg-orange-100 text-orange-800'
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColors[$asset->status] ?? 'bg-gray-100 text-gray-800' }} inline-flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ str_replace('bg-', 'bg-', str_replace('-100', '-600', $statusColors[$asset->status] ?? 'bg-gray-600')) }}"></span>
                                    {{ $asset->status }}
                                </span>
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
                            <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="text-sm text-gray-600">{{ $asset->assignedEmployee->department ?? 'N/A' }}</span>
                                </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-gray-500 text-lg">No assets found</p>
                                @if(request()->hasAny(['status', 'search']))
                                <a href="{{ route('dashboard.assets.type.detail', $assetType->id) }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
console.log('📄 Asset Type Detail page script loaded');

// Wait for both Chart.js and ICTAssetApp
function waitForDependencies() {
    if (typeof Chart === 'undefined') {
        console.log('⏳ Waiting for Chart.js...');
        setTimeout(waitForDependencies, 100);
        return;
    }
    
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForDependencies, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initAssetTypeDetail !== 'function') {
        console.error('❌ initAssetTypeDetail not found in ICTAssetApp');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }
    
    console.log('✅ All dependencies ready');
    
    // Prepare chart data
    const chartData = {
        labels: {!! json_encode($chartData['labels']) !!},
        data: {!! json_encode($chartData['data']) !!},
        colors: {!! json_encode($chartData['colors']) !!}
    };
    
    console.log('📊 Calling initAssetTypeDetail with data:', chartData);
    window.ICTAssetApp.initAssetTypeDetail(chartData);
}

// Start waiting
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForDependencies);
} else {
    waitForDependencies();
}
</script>
@endpush