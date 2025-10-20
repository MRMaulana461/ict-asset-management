@extends('layouts.app')

@section('title', 'Asset Analytics - ICT Assets')

@section('content')

<!-- Breadcrumb -->
<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-saipem-primary">
                <i data-lucide="home" class="w-4 h-4"></i>
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                <span class="ml-1 text-sm font-medium text-gray-700">Asset Analytics</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="bar-chart-3" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Complete Asset Analytics
            </h1>
            <p class="text-gray-600 mt-1">Comprehensive breakdown of asset status and distribution by type</p>
            
            @if(isset($activeFilters) && count($activeFilters) > 0)
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="text-xs text-gray-500 flex items-center">
                    <i data-lucide="filter" class="w-3 h-3 mr-1"></i>
                    Active Filters:
                </span>
                @foreach($activeFilters as $key => $value)
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                </span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Main Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- LEFT SIDEBAR - Filters -->
    <div class="xl:col-span-3">
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="filter" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Filter Analytics
            </h3>
            
            <form method="GET" class="space-y-4">
                <!-- Asset Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Asset Type</label>
                    <select name="asset_type_id" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Types</option>
                        @foreach($allAssetTypes as $type)
                            <option value="{{ $type->id }}" {{ request('asset_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ $type->category }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
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
                @if(request()->hasAny(['asset_type_id', 'status']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('dashboard.assets.analytics') }}" 
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
                    Filters will update all statistics, charts, and asset lists below.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Analytics -->
    <div class="xl:col-span-9 space-y-6">
        
        <!-- Overall Status Summary -->
        <div class="bg-gradient-to-r from-saipem-primary to-saipem-accent rounded-2xl shadow-xl p-6 text-white transform hover:scale-[1.01] transition-all duration-300">
            <h2 class="text-xl font-bold mb-6 flex items-center">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-2"></i>
                Overall Asset Status Distribution
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300 group">
                    <div class="bg-blue-500/20 p-2 rounded-full w-12 h-12 mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="package" class="w-6 h-6 mx-auto"></i>
                    </div>
                    <p class="text-3xl font-bold mb-1">{{ $statusBreakdown['in_stock'] }}</p>
                    <p class="text-sm opacity-90 font-medium">In Stock</p>
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                        <div class="bg-blue-300 h-1.5 rounded-full" style="width: {{ $totalAssets > 0 ? ($statusBreakdown['in_stock'] / $totalAssets) * 100 : 0 }}%"></div>
                    </div>
                    <p class="text-xs opacity-75 mt-1">{{ $totalAssets > 0 ? round(($statusBreakdown['in_stock'] / $totalAssets) * 100, 1) : 0 }}%</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300 group">
                    <div class="bg-green-500/20 p-2 rounded-full w-12 h-12 mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="check-circle" class="w-6 h-6 mx-auto"></i>
                    </div>
                    <p class="text-3xl font-bold mb-1">{{ $statusBreakdown['in_use'] }}</p>
                    <p class="text-sm opacity-90 font-medium">In Use</p>
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                        <div class="bg-green-300 h-1.5 rounded-full" style="width: {{ $totalAssets > 0 ? ($statusBreakdown['in_use'] / $totalAssets) * 100 : 0 }}%"></div>
                    </div>
                    <p class="text-xs opacity-75 mt-1">{{ $totalAssets > 0 ? round(($statusBreakdown['in_use'] / $totalAssets) * 100, 1) : 0 }}%</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300 group">
                    <div class="bg-red-500/20 p-2 rounded-full w-12 h-12 mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="alert-circle" class="w-6 h-6 mx-auto"></i>
                    </div>
                    <p class="text-3xl font-bold mb-1">{{ $statusBreakdown['broken'] }}</p>
                    <p class="text-sm opacity-90 font-medium">Broken</p>
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                        <div class="bg-red-300 h-1.5 rounded-full" style="width: {{ $totalAssets > 0 ? ($statusBreakdown['broken'] / $totalAssets) * 100 : 0 }}%"></div>
                    </div>
                    <p class="text-xs opacity-75 mt-1">{{ $totalAssets > 0 ? round(($statusBreakdown['broken'] / $totalAssets) * 100, 1) : 0 }}%</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300 group">
                    <div class="bg-gray-500/20 p-2 rounded-full w-12 h-12 mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="archive" class="w-6 h-6 mx-auto"></i>
                    </div>
                    <p class="text-3xl font-bold mb-1">{{ $statusBreakdown['retired'] }}</p>
                    <p class="text-sm opacity-90 font-medium">Retired</p>
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                        <div class="bg-gray-300 h-1.5 rounded-full" style="width: {{ $totalAssets > 0 ? ($statusBreakdown['retired'] / $totalAssets) * 100 : 0 }}%"></div>
                    </div>
                    <p class="text-xs opacity-75 mt-1">{{ $totalAssets > 0 ? round(($statusBreakdown['retired'] / $totalAssets) * 100, 1) : 0 }}%</p>
                </div>
                
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20 hover:bg-white/15 transition-all duration-300 group">
                    <div class="bg-orange-500/20 p-2 rounded-full w-12 h-12 mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="arrow-right" class="w-6 h-6 mx-auto"></i>
                    </div>
                    <p class="text-3xl font-bold mb-1">{{ $statusBreakdown['taken'] }}</p>
                    <p class="text-sm opacity-90 font-medium">Taken</p>
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                        <div class="bg-orange-300 h-1.5 rounded-full" style="width: {{ $totalAssets > 0 ? ($statusBreakdown['taken'] / $totalAssets) * 100 : 0 }}%"></div>
                    </div>
                    <p class="text-xs opacity-75 mt-1">{{ $totalAssets > 0 ? round(($statusBreakdown['taken'] / $totalAssets) * 100, 1) : 0 }}%</p>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Utilization Rate</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2 group-hover:text-green-600 transition-colors">{{ $utilizationRate }}%</p>
                        <p class="text-sm text-green-600 mt-2 flex items-center">
                            <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                            Assets actively in use
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="activity" class="w-8 h-8 text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Assets</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2 group-hover:text-blue-600 transition-colors">{{ $totalAssets }}</p>
                        <p class="text-sm text-blue-600 mt-2 flex items-center">
                            <i data-lucide="package" class="w-4 h-4 mr-1"></i>
                            Across All
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="database" class="w-8 h-8 text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Asset Types</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2 group-hover:text-purple-600 transition-colors">{{ $assetTypesCount }}</p>
                        <p class="text-sm text-purple-600 mt-2 flex items-center">
                            <i data-lucide="layers" class="w-4 h-4 mr-1"></i>
                            {{ request()->hasAny(['asset_type_id', 'status']) ? 'Filtered categories' : 'Different categories' }}
                        </p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="shapes" class="w-8 h-8 text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assets by Type with Status Breakdown -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i data-lucide="layers" class="w-6 h-6 mr-3 text-blue-600"></i>
                    Assets by Type - Detailed Breakdown
                </h2>
                <div class="text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                    {{ $assetsByType->count() }} types found
                </div>
            </div>
            
            <div class="space-y-4">
                @forelse($assetsByType as $asset)
                    <div class="border border-gray-200 rounded-xl p-5 hover:border-saipem-accent hover:shadow-md transition-all duration-300 group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <h3 class="text-lg font-bold text-gray-800 group-hover:text-saipem-primary transition-colors">{{ $asset->assetType->name }}</h3>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full font-medium border">
                                        {{ $asset->assetType->category }}
                                    </span>
                                    <span class="px-3 py-1 bg-gradient-to-r from-saipem-primary to-saipem-accent text-white text-sm rounded-full font-bold shadow-sm">
                                        Total: {{ $asset->total }}
                                    </span>
                                </div>
                                
                                <!-- Status Breakdown for this type -->
                                <div class="grid grid-cols-5 gap-3 mt-4">
                                    <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-100 hover:border-blue-300 transition-colors group/item">
                                        <p class="text-xl font-bold text-blue-600 group-hover/item:scale-110 transition-transform">{{ $asset->in_stock_count }}</p>
                                        <p class="text-xs text-blue-700 font-medium">In Stock</p>
                                        <div class="w-full bg-blue-200 rounded-full h-1 mt-2">
                                            <div class="bg-blue-500 h-1 rounded-full" style="width: {{ $asset->total > 0 ? ($asset->in_stock_count / $asset->total) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-center p-3 bg-green-50 rounded-lg border border-green-100 hover:border-green-300 transition-colors group/item">
                                        <p class="text-xl font-bold text-green-600 group-hover/item:scale-110 transition-transform">{{ $asset->in_use_count }}</p>
                                        <p class="text-xs text-green-700 font-medium">In Use</p>
                                        <div class="w-full bg-green-200 rounded-full h-1 mt-2">
                                            <div class="bg-green-500 h-1 rounded-full" style="width: {{ $asset->total > 0 ? ($asset->in_use_count / $asset->total) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded-lg border border-red-100 hover:border-red-300 transition-colors group/item">
                                        <p class="text-xl font-bold text-red-600 group-hover/item:scale-110 transition-transform">{{ $asset->broken_count }}</p>
                                        <p class="text-xs text-red-700 font-medium">Broken</p>
                                        <div class="w-full bg-red-200 rounded-full h-1 mt-2">
                                            <div class="bg-red-500 h-1 rounded-full" style="width: {{ $asset->total > 0 ? ($asset->broken_count / $asset->total) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-gray-300 transition-colors group/item">
                                        <p class="text-xl font-bold text-gray-600 group-hover/item:scale-110 transition-transform">{{ $asset->retired_count }}</p>
                                        <p class="text-xs text-gray-700 font-medium">Retired</p>
                                        <div class="w-full bg-gray-200 rounded-full h-1 mt-2">
                                            <div class="bg-gray-500 h-1 rounded-full" style="width: {{ $asset->total > 0 ? ($asset->retired_count / $asset->total) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-100 hover:border-orange-300 transition-colors group/item">
                                        <p class="text-xl font-bold text-orange-600 group-hover/item:scale-110 transition-transform">{{ $asset->taken_count }}</p>
                                        <p class="text-xs text-orange-700 font-medium">Taken</p>
                                        <div class="w-full bg-orange-200 rounded-full h-1 mt-2">
                                            <div class="bg-orange-500 h-1 rounded-full" style="width: {{ $asset->total > 0 ? ($asset->taken_count / $asset->total) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('dashboard.assets.type.detail', $asset->assetType->id) }}" 
                               class="ml-4 bg-gradient-to-r from-saipem-primary to-saipem-accent hover:from-saipem-accent hover:to-saipem-primary text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 inline-flex items-center whitespace-nowrap group/btn">
                                View Details
                                <i data-lucide="arrow-right" class="w-4 h-4 ml-2 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden mt-3 shadow-inner">
                            <div class="h-full flex rounded-full">
                                @if($asset->in_stock_count > 0)
                                    <div class="bg-blue-500 transition-all duration-500" style="width: {{ ($asset->in_stock_count / $asset->total) * 100 }}%" 
                                         title="In Stock: {{ $asset->in_stock_count }}"></div>
                                @endif
                                @if($asset->in_use_count > 0)
                                    <div class="bg-green-500 transition-all duration-500" style="width: {{ ($asset->in_use_count / $asset->total) * 100 }}%" 
                                         title="In Use: {{ $asset->in_use_count }}"></div>
                                @endif
                                @if($asset->broken_count > 0)
                                    <div class="bg-red-500 transition-all duration-500" style="width: {{ ($asset->broken_count / $asset->total) * 100 }}%" 
                                         title="Broken: {{ $asset->broken_count }}"></div>
                                @endif
                                @if($asset->retired_count > 0)
                                    <div class="bg-gray-500 transition-all duration-500" style="width: {{ ($asset->retired_count / $asset->total) * 100 }}%" 
                                         title="Retired: {{ $asset->retired_count }}"></div>
                                @endif
                                @if($asset->taken_count > 0)
                                    <div class="bg-orange-500 transition-all duration-500" style="width: {{ ($asset->taken_count / $asset->total) * 100 }}%" 
                                         title="Taken: {{ $asset->taken_count }}"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                        <p class="text-gray-500 text-lg">No assets found matching your filters</p>
                        <a href="{{ route('dashboard.assets.analytics') }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
                            Clear filters to see all assets
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
@vite(['resources/js/app.js'])
<script>
    // Initialize when app is ready
    function initializeAssetAnalytics() {
        if (window.ICTAssetApp && typeof window.ICTAssetApp.initAssetIndex === 'function') {
            window.ICTAssetApp.initAssetIndex();
        } else {
            setTimeout(initializeAssetAnalytics, 100);
        }
    }
    
    document.addEventListener('DOMContentLoaded', initializeAssetAnalytics);
</script>
@endpush