<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AssetType;
use App\Models\Asset;
use App\Models\LoanLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicLoanController extends Controller
{
    public function showForm()
    {
        $assetTypes = AssetType::where('category', 'peripheral')
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                
                $availableStock = Asset::where('asset_type_id', $type->id)
                    ->where('status', 'In Stock')
                    ->count();
                
                $type->available_stock = $availableStock;
                return $type;
            });

        return view('public.loan-form', compact('assetTypes'));
    }

    // API endpoint untuk autocomplete employee (mendukung ghrs_id, user_id, badge_id)
    public function getEmployee($employeeId)
    {
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
                'data' => [
                    'ghrs_id' => $employee->ghrs_id,
                    'user_id' => $employee->user_id ?? '-',
                    'badge_id' => $employee->badge_id ?? '-',
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'department' => $employee->department
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Employee not found or inactive'
        ], 404);
    }

    // API endpoint untuk cek stock
    public function checkStock($assetTypeId)
    {
        $assetType = AssetType::find($assetTypeId);
        
        if (!$assetType) {
            return response()->json([
                'success' => false,
                'message' => 'Asset type not found'
            ], 404);
        }

        $availableStock = Asset::where('asset_type_id', $assetTypeId)
            ->where('status', 'In Stock')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'asset_type' => $assetType->name,
                'available_stock' => $availableStock
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string', 
            'asset_type_id' => 'required|exists:asset_types,id',
            'duration_days' => 'required|integer|min:1|max:7',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|min:1|max:500' 
        ], [
            'duration_days.max' => 'Maximum loan duration is 7 days',
        ]);

        return DB::transaction(function () use ($validated) {
            
            $employee = Employee::where('is_active', true)
                ->where(function($query) use ($validated) {
                    $query->where('ghrs_id', $validated['employee_id'])
                        ->orWhere('user_id', $validated['employee_id'])
                        ->orWhere('badge_id', $validated['employee_id']);
                })
                ->lockForUpdate()
                ->first();

            if (!$employee) {
                return back()
                    ->with('error', 'Employee not found or inactive. Please check your Employee ID.')
                    ->withInput();
            }

            // Get asset type
            $assetType = AssetType::find($validated['asset_type_id']);

            // CEK KETERSEDIAAN STOCK dengan lock
            $availableStock = Asset::where('asset_type_id', $validated['asset_type_id'])
                ->where('status', 'In Stock')
                ->lockForUpdate()
                ->count();

            // Cek apakah stock mencukupi
            if ($availableStock < $validated['quantity']) {
                return back()
                    ->with('error', "Insufficient stock! {$assetType->name} available: {$availableStock}, requested: {$validated['quantity']}. Please reduce quantity or select another item.")
                    ->withInput();
            }

            // AMBIL ASSET YANG TERSEDIA dengan lock untuk update
            $assetsToLoan = Asset::where('asset_type_id', $validated['asset_type_id'])
                ->where('status', 'In Stock')
                ->lockForUpdate()
                ->limit($validated['quantity'])
                ->get();

            // Double check stock setelah lock
            if ($assetsToLoan->count() < $validated['quantity']) {
                return back()
                    ->with('error', "Insufficient stock after verification! {$assetType->name} available: {$assetsToLoan->count()}, requested: {$validated['quantity']}. Please try again.")
                    ->withInput();
            }

            $loanDate = Carbon::today();
            $durationDays = (int) $validated['duration_days'];

            // Create loan log untuk setiap asset
            foreach ($assetsToLoan as $asset) {
                LoanLog::create([
                    'borrower_id' => $employee->id,
                    'asset_id' => $asset->id,
                    'asset_type_id' => $validated['asset_type_id'],
                    'loan_date' => $loanDate,
                    'loan_time' => now()->format('H:i:s'),
                    'quantity' => 1,
                    'duration_days' => $durationDays,
                    'return_date' => null,
                    'status' => 'On Loan',
                    'reason' => $validated['purpose']
                ]);

                // UPDATE STATUS ASSET menjadi "In Use"
                $asset->update([
                    'status' => 'In Use',
                    'assigned_to' => $employee->id,
                    'assignment_date' => $loanDate,
                    'last_status_date' => now()
                ]);
            }

            // Pass duration_days ke session untuk dihitung di frontend
            return redirect()->route('loan.form')
                ->with('success', 'loan_success')
                ->with('loan_quantity', $validated['quantity'])
                ->with('loan_duration', $durationDays);
        });
    }
}