<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetType;

class AssetTypeController extends Controller
{
    public function index()
    {
        $assetTypes = AssetType::withCount('assets')->latest()->paginate(10);
        return view('asset-types.index', compact('assetTypes'));
    }

    public function create()
    {
        return view('asset-types.create');
    }

    public function store(Request $request)
    {
        $validated =$request->validate([
            'name' => 'required|unique:asset_types|max:50',
            'category' => 'required|in:Hardware,Peripheral',
            'description' => 'nullable'
        ]);

        AssetType::create($validated);

        return redirect()->route('asset-types.index')->with('success', 'Asset Type successfully stored');
    }

    public function edit(AssetType $assetType)
    {
        return view('asset-types.edit', compact('assetType'));
    }
    
    public function update(Request $request, AssetType $assetTypes)
    {
        $validated = $request->validate([
            'name' => 'required|max:50|unique:asset_types,name,' . $assetType->id,
            'category' => 'required|in:Hardware,Peripheral',
            'description' => 'nullable'
        ]);

        $assetType->update($validated);

        return redirect()->route('asset-types.index')->with('success', 'Asset type has been successfully updated');
    }

    public function destroy(AssetType $assetType)
    {
        if ($assetType->assets()->count() > 0) {
            return redirect()->route('asset-types.index')->with('error', 'failed to remove the asset type');
        }

        $assetType->delete();

        return redirect()->route('asset-types.index')->with('success', 'Asset type has been successfully deleted');
    }
}
