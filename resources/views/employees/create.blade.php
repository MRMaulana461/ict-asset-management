@extends('layouts.app')

@section('title', 'Tambah Employee')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Add New Employee</h1>
    
    <!-- Import Button -->
    <a href="{{ route('employees.import') }}" 
       class="bg-green-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-green-700 transition flex items-center gap-2 shadow-lg">
        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
        <span>Bulk Import from Excel</span>
    </a>
</div>

<!-- Info Banner -->
<div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
    <div class="flex">
        <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5"></i>
        <div class="text-sm text-blue-900">
            <p class="font-semibold mb-1">Multiple Employees to Add?</p>
            <p>Use <a href="{{ route('employees.import') }}" class="font-bold underline hover:text-blue-700">Bulk Import</a> to add many employees at once from Excel file (supports Active Directory export)</p>
        </div>
    </div>
</div>

<div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
    <form action="{{ route('employees.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" required
                   placeholder="Example: SAI001 atau KAR175458"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('employee_id') border-red-500 @enderror">
            @error('employee_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700">User ID</label>
            <input type="text" name="user_id" id="user_id" value="{{ old('user_id') }}"
                   placeholder="Example: USR001"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   placeholder="Example: Hendrafajalu Alamasyah"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                   placeholder="Example: hendrafajalu.alamasyah@saipem.com"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
            <input type="text" name="department" id="department" value="{{ old('department') }}"
                   placeholder="Example: DEPT. Karimun Yard Quality"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label for="cost_center" class="block text-sm font-medium text-gray-700">Cost Center</label>
            <input type="text" name="cost_center" id="cost_center" value="{{ old('cost_center') }}"
                   placeholder="Example: 862321"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-saipem-primary focus:ring-saipem-accent">
                <span class="ml-2 text-sm text-gray-700">Active Employee</span>
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('employees.index') }}" 
               class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                Batal
            </a>
            <button type="submit" 
                    class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition">
                Simpan Employee
            </button>
        </div>
    </form>
</div>
@endsection