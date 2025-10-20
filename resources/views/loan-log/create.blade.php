@extends('layouts.app')

@section('title', 'New Loan')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('loan-log.index') }}" class="text-saipem-primary hover:text-opacity-80 inline-flex items-center text-sm font-medium mb-4 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Loan Log
        </a>
        <h1 class="text-3xl font-bold text-gray-900">ICT Asset Loan Form</h1>
        <p class="mt-2 text-sm text-gray-600">Record a new asset loan transaction</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <form action="{{ route('loan-log.store') }}" method="POST" id="loanForm" class="space-y-6">
            @csrf

            <!-- Employee ID -->
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700">
                    Employee ID *
                </label>
                <input type="text" 
                       name="employee_id" 
                       id="employee_id" 
                       value="{{ old('employee_id') }}"
                       placeholder="Enter Employee ID (e.g., KAR175458)"
                       required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('employee_id') border-red-500 @enderror"/>
                @error('employee_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p id="employeeError" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>

            <!-- Employee Name (Auto-filled) -->
            <div>
                <label for="employee_name" class="block text-sm font-medium text-gray-700">
                    Employee Name *
                </label>
                <input type="text" 
                       id="employee_name" 
                       readonly
                       placeholder="Name will appear automatically"
                       class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm sm:text-sm"/>
                <input type="hidden" name="borrower_id" id="borrower_id">
            </div>

            <!-- Asset Selection - Modified: Display only asset types without stock -->
            <div>
                <label for="asset_id" class="block text-sm font-medium text-gray-700">
                    Asset to Borrow *
                </label>
                <select name="asset_id" 
                        id="asset_id" 
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('asset_id') border-red-500 @enderror">
                    <option value="">Select Asset Type</option>
                    @php
                        $groupedAssets = $assets->groupBy('asset_type_id');
                    @endphp
                    @foreach($groupedAssets as $typeId => $assetGroup)
                        @php
                            $assetType = $assetGroup->first()->assetType;
                        @endphp
                        <option label="{{ $assetType->name }}">
                            @foreach($assetGroup as $asset)
                                @if($asset->status == 'In Stock')
                                    <option value="{{ $asset->id }}" 
                                            {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->brand }} {{ $asset->model }} - {{ $asset->asset_tag }}
                                    </option>
                                @endif
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('asset_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <!-- Asset Info Display -->
                <div id="assetInfo" class="mt-2 text-sm"></div>
            </div>

            <!-- Quantity and Duration - Side by Side -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">
                        Quantity *
                    </label>
                    <input type="number" 
                           name="quantity" 
                           id="quantity" 
                           min="1" 
                           value="{{ old('quantity', 1) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('quantity') border-red-500 @enderror">
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="duration_days" class="block text-sm font-medium text-gray-700">
                        Loan Duration (days) *
                    </label>
                    <input type="number" 
                           name="duration_days" 
                           id="duration_days" 
                           min="1" 
                           max="7"
                           value="{{ old('duration_days', 1) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('duration_days') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Maximum 5 days per loan</p>
                    @error('duration_days')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Purpose / Reason -->
            <div>
                <label for="purpose" class="block text-sm font-medium text-gray-700">
                    Purpose / Reason for Borrowing *
                </label>
                <textarea name="purpose" 
                          id="purpose" 
                          rows="4"
                          required
                          placeholder="e.g., Project presentation, Client meeting, Testing purposes..."
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Please explain why the employee needs to borrow this item</p>
                @error('purpose')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expected Return Date (Auto-calculated) -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-blue-700 uppercase tracking-wide mb-1">Expected Return Date</p>
                        <p class="text-xl font-bold text-blue-900" id="expectedReturn">Please enter duration</p>
                        <p class="text-xs text-blue-600 mt-1">Loan will be recorded with today's date and current time</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-6 border-t border-gray-200">
                <button type="submit"
                        id="submitBtn"
                        disabled
                        class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-saipem-primary text-white rounded-lg hover:bg-opacity-90 transition-all font-semibold disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Submit Loan
                </button>
                <a href="{{ route('loan-log.index') }}"
                   class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all font-semibold border border-gray-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/app.js'])
    <script>
        // Method 1: Gunakan event listener untuk ICTAssetAppReady
        window.addEventListener('ICTAssetAppReady', function() {
            console.log('🎯 ICTAssetAppReady event received - initializing forms');
            
            if (window.ICTAssetApp && typeof window.ICTAssetApp.initAdminLoanForm === 'function') {
                try {
                    window.ICTAssetApp.initAdminLoanForm();
                    console.log('✅ Admin Loan Form initialized successfully');
                } catch (error) {
                    console.error('❌ Error initializing Admin Loan Form:', error);
                }
            } else {
                console.error('❌ initAdminLoanForm still not available after ready event');
            }
        });

        // Method 2: Fallback - check periodically
        const checkAppReady = setInterval(() => {
            if (window.ICTAssetApp && typeof window.ICTAssetApp.initAdminLoanForm === 'function') {
                clearInterval(checkAppReady);
                console.log('🕒 Fallback: App ready detected, initializing forms');
                window.ICTAssetApp.initAdminLoanForm();
            }
        }, 100);

        // Timeout after 5 seconds
        setTimeout(() => {
            clearInterval(checkAppReady);
            if (!window.ICTAssetApp?.initAdminLoanForm) {
                console.error('⏰ Timeout: ICTAssetApp not ready after 5 seconds');
            }
        }, 5000);
    </script>
@endpush
@endsection 