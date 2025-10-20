@extends('layouts.app')

@section('title', 'Edit Withdrawal Report')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Withdrawal Report</h1>
                <p class="mt-1 text-sm text-gray-600">Update damage report information</p>
            </div>
            <a href="{{ route('withdrawals.show', $withdrawal) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('withdrawals.update', $withdrawal) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="px-6 py-5 space-y-6">
                <!-- Report Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Report Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="date" 
                           id="date" 
                           value="{{ old('date', $withdrawal->date->format('Y-m-d')) }}"
                           required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Employee Selection -->
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Employee <span class="text-red-500">*</span>
                    </label>
                    <select name="employee_id" 
                            id="employee_id" 
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent @error('employee_id') border-red-500 @enderror">
                        <option value="">-- Select Employee --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" 
                                    {{ old('employee_id', $withdrawal->employee_id) == $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_id }} - {{ $employee->name }} 
                                @if($employee->department)
                                    ({{ $employee->department }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Select the employee who reported the damage</p>
                </div>

                <!-- Current Employee Info (for reference) -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <h3 class="text-sm font-medium text-blue-900 mb-2">Current Employee Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-blue-800">
                        <div>
                            <span class="font-medium">Name:</span> {{ $withdrawal->employee->name }}
                        </div>
                        <div>
                            <span class="font-medium">ID:</span> {{ $withdrawal->employee->employee_id }}
                        </div>
                        <div>
                            <span class="font-medium">Department:</span> {{ $withdrawal->employee->department ?? '-' }}
                        </div>
                        <div>
                            <span class="font-medium">Position:</span> {{ $withdrawal->employee->position ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Asset Type -->
                <div>
                    <label for="asset_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Item Type <span class="text-red-500">*</span>
                    </label>
                    <select name="asset_type_id" 
                            id="asset_type_id" 
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent @error('asset_type_id') border-red-500 @enderror">
                        <option value="">-- Select Item Type --</option>
                        @php
                            $currentCategory = null;
                        @endphp
                        @foreach($assetTypes as $assetType)
                            @if($currentCategory !== $assetType->category)
                                @if($currentCategory !== null)
                                    </optgroup>
                                @endif
                                @if($assetType->category)
                                    <optgroup label="{{ $assetType->category }}">
                                    @php $currentCategory = $assetType->category; @endphp
                                @endif
                            @endif
                            <option value="{{ $assetType->id }}" 
                                    {{ old('asset_type_id', $withdrawal->asset_type_id) == $assetType->id ? 'selected' : '' }}>
                                {{ $assetType->name }}
                            </option>
                        @endforeach
                        @if($currentCategory !== null)
                            </optgroup>
                        @endif
                    </select>
                    @error('asset_type_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="quantity" 
                           id="quantity" 
                           min="1" 
                           value="{{ old('quantity', $withdrawal->quantity) }}"
                           required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent @error('quantity') border-red-500 @enderror">
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Number of damaged items</p>
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Damage Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" 
                              id="reason" 
                              rows="5" 
                              required
                              maxlength="1000"
                              placeholder="Please describe the damage in detail..."
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent @error('reason') border-red-500 @enderror">{{ old('reason', $withdrawal->reason) }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    <span class="text-red-500">*</span> Required fields
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('withdrawals.show', $withdrawal) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-all">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 bg-saipem-primary text-white rounded-md hover:bg-opacity-90 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Report
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Additional Info -->
    <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Important Notice</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Make sure all information is accurate before updating</li>
                        <li>Changes will be logged with timestamp</li>
                        <li>Original submission date: {{ $withdrawal->created_at->format('d M Y, H:i') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection