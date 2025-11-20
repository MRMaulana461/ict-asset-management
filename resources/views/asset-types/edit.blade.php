@extends('layouts.app')

@section('title', 'Edit Asset Type')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Asset Type</h1>

<div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
    <!-- FORM UPDATE -->
    <form action="{{ route('asset-types.update', $assetType) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $assetType->name) }}" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
            <select name="category" id="category" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">
                <option value="Hardware" {{ old('category', $assetType->category) == 'Hardware' ? 'selected' : '' }}>Hardware</option>
                <option value="Peripheral" {{ old('category', $assetType->category) == 'Peripheral' ? 'selected' : '' }}>Peripheral</option>
            </select>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-saipem-accent focus:ring-saipem-accent">{{ old('description', $assetType->description) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('asset-types.index') }}" 
               class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90 transition">
                Update
            </button>
        </div>
    </form>

    <!-- ✅ FORM DELETE TERPISAH -->
    <form action="{{ route('asset-types.destroy', $assetType) }}" method="POST" 
          onsubmit="return confirm('Are you sure you want to delete this asset type?')"
          class="mt-6 pt-6 border-t border-gray-200">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition">
            Delete Asset Type
        </button>
    </form>
</div>
@endsection