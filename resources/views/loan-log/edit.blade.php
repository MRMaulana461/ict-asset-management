@extends('layouts.app')

@section('title', 'Extend Loan Duration')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('loan-log.index') }}" 
           class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Loan Log
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Extend Loan Duration</h1>
        <p class="mt-1 text-sm text-gray-600">Add additional days to the loan period</p>
    </div>

    <!-- Loan Information Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Loan Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Borrower</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->borrower->name }}</p>
                <p class="text-xs text-gray-500">{{ $loanLog->borrower->employee_id }}</p>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-500">Asset</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->asset->asset_tag }}</p>
                <p class="text-xs text-gray-500">{{ $loanLog->asset->assetType->name }}</p>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-500">Loan Date</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($loanLog->loan_date)->format('d M Y') }}
                </p>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-500">Current Duration</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->duration_days }} days</p>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-500">Current Due Date</p>
                @php
                    $currentDueDate = \Carbon\Carbon::parse($loanLog->loan_date)->addDays($loanLog->duration_days);
                    $isOverdue = $currentDueDate->isPast();
                @endphp
                <p class="mt-1 text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                    {{ $currentDueDate->format('d M Y') }}
                    @if($isOverdue)
                        <span class="text-xs">(Overdue)</span>
                    @endif
                </p>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-500">Purpose</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->signature ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Extension Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Loan Extension</h2>
        
        <form action="{{ route('loan-log.update', $loanLog) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Additional Days -->
            <div>
                <label for="additional_days" class="block text-sm font-medium text-gray-700 mb-1">
                    Additional Days <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="additional_days" 
                       id="additional_days" 
                       min="1" 
                       max="30"
                       value="{{ old('additional_days', 1) }}"
                       required
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('additional_days') border-red-500 @enderror">
                @error('additional_days')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Maximum 5 additional days</p>
            </div>

            <!-- New Due Date Preview -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <p class="text-sm font-medium text-blue-800">New Due Date Preview</p>
                <p class="mt-1 text-lg font-semibold text-blue-900" id="newDueDate">
                    {{ \Carbon\Carbon::parse($loanLog->loan_date)->addDays($loanLog->duration_days + 7)->format('d M Y') }}
                </p>
                <p class="text-xs text-blue-600 mt-1">
                    Total duration: <span id="totalDuration">{{ $loanLog->duration_days + 7 }}</span> days
                </p>
            </div>

            <!-- Extension Reason -->
            <div>
                <label for="extension_reason" class="block text-sm font-medium text-gray-700 mb-1">
                    Extension Reason <span class="text-red-500">*</span>
                </label>
                <textarea name="extension_reason" 
                          id="extension_reason" 
                          rows="3" 
                          required
                          maxlength="500"
                          placeholder="Why do you need to extend this loan?"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm @error('extension_reason') border-red-500 @enderror">{{ old('extension_reason') }}</textarea>
                @error('extension_reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Maximum 500 characters</p>
            </div>

            <!-- Additional Notes (Optional) -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                    Additional Notes <span class="text-gray-400">(Optional)</span>
                </label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3" 
                          maxlength="1000"
                          placeholder="Any additional information..."
                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm">{{ old('notes') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-saipem-primary text-white rounded-md hover:bg-opacity-90 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Extend Loan
                </button>
                <a href="{{ route('loan-log.index') }}"
                   class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for Date Preview -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const additionalDaysInput = document.getElementById('additional_days');
    const newDueDateElement = document.getElementById('newDueDate');
    const totalDurationElement = document.getElementById('totalDuration');
    
    const loanDate = new Date('{{ $loanLog->loan_date }}');
    const currentDuration = {{ $loanLog->duration_days }};
    
    function updateDatePreview() {
        const additionalDays = parseInt(additionalDaysInput.value) || 0;
        const totalDays = currentDuration + additionalDays;
        
        const newDueDate = new Date(loanDate);
        newDueDate.setDate(newDueDate.getDate() + totalDays);
        
        // Format date (dd MMM yyyy)
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        const formattedDate = newDueDate.toLocaleDateString('en-GB', options);
        
        newDueDateElement.textContent = formattedDate;
        totalDurationElement.textContent = totalDays;
    }
    
    additionalDaysInput.addEventListener('input', updateDatePreview);
    additionalDaysInput.addEventListener('change', updateDatePreview);
    
    // Initial update
    updateDatePreview();
});
</script>
@endsection