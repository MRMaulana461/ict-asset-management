@extends('layouts.app')

@section('title', 'Damage Reports - Asset Withdrawals')

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
                <span class="ml-1 text-sm font-medium text-gray-700">Damage Reports</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="alert-triangle" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Damage Reports Analytics
            </h1>
            <p class="text-gray-600 mt-1">Asset withdrawal and damage analysis</p>
            
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
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="filter" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Filter Reports
            </h3>
            
            <form method="GET" action="" class="space-y-4">
                <!-- Asset Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Asset Type</label>
                    <select name="asset_type_id" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Types</option>
                        @foreach($assetTypes as $type)
                            <option value="{{ $type->id }}" {{ request('asset_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Employee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <select name="employee_id" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Employee, reason..."
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
                @if(request()->hasAny(['asset_type_id', 'employee_id', 'date_from', 'date_to', 'search']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('dashboard.damages.detail') }}" 
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
                    Filters will update all statistics, charts, and damage records below.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Analytics -->
    <div class="xl:col-span-9 space-y-6">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Total Reports</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1 group-hover:text-purple-600 transition-colors">{{ $stats['total_withdrawals'] }}</p>
                        <p class="text-sm text-purple-600 mt-2 flex items-center">
                            <i data-lucide="file-text" class="w-4 h-4 mr-1"></i>
                            {{ request()->hasAny(['asset_type_id', 'employee_id', 'date_from', 'date_to', 'search']) ? 'Filtered' : 'All time' }}
                        </p>
                    </div>
                    <div class="bg-purple-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="file-text" class="w-4 h-4 sm:w-6 sm:h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">This Month</p>
                        <p class="text-xl sm:text-2xl font-bold text-purple-600 mt-1 group-hover:scale-110 transition-transform">{{ $stats['this_month'] }}</p>
                        <p class="text-sm text-purple-600 mt-2 flex items-center">
                            <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                            Current month
                        </p>
                    </div>
                    <div class="bg-purple-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="calendar" class="w-4 h-4 sm:w-6 sm:h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Total Quantity</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-600 mt-1 group-hover:scale-110 transition-transform">{{ $stats['total_quantity'] }}</p>
                        <p class="text-sm text-red-600 mt-2 flex items-center">
                            <i data-lucide="package-x" class="w-4 h-4 mr-1"></i>
                            Damaged items
                        </p>
                    </div>
                    <div class="bg-red-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="package-x" class="w-4 h-4 sm:w-6 sm:h-6 text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Reporters</p>
                        <p class="text-xl sm:text-2xl font-bold text-blue-600 mt-1 group-hover:scale-110 transition-transform">{{ $stats['unique_employees'] }}</p>
                        <p class="text-sm text-blue-600 mt-2 flex items-center">
                            <i data-lucide="users" class="w-4 h-4 mr-1"></i>
                            Unique employees
                        </p>
                    </div>
                    <div class="bg-blue-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="users" class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Damagers Chart -->
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i data-lucide="user-x" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Top Damagers
                    </h2>
                    <span class="text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                        Top 10
                    </span>
                </div>
                @if(!empty($chartData['reporters']['labels']) && count($chartData['reporters']['labels']) > 0)
                <div class="relative h-64">
                    <canvas id="reportersChart"></canvas>
                </div>
                @else
                <div class="relative h-64 flex items-center justify-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <div class="text-center">
                        <i data-lucide="user-x" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                        <p class="text-gray-500 text-sm">No reporter data available</p>
                        @if(request()->hasAny(['asset_type_id', 'employee_id', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('dashboard.damages.detail') }}" class="text-saipem-primary hover:text-saipem-accent text-xs mt-2 inline-block">
                            Clear filters
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Monthly Trend Chart -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i data-lucide="trending-up" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Monthly Trend
                    </h2>
                    <span class="text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                        Last 6 Months
                    </span>
                </div>
                <div class="relative h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Withdrawals Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center">
                        <i data-lucide="table" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Withdrawal Records
                    </h2>
                    <span class="text-sm text-gray-600 bg-gray-50 px-3 py-1 rounded-full">{{ $withdrawals->total() }} records found</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($withdrawal->date)->format('d M Y') }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $withdrawal->employee->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $withdrawal->employee->employee_id ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="text-sm text-gray-600">{{ $withdrawal->employee->department ?? 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $withdrawal->assetType->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ $withdrawal->quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-4 hidden lg:table-cell">
                                <span class="text-sm text-gray-700 line-clamp-2">{{ $withdrawal->reason }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-gray-500 text-lg">No damage records found</p>
                                @if(request()->hasAny(['asset_type_id', 'employee_id', 'date_from', 'date_to', 'search']))
                                <a href="{{ route('dashboard.damages.detail') }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
                                    Clear filters to see all records
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($withdrawals->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                {{ $withdrawals->onEachSide(1)->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
console.log('📄 Damages detail page script loaded');

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
    
    if (typeof window.ICTAssetApp.initDamagesDetail !== 'function') {
        console.error('❌ initDamagesDetail not found in ICTAssetApp');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }
    
    console.log('✅ All dependencies ready');
    
    // Prepare chart data
    const chartData = {
        reporters: {
            labels: {!! json_encode($chartData['reporters']['labels'] ?? []) !!},
            data: {!! json_encode($chartData['reporters']['data'] ?? []) !!}
        },
        trend: {
            labels: {!! json_encode($chartData['trend']['labels'] ?? []) !!},
            data: {!! json_encode($chartData['trend']['data'] ?? []) !!}
        }
    };
    
    console.log('📊 Calling initDamagesDetail with data:', chartData);
    window.ICTAssetApp.initDamagesDetail(chartData);
}

// Start waiting
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForDependencies);
} else {
    waitForDependencies();
}
</script>
@endpush