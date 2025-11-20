@extends('layouts.app')

@section('title', 'Add New Employee')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Add New Employee</h1>
            <p class="text-gray-600 mt-1">Fill in the basic employee information</p>
        </div>
        
        <!-- Import Button -->
        <a href="{{ route('employees.import') }}" 
           class="bg-green-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-green-700 transition flex items-center gap-2 shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span>Bulk Import from Excel</span>
        </a>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-blue-900">
                <p class="font-semibold mb-1">Multiple Employees to Add?</p>
                <p>Use <a href="{{ route('employees.import') }}" class="font-bold underline hover:text-blue-700">Bulk Import</a> to add many employees at once from Excel file (supports Active Directory export)</p>
            </div>
        </div>
    </div>

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-xl shadow-lg p-8 space-y-6">
            
            <!-- Required Fields Section -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Required Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- GHRS ID (Required) -->
                    <div>
                        <label for="ghrs_id" class="block text-sm font-medium text-gray-700 mb-2">
                            GHRS ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="ghrs_id" id="ghrs_id" value="{{ old('ghrs_id') }}" required
                               placeholder="e.g., KAR175458"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ghrs_id') border-red-500 @enderror">
                        @error('ghrs_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Unique employee identifier (Required, must be unique)</p>
                    </div>

                    <!-- Employee Status (Required via database default) -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                            Employee Status <span class="text-red-500">*</span>
                        </label>
                        <select name="is_active" id="is_active" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Current employment status</p>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Personal Information Section (Optional) -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Personal Information <span class="text-sm text-gray-500 font-normal ml-2">(Optional)</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                               placeholder="e.g., John"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                               placeholder="e.g., Doe"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name / Display Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                               placeholder="e.g., John Doe or full name with title"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Complete name as it appears in system</p>
                    </div>

                    <div>
                        <label for="badge_id" class="block text-sm font-medium text-gray-700 mb-2">Badge ID</label>
                        <input type="text" name="badge_id" id="badge_id" value="{{ old('badge_id') }}"
                               placeholder="e.g., BADGE001"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User ID / SAM Account</label>
                        <input type="text" name="user_id" id="user_id" value="{{ old('user_id') }}"
                               placeholder="e.g., jdoe"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               placeholder="e.g., john.doe@saipem.com"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Must be unique if provided</p>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Organization Information Section (Optional) -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Organization Information <span class="text-sm text-gray-500 font-normal ml-2">(Optional)</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                        <input type="text" name="company" id="company" value="{{ old('company') }}"
                               placeholder="e.g., Saipem Indonesia"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="org_context" class="block text-sm font-medium text-gray-700 mb-2">Org. Context</label>
                        <input type="text" name="org_context" id="org_context" value="{{ old('org_context') }}"
                               placeholder="e.g., Main Office"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <input type="text" name="department" id="department" value="{{ old('department') }}"
                               placeholder="e.g., IT Department"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="dept_id" class="block text-sm font-medium text-gray-700 mb-2">Department ID</label>
                        <input type="text" name="dept_id" id="dept_id" value="{{ old('dept_id') }}"
                               placeholder="e.g., DEPT001"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="org_relation" class="block text-sm font-medium text-gray-700 mb-2">Org. Relation</label>
                        <input type="text" name="org_relation" id="org_relation" value="{{ old('org_relation') }}"
                               placeholder="e.g., Direct / Contractor"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="agency" class="block text-sm font-medium text-gray-700 mb-2">Agency</label>
                        <input type="text" name="agency" id="agency" value="{{ old('agency') }}"
                               placeholder="e.g., Recruitment Agency Name"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="role_company" class="block text-sm font-medium text-gray-700 mb-2">Role Company</label>
                        <input type="text" name="role_company" id="role_company" value="{{ old('role_company') }}"
                               placeholder="e.g., IT Support"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="contractual_position" class="block text-sm font-medium text-gray-700 mb-2">Contractual Position</label>
                        <input type="text" name="contractual_position" id="contractual_position" value="{{ old('contractual_position') }}"
                               placeholder="e.g., Permanent / Contract"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Financial Information Section (Optional) -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Financial Information <span class="text-sm text-gray-500 font-normal ml-2">(Optional)</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="cost_center" class="block text-sm font-medium text-gray-700 mb-2">Cost Center</label>
                        <input type="text" name="cost_center" id="cost_center" value="{{ old('cost_center') }}"
                               placeholder="e.g., CC001"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="cost_center_descr" class="block text-sm font-medium text-gray-700 mb-2">Cost Center Description</label>
                        <input type="text" name="cost_center_descr" id="cost_center_descr" value="{{ old('cost_center_descr') }}"
                               placeholder="e.g., IT Operations Cost Center"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="boc" class="block text-sm font-medium text-gray-700 mb-2">BoC</label>
                        <input type="text" name="boc" id="boc" value="{{ old('boc') }}"
                               placeholder="e.g., Budget Code"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="empl_rcd" class="block text-sm font-medium text-gray-700 mb-2">Employee Record #</label>
                        <input type="number" name="empl_rcd" id="empl_rcd" value="{{ old('empl_rcd') }}"
                               placeholder="e.g., 1"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Classification Section (Optional) -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Classification <span class="text-sm text-gray-500 font-normal ml-2">(Optional)</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="employee_class" class="block text-sm font-medium text-gray-700 mb-2">Employee Class</label>
                        <select name="employee_class" id="employee_class"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Select Class --</option>
                            <option value="W" {{ old('employee_class') == 'W' ? 'selected' : '' }}>W (White Collar)</option>
                            <option value="B" {{ old('employee_class') == 'B' ? 'selected' : '' }}>B (Blue Collar)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="tipo_terzi" class="block text-sm font-medium text-gray-700 mb-2">Tipo Terzi</label>
                        <input type="text" name="tipo_terzi" id="tipo_terzi" value="{{ old('tipo_terzi') }}"
                               placeholder="e.g., Employee Type Classification"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('employees.index') }}" 
                   class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    💾 Save Employee
                </button>
            </div>

        </div>
    </form>
</div>
@endsection