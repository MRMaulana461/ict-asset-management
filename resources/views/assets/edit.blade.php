@extends('layouts.app')

@section('title', 'Edit Asset')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Asset Data</h1>
        <p class="text-gray-600 mt-1">Update asset information</p>
    </div>

    <form action="{{ route('assets.update', $asset) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-lg p-8 space-y-6">
            
            <!-- Asset Tag -->
            <div>
                <label for="asset_tag" class="block text-sm font-medium text-gray-700 mb-2">
                    Asset Tag <span class="text-red-500">*</span>
                </label>
                <input type="text" name="asset_tag" id="asset_tag" value="{{ old('asset_tag', $asset->asset_tag) }}" required
                       placeholder="e.g., LAP-2025-001"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('asset_tag') border-red-500 @enderror">
                @error('asset_tag')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Asset Type -->
                <div>
                    <label for="asset_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Asset Type <span class="text-red-500">*</span>
                    </label>
                    <select name="asset_type_id" id="asset_type_id" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('asset_type_id') border-red-500 @enderror">
                        <option value="">-- Select Type --</option>
                        @foreach($assetTypes as $type)
                            <option value="{{ $type->id }}" {{ old('asset_type_id', $asset->asset_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('asset_type_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Serial Number -->
                <div>
                    <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Serial Number
                    </label>
                    <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}"
                           placeholder="Optional"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('serial_number') border-red-500 @enderror">
                    @error('serial_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="In Stock" {{ old('status', $asset->status) == 'In Stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="In Use" {{ old('status', $asset->status) == 'In Use' ? 'selected' : '' }}>In Use</option>
                        <option value="Broken" {{ old('status', $asset->status) == 'Broken' ? 'selected' : '' }}>Broken</option>
                        <option value="Retired" {{ old('status', $asset->status) == 'Retired' ? 'selected' : '' }}>Retired</option>
                        <option value="Taken" {{ old('status', $asset->status) == 'Taken' ? 'selected' : '' }}>Taken</option>
                    </select>
                </div>

                <!-- Delivery Date -->
                <div>
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Delivery Date
                    </label>
                    <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date', $asset->delivery_date) }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📦 Item Details (Optional)</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                    <input type="text" name="brand" id="brand" value="{{ old('brand', $asset->brand) }}"
                           placeholder="e.g., Dell, HP"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Model/Type</label>
                    <input type="text" name="type" id="type" value="{{ old('type', $asset->type) }}"
                           placeholder="e.g., Latitude 5540"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="service_tag" class="block text-sm font-medium text-gray-700 mb-2">Service Tag</label>
                    <input type="text" name="service_tag" id="service_tag" value="{{ old('service_tag', $asset->service_tag) }}"
                           placeholder="Vendor service tag"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">Item Description</label>
                <input type="text" name="item_name" id="item_name" value="{{ old('item_name', $asset->item_name) }}"
                       placeholder="e.g., Dell Latitude 5540 Laptop"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="specifications" class="block text-sm font-medium text-gray-700 mb-2">Specifications</label>
                <textarea name="specifications" id="specifications" rows="2"
                          placeholder="e.g., Intel Core i7, 16GB RAM, 512GB SSD"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('specifications', $asset->specifications) }}</textarea>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">👤 Assign to Employee (Optional)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-4">
                <p class="text-sm text-blue-900">
                    <strong>💡 Smart Auto-Fill:</strong> Just enter GHRS ID - employee name, department, and badge ID will be filled automatically!
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- GHRS ID Input -->
                <div>
                    <label for="ghrs_id_input" class="block text-sm font-medium text-gray-700 mb-2">
                        GHRS ID
                    </label>
                    <input type="text" id="ghrs_id_input" value="{{ old('ghrs_id_input', $asset->ghrs_id) }}"
                           placeholder="e.g., GHRS001"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p id="employee_status" class="mt-1 text-xs"></p>
                </div>

                <!-- Employee Name (Auto-filled) -->
                <div>
                    <label for="employee_name_display" class="block text-sm font-medium text-gray-700 mb-2">
                        Employee Name <span class="text-gray-400 text-xs">(Auto-filled)</span>
                    </label>
                    <input type="text" id="employee_name_display" readonly
                           value="{{ old('employee_name_display', $asset->assignedEmployee->name ?? '') }}"
                           placeholder="Will appear automatically"
                           class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-50 text-gray-600">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Department (Auto-filled from employee) -->
                <div>
                    <label for="department_display" class="block text-sm font-medium text-gray-700 mb-2">
                        Department <span class="text-gray-400 text-xs">(Auto-filled)</span>
                    </label>
                    <input type="text" id="department_display" readonly
                           value="{{ old('department_display', $asset->dept_project ?? $asset->assignedEmployee->department ?? '') }}"
                           placeholder="From employee data"
                           class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-50 text-gray-600">
                </div>

                <!-- Badge ID (Auto-filled from employee) -->
                <div>
                    <label for="badge_id_display" class="block text-sm font-medium text-gray-700 mb-2">
                        Badge ID <span class="text-gray-400 text-xs">(Auto-filled)</span>
                    </label>
                    <input type="text" id="badge_id_display" readonly
                           value="{{ old('badge_id_display', $asset->badge_id ?? $asset->assignedEmployee->badge_id ?? '') }}"
                           placeholder="From employee data"
                           class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-50 text-gray-600">
                </div>
            </div>

            <!-- Hidden fields yang akan dikirim ke server -->
            <input type="hidden" name="ghrs_id" id="ghrs_id" value="{{ old('ghrs_id', $asset->ghrs_id) }}">
            <input type="hidden" name="badge_id" id="badge_id" value="{{ old('badge_id', $asset->badge_id) }}">
            <input type="hidden" name="assigned_to" id="assigned_to" value="{{ old('assigned_to', $asset->assigned_to) }}">
            <input type="hidden" name="dept_project" id="dept_project" value="{{ old('dept_project', $asset->dept_project) }}">

            <div>
                <label for="assignment_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Assignment Date
                </label>
                <input type="date" name="assignment_date" id="assignment_date" value="{{ old('assignment_date', $asset->assignment_date) }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📍 Additional Info (Optional)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="pr_ref" class="block text-sm font-medium text-gray-700 mb-2">PR Reference</label>
                    <input type="text" name="pr_ref" id="pr_ref" value="{{ old('pr_ref', $asset->pr_ref) }}"
                           placeholder="e.g., PR-2025-001"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="po_ref" class="block text-sm font-medium text-gray-700 mb-2">PO Reference</label>
                    <input type="text" name="po_ref" id="po_ref" value="{{ old('po_ref', $asset->po_ref) }}"
                           placeholder="e.g., PO-2025-001"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Physical Location</label>
                <input type="text" name="location" id="location" value="{{ old('location', $asset->location) }}"
                       placeholder="e.g., Main Office 2nd Floor"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          placeholder="Any additional notes, warranty info, or special conditions"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $asset->notes) }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('assets.index') }}" 
                   class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    💾 Update Asset
                </button>
            </div>

        </div>
    </form>

    <!-- FORM DELETE (DI LUAR FORM UPDATE) -->
    <div class="bg-white rounded-xl shadow-lg p-8 mt-6">
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-red-600 mb-4">⚠️ Danger Zone</h3>
            <p class="text-sm text-gray-600 mb-4">Deleting this asset is permanent and cannot be undone.</p>
            <form action="{{ route('assets.destroy', $asset) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this asset? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Asset
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ghrsIdInput = document.getElementById('ghrs_id_input');
    const ghrsIdHidden = document.getElementById('ghrs_id');
    const badgeIdHidden = document.getElementById('badge_id');
    const employeeNameDisplay = document.getElementById('employee_name_display');
    const departmentDisplay = document.getElementById('department_display');
    const badgeIdDisplay = document.getElementById('badge_id_display');
    const assignedToHidden = document.getElementById('assigned_to');
    const deptProjectHidden = document.getElementById('dept_project');
    const employeeStatus = document.getElementById('employee_status');

    let debounceTimer;
    ghrsIdInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const ghrsId = this.value.trim().toUpperCase();

        if (ghrsId === '') {
            // Clear all fields
            ghrsIdHidden.value = '';
            badgeIdHidden.value = '';
            employeeNameDisplay.value = '';
            departmentDisplay.value = '';
            badgeIdDisplay.value = '';
            assignedToHidden.value = '';
            deptProjectHidden.value = '';
            employeeStatus.textContent = '';
            employeeStatus.className = 'mt-1 text-xs';
            return;
        }

        employeeStatus.textContent = '🔍 Searching...';
        employeeStatus.className = 'mt-1 text-xs text-gray-500';

        debounceTimer = setTimeout(() => {
            fetch(`/api/employee/${encodeURIComponent(ghrsId)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Fill all fields from employee data
                        ghrsIdHidden.value = ghrsId;
                        badgeIdHidden.value = data.data.badge_id || '';
                        employeeNameDisplay.value = data.data.name;
                        departmentDisplay.value = data.data.department || '-';
                        badgeIdDisplay.value = data.data.badge_id || '-';
                        assignedToHidden.value = data.data.id || '';
                        deptProjectHidden.value = data.data.department || '';
                        
                        employeeStatus.textContent = '✓ Employee data loaded successfully';
                        employeeStatus.className = 'mt-1 text-xs text-green-600 font-medium';
                    } else {
                        // Clear all fields
                        ghrsIdHidden.value = '';
                        badgeIdHidden.value = '';
                        employeeNameDisplay.value = '';
                        departmentDisplay.value = '';
                        badgeIdDisplay.value = '';
                        assignedToHidden.value = '';
                        deptProjectHidden.value = '';
                        
                        employeeStatus.textContent = '✗ Employee not found';
                        employeeStatus.className = 'mt-1 text-xs text-red-600 font-medium';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Clear all fields
                    ghrsIdHidden.value = '';
                    badgeIdHidden.value = '';
                    employeeNameDisplay.value = '';
                    departmentDisplay.value = '';
                    badgeIdDisplay.value = '';
                    assignedToHidden.value = '';
                    deptProjectHidden.value = '';
                    
                    employeeStatus.textContent = '✗ Error searching employee';
                    employeeStatus.className = 'mt-1 text-xs text-red-600 font-medium';
                });
        }, 500);
    });
});
</script>
@endpush
@endsection