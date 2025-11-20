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
            <!-- Borrower Info -->
            <div>
                <p class="text-sm font-medium text-gray-500">Borrower</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->borrower->name }}</p>
                <p class="text-xs text-gray-500">{{ $loanLog->borrower->ghrs_id }}</p>
            </div>
            
            <!-- Asset Type Info -->
            <div>
                <p class="text-sm font-medium text-gray-500">Asset Type</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $loanLog->assetType?->name ?? $loanLog->asset?->assetType?->name ?? '-' }}
                </p>
                @if($loanLog->asset)
                    <p class="text-xs text-gray-500">Tag: {{ $loanLog->asset->asset_tag }}</p>
                @else
                    <p class="text-xs text-gray-500 italic">Historical entry (no specific asset)</p>
                @endif
            </div>
            
            <!-- Loan Date -->
            <div>
                <p class="text-sm font-medium text-gray-500">Loan Date</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($loanLog->loan_date)->format('d M Y') }}
                </p>
                <p class="text-xs text-gray-500">{{ $loanLog->loan_time }}</p>
            </div>
            
            <!-- Current Duration -->
            <div>
                <p class="text-sm font-medium text-gray-500">Current Duration</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->duration_days }} days</p>
                @if($loanLog->extended_at)
                    <p class="text-xs text-yellow-600">
                        <i class="fas fa-info-circle mr-1"></i>Already extended
                    </p>
                @endif
            </div>
            
            <!-- Current Due Date -->
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
            
            <!-- Purpose -->
            <div>
                <p class="text-sm font-medium text-gray-500">Purpose</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->purpose ?? 'N/A' }}</p>
            </div>

            <!-- Quantity -->
            @if($loanLog->quantity > 1)
            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-500">Quantity</p>
                <p class="mt-1 text-sm text-gray-900">{{ $loanLog->quantity }} units</p>
            </div>
            @endif

            <!-- Previous Extension Info -->
            @if($loanLog->extension_reason)
            <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm font-medium text-yellow-800 mb-1">Previous Extension</p>
                <p class="text-xs text-yellow-700">
                    <strong>Reason:</strong> {{ $loanLog->extension_reason }}
                </p>
                @if($loanLog->extension_notes)
                <p class="text-xs text-yellow-700 mt-1">
                    <strong>Notes:</strong> {{ $loanLog->extension_notes }}
                </p>
                @endif
                <p class="text-xs text-yellow-600 mt-1">
                    Extended at: {{ \Carbon\Carbon::parse($loanLog->extended_at)->format('d M Y H:i') }}
                </p>
            </div>
            @endif
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
                <p class="mt-1 text-xs text-gray-500">Maximum 7 additional days</p>
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
                <p class="mt-1 text-xs text-gray-500">
                    <span id="reasonCharsCount">0</span>/500 characters
                </p>
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
                <p class="mt-1 text-xs text-gray-500">
                    <span id="notesCharsCount">0</span>/1000 characters
                </p>
            </div>

            <!-- Warning for Overdue Loans -->
            @if($isOverdue)
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-red-800">
                        <p class="font-semibold">This loan is overdue!</p>
                        <p class="mt-1">Please ensure the borrower returns or extends the loan as soon as possible.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-saipem-primary text-white rounded-md hover:bg-opacity-90 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

<!-- JavaScript for Date Preview and Character Counter -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const additionalDaysInput = document.getElementById('additional_days');
    const newDueDateElement = document.getElementById('newDueDate');
    const totalDurationElement = document.getElementById('totalDuration');
    const extensionReasonTextarea = document.getElementById('extension_reason');
    const notesTextarea = document.getElementById('notes');
    const reasonCharsCount = document.getElementById('reasonCharsCount');
    const notesCharsCount = document.getElementById('notesCharsCount');
    
    const loanDate = new Date('{{ $loanLog->loan_date }}');
    const currentDuration = {{ $loanLog->duration_days }};
    
    // Update date preview
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
    
    // Character counter for extension reason
    function updateReasonCounter() {
        reasonCharsCount.textContent = extensionReasonTextarea.value.length;
    }
    
    // Character counter for notes
    function updateNotesCounter() {
        notesCharsCount.textContent = notesTextarea.value.length;
    }
    
    // Event listeners
    additionalDaysInput.addEventListener('input', updateDatePreview);
    additionalDaysInput.addEventListener('change', updateDatePreview);
    extensionReasonTextarea.addEventListener('input', updateReasonCounter);
    notesTextarea.addEventListener('input', updateNotesCounter);
    
    // Initial updates
    updateDatePreview();
    updateReasonCounter();
    updateNotesCounter();
});
</script>
@endsection