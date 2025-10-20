@extends('layouts.app')

@section('title', 'Loan Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('loan-log.index') }}"
               class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Loan Details</h1>
                <p class="mt-1 text-sm text-gray-600">Loan ID: #{{ $loanLog->id }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if($loanLog->status == 'On Loan')
                <a href="{{ route('loan-log.edit', $loanLog) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Extend Duration
                </a>
                <button onclick="showReturnModal()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Mark as Returned
                </button>
            @endif
        </div>
    </div>

    <!-- Status Alert -->
    @php
        $expectedReturn = \Carbon\Carbon::parse($loanLog->loan_date)->addDays($loanLog->duration_days);
        // Gunakan startOfDay untuk menghindari desimal dari perhitungan jam
        $daysUntilReturn = now()->startOfDay()->diffInDays($expectedReturn->startOfDay(), false);
        $isOverdue = $loanLog->status == 'On Loan' && $daysUntilReturn < 0;
        $isDueToday = $loanLog->status == 'On Loan' && $daysUntilReturn == 0;
        $isDueSoon = $loanLog->status == 'On Loan' && $daysUntilReturn == 1;
    @endphp

    @if($isOverdue)
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">OVERDUE: {{ abs($daysUntilReturn) }} day(s) late!</span>
            </div>
        </div>
    @elseif($isDueToday)
        <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">DUE TODAY: Asset must be returned today!</span>
            </div>
        </div>
    @elseif($isDueSoon)
        <div class="mb-6 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">DUE TOMORROW: Return expected tomorrow</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Status Badge -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Current Status</label>
                    @if($loanLog->status == 'On Loan')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            On Loan
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Returned
                        </span>
                    @endif
                </div>
                @if($loanLog->status == 'Returned')
                    <div class="text-right">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Returned On</label>
                        <p class="text-gray-900 font-semibold">{{ $loanLog->return_date->format('d M Y') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Borrower Information -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-saipem-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Borrower Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->borrower->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Employee ID</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->borrower->employee_id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Department</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->borrower->department ?? '-' }}</p>
                </div>
                @if($loanLog->borrower->email)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <a href="mailto:{{ $loanLog->borrower->email }}" class="text-blue-600 hover:underline">
                        {{ $loanLog->borrower->email }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Asset Information -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-saipem-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
                Asset Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Asset Type</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->asset->assetType->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Asset Tag</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->asset->asset_tag }}</p>
                </div>
                @if($loanLog->asset->serial_number)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Serial Number</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->asset->serial_number }}</p>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Quantity</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->quantity }}</p>
                </div>
            </div>
        </div>

        <!-- Loan Details -->
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-saipem-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Loan Timeline
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Loan Date</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->loan_date->format('l, d F Y') }}</p>
                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($loanLog->loan_time)->format('H:i') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->duration_days }} day(s)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Expected Return Date</label>
                    <p class="text-gray-900 font-semibold">{{ $expectedReturn->format('l, d F Y') }}</p>
                </div>
                @if($loanLog->status == 'Returned' && $loanLog->return_date)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Actual Return Date</label>
                    <p class="text-gray-900 font-semibold">{{ $loanLog->return_date->format('l, d F Y') }}</p>
                    @php
                        $returnDiff = $loanLog->return_date->diffInDays($expectedReturn, false);
                    @endphp
                    @if($returnDiff < 0)
                        <p class="text-sm text-red-600">{{ abs($returnDiff) }} day(s) late</p>
                    @elseif($returnDiff > 0)
                        <p class="text-sm text-green-600">{{ $returnDiff }} day(s) early</p>
                    @else
                        <p class="text-sm text-green-600">On time</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div id="returnModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Return</h3>
            <p class="text-sm text-gray-500 mb-6">Are you sure you want to mark this item as returned?</p>
            <form action="{{ route('loan-log.update', $loanLog) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Returned">
                <input type="hidden" name="return_date" value="{{ date('Y-m-d') }}">
                
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeReturnModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showReturnModal() {
    document.getElementById('returnModal').classList.remove('hidden');
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('returnModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReturnModal();
    }
});
</script>
@endpush
@endsection