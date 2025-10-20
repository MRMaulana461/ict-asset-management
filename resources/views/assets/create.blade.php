@extends('layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Asset Form Input</h1>

    <div class="bg-white p-8 rounded-xl shadow-lg">
        <form action="{{ route('assets.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Grid Container untuk 2 kolom -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Kolom Kiri -->
                <div class="space-y-6">
                    <!-- Items Type -->
                    <div>
                        <label for="asset_type_id" class="block text-sm font-medium text-gray-700">Items Type *</label>
                        <select name="asset_type_id" id="asset_type_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                            <option value="">Choose Type</option>
                            @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}" 
                                        data-category="{{ strtolower($type->category) }}"
                                        {{ old('asset_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->category }})
                                </option>
                            @endforeach
                        </select>
                        @error('asset_type_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Asset Tag (Hidden untuk Peripheral) -->
                    <div id="asset_tag_field">
                        <label for="asset_tag" class="block text-sm font-medium text-gray-700">
                            Asset Tag <span class="text-red-500" id="asset_tag_required">*</span>
                        </label>
                        <input type="text" name="asset_tag" id="asset_tag" value="{{ old('asset_tag') }}"
                               placeholder="Example: SAI-LAP-0123"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('asset_tag') border-red-500 @enderror">
                        @error('asset_tag')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Serial Number (Hidden untuk Peripheral) -->
                    <div id="serial_number_field">
                        <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                               placeholder="Example: SN123456789"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('serial_number') border-red-500 @enderror">
                        @error('serial_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantity (Only for Peripheral) -->
                    <div id="quantity_field" style="display: none;">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1"
                               placeholder="Enter quantity"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('quantity') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Number of peripheral items to add</p>
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <input type="hidden" name="status" value="{{ old('status', 'In Stock') }}">
                </div>

                <!-- Kolom Kanan -->
                <div class="space-y-6">
                    <!-- Assign to Employee -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign to Employee</label>
                        <select name="assigned_to" id="assigned_to"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                            <option value="">-- not assigned --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('assigned_to') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assignment Date -->
                    <div>
                        <label for="assignment_date" class="block text-sm font-medium text-gray-700">Assignment Date</label>
                        <input type="date" name="assignment_date" id="assignment_date" value="{{ old('assignment_date', date('Y-m-d')) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                    </div>

                    <!-- Status Date -->
                    <div>
                        <label for="last_status_date" class="block text-sm font-medium text-gray-700">Status Date *</label>
                        <input type="date" name="last_status_date" id="last_status_date" 
                               value="{{ old('last_status_date', date('Y-m-d')) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                    </div>
                </div>
            </div>

            <!-- Notes - Full Width -->
            <div class="pt-4">
                <label for="notes" class="block text-sm font-medium text-gray-700">Description / Notes</label>
                <textarea name="notes" id="notes" rows="4" placeholder="Example: Mouse Logitech, Keyboard Mechanical"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">{{ old('notes') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Enter details such as brand, model, specifications.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('assets.index') }}" 
                   class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const assetTypeSelect = document.getElementById('asset_type_id');
    const assetTagField = document.getElementById('asset_tag_field');
    const serialNumberField = document.getElementById('serial_number_field');
    const quantityField = document.getElementById('quantity_field');
    const assetTagInput = document.getElementById('asset_tag');
    const assetTagRequired = document.getElementById('asset_tag_required');

    function toggleFields() {
        const selectedOption = assetTypeSelect.options[assetTypeSelect.selectedIndex];
        const category = selectedOption.getAttribute('data-category');
        
        if (category === 'peripheral') {
            // Peripheral: hide asset_tag & serial_number, show quantity
            assetTagField.style.display = 'none';
            serialNumberField.style.display = 'none';
            quantityField.style.display = 'block';
            
            // Remove required attribute
            assetTagInput.removeAttribute('required');
            document.getElementById('quantity').setAttribute('required', 'required');
        } else {
            // Hardware: show asset_tag & serial_number, hide quantity
            assetTagField.style.display = 'block';
            serialNumberField.style.display = 'block';
            quantityField.style.display = 'none';
            
            // Add required attribute
            assetTagInput.setAttribute('required', 'required');
            document.getElementById('quantity').removeAttribute('required');
        }
    }

    // Initial check
    toggleFields();

    // Listen to changes
    assetTypeSelect.addEventListener('change', toggleFields);
});
</script>
@endpush
@endsection