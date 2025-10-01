<?php

namespace App\Http\Controllers;
use App\Models\Asset;
use App\Models\LoanLog;
use App\Exports\AssetsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('current_owner', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter type
        if ($request->has('asset_type') && $request->asset_type != '') {
            $query->where('asset_type', $request->asset_type);
        }

        $assets = $query->latest()->paginate(15);

        return view('assets.index', compact('assets'));
    }

    public function create()
    {
        return view('assets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|unique:assets|max:50',
            'serial_number' => 'nullable|unique:assets|max:100',
            'asset_type' => 'required|max:50',
            'current_owner' => 'nullable|max:100',
            'status' => 'required|in:Normal,Rusak,Dipinjam',
            'last_status_date' => 'required|date',
            'notes' => 'nullable'
        ]);

        Asset::create($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset berhasil ditambahkan');
    }

    public function show(Asset $asset)
    {
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        return view('assets.edit', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|max:50|unique:assets,asset_tag,' . $asset->id,
            'serial_number' => 'nullable|max:100|unique:assets,serial_number,' . $asset->id,
            'asset_type' => 'required|max:50',
            'current_owner' => 'nullable|max:100',
            'status' => 'required|in:Normal,Rusak,Dipinjam',
            'last_status_date' => 'required|date',
            'notes' => 'nullable'
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset berhasil diupdate');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset berhasil dihapus');
    }

    public function export(Request $request)
{
    $filters = [
        'asset_type' => $request->asset_type,
        'status' => $request->status,
        'owner' => $request->owner,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
    ];

    return Excel::download(new AssetsExport($filters), 'assets_' . date('Y-m-d') . '.xlsx');
}
}
