<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;

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
                $q->where('ghrs_id', 'like', "%{$search}%")
                ->orWhere('user_id', 'like', "%{$search}%")
                ->orWhere('badge_id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('dept_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('dept_id')) {  
            $query->where('dept_id', $request->dept_id);
        }
        
        if ($request->filled('company')) {
            $query->where('company', $request->company);
        }

        // Get paginated results
        $employees = $query->orderBy('name')->paginate(10);

        // Statistics based on filtered query
        $statsQuery = clone $query;
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'inactive' => (clone $statsQuery)->where('is_active', false)->count(),
            'departments' => (clone $statsQuery)->whereNotNull('dept_id')->distinct('dept_id')->count('dept_id')  // ✅ UBAH dari 'department' ke 'dept_id'
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
            'ghrs_id' => 'required|unique:employees|max:50',
            'comp_empl_id' => 'nullable|max:50',
            'empl_rcd' => 'nullable|integer',
            'badge_id' => 'nullable|max:50',
            'user_id' => 'nullable|max:50',
            'first_name' => 'nullable|max:100',
            'last_name' => 'nullable|max:100',
            'name' => 'required|max:255',
            'email' => 'nullable|email|unique:employees|max:100',
            'company' => 'nullable|max:150',
            'org_context' => 'nullable|max:150',
            'department' => 'nullable|max:100',
            'dept_id' => 'nullable|max:20',
            'org_relation' => 'nullable|max:100',
            'agency' => 'nullable|max:100',
            'boc' => 'nullable|max:50',
            'cost_center' => 'nullable|max:50',
            'cost_center_descr' => 'nullable|max:200',
            'role_company' => 'nullable|max:100',
            'employee_class' => 'nullable|in:W,B',
            'tipo_terzi' => 'nullable|max:50',
            'contractual_position' => 'nullable|max:150',
            'is_active' => 'boolean'
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee has been successfully stored');
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
            'ghrs_id' => 'required|max:50|unique:employees,ghrs_id,' . $employee->id,
            'comp_empl_id' => 'nullable|max:50',
            'empl_rcd' => 'nullable|integer',
            'badge_id' => 'nullable|max:50',
            'user_id' => 'nullable|max:50',
            'first_name' => 'nullable|max:100',
            'last_name' => 'nullable|max:100',
            'name' => 'required|max:255',
            'email' => 'nullable|email|max:100|unique:employees,email,' . $employee->id,
            'company' => 'nullable|max:150',
            'org_context' => 'nullable|max:150',
            'department' => 'nullable|max:100',
            'dept_id' => 'nullable|max:20',
            'org_relation' => 'nullable|max:100',
            'agency' => 'nullable|max:100',
            'boc' => 'nullable|max:50',
            'cost_center' => 'nullable|max:50',
            'cost_center_descr' => 'nullable|max:200',
            'role_company' => 'nullable|max:100',
            'employee_class' => 'nullable|in:W,B',
            'tipo_terzi' => 'nullable|max:50',
            'contractual_position' => 'nullable|max:150',
            'is_active' => 'boolean'
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee has been successfully updated');
    }
    
    public function destroy(Employee $employee)
    {
        // Get all assets assigned to this employee
        $assignedAssets = $employee->assets;

        if ($assignedAssets->count() > 0) {
            foreach ($assignedAssets as $asset) {
                // Unassign employee and set status to In Stock
                $asset->update([
                    'assigned_to' => null,
                    'status' => 'In Stock',
                    'assignment_date' => null
                ]);

                // Update history: set return_date for active assignments
                $activeHistory = $asset->assetHistories()
                    ->where('employee_id', $employee->id)
                    ->whereNull('return_date')
                    ->first();

                if ($activeHistory) {
                    $activeHistory->update([
                        'return_date' => now(),
                        'notes' => ($activeHistory->notes ?? '') . ' - Employee deleted, asset returned to stock'
                    ]);
                }
            }
        }

        // Delete employee
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee has been deleted. ' . $assignedAssets->count() . ' asset(s) have been returned to stock.');
    }

    public function export(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'is_active' => $request->is_active,
            'department' => $request->department,
            'company' => $request->company,
        ];

        // Generate dynamic filename
        $filename = 'Employees';
        
        if (!empty($request->company)) {
            $filename .= '_' . str_replace(['/', '\\', '?', '*', '[', ']', ':', ' '], '_', $request->company);
        }
        
        if (!empty($request->department)) {
            $filename .= '_' . str_replace(['/', '\\', '?', '*', '[', ']', ':', ' '], '_', $request->department);
        }
        
        if ($request->is_active !== null) {
            $filename .= '_' . ($request->is_active == '1' ? 'Active' : 'Inactive');
        }
        
        $filename .= '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new EmployeesExport($filters), $filename);
    }
}