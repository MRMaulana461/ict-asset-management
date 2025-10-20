@extends('layouts.app')

@section('title', 'Withdrawal Report Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Withdrawal Report Details</h1>
                <p class="mt-1 text-sm text-gray-600">View damage report information</p>
            </div>
            <a href="{{ route('withdrawals.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Report Info Section -->
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Report Information</h2>
                    <p class="mt-1 text-sm text-gray-600">Submitted on {{ $withdrawal->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('withdrawals.edit', $withdrawal) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('withdrawals.destroy', $withdrawal) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this withdrawal report? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date -->
                <div class="border-l-4 border-saipem-primary pl-4">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Report Date</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $withdrawal->date->format('d F Y') }}</dd>
                </div>

                <!-- Quantity -->
                <div class="border-l-4 border-saipem-primary pl-4">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Quantity</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $withdrawal->quantity }} unit(s)</dd>
                </div>

                <!-- Employee Name -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Employee Name</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $withdrawal->employee->name }}</dd>
                </div>

                <!-- Employee ID -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Employee ID</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $withdrawal->employee->employee_id }}</dd>
                </div>

                <!-- Department -->
                <div class="border-l-4 border-green-500 pl-4">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Department</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $withdrawal->employee->department ?? '-' }}</dd>
                </div>

                <!-- Position -->
                <div class="border-l-4 border-green-500 pl-4">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Position</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $withdrawal->employee->position ?? '-' }}</dd>
                </div>

                <!-- Asset Type -->
                <div class="border-l-4 border-purple-500 pl-4 md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Item Type</dt>
                    <dd class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        {{ $withdrawal->assetType->name }}
                        @if($withdrawal->assetType->category)
                            <span class="ml-2 text-xs text-gray-500">({{ $withdrawal->assetType->category }})</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Reason Section -->
        <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Damage Reason</h3>
            <div class="bg-white rounded-md border border-gray-200 p-4">
                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $withdrawal->reason }}</p>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-500">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Created: {{ $withdrawal->created_at->format('d M Y, H:i:s') }}</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Last Updated: {{ $withdrawal->updated_at->format('d M Y, H:i:s') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection