<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Employee;
use App\Models\AssetHistory;
use App\Exports\AssetsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AssetController extends Controller
{

    public function index(Request $request)
    {
        // Base query
        $query = Asset::with(['assetType', 'assignedEmployee']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_ref', 'like', "%{$search}%")
                ->orWhere('pr_ref', 'like', "%{$search}%")
                ->orWhere('item_name', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhere('service_tag', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('device_name', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('asset_tag', 'like', "%{$search}%")
                ->orWhereHas('assignedEmployee', function($empQuery) use ($search) {
                    $empQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('ghrs_id', 'like', "%{$search}%")
                            ->orWhere('user_id', 'like', "%{$search}%")
                            ->orWhere('badge_id', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%")
                            ->orWhere('department', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('asset_type_id')) {
            $query->where('asset_type_id', $request->asset_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get paginated results
        $assets = $query->latest('delivery_date')
                        ->latest('created_at')
                        ->paginate(10);

        // Statistics based on filtered query
        $statsQuery = clone $query;
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'in_stock' => (clone $statsQuery)->where('status', 'In Stock')->count(),
            'in_use' => (clone $statsQuery)->where('status', 'In Use')->count(),
            'broken' => (clone $statsQuery)->where('status', 'Broken')->count(),
            'taken' => (clone $statsQuery)->where('status', 'Taken')->count()
        ];

        // Get all asset types for filter dropdown
        $assetTypes = \App\Models\AssetType::orderBy('name')->get();

        return view('assets.index', compact('assets', 'assetTypes', 'stats'));
    }

    public function create()
    {
        $assetTypes = AssetType::all();
        // Tidak perlu load employees lagi karena pakai API
        
        return view('assets.create', compact('assetTypes'));
    }

    /**
     * API: Get employee by employee_id (mendukung ghrs_id, user_id, dan badge_id)
     * Route: GET /api/employees/by-employee-id/{employee_id}
     */
    public function getEmployeeByEmployeeId($employeeId)
    {
        try {
            // Cari employee berdasarkan ghrs_id, user_id, atau badge_id
            $employee = Employee::where('is_active', true)
                ->where(function($query) use ($employeeId) {
                    $query->where('ghrs_id', $employeeId)
                          ->orWhere('user_id', $employeeId)
                          ->orWhere('badge_id', $employeeId);
                })
                ->first();

            if ($employee) {
                return response()->json([
                    'success' => true,
                    'employee' => [
                        'id' => $employee->id,
                        'ghrs_id' => $employee->ghrs_id,
                        'user_id' => $employee->user_id ?? '-',
                        'badge_id' => $employee->badge_id ?? '-',
                        'name' => $employee->name,
                        'department' => $employee->department ?? '-',
                        'email' => $employee->email ?? '-',
                        'status' => $employee->status ?? 'active'
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Employee not found or inactive'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching employee: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching employee data'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Cek apakah asset type yang dipilih adalah peripheral
        $assetType = AssetType::find($request->asset_type_id);
        $isPeripheral = $assetType && strtolower($assetType->category) === 'peripheral';

        // Validasi berbeda untuk peripheral dan non-peripheral
        if ($isPeripheral) {
            $validated = $request->validate([
                'asset_type_id' => 'required|exists:asset_types,id',
                'quantity' => 'required|integer|min:1',
                'assigned_to' => 'nullable|exists:employees,id',
                'assignment_date' => 'nullable|date',
                'status' => 'required|in:In Stock,In Use,Broken,Retired,Taken',
                'last_status_date' => 'required|date',
                'notes' => 'nullable'
            ]);

            // Buat multiple assets sesuai quantity untuk peripheral
            $quantity = $validated['quantity'];
            $createdAssets = [];

            for ($i = 1; $i <= $quantity; $i++) {
                $assetData = [
                    'asset_tag' => 'PER-' . $assetType->name . '-' . uniqid(),
                    'serial_number' => null,
                    'asset_type_id' => $validated['asset_type_id'],
                    'assigned_to' => $validated['assigned_to'] ?? null,
                    'assignment_date' => $validated['assignment_date'] ?? null,
                    'status' => $validated['status'],
                    'last_status_date' => $validated['last_status_date'],
                    'notes' => $validated['notes'],
                    'quantity' => 1
                ];

                $asset = Asset::create($assetData);
                $createdAssets[] = $asset;

                // Jika di-assign ke employee, buat history
                if ($validated['assigned_to']) {
                    AssetHistory::create([
                        'asset_id' => $asset->id,
                        'ghrs_id' => $validated['assigned_to'],
                        'assignment_date' => $validated['assignment_date'] ?? now(),
                        'notes' => 'Initial assignment (Peripheral - ' . $i . ' of ' . $quantity . ')'
                    ]);
                }
            }

            return redirect()->route('assets.index')
                ->with('success', $quantity . ' peripheral asset(s) successfully stored');

        } else {
            // Validasi normal untuk hardware
            $validated = $request->validate([
                'asset_tag' => 'required|unique:assets|max:50',
                'serial_number' => 'nullable|unique:assets|max:100',
                'asset_type_id' => 'required|exists:asset_types,id',
                'assigned_to' => 'nullable|exists:employees,id',
                'assignment_date' => 'nullable|date',
                'status' => 'required|in:In Stock,In Use,Broken,Retired,Taken',
                'last_status_date' => 'required|date',
                'notes' => 'nullable'
            ]);

            $asset = Asset::create($validated);

            // Jika di-assign ke employee, buat history
            if ($validated['assigned_to']) {
                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'ghrs_id' => $validated['assigned_to'],
                    'assignment_date' => $validated['assignment_date'] ?? now(),
                    'notes' => 'Initial assignment'
                ]);
            }

            return redirect()->route('assets.index')
                ->with('success', 'Asset successfully stored');
        }
    }

    public function show(Asset $asset)
    {
        $asset->load(['assetType', 'assignedEmployee', 'assetHistories.employee', 'loanLogs.borrower']);
        
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assetTypes = AssetType::all();
        // Tidak perlu load employees karena pakai API auto-fill
        
        return view('assets.edit', compact('asset', 'assetTypes'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|max:100|unique:assets,asset_tag,' . $asset->id,
            'serial_number' => 'nullable|max:100',
            'asset_type_id' => 'required|exists:asset_types,id',
            'ghrs_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:In Stock,In Use,Broken,Retired,Taken',
            'notes' => 'nullable'
        ]);

        // Tangani perubahan employee (owner)
        $oldEmployeeId = $asset->assigned_to;
        $newEmployeeId = $validated['ghrs_id'] ?? null;

        // Update kolom assigned_to dengan nilai dari ghrs_id
        $asset->update([
            'asset_tag' => $validated['asset_tag'],
            'serial_number' => $validated['serial_number'],
            'asset_type_id' => $validated['asset_type_id'],
            'assigned_to' => $newEmployeeId,
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        // Catat perubahan assignment di history jika ada perubahan owner
        if ($oldEmployeeId != $newEmployeeId) {
            if ($newEmployeeId) {
                // Assignment baru
                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'ghrs_id' => $newEmployeeId,
                    'assignment_date' => now(),
                    'notes' => 'Asset reassigned'
                ]);
            } else {
                // Return asset (tidak ada owner)
                if ($oldEmployeeId) {
                    $lastHistory = AssetHistory::where('asset_id', $asset->id)
                        ->where('ghrs_id', $oldEmployeeId)
                        ->whereNull('return_date')
                        ->first();
                    
                    if ($lastHistory) {
                        $lastHistory->update(['return_date' => now()]);
                    }
                }
            }
        }

        return redirect()->route('assets.index')
            ->with('success', 'Asset has been successfully updated');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset has been successfully deleted');
    }

    public function export(Request $request)
    {
        $filters = [
            'asset_type_id' => $request->asset_type_id,
            'status' => $request->status,
            'assigned_to' => $request->assigned_to,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        // Generate dynamic filename berdasarkan filter
        $filename = 'Assets';
        
        // Jika ada filter asset_type_id (bukan "All")
        if (!empty($request->asset_type_id)) {
            $assetType = \App\Models\AssetType::find($request->asset_type_id);
            if ($assetType) {
                // Sanitize nama asset type untuk filename
                $typeName = str_replace(['/', '\\', '?', '*', '[', ']', ':', ' '], '_', $assetType->name);
                $filename = 'Assets_' . $typeName;
            }
        } else {
            // Jika "All" - akan ada multiple sheets
            $filename = 'Assets_All_Types';
        }
        
        // Tambahkan status filter jika ada
        if (!empty($request->status)) {
            $filename .= '_' . str_replace(' ', '_', $request->status);
        }
        
        // Tambahkan tanggal export
        $filename .= '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new AssetsExport($filters), $filename);
    }
}