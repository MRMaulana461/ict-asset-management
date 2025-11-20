<?php

namespace App\Http\Controllers;

use App\Models\LoanLog;
use App\Models\Employee;
use App\Models\Asset;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanLogController extends Controller
{
    public function index(Request $request)
    {
        // Base query dengan eager loading yang lebih lengkap
        $query = LoanLog::with(['borrower', 'asset.assetType', 'assetType']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('borrower', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('ghrs_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('time_filter') && $request->time_filter !== 'all') {
            switch ($request->time_filter) {
                case 'last_week':
                    $query->where('loan_date', '>=', now()->subWeek());
                    break;
                case 'last_month':
                    $query->where('loan_date', '>=', now()->subMonth());
                    break;
                case 'last_year':
                    $query->where('loan_date', '>=', now()->subYear());
                    break;
            }
        }

        $loans = $query->latest('loan_date')->latest('loan_time')->paginate(10);

        // ===== STATISTICS BASED ON FILTERED QUERY =====
        $statsQuery = LoanLog::query();
        
        // Apply same filters
        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $statsQuery->whereHas('borrower', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('ghrs_id', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('time_filter') && $request->time_filter !== 'all') {
            switch ($request->time_filter) {
                case 'last_week':
                    $statsQuery->where('loan_date', '>=', now()->subWeek());
                    break;
                case 'last_month':
                    $statsQuery->where('loan_date', '>=', now()->subMonth());
                    break;
                case 'last_year':
                    $statsQuery->where('loan_date', '>=', now()->subYear());
                    break;
            }
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'on_loan' => (clone $statsQuery)->where('status', 'On Loan')->count(),
            'returned' => (clone $statsQuery)->where('status', 'Returned')->count(),
            'overdue' => $this->getOverdueLoans()->count()
        ];

        // Get notifications (unfiltered for alerts)
        $overdueLoans = $this->getOverdueLoans();
        $dueSoonLoans = $this->getDueSoonLoans();

        return view('loan-log.index', compact('loans', 'overdueLoans', 'dueSoonLoans', 'stats'));
    }

    /**
     * Get overdue loans (past due date)
     */
    private function getOverdueLoans()
    {
        return LoanLog::with(['borrower', 'asset.assetType', 'assetType'])
            ->where('status', 'On Loan')
            ->whereRaw('DATE_ADD(loan_date, INTERVAL duration_days DAY) < CURDATE()')
            ->orderBy('loan_date')
            ->get();
    }

    /**
     * Get loans due today
     */
    private function getDueSoonLoans()
    {
        return LoanLog::with(['borrower', 'asset.assetType', 'assetType'])
            ->where('status', 'On Loan')
            ->whereRaw('DATE_ADD(loan_date, INTERVAL duration_days DAY) = CURDATE()')
            ->orderBy('loan_date')
            ->get();
    }
     
    /**
     * DEFAULT: Show form for loan entry (no stock validation, custom date)
     */
    public function create()
    {
        // Get all asset types (peripheral only)
        $assetTypes = AssetType::orderBy('name')->get();

        return view('loan-log.create', compact('assetTypes'));
    }

    /**
     * DEFAULT: Store loan entry (no stock validation, custom date)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_date' => 'required|date|before_or_equal:today',
            'loan_time' => 'required|date_format:H:i',
            'ghrs_id' => 'required|string|max:50',
            'asset_type_id' => 'required|exists:asset_types,id',
            'quantity' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1|max:365',
            'purpose' => 'required|string|max:500',
            'status' => 'required|in:On Loan,Returned',
            'return_date' => 'nullable|date|after_or_equal:loan_date',
            'return_time' => 'nullable|date_format:H:i',
        ]);

        // Find employee by ghrs_id
        $employee = Employee::where('ghrs_id', $validated['ghrs_id'])->first();
        
        if (!$employee) {
            return back()->withInput()->with('error', 'Employee not found in system.');
        }

        // Prepare data untuk historical entry
        $data = [
            'borrower_id' => $employee->id,
            'asset_id' => null,  // Null untuk data historis
            'asset_type_id' => $validated['asset_type_id'],
            'loan_date' => $validated['loan_date'],
            'loan_time' => $validated['loan_time'],
            'quantity' => $validated['quantity'],
            'duration_days' => $validated['duration_days'],
            'reason' => $validated['purpose'],
            'status' => $validated['status'],
            'return_date' => $validated['return_date'],
            'return_time' => $validated['return_time'],
        ];

        // Create loan log
        LoanLog::create($data);

        return redirect()->route('loan-log.create')
            ->with('success', 'Loan record added successfully!');
    }

    /**
     * NEW LOAN: Show form with stock validation (auto date)
     */
    public function createNew()
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        
        // Get available peripheral assets
        $assets = Asset::with('assetType')
                ->orderBy('asset_tag')
                ->get();

        return view('loan-log.create-new', compact('employees', 'assets'));
    }

    /**
     * NEW LOAN: Store with stock validation (auto date)
     */
    public function storeNew(Request $request)
    {
        $validated = $request->validate([
            'ghrs_id' => 'required|string|exists:employees,ghrs_id',
            'asset_id' => 'required|exists:assets,id',
            'quantity' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1|max:30',
            'purpose' => 'required|string|max:500'
        ]);

        // Gunakan database transaction untuk mencegah race condition
        return DB::transaction(function () use ($validated, $request) {
            
            // Get employee dengan lock untuk mencegah race condition
            $employee = Employee::where('ghrs_id', $validated['ghrs_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$employee) {
                return back()->with('error', 'Employee not found or inactive')->withInput();
            }

            // Check if asset is available
            $asset = Asset::where('id', $validated['asset_id'])
                        ->where('status', 'In Stock')
                        ->lockForUpdate()
                        ->first();

            if (!$asset) {
                return back()->with('error', 'Asset is not available for loan')->withInput();
            }

            // Set loan date dan time otomatis
            $loanDate = Carbon::today();
            $loanTime = now()->format('H:i:s');
            $durationDays = (int) $validated['duration_days'];
            $returnDate = $loanDate->copy()->addDays($durationDays);

            // Create loan log dengan "On Loan" status
            $loanLog = LoanLog::create([
                'borrower_id' => $employee->id,
                'asset_id' => $validated['asset_id'],
                'asset_type_id' => $asset->asset_type_id, // Ambil dari asset
                'loan_date' => $loanDate,
                'loan_time' => $loanTime,
                'quantity' => $validated['quantity'],
                'duration_days' => $durationDays,
                'return_date' => null,
                'status' => 'On Loan',
                'purpose' => $validated['purpose']
            ]);

            // Update asset status to "Taken"
            $asset->update([
                'status' => 'Taken',
                'assigned_to' => $employee->id,
                'assignment_date' => $loanDate,
                'last_status_date' => now()
            ]);

            return redirect()->route('loan-log.index')
                ->with('success', 'Loan has been successfully recorded');
        });
    }

    public function show(LoanLog $loanLog)
    {
        // Eager load dengan assetType langsung
        $loanLog->load(['borrower', 'asset.assetType', 'assetType']);
        return view('loan-log.show', compact('loanLog'));
    }

    public function edit(LoanLog $loanLog)
    {
        // Only allow editing if status is "On Loan"
        if ($loanLog->status !== 'On Loan') {
            return redirect()->route('loan-log.index')
                ->with('error', 'Cannot extend loan for returned assets');
        }

        // Load relationships
        $loanLog->load(['borrower', 'asset.assetType', 'assetType']);

        $overdueLoans = $this->getOverdueLoans();
        $dueSoonLoans = $this->getDueSoonLoans();

        return view('loan-log.edit', compact('loanLog', 'overdueLoans', 'dueSoonLoans'));
    }

    public function update(Request $request, LoanLog $loanLog)
    {
        // Only allow update if status is "On Loan"
        if ($loanLog->status !== 'On Loan') {
            return back()->with('error', 'Cannot update returned loan');
        }

        $validated = $request->validate([
            'additional_days' => 'required|integer|min:1|max:30',
            'extension_reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Calculate new duration
        $newDuration = $loanLog->duration_days + $validated['additional_days'];

        // Update loan log with extension info
        $loanLog->update([
            'duration_days' => $newDuration,
            'extension_reason' => $validated['extension_reason'],
            'extension_notes' => $validated['notes'],
            'extended_at' => now()
        ]);

        return redirect()->route('loan-log.index')
            ->with('success', 'Loan duration has been extended by ' . $validated['additional_days'] . ' days');
    }

    public function returnAsset(Request $request, $id)
    {
        $loanLog = LoanLog::findOrFail($id);

        if ($loanLog->status !== 'On Loan') {
            return redirect()->route('loan-log.index')
                ->with('error', 'This loan has already been returned');
        }

        $loanLog->update([
            'return_date' => now()->format('Y-m-d'),
            'return_time' => now()->format('H:i:s'),
            'status' => 'Returned'
        ]);

        // Update asset status hanya jika ada asset_id (bukan data historis)
        if ($loanLog->asset_id) {
            Asset::where('id', $loanLog->asset_id)->update([
                'status' => 'In Stock',
                'assigned_to' => null,
                'assignment_date' => null,
                'last_status_date' => now()
            ]);
        }

        return redirect()->route('loan-log.index')
            ->with('success', 'Asset has been marked as returned');
    }

    public function destroy(LoanLog $loanLog)
    {
        if ($loanLog->status !== 'Returned') {
            return back()->with('error', 'Cannot delete active loan. Please mark as returned first.');
        }

        $loanLog->delete();

        return redirect()->route('loan-log.index')
            ->with('success', 'Loan record has been deleted');
    }
}