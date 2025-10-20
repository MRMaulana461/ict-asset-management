@extends('layouts.app')

@section('title', 'Edit Aset')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Data Aset</h1>

<div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
    <form action="{{ route('assets.update', $asset) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div>
            <label for="asset_tag" class="block text-sm font-medium text-gray-700">Asset Tag *</label>
            <input type="text" name="asset_tag" id="asset_tag" value="{{ old('asset_tag', $asset->asset_tag) }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('asset_tag') border-red-500 @enderror">
            @error('asset_tag')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
            <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('serial_number') border-red-500 @enderror">
            @error('serial_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="asset_type_id" class="block text-sm font-medium text-gray-700">Asset Type *</label>
            <select name="asset_type_id" id="asset_type_id" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('asset_type_id') border-red-500 @enderror">
                <option value="">Pilih Tipe</option>
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

        <div>
            <label for="employee_id" class="block text-sm font-medium text-gray-700">Owner (Employee)</label>
            <select name="employee_id" id="employee_id"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('employee_id') border-red-500 @enderror">
                <option value="">- Tidak Ada Owner -</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id', $asset->employee_id) == $employee->id ? 'selected' : '' }}>
                        {{ $employee->employee_id }} - {{ $employee->name }}
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
            <select name="status" id="status" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('status') border-red-500 @enderror">
                <option value="In Stock" {{ old('status', $asset->status) == 'In Stock' ? 'selected' : '' }}>In Stock</option>
                <option value="In Use" {{ old('status', $asset->status) == 'In Use' ? 'selected' : '' }}>In Use</option>
                <option value="Broken" {{ old('status', $asset->status) == 'Broken' ? 'selected' : '' }}>Broken</option>
                <option value="Retired" {{ old('status', $asset->status) == 'Retired' ? 'selected' : '' }}>Retired</option>
                <option value="Taken" {{ old('status', $asset->status) == 'Taken' ? 'selected' : '' }}>Taken</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Description / Notes</label>
            <textarea name="notes" id="notes" rows="4"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">{{ old('notes', $asset->notes) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('assets.index') }}" 
               class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition">
                Update Asset
            </button>
        </div>
    </form>

    <!-- FORM DELETE (DI LUAR FORM UPDATE) -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <form action="{{ route('assets.destroy', $asset) }}" method="POST" 
              onsubmit="return confirm('Yakin ingin menghapus aset ini? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Aset
            </button>
        </form>
    </div>
</div>
@endsection