@extends('layouts.app')

@section('title', 'Manual Loan Entry')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Manual Loan Entry</h1>
                    <p class="text-sm text-gray-600 mt-1">Import historical loan data from physical records</p>
                </div>
                <a href="{{ route('loan-log.index') }}" 
                   class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Loan Log
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
                    <p class="font-semibold">Admin Manual Entry Mode</p>
                    <p class="mt-1">This form allows you to enter historical loan records with custom dates. Stock validation is disabled for historical data import.</p>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-green-800 font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-red-800 font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('loan-log.store') }}" method="POST" id="loanForm" class="space-y-6">
                @csrf

                <!-- Date & Time Section -->
                <div class="pb-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Date & Time</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="loan_date" class="block text-sm font-medium text-gray-700 mb-1">Loan Date *</label>
                            <input type="date" 
                                   name="loan_date" 
                                   id="loan_date" 
                                   value="{{ old('loan_date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('loan_date') border-red-500 @enderror">
                            @error('loan_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="loan_time" class="block text-sm font-medium text-gray-700 mb-1">Loan Time *</label>
                            <input type="time" 
                                   name="loan_time" 
                                   id="loan_time" 
                                   value="{{ old('loan_time', date('H:i')) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('loan_time') border-red-500 @enderror">
                            @error('loan_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Employee Section -->
                <div class="pb-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Borrower Information</h3>
                    
                    <div class="space-y-4">
                        <!-- Employee ID -->
                        <div>
                            <label for="ghrs_id" class="block text-sm font-medium text-gray-700 mb-1">Employee ID *</label>
                            <input type="text" 
                                   name="ghrs_id" 
                                   id="ghrs_id" 
                                   value="{{ old('ghrs_id') }}"
                                   placeholder="e.g., 191972"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ghrs_id') border-red-500 @enderror">
                            @error('ghrs_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p id="employeeError" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Employee Name (Auto-filled) -->
                        <div>
                            <label for="employee_name" class="block text-sm font-medium text-gray-700 mb-1">Employee Name</label>
                            <input type="text" 
                                   id="employee_name" 
                                   readonly
                                   placeholder="Name will appear automatically"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                        </div>

                        <!-- Department (Auto-filled) -->
                        <div>
                            <label for="employee_dept" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <input type="text" 
                                   id="employee_dept" 
                                   readonly
                                   placeholder="Department will appear automatically"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- Asset Section -->
                <div class="pb-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Asset Information</h3>
                    
                    <!-- Asset Type Selection -->
                    <div class="mb-4">
                        <label for="asset_type_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Asset Type *
                        </label>
                        <select name="asset_type_id" 
                                id="asset_type_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('asset_type_id') border-red-500 @enderror">
                            <option value="">Select Asset Type</option>
                            @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}" {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('asset_type_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            For historical data, select asset type only. Specific asset tracking not required.
                        </p>
                    </div>

                    <!-- Quantity & Duration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                            <input type="number" 
                                name="quantity" 
                                id="quantity" 
                                value="{{ old('quantity', 1) }}" 
                                min="1" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('quantity') border-red-500 @enderror">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">Duration (days) *</label>
                            <input type="number" 
                                name="duration_days" 
                                id="duration_days" 
                                value="{{ old('duration_days', 1) }}" 
                                min="1"
                                max="365"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('duration_days') border-red-500 @enderror">
                            @error('duration_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div class="mt-4">
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                        <textarea name="purpose" 
                                id="purpose" 
                                rows="3" 
                                required
                                maxlength="500"
                                placeholder="e.g., Project presentation, Client meeting..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            <span id="purposeCount">0</span>/500 characters
                        </p>
                    </div>
                </div>

                <!-- Return Section -->
                <div class="pb-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Return Information</h3>
                    
                    <!-- Status -->
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" 
                                id="status" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="On Loan" {{ old('status') == 'On Loan' ? 'selected' : '' }}>On Loan</option>
                            <option value="Returned" {{ old('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>

                    <!-- Return Date & Time (shown only if status is Returned) -->
                    <div id="returnFields" class="space-y-4" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="return_date" class="block text-sm font-medium text-gray-700 mb-1">Return Date</label>
                                <input type="date" 
                                       name="return_date" 
                                       id="return_date" 
                                       value="{{ old('return_date') }}"
                                       max="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="return_time" class="block text-sm font-medium text-gray-700 mb-1">Return Time</label>
                                <input type="time" 
                                       name="return_time" 
                                       id="return_time" 
                                       value="{{ old('return_time') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('loan-log.index') }}" 
                       class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            id="submitBtn"
                            disabled
                            class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>Save Loan Record
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
            const statusSelect = document.getElementById('status');
            const returnFields = document.getElementById('returnFields');
            const returnDateInput = document.getElementById('return_date');
            const returnTimeInput = document.getElementById('return_time');
            const purposeTextarea = document.getElementById('purpose');
            const purposeCount = document.getElementById('purposeCount');

            // Show/hide return fields based on status
            function toggleReturnFields() {
                if (statusSelect.value === 'Returned') {
                    returnFields.style.display = 'block';
                    returnDateInput.required = true;
                    returnTimeInput.required = true;
                } else {
                    returnFields.style.display = 'none';
                    returnDateInput.required = false;
                    returnTimeInput.required = false;
                    returnDateInput.value = '';
                    returnTimeInput.value = '';
                }
            }

            // Character counter for purpose
            function updatePurposeCounter() {
                purposeCount.textContent = purposeTextarea.value.length;
            }

            // Event listeners
            statusSelect.addEventListener('change', toggleReturnFields);
            purposeTextarea.addEventListener('input', updatePurposeCounter);

            // Initial checks
            toggleReturnFields();
            updatePurposeCounter();

            // Initialize form
            setTimeout(() => {
                if (window.ICTAssetApp && typeof window.ICTAssetApp.initLoanForm === 'function') {
                    window.ICTAssetApp.initLoanForm();
                } else {
                    console.error('initLoanForm not found');
                }
            }, 100);
        });
    </script>
@endpush
@endsection