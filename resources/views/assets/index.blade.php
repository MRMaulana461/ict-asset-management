@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Daftar Aset & Riwayat</h1>
    <a href="{{ route('assets.create') }}" 
       class="bg-saipem-primary text-white px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition flex items-center">
        <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
        Tambah Aset
    </a>
</div>

<div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg">
    <!-- Search & Filter -->
    <form method="GET" action="{{ route('assets.index') }}" class="mb-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari Asset Tag, SN, atau Owner..." 
                   class="border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
            
            <select name="asset_type" class="border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                <option value="">Semua Tipe</option>
                <option value="Laptop" {{ request('asset_type') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                <option value="Desktop/PC" {{ request('asset_type') == 'Desktop/PC' ? 'selected' : '' }}>Desktop/PC</option>
                <option value="Printer" {{ request('asset_type') == 'Printer' ? 'selected' : '' }}>Printer</option>
                <option value="Monitor" {{ request('asset_type') == 'Monitor' ? 'selected' : '' }}>Monitor</option>
            </select>

            <select name="status" class="border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                <option value="">Semua Status</option>
                <option value="Normal" {{ request('status') == 'Normal' ? 'selected' : '' }}>Normal</option>
                <option value="Rusak" {{ request('status') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                <option value="Dipinjam" {{ request('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
            </select>
        </div>
        
        <div class="flex gap-3">
            <button type="submit" class="bg-saipem-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90">
                <i data-lucide="search" class="w-4 h-4 inline mr-2"></i>Filter
            </button>
            <a href="{{ route('assets.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                Reset
            </a>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asset Tag</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($assets as $asset)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $asset->asset_tag }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->serial_number ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->asset_type }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->current_owner ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($asset->status == 'Normal')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Normal</span>
                            @elseif($asset->status == 'Rusak')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rusak</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Dipinjam</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('assets.show', $asset) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                            <a href="{{ route('assets.edit', $asset) }}" class="text-saipem-primary hover:text-opacity-80">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data aset</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $assets->links() }}
    </div>
</div>
@endsection