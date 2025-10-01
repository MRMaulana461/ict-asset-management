@extends('layouts.app')

@section('title', 'Catat Peminjaman')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Formulir Peminjaman Barang</h1>

<div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
    <form action="{{ route('loan-log.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="loan_date" class="block text-sm font-medium text-gray-700">Tanggal *</label>
                <input type="date" name="loan_date" id="loan_date" value="{{ old('loan_date', date('Y-m-d')) }}" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
            </div>
            <div>
                <label for="loan_time" class="block text-sm font-medium text-gray-700">Jam</label>
                <input type="time" name="loan_time" id="loan_time" value="{{ old('loan_time', date('H:i')) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
            </div>
        </div>

        <div>
            <label for="pic_user" class="block text-sm font-medium text-gray-700">Nama Peminjam (PIC / User) *</label>
            <input type="text" name="pic_user" id="pic_user" value="{{ old('pic_user') }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('pic_user') border-red-500 @enderror">
            @error('pic_user')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="sm:col-span-2">
                <label for="item_description" class="block text-sm font-medium text-gray-700">Item yang Dipinjam *</label>
                <input type="text" name="item_description" id="item_description" value="{{ old('item_description') }}" required
                       placeholder="Contoh: Mouse, Keyboard, Laptop, dll"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('item_description') border-red-500 @enderror">
                @error('item_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah *</label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
            </div>
        </div>

        <div class="border-t pt-6 mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Paraf / Tanda Tangan Digital</label>
            <div class="w-full h-32 bg-gray-100 rounded-md border-2 border-dashed border-gray-300 flex items-center justify-center">
                <span class="text-gray-400">Area Tanda Tangan (Coming Soon)</span>
            </div>
            <p class="mt-1 text-xs text-gray-500">* Fitur tanda tangan digital akan ditambahkan</p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('loan-log.index') }}" 
               class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                Batal
            </a>
            <button type="submit" 
                    class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition">
                Catat Peminjaman
            </button>
        </div>
    </form>
</div>
@endsection