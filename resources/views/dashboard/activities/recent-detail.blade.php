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
                <span class="ml-1 text-sm font-medium text-gray-700">Activity Tracker</span>
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
                Activity Tracker
            </h1>
            <p class="text-gray-600 mt-1">Track person's activity history or asset movement timeline</p>
            
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="user" class="w-4 h-4 inline mr-1"></i>
                        Search Person/Asset
                    </label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Name, Employee ID, Asset..."
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                    <p class="text-xs text-gray-500 mt-1">
                        <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                        Track someone's complete activity history
                    </p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range (Optional)</label>
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
                        Track Activities
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

            <!-- Usage Examples -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs font-semibold text-gray-700 mb-2">
                    <i data-lucide="lightbulb" class="w-3 h-3 inline mr-1"></i>
                    Usage Examples:
                </p>
                <ul class="text-xs text-gray-600 space-y-1.5 leading-relaxed">
                    <li>• Search "John Doe" to see all his activities</li>
                    <li>• Search "Laptop" to track asset movements</li>
                    <li>• Search employee ID to see their history</li>
                    <li>• Use date range to narrow down results</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Stats & Timeline -->
    <div class="xl:col-span-9 space-y-6">
        
        @if(request()->hasAny(['search', 'type', 'date_from', 'date_to']))
            <!-- Statistics Cards (Only shown when filtered) -->
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

            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i data-lucide="git-branch" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                            Activity Timeline
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            @if(request('search'))
                                Showing activities for: <span class="font-semibold text-saipem-primary">{{ request('search') }}</span>
                            @else
                                Chronological view of filtered activities
                            @endif
                        </p>
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
                                            
                                            <!-- Clickable Circle -->
                                            <button 
                                                onclick='openActivityModal(@json($activity))'
                                                class="w-14 h-14 {{ $color['bg'] }} rounded-full flex items-center justify-center ring-2 {{ $color['ring'] }} shadow-md hover:shadow-lg hover:scale-110 transition-all cursor-pointer">
                                                <i data-lucide="{{ $activity['icon'] }}" class="w-6 h-6 text-white"></i>
                                            </button>
                                            
                                            <!-- Connector -->
                                            @if($showDownConnector)
                                            <div class="hidden md:flex flex-col items-center mt-2">
                                                <div class="w-0 h-0 border-l-[5px] border-r-[5px] border-b-[6px] border-l-transparent border-r-transparent border-b-saipem-primary"></div>
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
                    <!-- No Results Found -->
                    <div class="text-center py-12">
                        <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="search-x" class="w-12 h-12 text-gray-400"></i>
                        </div>
                        <p class="text-gray-700 text-lg font-semibold mb-2">No activities found</p>
                        <p class="text-gray-500 text-sm">
                            No activities match your current filters. Try adjusting your search criteria.
                        </p>
                        <a href="{{ route('dashboard.activities.detail') }}" 
                           class="inline-flex items-center text-saipem-primary hover:text-saipem-accent text-sm mt-4 font-medium">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 mr-1"></i>
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- No Filter Applied - Show Instructions -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 md:p-12">
                <div class="max-w-2xl mx-auto text-center">
                    <!-- Icon -->
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-saipem-primary/10 to-saipem-accent/10 rounded-full mb-6">
                        <i data-lucide="search" class="w-12 h-12 text-saipem-primary"></i>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">
                        Track Activity History
                    </h2>
                    <p class="text-gray-600 mb-8 text-lg">
                        Use the search filter to track someone's complete activity timeline or monitor asset movements
                    </p>
                    
                    <!-- Features Grid -->
                    <div class="grid md:grid-cols-2 gap-6 mb-8 text-left">
                        <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <i data-lucide="user" class="w-5 h-5 text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Track Person</h3>
                                <p class="text-sm text-gray-600">See all activities of a specific person - loans, returns, damages</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3 p-4 bg-green-50 rounded-lg border border-green-100">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <i data-lucide="package" class="w-5 h-5 text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Track Asset</h3>
                                <p class="text-sm text-gray-600">Monitor where an asset has been and who used it</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3 p-4 bg-orange-50 rounded-lg border border-orange-100">
                            <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                                <i data-lucide="calendar" class="w-5 h-5 text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Date Range</h3>
                                <p class="text-sm text-gray-600">Filter activities within a specific time period</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3 p-4 bg-purple-50 rounded-lg border border-purple-100">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                                <i data-lucide="filter" class="w-5 h-5 text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Activity Type</h3>
                                <p class="text-sm text-gray-600">Filter by loans, returns, damages, or broken items</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CTA -->
                    <div class="bg-gradient-to-r from-saipem-primary/5 to-saipem-accent/5 rounded-lg p-6 border border-saipem-primary/20">
                        <p class="text-sm text-gray-700 mb-4">
                            <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i>
                            Start by entering a <strong>name, employee ID, or asset type</strong> in the search field
                        </p>
                        <p class="text-xs text-gray-500">
                            The timeline will appear instantly showing chronological activity history
                        </p>
                    </div>
                </div>
            </div>
        @endif
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
console.log('📄 Activity Tracker page script loaded');

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