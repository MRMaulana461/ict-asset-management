@extends('layouts.guest')

@section('title', 'ICT Asset Loan Form')

@section('content')
<main class="max-w-5xl mx-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Info Box (Kiri Atas) -->
        <div class="md:col-span-1 order-1 md:order-none">
            <div class="p-5 bg-blue-50 border border-blue-200 rounded-lg shadow-sm sticky top-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-lg font-semibold text-blue-800">Loan Guidelines</h2>
                        <p class="text-sm text-blue-700 mb-2">Please read these rules before filling in the form:</p>
                        <ul class="list-disc pl-5 text-sm text-blue-700 space-y-1">
                            <li>Maximum loan period is <strong>5 working days</strong>.</li>
                            <li>Make sure the requested item is available in stock.</li>
                            <li>Employee ID must be valid (auto verification applies).</li>
                            <li>Provide a clear reason for the loan.</li>
                            <li>All items must be returned on the <strong>Expected Return Date</strong>.</li>
                            <li>Late returns may affect future borrowing privileges.</li>
                            <li>Report any damages or issues immediately.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Area (Kanan) -->
        <div class="md:col-span-2 order-2 md:order-none">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">ICT Asset Loan Form</h1>
            
            <!-- Flash Messages -->
            @if(session('success') === 'loan_success')
            <div class="alert alert-success bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4" 
                data-loan-quantity="{{ session('loan_quantity') }}" 
                data-loan-duration="{{ session('loan_duration') }}">
                <!-- Content will be filled by JavaScript -->
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
            
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <form action="{{ route('loan.submit') }}" method="POST" id="loanForm" class="space-y-6">
                    @csrf

                    <!-- Employee ID -->
                    <div>
                        <label for="ghrs_id" class="block text-sm font-medium text-gray-700">
                            Employee ID *
                        </label>
                        <input type="text" 
                            name="employee_id"  ← UBAH INI (bukan ghrs_id)
                            id="ghrs_id" 
                            value="{{ old('employee_id') }}"  ← DAN INI
                            placeholder="Enter your Employee ID (e.g., KAR175458)"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('employee_id') border-red-500 @enderror"/>
                        @error('employee_id')  ← DAN INI
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
                    </div>

                    <!-- Asset Type Dropdown -->
                    <div>
                        <label for="asset_type_id" class="block text-sm font-medium text-gray-700">Item to Borrow *</label>
                        <select name="asset_type_id" 
                                id="asset_type_id" 
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('asset_type_id') border-red-500 @enderror">
                            <option value="">Select Item</option>
                            @php
                                $currentCategory = null;
                            @endphp
                            @foreach($assetTypes as $type)
                                @if($currentCategory !== $type->category)
                                    @if($currentCategory !== null)
                                        </optgroup>
                                    @endif                                   
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
                        
                        <!-- Stock Info -->
                        <p id="stockInfo" class="mt-1 text-sm text-red-600 hidden"></p>
                        
                        @error('asset_type_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantity with Stock Validation -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                        <input type="number" 
                               name="quantity" 
                               id="quantity" 
                               value="{{ old('quantity', 1) }}" 
                               min="1" 
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('quantity') border-red-500 @enderror"/>
                        <p id="quantityError" class="mt-1 text-sm text-red-600 hidden"></p>
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Duration -->
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
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('duration_days') border-red-500 @enderror"/>
                        <p class="mt-1 text-xs text-gray-500">Maximum 5 working days</p>
                        @error('duration_days')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Purpose / Reason -->
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700">
                            Purpose / Reason for Borrowing *
                        </label>
                        <textarea name="purpose" 
                                id="purpose" 
                                rows="3"
                                required
                                placeholder="e.g., Project presentation, Client meeting, Testing purposes... (minimum 10 characters)"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-xs text-gray-500">Minimum 10 characters</p>
                            <p class="text-xs text-gray-500" id="purposeCounter">0/500</p>
                        </div>
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

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit"
                                id="submitBtn"
                                disabled
                                class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            Submit Loan Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
console.log('📄 Public Loan Form page script loaded');

function waitForApp() {
    if (!window.ICTAssetApp) {
        console.log('⏳ Waiting for ICTAssetApp...');
        setTimeout(waitForApp, 100);
        return;
    }
    
    if (typeof window.ICTAssetApp.initPublicLoanForm !== 'function') {
        console.error('❌ initPublicLoanForm not found');
        console.log('Available:', Object.keys(window.ICTAssetApp));
        return;
    }
    
    console.log('✅ Calling initPublicLoanForm()');
    window.ICTAssetApp.initPublicLoanForm();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForApp);
} else {
    waitForApp();
}
</script>
@endpush
@endsection