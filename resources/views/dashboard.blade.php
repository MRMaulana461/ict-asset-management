@extends('layouts.app')

@section('title', 'Dashboard - ICT Assets')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard Overview</h1>
    <p class="text-gray-600 mt-1">Real-time ICT Asset Management Analytics</p>
</div>

<div class="col-span-1 lg:col-span-4 mb-6">
    <div class="bg-gradient-to-r from-saipem-primary to-saipem-accent rounded-xl shadow-lg p-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold mb-2">View All Recent Activities</h3>
                <p class="text-blue-100 text-sm">Complete timeline of loans, returns, broken assets, and damages</p>
            </div>
            <a href="{{ route('dashboard.activities.detail') }}" 
               class="bg-white text-saipem-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-flex items-center">
                Open Timeline
                <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- ALERTS SECTION -->
<div class="space-y-4 mb-8">
    @if($lowStockItems->count() > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
        <div class="flex items-start">
            <i data-lucide="package" class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0"></i>
            <div class="flex-1">
                <h3 class="text-yellow-800 font-semibold">Low Stock Warning</h3>
                <p class="text-yellow-700 text-sm mt-1">The following items are running low on stock:</p>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($lowStockItems as $item)
                        <div class="bg-white p-2 rounded border border-yellow-200">
                            <strong class="text-yellow-800 text-sm">{{ $item['name'] }}</strong>
                            <span class="text-yellow-700 text-xs block">Only {{ $item['available'] }} unit(s) available</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- MAIN ANALYTICS GRID -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    
    <!-- Asset Analytics - Combined View -->
    <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2 text-blue-600"></i>
                Asset Analytics Overview
            </h2>
            <a href="{{ route('dashboard.assets.analytics') }}" 
               class="text-sm text-saipem-primary hover:text-saipem-accent font-medium inline-flex items-center">
                View Detailed Analytics
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>
        
        <!-- Quick Status Summary -->
        <div class="grid grid-cols-5 gap-3 mb-6">
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ $statusBreakdown['in_stock'] }}</p>
                <p class="text-xs text-blue-700 mt-1">In Stock</p>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ $statusBreakdown['in_use'] }}</p>
                <p class="text-xs text-green-700 mt-1">In Use</p>
            </div>
            <div class="text-center p-3 bg-red-50 rounded-lg">
                <p class="text-2xl font-bold text-red-600">{{ $statusBreakdown['broken'] }}</p>
                <p class="text-xs text-red-700 mt-1">Broken</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-gray-600">{{ $statusBreakdown['retired'] }}</p>
                <p class="text-xs text-gray-700 mt-1">Retired</p>
            </div>
            <div class="text-center p-3 bg-orange-50 rounded-lg">
                <p class="text-2xl font-bold text-orange-600">{{ $statusBreakdown['taken'] }}</p>
                <p class="text-xs text-orange-700 mt-1">Taken</p>
            </div>
        </div>

        <!-- Top Asset Types -->
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Top Asset Types</h3>
            @foreach($assetsByType->take(5) as $asset)
                <div class="group hover:bg-gray-50 p-3 rounded-lg transition-colors cursor-pointer border border-transparent hover:border-saipem-accent">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <span class="font-medium text-gray-800 group-hover:text-saipem-primary transition-colors">
                                {{ $asset->assetType->name }}
                            </span>
                            <span class="text-xs text-gray-500 ml-2">({{ $asset->assetType->category }})</span>
                        </div>
                        <span class="font-semibold text-gray-900 bg-gray-100 px-2 py-1 rounded text-sm group-hover:bg-saipem-primary group-hover:text-white transition-colors">
                            {{ $asset->total }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-saipem-primary h-2 rounded-full transition-all duration-500 group-hover:bg-saipem-accent" 
                            style="width: {{ $totalAssets > 0 ? ($asset->total / $totalAssets) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Most Borrowed Items -->
    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i data-lucide="trending-up" class="w-5 h-5 mr-2 text-purple-600"></i>
                Most Borrowed Items
            </h2>
        </div>
        @if($mostBorrowedItems->count() > 0)
            <div class="space-y-3">
                @foreach($mostBorrowedItems as $index => $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-purple-50 transition-colors cursor-pointer">
                        <div class="flex items-center">
                            <span class="w-6 h-6 bg-saipem-primary text-white rounded-full text-xs flex items-center justify-center font-bold mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $item['asset_type'] }}</p>
                            </div>
                        </div>
                        <span class="bg-saipem-accent text-white px-3 py-1 rounded-full text-sm font-bold">
                            {{ $item['count'] }}x
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('dashboard.loans.borrowed') }}" 
                   class="text-sm text-saipem-primary hover:text-saipem-accent font-medium inline-flex items-center">
                    View Complete History
                    <i data-lucide="external-link" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        @else
            <div class="text-center py-8">
                <i data-lucide="bar-chart-3" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <p class="text-gray-500">No loan data available</p>
            </div>
        @endif
    </div>

    <!-- Most Damaged Items -->
    <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i data-lucide="alert-octagon" class="w-5 h-5 mr-2 text-red-600"></i>
                Most Damaged Items
            </h2>
        </div>
        @if($mostDamagedItems->count() > 0)
            <div class="space-y-3">
                @foreach($mostDamagedItems as $index => $item)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors cursor-pointer">
                        <div class="flex items-center">
                            <span class="w-6 h-6 bg-red-600 text-white rounded-full text-xs flex items-center justify-center font-bold mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $item['asset_type'] }}</p>
                            </div>
                        </div>
                        <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                            {{ $item['count'] }}x
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('dashboard.damages.detail') }}" 
                   class="text-sm text-saipem-primary hover:text-saipem-accent font-medium inline-flex items-center">
                    View Damage Reports
                    <i data-lucide="external-link" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        @else
            <div class="text-center py-8">
                <i data-lucide="smile" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <p class="text-gray-500">No damage reports</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
console.log('📄 Dashboard page script loaded');

// Wait for ICTAssetApp
function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForApp, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initDashboardPanel !== 'function') {
        console.error('❌ initDashboardPanel not found in ICTAssetApp');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }
    
    console.log('✅ ICTAssetApp ready, calling initDashboardPanel()');
    window.ICTAssetApp.initDashboardPanel();
}

// Start waiting
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForApp);
} else {
    waitForApp();
}
</script>
@endpush
@endsection