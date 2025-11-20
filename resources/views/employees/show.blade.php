@extends('layouts.app')

@section('title', 'Detail Employee')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Employee Details</h1>
    <div class="flex gap-3">
        <a href="{{ route('employees.edit', $employee) }}" 
           class="bg-saipem-primary text-white px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition flex items-center">
            <i data-lucide="edit" class="w-5 h-5 mr-2"></i>
            Edit
        </a>
        <a href="{{ route('employees.index') }}" 
           class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
            Back
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Employee Info -->
    <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Employee Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Employee ID</label>
                <p class="text-lg font-semibold text-gray-900">{{ $employee->ghrs_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">User ID</label>
                <p class="text-lg font-semibold text-gray-900">{{ $employee->user_id ?? '-' }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500 mb-1">Full Name</label>
                <p class="text-lg font-semibold text-gray-900">{{ $employee->name }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500 mb-1">Email Address</label>
                <p class="text-lg font-semibold text-gray-900">{{ $employee->email }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Department</label>
                <p class="text-lg font-semibold text-gray-900">{{ $employee->department ?? '-' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Cost Center</label>
                <p class="text-lg font-semibold text-gray-900">{{ $employee->cost_center ?? '-' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                <div>
                    @if($employee->is_active)
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Assets Assigned</span>
                    <span class="font-bold text-gray-900">{{ $employee->assets->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Loans</span>
                    <span class="font-bold text-gray-900">{{ $employee->loanLogs->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Loans</span>
                    <span class="font-bold text-orange-600">{{ $employee->loanLogs->where('status', 'On Loan')->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assets Assigned -->
<div class="mt-8 bg-white p-6 rounded-xl shadow-lg">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Assigned Asset</h2>
    
    @if($employee->assets->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asset Tag</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assignment Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($employee->assets as $asset)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('assets.show', $asset) }}" class="text-saipem-primary hover:underline">
                                    {{ $asset->asset_tag }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $asset->assetType->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $asset->serial_number ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">{{ $asset->status }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $asset->assignment_date ? $asset->assignment_date->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500">There are no assigned asset</p>
    @endif
</div>

<!-- Loan History -->
<div class="mt-8 bg-white p-6 rounded-xl shadow-lg">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Loan History</h2>
    
    @if($employee->loanLogs->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($employee->loanLogs as $loan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $loan->loan_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $loan->asset->assetType->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $loan->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $loan->return_date ? $loan->return_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($loan->status == 'On Loan')
                                    <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">Borrowed</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Returned</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500">There are no loan history</p>
    @endif
</div>
@endsection