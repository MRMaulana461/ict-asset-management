<?php

namespace App\Http\Controllers;
use App\Models\Asset;
use App\Models\LoanLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class DashboardController extends Controller
{
    public function index()
    {
        // Total aset
        $totalAssets = Asset::count();
        
        // Barang rusak
        $damagedAssets = Asset::where('status', 'Rusak')->count();
        
        // Barang dipinjam (dari loan_log yang statusnya On Loan)
        $onLoanItems = LoanLog::where('status', 'On Loan')->sum('quantity');
        
        // Aset berdasarkan tipe
        $assetsByType = Asset::select('asset_type', DB::raw('count(*) as total'))
            ->groupBy('asset_type')
            ->get();
        
        // Aktivitas terbaru (gabungan dari assets dan loan_log)
        $recentAssets = Asset::where('status', '!=', 'Normal')
            ->latest('updated_at')
            ->take(3)
            ->get();
            
        $recentLoans = LoanLog::latest('created_at')
            ->take(3)
            ->get();

        return view('dashboard', compact(
            'totalAssets',
            'damagedAssets',
            'onLoanItems',
            'assetsByType',
            'recentAssets',
            'recentLoans'
        ));
    }
}
