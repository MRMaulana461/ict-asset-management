<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\AssetImportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\LoanLogController;
use App\Http\Controllers\PublicLoanController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

// ===== PUBLIC ROUTES (No Authentication) =====

// Public Loan Form
Route::get('/', [PublicLoanController::class, 'showForm'])->name('loan.form');
Route::post('/loan/submit', [PublicLoanController::class, 'store'])->name('loan.submit');

// Public Withdrawal Form
Route::get('/withdrawal', [WithdrawalController::class, 'create'])->name('withdrawal.create');
Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('withdrawal.store');

// API Routes untuk Public Forms
Route::get('/api/employee/{employeeId}', [PublicLoanController::class, 'getEmployee']);
Route::get('/api/asset-stock/{assetTypeId}', [PublicLoanController::class, 'checkStock']);

// API Routes untuk Asset Form (Employee Lookup by employee_id)
Route::get('/api/employees/by-employee-id/{employee_id}', [AssetController::class, 'getEmployeeByEmployeeId']);

// CSRF Refresh Endpoint
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

// ===== AUTH ROUTES (Breeze default) =====
require __DIR__.'/auth.php';

// ===== AUTHENTICATED ROUTES =====
Route::middleware(['auth', 'verified'])->group(function () {
    
    // ===== DASHBOARD =====
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/status-breakdown', [DashboardController::class, 'getStatusBreakdown'])->name('dashboard.status-breakdown');
    
    // ===== DASHBOARD DETAIL PAGES =====
    Route::prefix('dashboard')->name('dashboard.')->group(function() {
        
        // Asset Analytics (Combined View)
        Route::get('assets/analytics', [DashboardController::class, 'assetAnalyticsDetail'])
            ->name('assets.analytics');
        
        // Assets by Type Detail (Individual type view)
        Route::get('assets/type/{id}', [DashboardController::class, 'assetTypeDetail'])
            ->name('assets.type.detail');
        
        // Most Borrowed Items Detail
        Route::get('loans/borrowed', [DashboardController::class, 'mostBorrowedDetail'])
            ->name('loans.borrowed');
        
        // Most Damaged Items Detail
        Route::get('damages/report', [DashboardController::class, 'damagesDetail'])
            ->name('damages.detail');
            
        // Route::get('it-analytics', [DashboardController::class, 'itAnalytics'])
        //     ->name('it-analytics');

        // Recent Activities Detail
        Route::get('activities/recent', [DashboardController::class, 'recentActivitiesDetail'])
            ->name('activities.detail');
                    
        // Route::get('dashboard/export/ict-dashboard', [DashboardController::class, 'exportICTDashboard'])
        //    ->name('export.ict');

        // Combined Report Export
        Route::get('reports/export', [DashboardController::class, 'exportCombinedReport'])
            ->name('reports.export');
    });
    
    // ===== DASHBOARD API ENDPOINTS =====
    Route::prefix('api/dashboard')->name('api.dashboard.')->group(function() {
        // Assets API
        Route::get('assets/type/{assetTypeId}', [DashboardController::class, 'getAssetsByType'])
            ->name('assets.by-type');
        Route::get('assets/broken', [DashboardController::class, 'getBrokenAssets'])
            ->name('assets.broken');
        
        // Loans API
        Route::get('loans/overdue', [DashboardController::class, 'getOverdueLoans'])
            ->name('loans.overdue');
        Route::get('loans/history', [DashboardController::class, 'getLoanHistory'])
            ->name('loans.history');

           // Withdrawals API
        Route::get('withdrawals/history', [DashboardController::class, 'getWithdrawalHistory'])
            ->name('withdrawals.history');
        Route::get('withdrawals/by-department', [DashboardController::class, 'getWithdrawalsByDepartment'])
            ->name('withdrawals.by-department');
        Route::get('withdrawals/trends', [DashboardController::class, 'getWithdrawalTrends'])
            ->name('withdrawals.trends');
    });

    // ===== PROFILE =====
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ===== WITHDRAWALS =====
    // Manual entry with custom date (Before resource)
    Route::get('/withdrawals-manual/create', [WithdrawalController::class, 'createManual'])->name('withdrawals.create-manual');
    Route::post('/withdrawals-manual', [WithdrawalController::class, 'storeManual'])->name('withdrawals.store-manual');
    
    // Withdrawal CRUD Routes
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::get('/withdrawals/{withdrawal}/edit', [WithdrawalController::class, 'edit'])->name('withdrawals.edit');
    Route::put('/withdrawals/{withdrawal}', [WithdrawalController::class, 'update'])->name('withdrawals.update');
    Route::delete('/withdrawals/{withdrawal}', [WithdrawalController::class, 'destroy'])->name('withdrawals.destroy');
    
    // ===== EMPLOYEES =====
    // Employee Import (Before resource)
    Route::get('employees/import', [EmployeeImportController::class, 'showForm'])
        ->name('employees.import');
    Route::post('employees/import', [EmployeeImportController::class, 'import'])
        ->name('employees.import.process');
    
    // Employee Export (Before resource)
    Route::get('employees/export', [EmployeeController::class, 'export'])
        ->name('employees.export');
    
    // Employees Resource
    Route::resource('employees', EmployeeController::class);
    
    // ===== LOAN LOG =====
    // Custom Create Routes (Before resource)
    Route::get('/loan-log/create', [LoanLogController::class, 'create'])
        ->name('loan-log.create');
    Route::post('/loan-log', [LoanLogController::class, 'store'])
        ->name('loan-log.store');
    
    // New Loan with stock validation
    Route::get('/loan-log-new/create', [LoanLogController::class, 'createNew'])
        ->name('loan-log.create-new');
    Route::post('/loan-log-new', [LoanLogController::class, 'storeNew'])
        ->name('loan-log.store-new');
    
    // Custom Route untuk Return Asset (HARUS SEBELUM resource)
    Route::put('/loan-log/{loanLog}/return', [LoanLogController::class, 'returnAsset'])
        ->name('loan-log.return');
    
    // Loan Log Resource (index, show, edit, update, destroy)
    Route::resource('loan-log', LoanLogController::class)
        ->except(['create', 'store']);
    
    // ===== ASSET TYPES =====
    Route::resource('asset-types', AssetTypeController::class);
    
    // ===== ASSETS =====
    // Asset Import (HARUS SEBELUM resource)
    Route::get('assets/import', [AssetImportController::class, 'showForm'])->name('assets.import');
    Route::post('assets/import', [AssetImportController::class, 'import'])->name('assets.import.process');
    
    // Asset Export (HARUS SEBELUM resource)
    Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
   
    // Assets Resource (HARUS TERAKHIR)
    Route::resource('assets', AssetController::class);
});