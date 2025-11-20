@extends('layouts.app')

@section('title', 'Withdrawal Reports')

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
                <span class="ml-1 text-sm font-medium text-gray-700">Withdrawal Records</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Judul & deskripsi -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <i data-lucide="file-minus" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Withdrawal Records
            </h1>
            <p class="text-gray-600 mt-1">Manage ICT asset damage records</p>

            @if(request()->hasAny(['search', 'department', 'asset_type']))
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
                @if(request('department'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Department: {{ request('department') }}
                </span>
                @endif
                @if(request('asset_type'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Item Type: {{ $assetTypes->find(request('asset_type'))->name ?? 'Unknown' }}
                </span>
                @endif
            </div>
            @endif
        </div>

        <!-- Tombol Add -->
        <a href="{{ route('withdrawals.create-manual') }}"  
           class="bg-gradient-to-r from-saipem-primary to-saipem-accent hover:from-saipem-accent hover:to-saipem-primary 
                  text-white px-6 py-2.5 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl flex items-center">
            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
            Add withdrawal
        </a>
    </div>
</div>

<!-- Toast Container (Fixed Position) -->
@if(session('success'))
<div id="toastContainer" class="fixed top-20 right-6 z-50 space-y-3 max-w-sm w-full pointer-events-none">
    <div class="toast-notification pointer-events-auto bg-white rounded-lg shadow-2xl border-l-4 border-green-500 p-4 transform transition-all duration-300 ease-out opacity-0 translate-x-full" 
         data-toast="success"
         style="animation: slideIn 0.5s ease-out forwards;">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-sm font-bold text-green-800">Success</h4>
                    <button onclick="dismissToast('success')" class="text-green-400 hover:text-green-600 transition-colors flex-shrink-0">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-xs text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
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
@endif

<!-- Main Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- LEFT SIDEBAR - Filters -->
    <div class="xl:col-span-3">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="filter" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                Filter Reports
            </h3>
            
            <form method="GET" action="{{ route('withdrawals.index') }}" class="space-y-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Employee, item, or reason..."
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select name="department" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Item Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
                    <select name="asset_type" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Items</option>
                        @foreach($assetTypes as $type)
                            <option value="{{ $type->id }}" {{ request('asset_type') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
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
                @if(request()->hasAny(['search', 'department', 'asset_type']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('withdrawals.index') }}" 
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
                    Use filters to find specific withdrawal reports quickly.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Stats & Table -->
    <div class="xl:col-span-9 space-y-6">

    <!-- Withdrawal Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center">
                        <i data-lucide="table" class="w-5 h-5 mr-2 text-saipem-primary"></i>
                        Withdrawal Records
                    </h2>
                    <span class="text-sm text-gray-600 bg-gray-50 px-3 py-1 rounded-full">
                        {{ $withdrawals->total() }} records found
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Item Type</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($withdrawals as $withdrawal)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $withdrawal->date->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $withdrawal->date->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-2">
                                            <div class="text-sm font-medium text-gray-900">{{ $withdrawal->employee->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $withdrawal->employee->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $withdrawal->assetType->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('withdrawals.show', $withdrawal) }}" 
                                           class="text-blue-600 hover:text-blue-900" 
                                           title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route('withdrawals.destroy', $withdrawal) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this withdrawal report?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <p class="text-gray-500 text-lg">No withdrawal reports found</p>
                                    @if(request()->hasAny(['search', 'department', 'asset_type']))
                                    <a href="{{ route('withdrawals.index') }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
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
<script>
console.log('📄 Withdrawal Reports page script loaded');

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

// Auto-dismiss toasts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast-notification');
    toasts.forEach(toast => {
        setTimeout(() => {
            dismissToast(toast.getAttribute('data-toast'));
        }, 5000); // 5 seconds
    });
});

function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForApp, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initWithdrawalIndex !== 'function') {
        console.log('⚠️ initWithdrawalIndex not found, skipping');
        return;
    }
    
    console.log('✅ Calling initWithdrawalIndex()');
    window.ICTAssetApp.initWithdrawalIndex();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForApp);
} else {
    waitForApp();
}
</script>
@endpush