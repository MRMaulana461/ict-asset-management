<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\LoanLog;
use App\Models\Employee;
use App\Models\Withdrawal;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        // ===== KEY METRICS =====
        $totalAssets = Asset::count();
        $inUseAssets = Asset::where('status', 'In Use')->count();
        $inStockAssets = Asset::where('status', 'In Stock')->count();
        $brokenAssets = Asset::where('status', 'Broken')->count();
        $takenAssets = Asset::where('status', 'Taken')->count();
        $retiredAssets = Asset::where('status', 'Retired')->count();
        
        // Active loans
        $activeLoans = LoanLog::where('status', 'On Loan')->count();
        
        // Withdrawal metrics
        $totalWithdrawals = Withdrawal::count();
        $monthlyWithdrawals = Withdrawal::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
        
        // Utilization Rate
        $utilizationRate = $totalAssets > 0 
            ? round(($inUseAssets / $totalAssets) * 100, 1) 
            : 0;
        
        // ===== STATUS BREAKDOWN =====
        $statusBreakdown = [
            'in_stock' => $inStockAssets,
            'in_use' => $inUseAssets,
            'broken' => $brokenAssets,
            'retired' => $retiredAssets,
            'taken' => $takenAssets,
        ];
        
        // ===== OVERDUE LOANS (Critical Alert) =====
        $overdueLoans = LoanLog::where('status', 'On Loan')
            ->with(['borrower', 'asset.assetType'])
            ->get()
            ->filter(function($loan) {
                $expectedReturn = Carbon::parse($loan->loan_date)->addDays($loan->duration_days);
                return $expectedReturn->isPast();
            })
            ->sortBy(function($loan) {
                return Carbon::parse($loan->loan_date)->addDays($loan->duration_days);
            });
        
        // ===== ASSETS BY TYPE =====
        $assetsByType = Asset::select('asset_type_id', DB::raw('count(*) as total'))
            ->with('assetType')
            ->groupBy('asset_type_id')
            ->orderByDesc('total')
            ->get();
        
        // ===== MOST BORROWED ITEMS =====
        $mostBorrowedItems = LoanLog::select('asset_id', DB::raw('count(*) as borrow_count'))
            ->with('asset.assetType')
            ->groupBy('asset_id')
            ->orderByDesc('borrow_count')
            ->take(5)
            ->get()
            ->map(function ($loan) {
                return [
                    'asset_type' => $loan->asset->assetType->name ?? 'Unknown',
                    'asset_tag' => $loan->asset->asset_tag ?? 'N/A',
                    'count' => $loan->borrow_count
                ];
            });
        
        // ===== MOST DAMAGED ITEMS =====
        $mostDamagedItems = Withdrawal::select('asset_type_id', DB::raw('SUM(quantity) as damage_count'))
            ->with('assetType')
            ->groupBy('asset_type_id')
            ->orderByDesc('damage_count')
            ->take(5)
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'asset_type' => $withdrawal->assetType->name,
                    'count' => $withdrawal->damage_count
                ];
            });
        
        // ===== LOW STOCK WARNING =====
        $lowStockItems = AssetType::where('category', 'Peripheral')
            ->withCount(['assets' => function($query) {
                $query->where('status', 'In Stock');
            }])
            ->get()
            ->filter(function($type) {
                return $type->assets_count < 3 && $type->assets_count > 0;
            })
            ->map(function($type) {
                return [
                    'name' => $type->name,
                    'available' => $type->assets_count
                ];
            });
        
        // ===== RECENT ACTIVITIES =====
        $recentBroken = Asset::where('status', 'Broken')
            ->with(['assetType', 'assignedEmployee'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $recentLoans = LoanLog::with(['borrower', 'asset.assetType'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $recentReturns = LoanLog::where('status', 'Returned')
            ->with(['borrower', 'asset.assetType'])
            ->latest('return_date')
            ->take(5)
            ->get();

        $recentWithdrawals = Withdrawal::with(['employee', 'assetType'])
            ->latest('created_at')
            ->take(5)
            ->get();
        
        $allAssetTypes = AssetType::orderBy('name')->get();

        return view('dashboard', compact(
            'totalAssets',
            'inUseAssets',
            'inStockAssets',
            'brokenAssets',
            'activeLoans',
            'utilizationRate',
            'overdueLoans',
            'assetsByType',
            'mostBorrowedItems',
            'mostDamagedItems',
            'lowStockItems',
            'recentBroken',
            'recentLoans',
            'recentReturns',
            'recentWithdrawals',
            'statusBreakdown',
            'allAssetTypes',
            'totalWithdrawals',
            'monthlyWithdrawals'
        ));
    }

    /**
     * Asset Analytics Detail Page - Combined Status & Type View
     * MOVED TO SERVICE
     */
    public function assetAnalyticsDetail(Request $request)
    {
        $data = $this->dashboardService->getAssetAnalyticsData($request);
        return view('dashboard.assets.analytics-detail', $data);
    }

    // ===== WITHDRAWAL HISTORY =====
    
    public function getWithdrawalHistory(Request $request)
    {
        $query = Withdrawal::with(['employee', 'assetType']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('asset_type_id')) {
            $query->where('asset_type_id', $request->asset_type_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

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

        $withdrawals = $query->latest('date')
                             ->latest('created_at')
                             ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $withdrawals->map(function($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'date' => Carbon::parse($withdrawal->date)->format('d M Y'),
                    'employee_name' => $withdrawal->employee->name,
                    'employee_id' => $withdrawal->employee->employee_id,
                    'department' => $withdrawal->employee->department ?? 'N/A',
                    'asset_type' => $withdrawal->assetType->name,
                    'quantity' => $withdrawal->quantity,
                    'reason' => $withdrawal->reason,
                    'created_at' => $withdrawal->created_at->diffForHumans()
                ];
            }),
            'pagination' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total()
            ]
        ]);
    }

    public function getWithdrawalsByDepartment(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(3));
        $dateTo = $request->get('date_to', now());

        $withdrawalsByDept = Withdrawal::with('employee')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get()
            ->groupBy(function($withdrawal) {
                return $withdrawal->employee->department ?? 'Unassigned';
            })
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_quantity' => $group->sum('quantity')
                ];
            })
            ->sortByDesc('count');

        return response()->json([
            'success' => true,
            'data' => $withdrawalsByDept
        ]);
    }

    public function getWithdrawalTrends(Request $request)
    {
        $months = $request->get('months', 6);
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            
            $count = Withdrawal::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->count();
            
            $quantity = Withdrawal::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('quantity');

            $trends[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
                'quantity' => $quantity
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $trends
        ]);
    }

    // ===== EXISTING API ENDPOINTS =====
    
    public function getAssetsByType(Request $request, $assetTypeId)
    {
        $query = Asset::where('asset_type_id', $assetTypeId)
            ->with(['assetType', 'assignedEmployee']);

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhereHas('assignedEmployee', function($empQuery) use ($search) {
                      $empQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $assets = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $assets->map(function($asset) {
                return [
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'serial_number' => $asset->serial_number,
                    'status' => $asset->status,
                    'department' => $asset->assignedEmployee->department ?? null,
                    'assigned_to' => $asset->assignedEmployee->name ?? null,
                    'assigned_employee_id' => $asset->assignedEmployee->employee_id ?? null,
                    'acquisition_date' => $asset->acquisition_date ? $asset->acquisition_date->format('d M Y') : null,
                    'asset_type' => $asset->assetType->name
                ];
            }),
            'pagination' => [
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
                'per_page' => $assets->perPage(),
                'total' => $assets->total()
            ]
        ]);
    }

    public function getOverdueLoans(Request $request)
    {
        $query = LoanLog::where('status', 'On Loan')
            ->whereRaw('DATE_ADD(loan_date, INTERVAL duration_days DAY) < NOW()')
            ->with(['borrower', 'asset.assetType']);

        $loans = $query->get()->map(function($loan) {
            $expectedReturn = Carbon::parse($loan->loan_date)->addDays($loan->duration_days);
            $daysOverdue = $expectedReturn->diffInDays(Carbon::now());
            $hoursOverdue = $expectedReturn->diffInHours(Carbon::now()) % 24;
            
            return [
                'id' => $loan->id,
                'asset_tag' => $loan->asset->asset_tag,
                'asset_type' => $loan->asset->assetType->name,
                'borrower_name' => $loan->borrower->name,
                'borrower_id' => $loan->borrower->employee_id,
                'department' => $loan->borrower->department,
                'loan_date' => Carbon::parse($loan->loan_date)->format('d M Y'),
                'expected_return' => $expectedReturn->format('d M Y'),
                'days_overdue' => $daysOverdue,
                'hours_overdue' => $hoursOverdue,
                'overdue_message' => $this->formatOverdueMessage($daysOverdue, $hoursOverdue),
                'duration_days' => $loan->duration_days
            ];
        })->sortByDesc('days_overdue')->values();

        return response()->json([
            'success' => true,
            'data' => $loans,
            'total' => $loans->count()
        ]);
    }

    private function formatOverdueMessage($days, $hours)
    {
        if ($days > 0 && $hours > 0) {
            return "{$days}d {$hours}h overdue";
        } elseif ($days > 0) {
            return "{$days}d overdue";
        } else {
            return "{$hours}h overdue";
        }
    }

    public function getLoanHistory(Request $request)
    {
        $query = LoanLog::with(['borrower', 'asset.assetType']);

        if ($request->has('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->has('borrower_id')) {
            $query->where('borrower_id', $request->borrower_id);
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $loans = $query->latest('created_at')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $loans->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'asset_tag' => $loan->asset->asset_tag,
                    'asset_type' => $loan->asset->assetType->name,
                    'borrower_name' => $loan->borrower->name,
                    'status' => $loan->status,
                    'loan_date' => Carbon::parse($loan->loan_date)->format('d M Y'),
                    'return_date' => $loan->return_date ? Carbon::parse($loan->return_date)->format('d M Y') : null,
                    'created_at' => $loan->created_at->diffForHumans()
                ];
            }),
            'pagination' => [
                'current_page' => $loans->currentPage(),
                'last_page' => $loans->lastPage(),
                'total' => $loans->total()
            ]
        ]);
    }

    public function getBrokenAssets(Request $request)
    {
        $query = Asset::where('status', 'Broken')
            ->with(['assetType', 'assignedEmployee']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhereHas('assetType', function($typeQuery) use ($search) {
                      $typeQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $assets = $query->latest('updated_at')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $assets->map(function($asset) {
                return [
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'asset_type' => $asset->assetType->name,
                    'last_assigned_to' => $asset->assignedEmployee->name ?? 'N/A',
                    'updated_at' => $asset->updated_at->format('d M Y'),
                    'broken_since' => $asset->updated_at->diffForHumans()
                ];
            }),
            'pagination' => [
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
                'total' => $assets->total()
            ]
        ]);
    }

    public function assetTypeDetail(Request $request, $id)
    {
        $assetType = AssetType::findOrFail($id);
        
        $statusBreakdown = [
            'in_stock' => Asset::where('asset_type_id', $id)->where('status', 'In Stock')->count(),
            'in_use' => Asset::where('asset_type_id', $id)->where('status', 'In Use')->count(),
            'broken' => Asset::where('asset_type_id', $id)->where('status', 'Broken')->count(),
            'retired' => Asset::where('asset_type_id', $id)->where('status', 'Retired')->count(),
            'taken' => Asset::where('asset_type_id', $id)->where('status', 'Taken')->count(),
        ];
        
        $totalAssets = array_sum($statusBreakdown);
        
        $query = Asset::where('asset_type_id', $id)->with(['assignedEmployee']);
        
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhereHas('assignedEmployee', function($empQuery) use ($search) {
                    $empQuery->where('name', 'like', "%{$search}%");
                });
            });
        }
        
        $assets = $query->latest()->paginate(15);
        
        $chartData = [
            'labels' => ['In Stock', 'In Use', 'Broken', 'Retired', 'Taken'],
            'data' => [
                $statusBreakdown['in_stock'],
                $statusBreakdown['in_use'],
                $statusBreakdown['broken'],
                $statusBreakdown['retired'],
                $statusBreakdown['taken']
            ],
            'colors' => ['#3B82F6', '#10B981', '#EF4444', '#6B7280', '#F59E0B']
        ];
        
        return view('dashboard.assets.type-detail', compact(
            'assetType',
            'statusBreakdown',
            'totalAssets',
            'assets',
            'chartData'
        ));
    }

    /**
     * Most Borrowed Detail - MOVED TO SERVICE
     */
    public function mostBorrowedDetail(Request $request)
    {
        $data = $this->dashboardService->getMostBorrowedData($request);
        return view('dashboard.loans.borrowed-detail', $data);
    }

    /**
     * Damages Detail - MOVED TO SERVICE
     */
    public function damagesDetail(Request $request)
    {
        $data = $this->dashboardService->getDamagesData($request);
        return view('dashboard.damages.detail', $data);
    }

    /**
     * Recent Activities Detail - MOVED TO SERVICE
     */
    public function recentActivitiesDetail(Request $request)
    {
        $data = $this->dashboardService->getRecentActivitiesData($request);
        return view('dashboard.activities.recent-detail', $data);
    }

    public function exportCombinedReport()
    {
        return redirect()->route('dashboard')->with('info', 'Export feature will be implemented next');
    }
}