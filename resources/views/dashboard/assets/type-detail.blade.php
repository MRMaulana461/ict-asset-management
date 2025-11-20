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
            
            @if(request()->hasAny(['status', 'search', 'brand']))
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
                @if(request('brand'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Brand: {{ request('brand') }}
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

                <!-- Brand Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                    <select name="brand" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Brands</option>
                        @foreach($availableBrands ?? [] as $brand)
                        <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="PO, Item, Serial..."
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
                @if(request()->hasAny(['status', 'search', 'brand']))
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

        <!-- Additional Info Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Top Brands -->
            @if(isset($topBrands) && $topBrands->count() > 0)
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="award" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                    Top Brands
                </h3>
                <div class="space-y-3">
                    @foreach($topBrands as $brand)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">{{ $brand->brand ?? 'Unknown' }}</span>
                        <span class="px-2.5 py-1 bg-saipem-primary/10 text-saipem-primary text-xs font-semibold rounded-full">
                            {{ $brand->total }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Deliveries -->
            @if(isset($recentDeliveries) && $recentDeliveries->count() > 0)
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="truck" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                    Recent Deliveries
                </h3>
                <div class="space-y-3">
                    @foreach($recentDeliveries as $asset)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <span class="font-medium text-gray-900">{{ $asset->po_ref ?? 'N/A' }}</span>
                            <span class="text-gray-500 ml-2">{{ $asset->item_name ?? 'Item' }}</span>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $asset->delivery_date ? \Carbon\Carbon::parse($asset->delivery_date)->format('M d') : 'N/A' }}
                        </span>
                    </div>
                    @endforeach
                </div>
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