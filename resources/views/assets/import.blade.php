@extends('layouts.app')

@section('title', 'Import Assets')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Bulk Import Assets from Excel</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Upload Form -->
    <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload Excel File</h2>
        
        <form action="{{ route('assets.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-saipem-primary file:text-white hover:file:bg-opacity-90 @error('file') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Supported formats: .xlsx, .xls, .csv (Max 10MB)</p>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-l-4 border-purple-500 p-4 rounded">
                <div class="flex">
                    <i data-lucide="sparkles" class="w-6 h-6 text-purple-500 mr-3 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm text-purple-900">
                        <p class="font-bold text-base mb-2">🤖 AI-Like Smart Detection</p>
                        <ul class="list-disc list-inside space-y-1.5">
                            <li><strong>No template required!</strong> - Upload any Excel format</li>
                            <li><strong>Auto-detect columns</strong> - Recognizes various column names (asset_tag, Asset Tag, tag, etc.)</li>
                            <li><strong>Ignore unknown columns</strong> - Extra columns are automatically ignored</li>
                            <li><strong>Skip empty cells only</strong> - Empty cells use default values, not entire rows</li>
                            <li><strong>Auto-create employees</strong> - Creates employees automatically if needed</li>
                            <li><strong>Update existing assets</strong> - Automatically updates if asset_tag exists</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm text-blue-900">
                        <p class="font-semibold mb-2">Required Columns (case-insensitive):</p>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded">
                                <strong class="text-red-600">asset_tag</strong> 
                                <span class="text-gray-600">- Unique identifier (variants: tag, asset tag, no_asset)</span>
                            </div>
                            <div class="bg-white p-2 rounded">
                                <strong class="text-red-600">asset_type</strong> 
                                <span class="text-gray-600">- Asset type name (variants: type, tipe) - must exist in database</span>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-600">All other columns are optional</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('assets.index') }}" 
                   class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition flex items-center">
                    <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                    Import Assets
                </button>
            </div>
        </form>
    </div>

    <!-- Instructions -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i data-lucide="file-spreadsheet" class="w-5 h-5 mr-2 text-green-600"></i>
                How It Works
            </h3>
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex items-start">
                    <span class="font-bold text-purple-600 mr-2 text-lg">1.</span>
                    <span>Create Excel file with <strong>any format</strong> (.xlsx, .xls, .csv)</span>
                </div>
                <div class="flex items-start">
                    <span class="font-bold text-purple-600 mr-2 text-lg">2.</span>
                    <span>Ensure columns <strong>asset_tag</strong> and <strong>asset_type</strong> exist</span>
                </div>
                <div class="flex items-start">
                    <span class="font-bold text-purple-600 mr-2 text-lg">3.</span>
                    <span>Column names are flexible: "Asset Tag", "asset_tag", "tag" all work!</span>
                </div>
                <div class="flex items-start">
                    <span class="font-bold text-purple-600 mr-2 text-lg">4.</span>
                    <span>Upload and system will auto-detect everything!</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Example Excel Format</h3>
            <div class="bg-gray-50 p-3 rounded text-xs font-mono overflow-x-auto border">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-400">
                            <th class="text-left p-2 bg-blue-100">asset_tag</th>
                            <th class="text-left p-2 bg-blue-100">type</th>
                            <th class="text-left p-2 bg-gray-100">serial</th>
                            <th class="text-left p-2 bg-gray-100">employee_id</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <tr class="border-b">
                            <td class="p-2">LAP001</td>
                            <td class="p-2">Laptop</td>
                            <td class="p-2">SN123456</td>
                            <td class="p-2">EMP001</td>
                        </tr>
                        <tr>
                            <td class="p-2">MOU001</td>
                            <td class="p-2">Mouse</td>
                            <td class="p-2"></td>
                            <td class="p-2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <span class="bg-blue-100 px-1 rounded">Blue columns</span> = Required | 
                <span class="bg-gray-100 px-1 rounded">Gray columns</span> = Optional
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Recognized Column Names</h3>
            <div class="space-y-2 text-xs">
                <div class="grid grid-cols-1 gap-2">
                    <div class="bg-red-50 p-2 rounded border border-red-200">
                        <strong class="text-red-700">asset_tag:</strong><br>
                        <span class="text-gray-600">asset_tag, tag, asset tag, no_asset</span>
                    </div>
                    <div class="bg-red-50 p-2 rounded border border-red-200">
                        <strong class="text-red-700">asset_type:</strong><br>
                        <span class="text-gray-600">asset_type, type, tipe, jenis</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>serial_number:</strong><br>
                        <span class="text-gray-600">serial_number, serial, sn</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>employee_id:</strong><br>
                        <span class="text-gray-600">employee_id, nik, nip, emp_id</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded border">
                        <strong>status:</strong><br>
                        <span class="text-gray-600">status, condition (In Stock, In Use, Broken, Retired, Taken)</span>
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
                        <li>Asset Type must already exist in the database</li>
                        <li>Duplicate asset_tag will UPDATE existing asset</li>
                        <li>Empty cells are skipped (use existing/default values)</li>
                        <li>Employees are auto-created if employee_id not found</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection