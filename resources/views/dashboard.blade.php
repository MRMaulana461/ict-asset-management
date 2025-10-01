@extends('layouts.app')

@section('title', 'Dashboard - ICT Assets')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Asset ICT</h1>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform hover:scale-105">
        <div class="bg-blue-100 p-3 rounded-full">
            <i data-lucide="hard-drive" class="w-8 h-8 text-saipem-primary"></i>
        </div>
        <div>
            <p class="text-gray-500">Total Aset Terdata</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalAssets }}</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform hover:scale-105">
        <div class="bg-red-100 p-3 rounded-full">
            <i data-lucide="shield-alert" class="w-8 h-8 text-red-600"></i>
        </div>
        <div>
            <p class="text-gray-500">Barang Rusak</p>
            <p class="text-3xl font-bold text-gray-800">{{ $damagedAssets }}</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-transform hover:scale-105">
        <div class="bg-orange-100 p-3 rounded-full">
            <i data-lucide="arrow-right-left" class="w-8 h-8 text-saipem-accent"></i>
        </div>
        <div>
            <p class="text-gray-500">Barang Dipinjam</p>
            <p class="text-3xl font-bold text-gray-800">{{ $onLoanItems }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    <!-- Aset by Type -->
    <div class="bg-white p-6 rounded-xl shadow-lg lg:col-span-3">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Aset Berdasarkan Tipe</h2>
        <div class="space-y-4">
            @forelse($assetsByType as $asset)
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-600">{{ $asset->asset_type }}</span>
                        <span class="text-sm font-medium text-gray-600">{{ $asset->total }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-saipem-primary h-2.5 rounded-full" 
                             style="width: {{ ($asset->total / $totalAssets) * 100 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">Belum ada data aset</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Aktivitas Terbaru</h2>
            <ul class="divide-y divide-gray-200">
                @forelse($recentAssets as $asset)
                    <li class="py-2.5 flex items-center text-sm">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-red-500 mr-3 flex-shrink-0"></i>
                        <span>Aset <span class="font-semibold">{{ $asset->asset_tag }}</span> 
                        status: {{ $asset->status }}</span>
                    </li>
                @empty
                    <li class="py-2.5 text-sm text-gray-500">Belum ada aktivitas</li>
                @endforelse

                @foreach($recentLoans as $loan)
                    <li class="py-2.5 flex items-center text-sm">
                        <i data-lucide="arrow-right" class="w-4 h-4 text-saipem-accent mr-3 flex-shrink-0"></i>
                        <span><span class="font-semibold">{{ $loan->item_description }}</span> 
                        dipinjam oleh {{ $loan->pic_user }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection