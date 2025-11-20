<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\Employee;
use App\Models\AssetType;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    // ========================================
    // PUBLIC METHODS (No Authentication)
    // ========================================
    
    public function create()
    {
        $assetTypes = AssetType::where('category', 'Peripheral')->orderBy('name')->get();
        
        return view('public.withdrawal-form', compact('assetTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ghrs_id' => 'required|string|max:50',  // ✅ Ubah dari employee_id ke ghrs_id
            'asset_type_id' => 'required|exists:asset_types,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        // ✅ Find employee by ghrs_id
        $employee = Employee::where('ghrs_id', $validated['ghrs_id'])->first();
        
        if (!$employee) {
            return back()->withInput()->with('error', 'Employee not found in system.');
        }

        // ✅ Set today's date and replace ghrs_id with numeric employee_id
        $validated['date'] = now()->toDateString();
        $validated['employee_id'] = $employee->id;
        unset($validated['ghrs_id']);  // Remove ghrs_id from array

        Withdrawal::create($validated);

        return redirect()->route('withdrawal.create')
            ->with('success', 'Withdrawal report submitted successfully! Your damage report has been recorded.');
    }

    // ========================================
    // ADMIN METHODS (With Authentication)
    // ========================================
    
    public function index(Request $request)
    {
        // Base query
        $query = Withdrawal::with(['employee', 'assetType']);
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('employee', function($empQuery) use ($search) {
                    $empQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                })
                ->orWhereHas('assetType', function($typeQuery) use ($search) {
                    $typeQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('department')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        if ($request->filled('asset_type')) {
            $query->where('asset_type_id', $request->asset_type);
        }
        
        $withdrawals = $query->latest('date')->paginate(10);
        
        // Statistics based on filtered query
        $statsQuery = Withdrawal::query();
        
        // Apply same filters
        if ($request->filled('search')) {
            $search = $request->search;
            $statsQuery->where(function($q) use ($search) {
                $q->whereHas('employee', function($empQuery) use ($search) {
                    $empQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                })
                ->orWhereHas('assetType', function($typeQuery) use ($search) {
                    $typeQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('department')) {
            $statsQuery->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        if ($request->filled('asset_type')) {
            $statsQuery->where('asset_type_id', $request->asset_type);
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'this_month' => (clone $statsQuery)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
            'total_quantity' => (clone $statsQuery)->sum('quantity'),
            'unique_employees' => (clone $statsQuery)->distinct('employee_id')->count('employee_id')
        ];
        
        // Dropdown data
        $departments = Employee::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort();
        
        $assetTypes = AssetType::orderBy('name')->get();
        
        return view('withdrawals.index', compact(
            'withdrawals',
            'departments',
            'assetTypes',
            'stats'
        ));
    }

    /**
     * Show form to manually add withdrawal (with custom date)
     */
    public function createManual()
    {
        $employees = Employee::where('is_active', 1)
            ->orderBy('name')
            ->get();
        
        $assetTypes = AssetType::orderBy('category')
            ->orderBy('name')
            ->get();
        
        return view('withdrawals.create', compact('employees', 'assetTypes'));
    }

    /**
     * Store manually added withdrawal (with custom date)
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'ghrs_id' => 'required|string|max:50',  // ✅ Ubah menjadi 'ghrs_id'
            'asset_type_id' => 'required|exists:asset_types,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        // Find employee by ghrs_id code
        $employee = Employee::where('ghrs_id', $validated['ghrs_id'])->first();  // ✅ Ubah key
        
        if (!$employee) {
            return back()->withInput()->with('error', 'Employee not found in system.');
        }

        // Set employee_id (numeric) dan hapus ghrs_id dari array
        $validated['employee_id'] = $employee->id;
        unset($validated['ghrs_id']);  // ✅ Hapus ghrs_id dari validated data

        Withdrawal::create($validated);

        return redirect()->route('withdrawals.create-manual')
            ->with('success', 'Withdrawal report added successfully!');
    }
    
    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['employee', 'assetType']);
        return view('withdrawals.show', compact('withdrawal'));
    }

    public function edit(Withdrawal $withdrawal)
    {
        $employees = Employee::where('is_active', 1)->get();
        $assetTypes = AssetType::orderBy('category')->orderBy('name')->get();
        
        return view('withdrawals.edit', compact('withdrawal', 'employees', 'assetTypes'));
    }

    public function update(Request $request, Withdrawal $withdrawal)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'employee_id' => 'required|exists:employees,id',
            'asset_type_id' => 'required|exists:asset_types,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        $withdrawal->update($validated);

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal report updated successfully!');
    }

    public function destroy(Withdrawal $withdrawal)
    {
        $withdrawal->delete();

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal report deleted successfully!');
    }
}