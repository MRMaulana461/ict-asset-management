@extends('layouts.app')

@section('title', 'Asset Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Asset Details</h1>
            <p class="text-gray-600 mt-1">Complete information about this asset</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('assets.edit', $asset) }}" 
               class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            <a href="{{ route('assets.index') }}" 
               class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Info Card -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-8 space-y-6">
            
            <!-- Asset Identification -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Asset Identification
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Asset Tag</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->asset_tag }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Serial Number</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->serial_number ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Asset Type</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->assetType->name ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Service Tag</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->service_tag ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Item Details -->
            <div class="border-t pt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Item Details
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Brand</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->brand ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Model/Type</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->type ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Delivery Date</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->delivery_date ? date('d/m/Y', strtotime($asset->delivery_date)) : '-' }}</p>
                    </div>
                </div>
                <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Item Name</label>
                    <p class="text-gray-900">{{ $asset->item_name ?? '-' }}</p>
                </div>
                @if($asset->specifications)
                <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Specifications</label>
                    <p class="text-gray-900">{{ $asset->specifications }}</p>
                </div>
                @endif
            </div>

            <!-- Assignment Info -->
            <div class="border-t pt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Assignment Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">GHRS ID</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->ghrs_id ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Badge ID</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->badge_id ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Employee Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->assignedEmployee->name ?? $asset->username ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Department/Project</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->dept_project ?? ($asset->assignedEmployee->department ?? '-') }}</p>
                    </div>
                    @if($asset->assignment_date)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Assignment Date</label>
                        <p class="text-lg font-semibold text-gray-900">{{ date('d/m/Y', strtotime($asset->assignment_date)) }}</p>
                    </div>
                    @endif
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Device Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->device_name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="border-t pt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Additional Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">PR Reference</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->pr_ref ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-500 mb-1">PO Reference</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->po_ref ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Physical Location</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $asset->location ?? '-' }}</p>
                    </div>
                    @if($asset->remarks)
                    <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Remarks</label>
                        <p class="text-gray-900">{{ $asset->remarks }}</p>
                    </div>
                    @endif
                </div>
                @if($asset->notes)
                <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Notes</label>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $asset->notes }}</p>
                </div>
                @endif
            </div>

        </div>

        <!-- Status & Timeline Card -->
        <div class="space-y-6">
            
            <!-- Status Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Current Status
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($asset->status == 'In Stock') bg-blue-100 text-blue-800
                            @elseif($asset->status == 'In Use') bg-green-100 text-green-800
                            @elseif($asset->status == 'Broken') bg-red-100 text-red-800
                            @elseif($asset->status == 'Retired') bg-gray-100 text-gray-800
                            @elseif($asset->status == 'Taken') bg-purple-100 text-purple-800
                            @endif">
                            {{ $asset->status }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t">
                        <span class="text-sm text-gray-600">Last Status Update</span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ $asset->last_status_date ? date('d/m/Y', strtotime($asset->last_status_date)) : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- System Info Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    System Information
                </h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-start">
                        <span class="text-gray-600">Created</span>
                        <span class="text-gray-900 font-medium text-right">
                            {{ $asset->created_at->format('d/m/Y') }}<br>
                            <span class="text-xs text-gray-500">{{ $asset->created_at->format('H:i') }}</span>
                        </span>
                    </div>
                    <div class="flex justify-between items-start pt-2 border-t">
                        <span class="text-gray-600">Last Update</span>
                        <span class="text-gray-900 font-medium text-right">
                            {{ $asset->updated_at->format('d/m/Y') }}<br>
                            <span class="text-xs text-gray-500">{{ $asset->updated_at->format('H:i') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 border border-blue-200">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Quick Actions
                </h2>
                <div class="space-y-2">
                    <a href="{{ route('assets.edit', $asset) }}" 
                       class="block w-full bg-white text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition text-center text-sm font-medium border border-gray-200">
                        📝 Edit Asset
                    </a>
                    <button onclick="window.print()" 
                            class="block w-full bg-white text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition text-center text-sm font-medium border border-gray-200">
                        🖨️ Print Details
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Asset History Section (if available) -->
    @if($asset->assetHistories && $asset->assetHistories->count() > 0)
    <div class="mt-6 bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Assignment History
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GHRS ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assignment Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($asset->assetHistories as $history)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $history->employee->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $history->ghrs_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $history->assignment_date ? date('d/m/Y', strtotime($history->assignment_date)) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $history->return_date ? date('d/m/Y', strtotime($history->return_date)) : 'Active' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $history->notes ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white;
        }
    }
</style>
@endpush
@endsection