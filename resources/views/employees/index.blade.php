@extends('layouts.app')

@section('title', 'Employees List')

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
                <i data-lucide="users" class="w-8 h-8 mr-3 text-saipem-primary"></i>
                Employees Directory
            </h1>
            <p class="text-gray-600 mt-1">Manage employee records and information</p>
            
            @if(request()->hasAny(['search', 'is_active', 'department']))
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
            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
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
                <i data-lucide="filter" class="w-5 h-5 mr-2 text-saipem-primary"></i>
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
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Apply Filters
                    </button>
                </div>

                <!-- Clear Filters -->
                @if(request()->hasAny(['search', 'is_active', 'department']))
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('employees.index') }}" 
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
                        <i data-lucide="table" class="w-5 h-5 mr-2 text-saipem-primary"></i>
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
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
                                    <span class="text-sm font-medium text-gray-900">{{ $employee->employee_id }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $employee->name }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">
                                    <span class="text-sm text-gray-600">{{ $employee->email }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-sm text-gray-600">{{ $employee->department ?? '-' }}</span>
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
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('employees.edit', $employee) }}" 
                                           class="text-saipem-primary hover:text-saipem-accent transition-colors"
                                           title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
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

@endsection
@push('scripts')
<script>
console.log('📄 Employees page script loaded');

// Wait for ICTAssetApp to be ready
function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForApp, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initEmployeesIndex !== 'function') {
        console.error('❌ initEmployeesIndex not found in ICTAssetApp');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }
    
    console.log('✅ ICTAssetApp ready, calling initEmployeesIndex()');
    window.ICTAssetApp.initEmployeesIndex();
}

// Start waiting
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForApp);
} else {
    waitForApp();
}
</script>
@endpush