{{-- resources/views/withdrawals/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Withdrawal Report (Manual Entry)')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Add Withdrawal Report</h1>
                    <p class="text-sm text-gray-600 mt-1">Manual entry for historical data</p>
                </div>
                <a href="{{ route('withdrawals.index') }}" 
                   class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>
        </div>

        <!-- Alert Info -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold">Admin Manual Entry</p>
                    <p class="mt-1">This form allows you to enter withdrawal reports with custom dates for importing historical data into the system.</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('withdrawals.store-manual') }}" method="POST" id="manualWithdrawalForm" class="space-y-6">
                @csrf

                <!-- Date (ONLY DIFFERENCE FROM PUBLIC FORM) -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                        Date *
                    </label>
                    <input type="date" 
                           name="date" 
                           id="date" 
                           value="{{ old('date', date('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Select the date when the damage occurred</p>
                </div>

                <!-- Employee ID -->
                <div>
                    <label for="ghrs_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
                    <input type="text" name="ghrs_id" id="ghrs_id" value="{{ old('ghrs_id') }}"
                           placeholder="e.g., KAR175458" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('ghrs_id') border-red-500 @enderror"/>
                    @error('ghrs_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p id="employeeError" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Employee Name -->
                <div>
                    <label for="employee_name" class="block text-sm font-medium text-gray-700">Employee Name *</label>
                    <input type="text" id="employee_name" readonly placeholder="Name will appear automatically"
                           class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm sm:text-sm"/>
                </div>

                <!-- Department -->
                <div>
                    <label for="employee_dept" class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" id="employee_dept" readonly placeholder="Department will appear automatically"
                           class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm sm:text-sm"/>
                </div>

                <!-- Asset Type -->
                <div>
                    <label for="asset_type_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Damaged Item Type *
                    </label>
                    <select name="asset_type_id" 
                            id="asset_type_id" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('asset_type_id') border-red-500 @enderror">
                        <option value="">Select Item</option>
                        @php
                            $currentCategory = null;
                        @endphp
                        @foreach($assetTypes as $type)
                            @if($currentCategory !== $type->category)
                                @if($currentCategory !== null)
                                    </optgroup>
                                @endif
                                <optgroup label="{{ $type->category }}">
                                @php
                                    $currentCategory = $type->category;
                                @endphp
                            @endif
                            <option value="{{ $type->id }}" 
                                    data-stock="{{ $type->available_stock }}"
                                    {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
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
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                        Quantity *
                    </label>
                    <input type="number" 
                           name="quantity" 
                           id="quantity" 
                           value="{{ old('quantity', 1) }}" 
                           min="1" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('quantity') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Number of damaged items</p>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Damage Description / Reason *
                    </label>
                    <textarea name="reason" 
                              id="reason" 
                              rows="4" 
                              required 
                              placeholder="Please describe the damage in detail (e.g., Screen broken, Not turning on, Cable damaged...)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Be as detailed as possible to help ICT team understand the issue</p>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('withdrawals.index') }}" 
                       class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            id="submitBtn"
                            disabled
                            class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>Submit Damage Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/app.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu sedikit untuk memastikan semua script sudah loaded
            setTimeout(() => {
                console.log('ICTAssetApp:', window.ICTAssetApp);
                
                // Initialize withdrawal form dengan auto-fill employee
                if (window.ICTAssetApp && typeof window.ICTAssetApp.initWithdrawalForm === 'function') {
                    window.ICTAssetApp.initWithdrawalForm();
                } else {
                    console.error('initWithdrawalForm not found');
                }
            }, 100);
        });
    </script>
@endpush
@endsection