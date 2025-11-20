@extends('layouts.app')

@section('title', 'Dashboard - ICT Assets')

@section('content')

<!-- HEADER -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Overview</h1>
            <p class="text-gray-600 mt-1">Real-time ICT Asset Management Analytics</p>
        </div>
    </div>
</div>

<!-- EXPORT PROGRESS MODAL -->
<div id="exportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-saipem-primary mx-auto mb-4"></div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Generating Report...</h3>
            <p class="text-gray-600 text-sm">
                Creating comprehensive Excel dashboard with 12 sheets.<br>
                This may take a moment.
            </p>

            <div class="mt-4 space-y-2 text-left">
                <div class="flex items-center text-sm text-gray-600">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i> Executive Summary
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i> Asset Inventory
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i> Loan & Damage Reports
                </div>
                <div class="flex items-center text-sm text-gray-600 animate-pulse">
                    <i data-lucide="loader" class="w-4 h-4 text-blue-500 mr-2"></i> Analytics & Insights
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RECENT ACTIVITIES -->
<div class="mb-6">
    <div class="bg-gradient-to-r from-saipem-primary to-saipem-accent rounded-xl shadow-lg p-6 text-white">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold mb-2 flex items-center">
                    <i data-lucide="activity" class="w-5 h-5 mr-2"></i> Recent Activities Timeline
                </h3>
                <p class="text-blue-100 text-sm">
                    Complete timeline of loans, returns, broken assets, and damages
                </p>
            </div>

            <a href="{{ route('dashboard.activities.detail') }}" 
               class="bg-white text-saipem-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-flex items-center whitespace-nowrap">
                Open Timeline
                <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- ALERTS: LOW STOCK -->
@if($lowStockItems->count() > 0)
<div class="mb-6">
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
        <div class="flex items-start">
            <i data-lucide="package" class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0"></i>
            <div class="flex-1">
                <h3 class="text-yellow-800 font-semibold flex items-center">
                    Low Stock Warning
                    <span class="ml-2 px-2 py-1 bg-yellow-200 text-yellow-800 text-xs rounded-full">
                        {{ $lowStockItems->count() }} items
                    </span>
                </h3>
                <p class="text-yellow-700 text-sm mt-1">The following items are running low on stock:</p>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($lowStockItems as $item)
                        <div class="bg-white p-3 rounded border border-yellow-200 hover:border-yellow-400 transition-colors">
                            <strong class="text-yellow-800 text-sm">{{ $item['name'] }}</strong>
                            <span class="text-yellow-700 text-xs block mt-1">
                                <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>
                                Only {{ $item['available'] }} unit(s) available
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- MAIN ANALYTICS GRID -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- ASSET ANALYTICS -->
    <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2 text-blue-600"></i> Asset Analytics Overview
            </h2>
            <a href="{{ route('dashboard.assets.analytics') }}" 
               class="text-sm text-saipem-primary hover:text-saipem-accent font-medium inline-flex items-center">
                View Detailed Analytics
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>

        <!-- STATUS SUMMARY -->
        <div class="grid grid-cols-5 gap-3 mb-6">
            @php
                $statuses = [
                    ['label' => 'In Stock', 'color' => 'blue', 'icon' => 'package', 'count' => $statusBreakdown['in_stock']],
                    ['label' => 'In Use', 'color' => 'green', 'icon' => 'check-circle', 'count' => $statusBreakdown['in_use']],
                    ['label' => 'Broken', 'color' => 'red', 'icon' => 'alert-circle', 'count' => $statusBreakdown['broken']],
                    ['label' => 'Retired', 'color' => 'gray', 'icon' => 'archive', 'count' => $statusBreakdown['retired']],
                    ['label' => 'Taken', 'color' => 'orange', 'icon' => 'arrow-right', 'count' => $statusBreakdown['taken']],
                ];
            @endphp

            @foreach($statuses as $status)
            <div class="text-center p-3 bg-{{ $status['color'] }}-50 rounded-lg hover:bg-{{ $status['color'] }}-100 transition-colors cursor-pointer">
                <div class="flex items-center justify-center mb-2">
                    <i data-lucide="{{ $status['icon'] }}" class="w-5 h-5 text-{{ $status['color'] }}-600"></i>
                </div>
                <p class="text-2xl font-bold text-{{ $status['color'] }}-600">{{ $status['count'] }}</p>
                <p class="text-xs text-{{ $status['color'] }}-700 mt-1 font-medium">{{ $status['label'] }}</p>
            </div>
            @endforeach
        </div>

        <!-- TOP ASSET TYPES -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Top 5 Asset Types
            </h3>

            <div class="space-y-3">
                @foreach($assetsByType->take(5) as $asset)
                <div class="group hover:bg-gray-50 p-3 rounded-lg transition-colors cursor-pointer border border-transparent hover:border-saipem-accent">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <span class="font-medium text-gray-800 group-hover:text-saipem-primary transition-colors">
                                {{ $asset->assetType->name }}
                            </span>
                            <span class="text-xs text-gray-500 ml-2">
                                ({{ $asset->assetType->category }})
                            </span>
                        </div>
                        <span class="font-semibold text-gray-900 bg-gray-100 px-3 py-1 rounded-full text-sm group-hover:bg-saipem-primary group-hover:text-white transition-colors">
                            {{ $asset->total }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-saipem-primary h-2 rounded-full transition-all duration-500 group-hover:bg-saipem-accent"
                             style="width: {{ $totalAssets > 0 ? ($asset->total / $totalAssets) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MOST BORROWED -->
    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i data-lucide="trending-up" class="w-5 h-5 mr-2 text-purple-600"></i> Most Borrowed Items
            </h2>
            <p class="text-xs text-gray-500 mt-1">Top 5 most frequently borrowed</p>
        </div>

        @if($mostBorrowedItems->count() > 0)
            <div class="space-y-3">
                @foreach($mostBorrowedItems as $index => $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-purple-50 transition-colors cursor-pointer group">
                        <div class="flex items-center flex-1 min-w-0">
                            <span class="w-8 h-8 bg-saipem-primary group-hover:bg-purple-600 text-white rounded-full text-xs flex items-center justify-center font-bold mr-3 flex-shrink-0 transition-colors">
                                {{ $index + 1 }}
                            </span>
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item['asset_type'] }}</p>
                        </div>
                        <span class="bg-purple-100 group-hover:bg-purple-600 group-hover:text-white text-purple-700 px-3 py-1 rounded-full text-sm font-bold ml-2 transition-colors">
                            {{ $item['count'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                <a href="{{ route('dashboard.loans.borrowed') }}" 
                   class="text-sm text-saipem-primary hover:text-saipem-accent font-medium inline-flex items-center">
                    View Complete History
                    <i data-lucide="external-link" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        @else
            <div class="text-center py-8">
                <i data-lucide="bar-chart-3" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <p class="text-gray-500 text-sm">No loan data available</p>
            </div>
        @endif
    </div>

    <!-- MOST DAMAGED -->
    <div class="xl:col-span-3 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i data-lucide="alert-octagon" class="w-5 h-5 mr-2 text-red-600"></i> Most Damaged Items
            </h2>
            <p class="text-xs text-gray-500 mt-1">Top 5 items with most damage reports</p>
        </div>

        @if($mostDamagedItems->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                @foreach($mostDamagedItems as $index => $item)
                    <div class="flex flex-col items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors cursor-pointer group border border-red-100 hover:border-red-300">
                        <span class="w-10 h-10 bg-red-600 group-hover:scale-110 text-white rounded-full text-sm flex items-center justify-center font-bold mb-3 transition-transform">
                            {{ $index + 1 }}
                        </span>
                        <p class="text-sm font-medium text-gray-800 text-center mb-2">{{ $item['asset_type'] }}</p>
                        <span class="bg-red-600 text-white px-4 py-1 rounded-full text-sm font-bold">
                            {{ $item['count'] }}x
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                <a href="{{ route('dashboard.damages.detail') }}" 
                   class="text-sm text-saipem-primary hover:text-saipem-accent font-medium inline-flex items-center">
                    View Damage Reports
                    <i data-lucide="external-link" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        @else
            <div class="text-center py-8">
                <i data-lucide="smile" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <p class="text-gray-500 text-sm">No damage reports</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
console.log('📄 Dashboard page script loaded');

// EXPORT PROGRESS MODAL
function showExportProgress(event) {
    document.getElementById('exportModal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();

    setTimeout(() => {
        document.getElementById('exportModal').classList.add('hidden');
    }, 5000);
}

// WAIT FOR ICTAssetApp INIT
function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        return setTimeout(waitForApp, 100);
    }

    if (typeof window.ICTAssetApp.initDashboardPanel !== 'function') {
        console.error('❌ initDashboardPanel not found in ICTAssetApp');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }

    console.log('✅ ICTAssetApp ready, calling initDashboardPanel()');
    window.ICTAssetApp.initDashboardPanel();
}

document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', waitForApp)
    : waitForApp();
</script>
@endpush
