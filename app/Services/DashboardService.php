<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\LoanLog;
use App\Models\Employee;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get Asset Analytics Detail Data
     */
    public function getAssetAnalyticsData($request)
    {
        // Start with base query for filtering
        $baseQuery = Asset::query();
        
        // Apply filters to base query
        if ($request->filled('asset_type_id')) {
            $baseQuery->where('asset_type_id', $request->asset_type_id);
        }
        
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        
        // ===== TOTAL ASSETS (FILTERED) =====
        $totalAssets = (clone $baseQuery)->count();
        
        // ===== STATUS BREAKDOWN (FILTERED) =====
        $statusBreakdown = [
            'in_stock' => (clone $baseQuery)->where('status', 'In Stock')->count(),
            'in_use' => (clone $baseQuery)->where('status', 'In Use')->count(),
            'broken' => (clone $baseQuery)->where('status', 'Broken')->count(),
            'retired' => (clone $baseQuery)->where('status', 'Retired')->count(),
            'taken' => (clone $baseQuery)->where('status', 'Taken')->count(),
        ];
        
        // ===== UTILIZATION RATE (FILTERED) =====
        $utilizationRate = $totalAssets > 0 
            ? round(($statusBreakdown['in_use'] / $totalAssets) * 100, 1) 
            : 0;
        
        // ===== ASSETS BY TYPE WITH STATUS BREAKDOWN (FILTERED) =====
        $assetsByTypeQuery = Asset::select(
                'asset_type_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "In Stock" THEN 1 ELSE 0 END) as in_stock_count'),
                DB::raw('SUM(CASE WHEN status = "In Use" THEN 1 ELSE 0 END) as in_use_count'),
                DB::raw('SUM(CASE WHEN status = "Broken" THEN 1 ELSE 0 END) as broken_count'),
                DB::raw('SUM(CASE WHEN status = "Retired" THEN 1 ELSE 0 END) as retired_count'),
                DB::raw('SUM(CASE WHEN status = "Taken" THEN 1 ELSE 0 END) as taken_count')
            )
            ->with('assetType')
            ->groupBy('asset_type_id');
        
        // Apply same filters to assets by type
        if ($request->filled('asset_type_id')) {
            $assetsByTypeQuery->where('asset_type_id', $request->asset_type_id);
        }
        
        if ($request->filled('status')) {
            $assetsByTypeQuery->where('status', $request->status);
        }
        
        $assetsByType = $assetsByTypeQuery->orderByDesc('total')->get();
        
        // Count of asset types (after filtering)
        $assetTypesCount = $assetsByType->count();
        
        // Get all asset types for filter dropdown
        $allAssetTypes = AssetType::orderBy('name')->get();
        
        // Get active filter info for display
        $activeFilters = [];
        if ($request->filled('asset_type_id')) {
            $selectedType = AssetType::find($request->asset_type_id);
            if ($selectedType) {
                $activeFilters['asset_type'] = $selectedType->name . ' (' . $selectedType->category . ')';
            }
        }
        if ($request->filled('status')) {
            $activeFilters['status'] = $request->status;
        }
        
        return compact(
            'assetsByType',
            'statusBreakdown',
            'totalAssets',
            'utilizationRate',
            'allAssetTypes',
            'assetTypesCount',
            'activeFilters'
        );
    }

    /**
    * Get Most Borrowed Detail Data
    */
    public function getMostBorrowedData($request)
    {
        // Base query for filtering
        $baseQuery = LoanLog::query()->with(['borrower', 'asset.assetType']);
        
        // Apply filters to base query
        if ($request->filled('status') && $request->status != 'all') {
            $baseQuery->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('loan_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('loan_date', '<=', $request->date_to);
        }
        
        // UPDATED: Search by asset type name instead of asset_tag
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->whereHas('asset.assetType', function($typeQuery) use ($search) {
                    $typeQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%");
                })
                ->orWhereHas('borrower', function($empQuery) use ($search) {
                    $empQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                });
            });
        }
        
        // ===== STATISTICS (FILTERED) =====
        $stats = [
            'total_loans' => (clone $baseQuery)->count(),
            'active_loans' => (clone $baseQuery)->where('status', 'On Loan')->count(),
            'returned' => (clone $baseQuery)->where('status', 'Returned')->count(),
            'overdue' => (clone $baseQuery)->where('status', 'On Loan')
                ->whereRaw('DATE_ADD(loan_date, INTERVAL duration_days DAY) < NOW()')
                ->count()
        ];
        
        // ===== PAGINATED LOANS (FILTERED) =====
        $loans = (clone $baseQuery)->latest('loan_date')->paginate(15);
        
        // ===== TOP BORROWED ASSET TYPES (FILTERED) =====
        $topBorrowedQuery = LoanLog::query()
            ->join('asset_types', 'loan_log.asset_type_id', '=', 'asset_types.id')
            ->select('asset_types.id as asset_type_id', 'asset_types.name as asset_type_name', DB::raw('count(*) as count'));
        
        // Apply same filters to chart data
        if ($request->filled('status') && $request->status != 'all') {
            $topBorrowedQuery->where('loan_log.status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $topBorrowedQuery->whereDate('loan_log.loan_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $topBorrowedQuery->whereDate('loan_log.loan_date', '<=', $request->date_to);
        }
        
        // UPDATED: Apply search filter to chart query (search by asset type)
        if ($request->filled('search')) {
            $search = $request->search;
            $topBorrowedQuery->where(function($q) use ($search) {
                $q->where('asset_types.name', 'like', "%{$search}%")
                ->orWhere('asset_types.category', 'like', "%{$search}%")
                ->orWhereExists(function($subQuery) use ($search) {
                    $subQuery->select(DB::raw(1))
                        ->from('employees')
                        ->whereColumn('employees.id', 'loan_log.borrower_id')
                        ->where(function($empQuery) use ($search) {
                            $empQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('employee_id', 'like', "%{$search}%");
                        });
                });
            });
        }
        
        $topBorrowed = $topBorrowedQuery
            ->groupBy('asset_types.id', 'asset_types.name')
            ->orderByDesc('count')
            ->take(10)
            ->get();
        
        // Ensure chart data is always an array (even if empty)
        $chartData = [
            'labels' => $topBorrowed->pluck('asset_type_name')->toArray() ?: [],
            'data' => $topBorrowed->pluck('count')->toArray() ?: []
        ];
        
        // DEBUG: Log chart data
        \Log::info('Chart Data Created:', $chartData);
        
        // Get borrowers for filter dropdown
        $borrowers = Employee::orderBy('name')->get();
        
        // Get active filters for display
        $activeFilters = [];
        if ($request->filled('status') && $request->status != 'all') {
            $activeFilters['status'] = $request->status;
        }
        if ($request->filled('date_from')) {
            $activeFilters['date_from'] = Carbon::parse($request->date_from)->format('d M Y');
        }
        if ($request->filled('date_to')) {
            $activeFilters['date_to'] = Carbon::parse($request->date_to)->format('d M Y');
        }
        if ($request->filled('search')) {
            $activeFilters['search'] = $request->search;
        }
        
        return compact(
            'loans',
            'stats',
            'chartData',
            'borrowers',
            'activeFilters'
        );
    }

    /**
     * Get Damages Detail Data
     */
    public function getDamagesData($request)
    {
        // Base query for filtering
        $baseQuery = Withdrawal::query()->with(['employee', 'assetType']);
        
        // Apply filters to base query
        if ($request->filled('asset_type_id')) {
            $baseQuery->where('asset_type_id', $request->asset_type_id);
        }
        
        // Filter by dept_id
        if ($request->filled('dept_id')) {
            $baseQuery->whereHas('employee', function($q) use ($request) {
                $q->where('dept_id', $request->dept_id);
            });
        }
        
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('date', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->whereHas('employee', function($empQuery) use ($search) {
                    $empQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('ghrs_id', 'like', "%{$search}%")
                            ->orWhere('dept_id', 'like', "%{$search}%");
                })
                ->orWhereHas('assetType', function($typeQuery) use ($search) {
                    $typeQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%");
            });
        }
        
        // ===== STATISTICS (FILTERED) =====
        $stats = [
            'total_withdrawals' => (clone $baseQuery)->count(),
            'this_month' => (clone $baseQuery)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
            'total_quantity' => (clone $baseQuery)->sum('quantity'),
            'unique_dept_ids' => (clone $baseQuery)
                ->join('employees', 'withdrawals.employee_id', '=', 'employees.id')
                ->whereNotNull('employees.dept_id')
                ->distinct('employees.dept_id')
                ->count('employees.dept_id')
        ];
        
        // ===== PAGINATED WITHDRAWALS (FILTERED) =====
        $withdrawals = (clone $baseQuery)->latest('date')->paginate(15);
        
        // ===== DAMAGE BY dept_id CHART (FILTERED) =====
        $damageByDeptQuery = Withdrawal::query()
            ->join('employees', 'withdrawals.employee_id', '=', 'employees.id')
            ->select(
                'employees.dept_id',
                DB::raw('COUNT(*) as report_count'),
                DB::raw('SUM(withdrawals.quantity) as total_quantity')
            )
            ->whereNotNull('employees.dept_id')
            ->where('employees.dept_id', '!=', '');
        
        // Apply same filters
        if ($request->filled('asset_type_id')) {
            $damageByDeptQuery->where('withdrawals.asset_type_id', $request->asset_type_id);
        }
        
        if ($request->filled('dept_id')) {
            $damageByDeptQuery->where('employees.dept_id', $request->dept_id);
        }
        
        if ($request->filled('date_from')) {
            $damageByDeptQuery->whereDate('withdrawals.date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $damageByDeptQuery->whereDate('withdrawals.date', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $damageByDeptQuery->where(function($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                ->orWhere('employees.ghrs_id', 'like', "%{$search}%")
                ->orWhere('employees.dept_id', 'like', "%{$search}%")
                ->orWhereExists(function($subQuery) use ($search) {
                    $subQuery->select(DB::raw(1))
                        ->from('asset_types')
                        ->whereColumn('asset_types.id', 'withdrawals.asset_type_id')
                        ->where('asset_types.name', 'like', "%{$search}%");
                })
                ->orWhere('withdrawals.reason', 'like', "%{$search}%");
            });
        }
        
        $damageByDept = $damageByDeptQuery
            ->groupBy('employees.dept_id')
            ->orderByDesc('report_count')
            ->take(10)
            ->get();

        // ===== MONTHLY TREND (FILTERED) =====
        $monthlyTrendQuery = Withdrawal::query();
        
        // Apply same filters to monthly trend query
        if ($request->filled('asset_type_id')) {
            $monthlyTrendQuery->where('asset_type_id', $request->asset_type_id);
        }
        
        if ($request->filled('dept_id')) {
            $monthlyTrendQuery->whereHas('employee', function($q) use ($request) {
                $q->where('dept_id', $request->dept_id);
            });
        }
        
        if ($request->filled('date_from')) {
            $monthlyTrendQuery->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $monthlyTrendQuery->whereDate('date', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $monthlyTrendQuery->where(function($q) use ($search) {
                $q->whereHas('employee', function($empQuery) use ($search) {
                    $empQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('ghrs_id', 'like', "%{$search}%")
                            ->orWhere('dept_id', 'like', "%{$search}%");
                })
                ->orWhereHas('assetType', function($typeQuery) use ($search) {
                    $typeQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            
            $count = (clone $monthlyTrendQuery)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->count();
            
            $monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'count' => $count
            ];
        }
        
        $chartData = [
            'dept_id' => [
                'labels' => $damageByDept->pluck('dept_id')->toArray() ?: [],
                'data' => $damageByDept->pluck('report_count')->toArray() ?: []
            ],
            'trend' => [
                'labels' => array_column($monthlyTrend, 'month'),
                'data' => array_column($monthlyTrend, 'count')
            ]
        ];
        
        // Dropdown data
        $assetTypes = AssetType::orderBy('name')->get();
        
        // Get unique dept_ids for filter
        $dept_ids = Employee::whereNotNull('dept_id')
            ->where('dept_id', '!=', '')
            ->distinct()
            ->orderBy('dept_id')
            ->pluck('dept_id');
        
        // Active filters
        $activeFilters = [];
        if ($request->filled('asset_type_id')) {
            $selectedType = AssetType::find($request->asset_type_id);
            if ($selectedType) {
                $activeFilters['asset_type'] = $selectedType->name;
            }
        }
        if ($request->filled('dept_id')) {
            $activeFilters['dept_id'] = $request->dept_id;
        }
        if ($request->filled('date_from')) {
            $activeFilters['date_from'] = Carbon::parse($request->date_from)->format('d M Y');
        }
        if ($request->filled('date_to')) {
            $activeFilters['date_to'] = Carbon::parse($request->date_to)->format('d M Y');
        }
        if ($request->filled('search')) {
            $activeFilters['search'] = $request->search;
        }
        
        return compact(
            'withdrawals',
            'stats',
            'chartData',
            'assetTypes',
            'dept_ids', // Perbaiki nama variabel - dengan 's'
            'activeFilters'
        );
    }

    /**
     * Get Recent Activities Detail Data
     */
    public function getRecentActivitiesData($request)
    {
        $perPage = 40; // 4 rows × 5 items
        $itemsPerRow = 5;
        
        // Build activities query with filters
        $query = $this->buildActivitiesQuery($request);
        
        // Get current page
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        // Get paginated items
        $allActivities = $query->slice($offset, $perPage);
        $total = $query->count();
        
        // Create Laravel paginator
        $activities = new \Illuminate\Pagination\LengthAwarePaginator(
            $allActivities,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Split into rows with zigzag pattern
        $activityRows = collect($activities->items())
            ->chunk($itemsPerRow)
            ->map(function($row, $index) {
                $isReversed = $index % 2 === 1;
                
                return [
                    'activities' => $isReversed ? $row->reverse()->values() : $row->values(),
                    'isReversed' => $isReversed,
                    'rowIndex' => $index
                ];
            })
            ->values();
        
        // Stats (filtered if search is active)
        $statsQuery = $this->buildActivitiesQuery($request);
        
        $stats = [
            'total_loans' => $statsQuery->where('type', 'loan')->count(),
            'total_returns' => $statsQuery->where('type', 'return')->count(),
            'total_broken' => $statsQuery->where('type', 'broken')->count(),
            'total_withdrawals' => $statsQuery->where('type', 'withdrawal')->count()
        ];
        
        // Active filters
        $activeFilters = [];
        if ($request->filled('type') && $request->type !== 'all') {
            $activeFilters['type'] = ucfirst($request->type);
        }
        if ($request->filled('search')) {
            $activeFilters['search'] = $request->search;
        }
        if ($request->filled('date_from')) {
            $activeFilters['date_from'] = Carbon::parse($request->date_from)->format('d M Y');
        }
        if ($request->filled('date_to')) {
            $activeFilters['date_to'] = Carbon::parse($request->date_to)->format('d M Y');
        }
        
        return compact(
            'activityRows',
            'activities',
            'stats',
            'activeFilters'
        );
    }

    /**
     * Build activities query with filters
     */
    private function buildActivitiesQuery($request)
    {
        $activities = collect();
        $type = $request->get('type', 'all');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // === LOANS ===
        if (in_array($type, ['all', 'loan'])) {
            $loansQuery = LoanLog::with(['borrower', 'asset.assetType'])
                ->latest('created_at');
            
            if ($dateFrom) $loansQuery->whereDate('loan_date', '>=', $dateFrom);
            if ($dateTo) $loansQuery->whereDate('loan_date', '<=', $dateTo);
            
            $loans = $loansQuery->limit(200)->get()->map(function($loan) {
      
                $assetTypeName = $loan->asset && $loan->asset->assetType 
                    ? $loan->asset->assetType->name 
                    : ($loan->asset_type_name ?? 'Unknown Asset Type');
                
                return [
                    'id' => 'loan-' . $loan->id,
                    'type' => 'loan',
                    'icon' => 'arrow-right',
                    'color' => 'orange',
                    'title' => 'Asset Borrowed',
                    'description' => ($loan->borrower->name ?? 'Unknown') . ' borrowed ' . $assetTypeName,
                    'details' => 'Asset Type: ' . $assetTypeName . ' | Duration: ' . $loan->duration_days . ' days',
                    'timestamp' => $loan->created_at,
                    'date' => $loan->created_at->format('d M Y H:i'),
                    'search_text' => strtolower(($loan->borrower->name ?? '') . ' ' . $assetTypeName . ' ' . ($loan->borrower->employee_id ?? ''))
                ];
            });
            $activities = $activities->merge($loans);
        }
        
        // === RETURNS ===
        if (in_array($type, ['all', 'return'])) {
            $returnsQuery = LoanLog::where('status', 'Returned')
                ->with(['borrower', 'asset.assetType'])
                ->whereNotNull('return_date')
                ->latest('return_date');
            
            if ($dateFrom) $returnsQuery->whereDate('return_date', '>=', $dateFrom);
            if ($dateTo) $returnsQuery->whereDate('return_date', '<=', $dateTo);
            
            $returns = $returnsQuery->limit(200)->get()->map(function($loan) {
                
                $assetTypeName = $loan->asset && $loan->asset->assetType 
                    ? $loan->asset->assetType->name 
                    : ($loan->asset_type_name ?? 'Unknown Asset Type');
                
                return [
                    'id' => 'return-' . $loan->id,
                    'type' => 'return',
                    'icon' => 'arrow-left',
                    'color' => 'green',
                    'title' => 'Asset Returned',
                    'description' => ($loan->borrower->name ?? 'Unknown') . ' returned ' . $assetTypeName,
                    'details' => 'Asset Type: ' . $assetTypeName,
                    'timestamp' => Carbon::parse($loan->return_date),
                    'date' => Carbon::parse($loan->return_date)->format('d M Y H:i'),
                    'search_text' => strtolower(($loan->borrower->name ?? '') . ' ' . $assetTypeName)
                ];
            });
            $activities = $activities->merge($returns);
        }
        
        // === BROKEN ===
        if (in_array($type, ['all', 'broken'])) {
            $brokenQuery = Asset::where('status', 'Broken')
                ->with(['assetType', 'assignedEmployee'])
                ->latest('updated_at');
            
            if ($dateFrom) $brokenQuery->whereDate('updated_at', '>=', $dateFrom);
            if ($dateTo) $brokenQuery->whereDate('updated_at', '<=', $dateTo);
            
            $broken = $brokenQuery->limit(200)->get()->map(function($asset) {
               
                $assetTypeName = $asset->assetType ? $asset->assetType->name : 'Unknown Asset Type';
                
                return [
                    'id' => 'broken-' . $asset->id,
                    'type' => 'broken',
                    'icon' => 'alert-circle',
                    'color' => 'red',
                    'title' => 'Asset Broken',
                    'description' => $assetTypeName . ' reported as broken',
                    'details' => 'Asset Type: ' . $assetTypeName . ($asset->assignedEmployee ? ' | Last user: ' . $asset->assignedEmployee->name : ''),
                    'timestamp' => $asset->updated_at,
                    'date' => $asset->updated_at->format('d M Y H:i'),
                    'search_text' => strtolower($assetTypeName . ' ' . ($asset->assignedEmployee->name ?? ''))
                ];
            });
            $activities = $activities->merge($broken);
        }
        
        // === WITHDRAWALS ===
        if (in_array($type, ['all', 'withdrawal'])) {
            $withdrawalsQuery = Withdrawal::with(['employee', 'assetType'])
                ->latest('date');
            
            if ($dateFrom) $withdrawalsQuery->whereDate('date', '>=', $dateFrom);
            if ($dateTo) $withdrawalsQuery->whereDate('date', '<=', $dateTo);
            
            $withdrawals = $withdrawalsQuery->limit(200)->get()->map(function($withdrawal) {
                
                $assetTypeName = $withdrawal->assetType ? $withdrawal->assetType->name : 'Unknown Asset Type';
                
                return [
                    'id' => 'withdrawal-' . $withdrawal->id,
                    'type' => 'withdrawal',
                    'icon' => 'alert-triangle',
                    'color' => 'purple',
                    'title' => 'Damage Reported',
                    'description' => ($withdrawal->employee->name ?? 'Unknown') . ' reported damage - ' . $assetTypeName,
                    'details' => 'Asset Type: ' . $assetTypeName . ' | Quantity: ' . $withdrawal->quantity . ' | Reason: ' . \Str::limit($withdrawal->reason, 50),
                    'timestamp' => $withdrawal->date,
                    'date' => Carbon::parse($withdrawal->date)->format('d M Y H:i'),
                    'search_text' => strtolower(($withdrawal->employee->name ?? '') . ' ' . $assetTypeName . ' ' . $withdrawal->reason)
                ];
            });
            $activities = $activities->merge($withdrawals);
        }
        
        // Apply search filter
        if ($search) {
            $searchLower = strtolower($search);
            $activities = $activities->filter(function($activity) use ($searchLower) {
                return str_contains($activity['search_text'], $searchLower);
            });
        }
        
        // Sort by timestamp
        return $activities->sortByDesc('timestamp')->values();
    }
}