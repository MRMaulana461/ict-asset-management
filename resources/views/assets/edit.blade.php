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
            <label for="asset_type" class="block text-sm font-medium text-gray-700">Tipe Perangkat *</label>
            <select name="asset_type" id="asset_type" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                <option value="">Pilih Tipe</option>
                <option value="Laptop" {{ old('asset_type', $asset->asset_type) == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                <option value="Desktop/PC" {{ old('asset_type', $asset->asset_type) == 'Desktop/PC' ? 'selected' : '' }}>Desktop/PC</option>
                <option value="Workstation" {{ old('asset_type', $asset->asset_type) == 'Workstation' ? 'selected' : '' }}>Workstation</option>
                <option value="Printer" {{ old('asset_type', $asset->asset_type) == 'Printer' ? 'selected' : '' }}>Printer</option>
                <option value="Monitor" {{ old('asset_type', $asset->asset_type) == 'Monitor' ? 'selected' : '' }}>Monitor</option>
            </select>
        </div>

        <div>
            <label for="current_owner" class="block text-sm font-medium text-gray-700">Owner / Penanggung Jawab</label>
            <input type="text" name="current_owner" id="current_owner" value="{{ old('current_owner', $asset->current_owner) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
            <select name="status" id="status" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                <option value="Normal" {{ old('status', $asset->status) == 'Normal' ? 'selected' : '' }}>Normal</option>
                <option value="Rusak" {{ old('status', $asset->status) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                <option value="Dipinjam" {{ old('status', $asset->status) == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
            </select>
        </div>

        <div>
            <label for="last_status_date" class="block text-sm font-medium text-gray-700">Tanggal Status *</label>
            <input type="date" name="last_status_date" id="last_status_date" 
                   value="{{ old('last_status_date', $asset->last_status_date->format('Y-m-d')) }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Deskripsi / Catatan</label>
            <textarea name="notes" id="notes" rows="4"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">{{ old('notes', $asset->notes) }}</textarea>
        </div>

        <div class="flex justify-between pt-2">
            <!-- FORM DELETE DIPINDAHKAN KE LUAR FORM UPDATE -->
            <div>
                <!-- Nanti kita akan taruh di sini -->
            </div>

            <div class="flex gap-3">
                <a href="{{ route('assets.index') }}" 
                   class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition">
                    Update Data
                </button>
            </div>
        </div>
    </form>

    <!-- FORM DELETE DIPINDAHKAN KE SINI (DI LUAR FORM UPDATE) -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <form action="{{ route('assets.destroy', $asset) }}" method="POST" 
              onsubmit="return confirm('Yakin ingin menghapus aset ini? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition">
                🗑️ Hapus Aset
            </button>
        </form>
    </div>
</div>
@endsection