@extends('layouts.app')

@section('title', 'Loan History - Most Borrowed Items')

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
                <span class="ml-1 text-sm font-medium text-gray-700">Loan History</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="arrow-right-left" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Loan History Analytics
            </h1>
            <p class="text-gray-600 mt-1">Complete borrowing records and statistics</p>
            
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
                Filter Loans
            </h3>
            
            <form method="GET" action="" class="space-y-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="On Loan" {{ request('status') == 'On Loan' ? 'selected' : '' }}>On Loan</option>
                        <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
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
                           placeholder="Asset type, borrower..."
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
                @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']) && request('status') != 'all')
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('dashboard.loans.borrowed') }}" 
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
                    Filters will update all statistics, charts, and loan records below.
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
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Total Loans</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1 group-hover:text-blue-600 transition-colors">{{ $stats['total_loans'] }}</p>
                        <p class="text-sm text-blue-600 mt-2 flex items-center">
                            <i data-lucide="list" class="w-4 h-4 mr-1"></i>
                            {{ request()->hasAny(['status', 'date_from', 'date_to', 'search']) ? 'Filtered' : 'All time' }}
                        </p>
                    </div>
                    <div class="bg-blue-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="list" class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Active Loans</p>
                        <p class="text-xl sm:text-2xl font-bold text-orange-600 mt-1 group-hover:scale-110 transition-transform">{{ $stats['active_loans'] }}</p>
                        <p class="text-sm text-orange-600 mt-2 flex items-center">
                            <i data-lucide="clock" class="w-4 h-4 mr-1"></i>
                            Currently borrowed
                        </p>
                    </div>
                    <div class="bg-orange-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="arrow-right-left" class="w-4 h-4 sm:w-6 sm:h-6 text-orange-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Returned</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-600 mt-1 group-hover:scale-110 transition-transform">{{ $stats['returned'] }}</p>
                        <p class="text-sm text-green-600 mt-2 flex items-center">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i>
                            Completed loans
                        </p>
                    </div>
                    <div class="bg-green-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="check-circle" class="w-4 h-4 sm:w-6 sm:h-6 text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Overdue</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-600 mt-1 group-hover:scale-110 transition-transform">{{ $stats['overdue'] }}</p>
                        <p class="text-sm text-red-600 mt-2 flex items-center">
                            <i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i>
                            Need attention
                        </p>
                    </div>
                    <div class="bg-red-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="alert-circle" class="w-4 h-4 sm:w-6 sm:h-6 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                    Top Borrowed Items
                </h2>
                <span class="text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                    Top 10
                </span>
            </div>
            <div class="relative h-64">
                <canvas id="borrowedChart"></canvas>
            </div>
        </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('borrowedChart');
    
    // Debug: Log chart data
    const chartLabels = {!! json_encode($chartData['labels'] ?? []) !!};
    const chartValues = {!! json_encode($chartData['data'] ?? []) !!};
    
    console.log('📊 Chart Debug Info:');
    console.log('Labels:', chartLabels);
    console.log('Data:', chartValues);
    console.log('Labels length:', chartLabels.length);
    console.log('Data length:', chartValues.length);
    
    if (ctx) {
        // Check if data exists
        if (chartLabels.length === 0 || chartValues.length === 0) {
            console.warn('⚠️ No chart data available');
            // Show "No data" message
            const chartContainer = ctx.parentElement;
            chartContainer.innerHTML = '<div class="flex items-center justify-center h-64 text-gray-400"><div class="text-center"><i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-2 opacity-50"></i><p>No borrowing data available</p></div></div>';
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        } else {
            console.log('✅ Creating chart with data');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Times Borrowed',
                        data: chartValues,
                        backgroundColor: '#0033A0',
                        borderRadius: 6,
                        barThickness: 60
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Borrowed ' + context.parsed.y + ' times';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            },
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ Chart created successfully');
        }
    } else {
        console.error('❌ Canvas element #borrowedChart not found');
    }
});
</script>
@endpush