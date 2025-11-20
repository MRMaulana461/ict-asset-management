@extends('layouts.app')

@section('title', 'Employees List')

@section('content')

<!-- Breadcrumb -->
<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-saipem-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="ml-1 text-sm font-medium text-gray-700">Employees</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
                <svg class="w-8 h-8 mr-3 text-saipem-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Employees Directory
            </h1>
            <p class="text-gray-600 mt-1">Manage employee records and information</p>
            
            @if(request()->hasAny(['search', 'is_active', 'department']))
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="text-xs text-gray-500 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Active Filters:
                </span>
                @if(request('search'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Search: {{ request('search') }}
                </span>
                @endif
                @if(request('is_active') !== null)
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Status: {{ request('is_active') == '1' ? 'Active' : 'Inactive' }}
                </span>
                @endif
                @if(request('department'))
                <span class="px-3 py-1 bg-saipem-primary/10 text-saipem-primary text-xs rounded-full font-medium border border-saipem-primary/20">
                    Department: {{ request('department') }}
                </span>
                @endif
            </div>
            @endif
        </div>
        <a href="{{ route('employees.create') }}" 
           class="bg-gradient-to-r from-saipem-primary to-saipem-accent hover:from-saipem-accent hover:to-saipem-primary text-white px-6 py-2.5 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Employee
        </a>
    </div>
</div>

<!-- Main Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    
    <!-- LEFT SIDEBAR - Filters -->
    <div class="xl:col-span-3">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-saipem-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter Employees
            </h3>
            
            <form method="GET" action="{{ route('employees.index') }}" class="space-y-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="ID, Name, Email..."
                           class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_active" 
                            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary transition-all py-2.5">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Apply Button -->
                <div>
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-saipem-primary to-saipem-accent hover:from-saipem-accent hover:to-saipem-primary text-white px-4 py-2.5 rounded-lg font-medium transition-all duration-300 shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Apply Filters
                    </button>
                </div>

                <!-- Clear Filters -->
                @if(request()->hasAny(['search', 'is_active', 'department']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('employees.index') }}" 
                       class="text-sm text-saipem-primary hover:text-saipem-accent flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Clear All Filters
                    </a>
                </div>
                @endif
            </form>

            <!-- Export Button -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <button type="button" 
                        data-action="open-export-modal" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </button>
            </div>

            <!-- Filter Info -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 leading-relaxed">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Use filters to quickly find employees by their details or status.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT - Employee List -->
    <div class="xl:col-span-9 space-y-6">
        
        <!-- Employee Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-saipem-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Employee Records
                    </h2>
                    <span class="text-sm text-gray-600 bg-gray-50 px-3 py-1 rounded-full">
                        {{ $employees->total() }} employees found
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $employee->ghrs_id }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $employee->name }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">
                                    <span class="text-sm text-gray-600">{{ $employee->email }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-sm text-gray-600">{{ $employee->dept_id ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($employee->is_active)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 inline-flex items-center">
                                            <span class="w-1.5 h-1.5 bg-green-600 rounded-full mr-1.5"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 inline-flex items-center">
                                            <span class="w-1.5 h-1.5 bg-gray-600 rounded-full mr-1.5"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('employees.show', $employee) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition-colors"
                                           title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('employees.edit', $employee) }}" 
                                           class="text-saipem-primary hover:text-saipem-accent transition-colors"
                                           title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-500 text-lg">No employees found</p>
                                    @if(request()->hasAny(['search', 'is_active', 'department']))
                                    <a href="{{ route('employees.index') }}" class="text-saipem-primary hover:text-saipem-accent text-sm mt-2 inline-block">
                                        Clear filters to see all employees
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($employees->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                {{ $employees->onEachSide(1)->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 m-4">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-saipem-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Employee Data
            </h2>
            <button type="button" data-action="close-export-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('employees.export') }}" method="GET" class="space-y-4">
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-900">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <strong>Export Format:</strong> All 22 columns including Company, Org. Context, DeptID, GHRS ID, Badge ID, Employee details, and more.
                </p>
            </div>

            <!-- <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Filter</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Filter by ID, Name, Email, Department..."
                       class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                <p class="text-xs text-gray-500 mt-1">Leave empty to export all employees</p>
            </div> -->

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Employee Status</label>
                <select name="is_active" 
                        class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                    <option value="">All Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>
<!-- 
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <input type="text" 
                       name="department" 
                       value="{{ request('department') }}"
                       placeholder="e.g., IT, HR, Finance..."
                       class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                <p class="text-xs text-gray-500 mt-1">Filter by specific department</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                <input type="text" 
                       name="company" 
                       value="{{ request('company') }}"
                       placeholder="e.g., Saipem..."
                       class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-saipem-primary focus:border-saipem-primary text-sm">
                <p class="text-xs text-gray-500 mt-1">Filter by company name</p>
            </div> -->

            <div class="flex gap-3 pt-4 border-t">
                <button type="button" 
                        data-action="close-export-modal"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2.5 rounded-lg transition font-medium shadow-lg hover:shadow-xl inline-flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export to Excel
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportModal = document.getElementById('exportModal');
    const openModalBtn = document.querySelector('[data-action="open-export-modal"]');
    const closeModalBtns = document.querySelectorAll('[data-action="close-export-modal"]');

    // Open modal
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function() {
            exportModal.classList.remove('hidden');
        });
    }

    // Close modal
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            exportModal.classList.add('hidden');
        });
    });

    // Close modal on outside click
    exportModal.addEventListener('click', function(e) {
        if (e.target === exportModal) {
            exportModal.classList.add('hidden');
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !exportModal.classList.contains('hidden')) {
            exportModal.classList.add('hidden');
        }
    });
});
</script>
@endpush