@extends('layouts.app')

@section('title', 'Loan Log')

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
                <span class="ml-1 text-sm font-medium text-gray-700">Loan Log</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="clipboard-list" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Loan Log
            </h1>
            <p class="text-gray-600 mt-1">Track and manage asset loans</p>
            
            @if(request()->hasAny(['search', 'status', 'time_filter']))
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
                @if(request('status'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Status: {{ request('status') }}
                </span>
                @endif
                @if(request('time_filter') && request('time_filter') != 'all')
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Period: {{ ucwords(str_replace('_', ' ', request('time_filter'))) }}
                </span>
                @endif
            </div>
            @endif
        </div>
        <a href="{{ route('loan-log.create') }}" 
           class="bg-gradient-to-r from-saipem-primary to-saipem-accent hover:from-saipem-accent hover:to-saipem-primary text-white px-6 py-2.5 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl flex items-center">
            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
            New Loan
        </a>
    </div>
</div>

<!-- Toast Container (Fixed Position) -->
<div id="toastContainer" class="fixed top-20 right-6 z-50 space-y-3 max-w-sm w-full pointer-events-none">
    <!-- Overdue Toast -->
    @if($overdueLoans->count() > 0)
    <div class="toast-notification pointer-events-auto bg-white rounded-lg shadow-2xl border-l-4 border-red-500 p-4 transform transition-all duration-300 ease-out opacity-0 translate-x-full" 
         data-toast="overdue"
         style="animation: slideIn 0.5s ease-out forwards;">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-sm font-bold text-red-800">Overdue Returns</h4>
                    <button onclick="dismissToast('overdue')" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-xs text-red-700 mb-2">{{ $overdueLoans->count() }} loan(s) are overdue</p>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                    @foreach($overdueLoans->take(2) as $loan)
                        @php
                            $dueDate = \App\Helpers\DateHelper::addBusinessDays($loan->loan_date, $loan->duration_days);
                        @endphp
                        <div class="text-xs text-red-600 bg-red-50 rounded px-2 py-1">
                            <strong>{{ $loan->borrower->name }}</strong> - {{ $loan->asset->assetType->name }}
                        </div>
                    @endforeach
                    @if($overdueLoans->count() > 2)
                    <p class="text-xs text-red-500 italic">+ {{ $overdueLoans->count() - 2 }} more</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Due Today Toast -->
    @if($dueSoonLoans->count() > 0)
    <div class="toast-notification pointer-events-auto bg-white rounded-lg shadow-2xl border-l-4 border-yellow-500 p-4 transform transition-all duration-300 ease-out opacity-0 translate-x-full" 
         data-toast="duetoday"
         style="animation: slideIn 0.5s ease-out 0.2s forwards;">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-sm font-bold text-yellow-800">Due Today</h4>
                    <button onclick="dismissToast('duetoday')" class="text-yellow-400 hover:text-yellow-600 transition-colors flex-shrink-0">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-xs text-yellow-700 mb-2">{{ $dueSoonLoans->count() }} loan(s) due today</p>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                    @foreach($dueSoonLoans->take(2) as $loan)
                    <div class="text-xs text-yellow-600 bg-yellow-50 rounded px-2 py-1">
                        <strong>{{ $loan->borrower->name }}</strong> - {{ $loan->asset->assetType->name }}
                    </div>
                    @endforeach
                    @if($dueSoonLoans->count() > 2)
                    <p class="text-xs text-yellow-500 italic">+ {{ $dueSoonLoans->count() - 2 }} more</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

.toast-dismiss {
    animation: slideOut 0.3s ease-out forwards;
}
</style>

<!-- Main Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- LEFT SIDEBAR - Filters -->
    <div class="xl:col-span-3">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="filter" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Filter Loans
            </h3>
            
            <form method="GET" action="{{ route('loan-log.index') }}" class="space-y-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Employee name or ID..."
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Status</option>
                        <option value="On Loan" {{ request('status') == 'On Loan' ? 'selected' : '' }}>On Loan</option>
                        <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </div>

                <!-- Time Period -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Period</label>
                    <select name="time_filter" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="all" {{ request('time_filter') == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="last_week" {{ request('time_filter') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                        <option value="last_month" {{ request('time_filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="last_year" {{ request('time_filter') == 'last_year' ? 'selected' : '' }}>Last Year</option>
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
                @if(request()->hasAny(['search', 'status', 'time_filter']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('loan-log.index') }}" 
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
                    Use filters to find specific loan records quickly.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Stats & Table -->
    <div class="xl:col-span-9 space-y-6">
        
        <!-- Loans Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center">
                        <i data-lucide="table" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Loan Records
                    </h2>
                    <span class="text-sm text-gray-600 bg-gray-50 px-3 py-1 rounded-full">
                        {{ $loans->total() }} records found
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Asset</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Duration</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($loans as $loan)
                            @php
                                $dueDate = \App\Helpers\DateHelper::addBusinessDays($loan->loan_date, $loan->duration_days);
                                $isOverdue = $loan->status == 'On Loan' && $dueDate->isPast();
                                $isDueToday = $loan->status == 'On Loan' && $dueDate->isToday();
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors {{ $isOverdue ? 'bg-red-50' : ($isDueToday ? 'bg-yellow-50' : '') }}">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $loan->loan_time }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-2">
                                            <div class="text-sm font-medium text-gray-900">{{ $loan->borrower->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $loan->borrower->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">
                                    <div class="text-sm font-medium text-gray-900">{{ $loan->asset->assetType->name }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 hidden sm:table-cell">
                                    {{ $loan->duration_days }} days
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : ($isDueToday ? 'text-yellow-600 font-semibold' : 'text-gray-900') }}">
                                        {{ $dueDate->format('d M Y') }}
                                    </div>
                                    @if($isOverdue)
                                        <span class="text-xs text-red-600">Overdue!</span>
                                    @elseif($isDueToday)
                                        <span class="text-xs text-yellow-600">Due Today</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($loan->status == 'On Loan')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 inline-flex items-center">
                                            <span class="w-1.5 h-1.5 bg-yellow-600 rounded-full mr-1.5"></span>
                                            On Loan
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 inline-flex items-center">
                                            <span class="w-1.5 h-1.5 bg-green-600 rounded-full mr-1.5"></span>
                                            Returned
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('loan-log.show', $loan) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @if($loan->status == 'On Loan')
                                            <a href="{{ route('loan-log.edit', $loan) }}" class="text-yellow-600 hover:text-yellow-900" title="Extend">
                                                <i data-lucide="clock" class="w-4 h-4"></i>
                                            </a>
                                            <button onclick="confirmReturn({{ $loan->id }}, '{{ $loan->borrower->name }}')" class="text-green-600 hover:text-green-900" title="Return">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <p class="text-gray-500 text-lg">No loan records found</p>
                                    @if(request()->hasAny(['search', 'status', 'time_filter']))
                                    <a href="{{ route('loan-log.index') }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
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
            @if($loans->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                {{ $loans->onEachSide(1)->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Return Modal -->
<div id="returnModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-96 p-6 m-4">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full mb-4">
            <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Confirm Return</h3>
        <p class="text-sm text-gray-600 text-center mb-6">
            Has <strong id="borrowerName"></strong> returned the asset?
        </p>
        <form id="returnForm" method="POST">
            @csrf
            @method('PUT')
            <div class="flex gap-3">
                <button type="button" onclick="closeReturnModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
console.log('📄 Loan Log Index page script loaded');

// Toast dismiss function
function dismissToast(toastId) {
    const toast = document.querySelector(`[data-toast="${toastId}"]`);
    if (toast) {
        toast.classList.add('toast-dismiss');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// Auto-dismiss toasts after 10 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast-notification');
    toasts.forEach(toast => {
        setTimeout(() => {
            dismissToast(toast.getAttribute('data-toast'));
        }, 10000); // 10 seconds
    });
});

function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForApp, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initLoanLogIndex !== 'function') {
        console.error('❌ initLoanLogIndex not found');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }
    
    console.log('✅ Calling initLoanLogIndex()');
    window.ICTAssetApp.initLoanLogIndex();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForApp);
} else {
    waitForApp();
}
</script>
@endpush