<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    
    public function index(Request $request)
    {
        // Base query
        $query = Employee::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Get paginated results
        $employees = $query->orderBy('name')->paginate(20);

        // ===== STATISTICS BASED ON FILTERED QUERY =====
        // Clone query untuk stats (tanpa pagination)
        $statsQuery = clone $query;
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'inactive' => (clone $statsQuery)->where('is_active', false)->count(),
            'departments' => (clone $statsQuery)->whereNotNull('department')->distinct('department')->count('department')
        ];

        return view('employees.index', compact('employees', 'stats'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:employees|max:50',
            'user_id' => 'nullable|max:50',
            'name' => 'required|max:100',
            'email' => 'required|email|unique:employees|max:100',
            'department' => 'nullable|max:100',
            'cost_center' => 'nullable|max:50',
            'is_active' => 'boolean'
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee has been succesfully stored');
    }

    public function show(Employee $employee)
    {
        $employee->load(['assets.assetType', 'loanLogs.asset.assetType', 'assetHistories.asset']);
        
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id' => 'required|max:50|unique:employees,employee_id,' . $employee->id,
            'user_id' => 'nullable|max:50',
            'name' => 'required|max:100',
            'email' => 'required|email|max:100|unique:employees,email,' . $employee->id,
            'department' => 'nullable|max:100',
            'cost_center' => 'nullable|max:50',
            'is_active' => 'boolean'
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee has been succesfully updated');
    }
    
    public function destroy(Employee $employee)
    {
        // Ambil semua asset yang di-assign ke employee ini
        $assignedAssets = $employee->assets;

        if ($assignedAssets->count() > 0) {
            foreach ($assignedAssets as $asset) {
                // Update asset: unassign employee dan set status ke In Stock
                $asset->update([
                    'assigned_to' => null,
                    'status' => 'In Stock',
                    'assignment_date' => null
                ]);

                // Update history: set return_date untuk assignment yang masih aktif
                $activeHistory = $asset->assetHistories()
                    ->where('employee_id', $employee->id)
                    ->whereNull('return_date')
                    ->first();

                if ($activeHistory) {
                    $activeHistory->update([
                        'return_date' => now(),
                        'notes' => $activeHistory->notes . ' - Employee deleted, asset returned to stock'
                    ]);
                }
            }
        }

        // Hapus employee
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee has been deleted. ' . $assignedAssets->count() . ' asset(s) have been returned to stock.');
    }
}