@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Employee</h1>

<div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
    <form action="{{ route('employees.update', $employee) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('employee_id') border-red-500 @enderror">
            @error('employee_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700">User ID</label>
            <input type="text" name="user_id" id="user_id" value="{{ old('user_id', $employee->user_id) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
            <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
            <input type="text" name="department" id="department" value="{{ old('department', $employee->department) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label for="cost_center" class="block text-sm font-medium text-gray-700">Cost Center</label>
            <input type="text" name="cost_center" id="cost_center" value="{{ old('cost_center', $employee->cost_center) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
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
                Update Employee
            </button>
        </div>
    </form>

        <!-- Form DELETE -->
    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="mt-6 flex justify-start"
          onsubmit="return confirm('Yakin ingin menghapus employee ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="bg-red-500 text-white font-semibold px-6 py-2.5 rounded-lg shadow-md hover:bg-red-600 hover:shadow-lg transition">
            Hapus Employee
        </button>
    </form>
@endsection