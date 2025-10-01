@extends('layouts.app')

@section('title', 'Detail Aset')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Detail Aset</h1>
    <div class="flex gap-3">
        <a href="{{ route('assets.edit', $asset) }}" 
           class="bg-saipem-primary text-white px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition flex items-center">
            <i data-lucide="edit" class="w-5 h-5 mr-2"></i>
            Edit
        </a>
        <a href="{{ route('assets.index') }}" 
           class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
            Kembali
        </a>
    </div>
</div>

<div class="bg-white p-8 rounded-xl shadow-lg max-w-3xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Asset Tag</label>
            <p class="text-lg font-semibold text-gray-900">{{ $asset->asset_tag }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Serial Number</label>
            <p class="text-lg font-semibold text-gray-900">{{ $asset->serial_number ?? '-' }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Tipe Perangkat</label>
            <p class="text-lg font-semibold text-gray-900">{{ $asset->asset_type }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Owner</label>
            <p class="text-lg font-semibold text-gray-900">{{ $asset->current_owner ?? '-' }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
            <div>
                @if($asset->status == 'Normal')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Normal</span>
                @elseif($asset->status == 'Rusak')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Rusak</span>
                @else
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">Dipinjam</span>
                @endif
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Status</label>
            <p class="text-lg font-semibold text-gray-900">{{ $asset->last_status_date->format('d/m/Y') }}</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-500 mb-1">Catatan</label>
            <p class="text-gray-900">{{ $asset->notes ?? '-' }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat</label>
            <p class="text-sm text-gray-700">{{ $asset->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">Terakhir Update</label>
            <p class="text-sm text-gray-700">{{ $asset->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection