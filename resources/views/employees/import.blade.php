@extends('layouts.app')

@section('title', 'Import Employees')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Bulk Import Employees from Excel</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Upload Form -->
    <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload Excel File</h2>
        
        <form action="{{ route('employees.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Excel File *
                </label>
                <input type="file" 
                       name="file" 
                       id="file" 
                       accept=".xlsx,.xls,.csv"
                       required
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 @error('file') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Supported formats: .xlsx, .xls, .csv (Max 10MB)</p>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <i data-lucide="sparkles" class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm text-blue-900">
                        <p class="font-bold text-base mb-2">🤖 Smart Employee Import</p>
                        <ul class="list-disc list-inside space-y-1.5">
                            <li><strong>No template required!</strong> - Upload Excel from Active Directory export or any format</li>
                            <li><strong>Auto-detect columns</strong> - Recognizes SamAccountName, EmailAddress, Enabled, etc.</li>
                            <li><strong>Auto-create/update</strong> - Creates new or updates existing employees</li>
                            <li><strong>Smart boolean parsing</strong> - TRUE/FALSE, 1/0, yes/no all recognized</li>
                            <li><strong>Ignore unknown columns</strong> - Extra columns are automatically ignored</li>
                            <li><strong>Skip empty cells</strong> - Empty cells use default values</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <i data-lucide="info" class="w-5 h-5 text-green-600 mr-3 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm text-green-900">
                        <p class="font-semibold mb-2">Required Column (case-insensitive):</p>
                        <div class="bg-white p-2 rounded">
                            <strong class="text-red-600">employee_id</strong> 
                            <span class="text-gray-600">- Unique identifier (variants: employeeID, emp_id, nik, nip)</span>
                        </div>
                        <p class="mt-3 text-xs text-gray-600">All other columns are optional and will use default values if empty</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('employees.index') }}" 
                   class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition flex items-center">
                    <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                    Import Employees
                </button>
            </div>
        </form>
    </div>

    <!-- Instructions -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="users" class="w-5 h-5 mr-2 text-blue-600"></i>
                How It Works
            </h3>
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex items-start">
                    <span class="font-bold text-blue-600 mr-2 text-lg">1.</span>
                    <span>Export employees from Active Directory or create your own Excel</span>
                </div>
                <div class="flex items-start">
                    <span class="font-bold text-blue-600 mr-2 text-lg">2.</span>
                    <span>Ensure <strong>employee_id</strong> column exists (any name variant works)</span>
                </div>
                <div class="flex items-start">
                    <span class="font-bold text-blue-600 mr-2 text-lg">3.</span>
                    <span>Column names are flexible - system auto-detects!</span>
                </div>
                <div class="flex items-start">
                    <span class="font-bold text-blue-600 mr-2 text-lg">4.</span>
                    <span>Upload and system will auto-create or update employees</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Example: AD Export</h3>
            <div class="bg-gray-50 p-3 rounded text-xs font-mono overflow-x-auto border">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-400">
                            <th class="text-left p-2 bg-green-100">employeeID</th>
                            <th class="text-left p-2 bg-gray-100">SamAccountName</th>
                            <th class="text-left p-2 bg-gray-100">EmailAddress</th>
                            <th class="text-left p-2 bg-gray-100">Enabled</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <tr class="border-b">
                            <td class="p-2">969214</td>
                            <td class="p-2">KAR969214</td>
                            <td class="p-2">user@saipem.com</td>
                            <td class="p-2">TRUE</td>
                        </tr>
                        <tr>
                            <td class="p-2">183100</td>
                            <td class="p-2">PSS183100</td>
                            <td class="p-2">user2@saipem.com</td>
                            <td class="p-2">FALSE</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <span class="bg-green-100 px-1 rounded">Green</span> = Required | 
                <span class="bg-gray-100 px-1 rounded">Gray</span> = Optional
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Recognized Column Names</h3>
            <div class="space-y-2 text-xs">
                <div class="grid grid-cols-1 gap-2">
                    <div class="bg-green-50 p-2 rounded border border-green-200">
                        <strong class="text-green-700">employee_id:</strong><br>
                        <span class="text-gray-600">employeeID, emp_id, nik, nip</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>user_id:</strong><br>
                        <span class="text-gray-600">SamAccountName, username, user_id</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>email:</strong><br>
                        <span class="text-gray-600">EmailAddress, email, mail</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>name:</strong><br>
                        <span class="text-gray-600">name, title, display name</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>is_active:</strong><br>
                        <span class="text-gray-600">Enabled, active, status</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>department:</strong><br>
                        <span class="text-gray-600">department, dept, divisi</span>
                    </div>
                </div>
                <p class="text-gray-500 mt-2">+ many more variants automatically recognized...</p>
            </div>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
            <div class="flex">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-yellow-900">
                    <p class="font-semibold mb-1">Important Notes</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Duplicate employee_id will UPDATE existing employee</li>
                        <li>Empty email will auto-generate: {employee_id}@temp.local</li>
                        <li>Empty cells keep existing values (for updates)</li>
                        <li>Enabled values: TRUE/true/1/yes → Active, FALSE/false/0/no → Inactive</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection