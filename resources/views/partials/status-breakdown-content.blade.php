@php
    $statusItems = [
        ['key' => 'in_stock', 'label' => 'In Stock', 'color' => 'blue', 'icon' => 'package'],
        ['key' => 'in_use', 'label' => 'In Use', 'color' => 'green', 'icon' => 'check-circle'],
        ['key' => 'broken', 'label' => 'Broken', 'color' => 'red', 'icon' => 'alert-circle'],
        ['key' => 'retired', 'label' => 'Retired', 'color' => 'gray', 'icon' => 'archive'],
        ['key' => 'taken', 'label' => 'Taken', 'color' => 'orange', 'icon' => 'arrow-right']
    ];
@endphp

<div class="space-y-4">
    @foreach($statusItems as $item)
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
            <div class="flex items-center">
                <div class="bg-{{ $item['color'] }}-100 p-2 rounded-lg mr-3">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 text-{{ $item['color'] }}-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">{{ $item['label'] }}</span>
            </div>
            <span class="font-bold text-{{ $item['color'] }}-600 text-lg">{{ $statusBreakdown[$item['key']] }}</span>
        </div>
    @endforeach
</div>

@if($selectedAssetTypeId && $totalFilteredAssets !== null)
    <div class="mt-6 pt-4 border-t border-gray-200">
        <div class="flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-700">Total Filtered</span>
            <span class="bg-saipem-primary text-white px-3 py-1 rounded-full text-sm font-bold">
                {{ $totalFilteredAssets }}
            </span>
        </div>
    </div>
@endif