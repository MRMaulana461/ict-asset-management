@extends('layouts.guest')

@section('title', 'ICT Asset Damage Report')

@section('content')
<main class="max-w-5xl mx-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Info Box (Kanan Atas) -->
        <div class="md:col-span-1 order-1 md:order-none">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg sticky top-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Important Notes:</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                            <li>This form is for reporting damaged or broken ICT assets</li>
                            <li>Please provide detailed information about the damage</li>
                            <li>ICT team will contact you for further action</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form (Kiri, lebih besar) -->
        <div class="md:col-span-2 order-2 md:order-none">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">ICT Asset Damage Report</h1>

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

            <div class="bg-white p-8 rounded-xl shadow-lg">
                <form action="{{ route('withdrawal.store') }}" method="POST" id="withdrawalForm" class="space-y-6">
                    @csrf

                    <!-- Employee ID -->
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}"
                               placeholder="e.g., KAR175458" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('employee_id') border-red-500 @enderror"/>
                        @error('employee_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
                        <label for="asset_type_id" class="block text-sm font-medium text-gray-700">Damaged Item Type *</label>
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
                        @error('asset_type_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('quantity') border-red-500 @enderror"/>
                        <p class="mt-1 text-xs text-gray-500">Number of damaged items</p>
                        @error('quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Reason -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">Damage Description / Reason *</label>
                        <textarea name="reason" id="reason" rows="4" required placeholder="Please describe the damage in detail (e.g., Screen broken, Not turning on, Cable damaged...)"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Be as detailed as possible to help ICT team understand the issue</p>
                        @error('reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" id="submitBtn" disabled
                                class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            Submit Damage Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@push('scripts')
    @vite(['resources/js/app.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu sedikit untuk memastikan semua script sudah loaded
            setTimeout(() => {
                console.log('ICTAssetApp:', window.ICTAssetApp);
                
                if (window.ICTAssetApp && typeof window.ICTAssetApp.initPublicLoanForm === 'function') {
                    window.ICTAssetApp.initPublicLoanForm();
                } else {
                    console.error('initPublicLoanForm not found');
                }
                
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
