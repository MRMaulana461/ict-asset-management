@extends('layouts.app')

@section('title', 'Recent Activities - Timeline View')

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
                <span class="ml-1 text-sm font-medium text-gray-700">Recent Activities</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="activity" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Recent Activities
            </h1>
            <p class="text-gray-600 mt-1">Timeline of all asset activities</p>
            
            @if(!empty($activeFilters))
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
                Filter Activities
            </h3>
            
            <form method="GET" action="{{ route('dashboard.activities.detail') }}" class="space-y-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Name, asset, reason..."
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                    <p class="text-xs text-gray-500 mt-1">Track someone's activity history</p>
                </div>

                <!-- Activity Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
                    <select name="type" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>All Activities</option>
                        <option value="loan" {{ request('type') == 'loan' ? 'selected' : '' }}>Loans Only</option>
                        <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Returns Only</option>
                        <option value="broken" {{ request('type') == 'broken' ? 'selected' : '' }}>Broken Only</option>
                        <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Damages Only</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               class="border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               class="border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                    </div>
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
                @if(request()->hasAny(['search', 'type', 'date_from', 'date_to']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('dashboard.activities.detail') }}" 
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
                    Click on any activity circle to view details.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Stats & Timeline -->
    <div class="xl:col-span-9 space-y-6">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Total Loans</p>
                        <p class="text-xl sm:text-2xl font-bold text-orange-600 mt-1">{{ $stats['total_loans'] }}</p>
                    </div>
                    <div class="bg-orange-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="arrow-right" class="w-4 h-4 sm:w-6 sm:h-6 text-orange-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Returns</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-600 mt-1">{{ $stats['total_returns'] }}</p>
                    </div>
                    <div class="bg-green-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="arrow-left" class="w-4 h-4 sm:w-6 sm:h-6 text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Broken Assets</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-600 mt-1">{{ $stats['total_broken'] }}</p>
                    </div>
                    <div class="bg-red-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="alert-circle" class="w-4 h-4 sm:w-6 sm:h-6 text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Damages</p>
                        <p class="text-xl sm:text-2xl font-bold text-purple-600 mt-1">{{ $stats['total_withdrawals'] }}</p>
                    </div>
                    <div class="bg-purple-100 p-2 sm:p-3 rounded-full group-hover:scale-110 transition-transform">
                        <i data-lucide="alert-triangle" class="w-4 h-4 sm:w-6 sm:h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zigzag Timeline -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i data-lucide="git-branch" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Activity Timeline
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Click circles for details</p>
                </div>
                <span class="text-sm text-gray-600 bg-gray-50 px-4 py-2 rounded-full border border-gray-200">
                    {{ $activities->total() }} activities
                </span>
            </div>
            
            @if($activityRows->count() > 0)
                <!-- Zigzag Timeline Container -->
                <div class="space-y-8">
                    @foreach($activityRows as $row)
                        <div class="timeline-row relative" data-row="{{ $row['rowIndex'] }}">
                            <!-- Horizontal Line -->
                            <div class="absolute top-1/2 left-0 right-0 transform -translate-y-1/2 hidden md:block z-0">
                                <div class="h-0.5 bg-gradient-to-r from-saipem-primary/30 via-saipem-accent/50 to-saipem-primary/30"></div>
                            </div>
                            
                            <!-- Activities in Row -->
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-6 md:gap-4 relative z-10 {{ $row['isReversed'] ? 'md:flex md:flex-row-reverse md:justify-between' : '' }}">
                                @foreach($row['activities'] as $index => $activity)
                                    @php
                                        $colors = [
                                            'loan' => ['bg' => 'bg-blue-500', 'ring' => 'ring-blue-200'],
                                            'return' => ['bg' => 'bg-emerald-500', 'ring' => 'ring-emerald-200'],
                                            'broken' => ['bg' => 'bg-rose-500', 'ring' => 'ring-rose-200'],
                                            'withdrawal' => ['bg' => 'bg-violet-500', 'ring' => 'ring-violet-200']
                                        ];
                                        $color = $colors[$activity['type']];
                                        
                                        $isLastInRow = $index == $row['activities']->count() - 1;
                                        $showDownConnector = $isLastInRow && !$loop->parent->last;
                                    @endphp
                                    
                                    <div class="activity-node flex flex-col items-center {{ $row['isReversed'] ? 'md:w-1/5' : '' }}">
                                        <!-- Date Label -->
                                        <div class="text-center mb-2">
                                            <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full">
                                                {{ $activity['date'] }}
                                            </span>
                                        </div>
                                        
                                        <!-- Clickable Circle (Smaller) -->
                                        <button 
                                            onclick='openActivityModal(@json($activity))'
                                            class="w-14 h-14 {{ $color['bg'] }} rounded-full flex items-center justify-center ring-2 {{ $color['ring'] }} shadow-md hover:shadow-lg transition-shadow cursor-pointer">
                                            <i data-lucide="{{ $activity['icon'] }}" class="w-6 h-6 text-white"></i>
                                        </button>
                                        
                                        <!-- Up Connector (karena urutan oldest di bawah) -->
                                        @if($showDownConnector)
                                        <div class="hidden md:flex flex-col items-center mt-2">
                                            <!-- Arrow Up -->
                                            <div class="w-0 h-0 border-l-[5px] border-r-[5px] border-b-[6px] border-l-transparent border-r-transparent border-b-saipem-primary"></div>
                                            <!-- Vertical line -->
                                            <div class="w-0.5 h-8 bg-gradient-to-t from-saipem-accent to-saipem-primary"></div>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($activities->hasPages())
                <div class="mt-8 pt-6 border-t border-gray-200">
                    {{ $activities->onEachSide(1)->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <p class="text-gray-500 text-lg">No activities found</p>
                    @if(request()->hasAny(['search', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('dashboard.activities.detail') }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
                        Clear filters to see all activities
                    </a>
                    @else
                    <p class="text-gray-400 text-sm mt-2">Activities will appear here as they occur</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Activity Detail Modal -->
<div id="activityModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" id="modalContent">
        <!-- Modal Header -->
        <div class="relative p-6 border-b border-gray-200">
            <div class="flex items-center justify-center mb-4">
                <div id="modalIcon" class="w-16 h-16 rounded-full flex items-center justify-center ring-4 shadow-lg">
                    <i id="modalIconSvg" class="w-8 h-8 text-white"></i>
                </div>
            </div>
            <h3 id="modalTitle" class="text-xl font-bold text-gray-900 text-center"></h3>
            <button onclick="closeActivityModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="user" class="w-4 h-4 text-gray-500"></i>
                    <span class="text-xs font-medium text-gray-500">Person</span>
                </div>
                <p id="modalDescription" class="text-sm font-semibold text-gray-900"></p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="info" class="w-4 h-4 text-gray-500"></i>
                    <span class="text-xs font-medium text-gray-500">Details</span>
                </div>
                <p id="modalDetails" class="text-sm text-gray-700"></p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-gray-500"></i>
                    <span class="text-xs font-medium text-gray-500">Date & Time</span>
                </div>
                <p id="modalDate" class="text-sm font-medium text-gray-900"></p>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200">
            <button onclick="closeActivityModal()" 
                    class="w-full bg-gradient-to-r from-saipem-primary to-saipem-accent hover:from-saipem-accent hover:to-saipem-primary text-white px-4 py-2.5 rounded-lg font-medium transition-all duration-300 shadow-lg hover:shadow-xl">
                Close
            </button>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
console.log('📄 Recent Activities Detail page script loaded');

function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForApp, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initActivitiesDetail !== 'function') {
        console.log('⚠️ initActivitiesDetail not found, skipping');
        return;
    }
    
    console.log('✅ Calling initActivitiesDetail()');
    window.ICTAssetApp.initActivitiesDetail();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForApp);
} else {
    waitForApp();
}
</script>
@endpush