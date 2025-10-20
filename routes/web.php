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

// Public Routes
Route::get('/', [PublicLoanController::class, 'showForm'])->name('loan.form');

// Public Routes - Withdrawal Form
Route::get('/withdrawal', [WithdrawalController::class, 'create'])->name('withdrawal.create');
Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('withdrawal.store');

// API Routes untuk Loan Form
Route::get('/api/employee/{employeeId}', [PublicLoanController::class, 'getEmployee']);
Route::get('/api/asset-stock/{assetTypeId}', [PublicLoanController::class, 'checkStock']);
Route::post('/loan/submit', [PublicLoanController::class, 'store'])->name('loan.submit');

// Auth Routes (Breeze default)
require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/status-breakdown', [DashboardController::class, 'getStatusBreakdown'])->name('dashboard.status-breakdown');
    
    // ===== DASHBOARD DETAIL PAGES =====
    Route::prefix('dashboard')->name('dashboard.')->group(function() {
        
        // ===== ASSET ANALYTICS (NEW - COMBINED VIEW) =====
        // Combined Asset Analytics Detail Page (Status + Type breakdown)
        Route::get('assets/analytics', [DashboardController::class, 'assetAnalyticsDetail'])
            ->name('assets.analytics');
        
        // Assets by Type Detail (EXISTING - individual type view)
        Route::get('assets/type/{id}', [DashboardController::class, 'assetTypeDetail'])
            ->name('assets.type.detail');
        
        // ===== LOAN ANALYTICS =====
        // Most Borrowed Items Detail
        Route::get('loans/borrowed', [DashboardController::class, 'mostBorrowedDetail'])
            ->name('loans.borrowed');
        
        // ===== DAMAGE ANALYTICS =====
        // Most Damaged Items Detail
        Route::get('damages/report', [DashboardController::class, 'damagesDetail'])
            ->name('damages.detail');
        
        // ===== ACTIVITY ANALYTICS =====
        // Recent Activities Detail
        Route::get('activities/recent', [DashboardController::class, 'recentActivitiesDetail'])
            ->name('activities.detail');
        
        // ===== REPORTS =====
        // Combined Report Export (ALL REPORTS IN ONE FILE)
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

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Withdrawal - History Page (HARUS SEBELUM resource routes)
    Route::get('/withdrawals-history', [WithdrawalController::class, 'history'])->name('withdrawals.history.page');
    
    // Withdrawal - CRUD Routes
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::get('/withdrawals/{withdrawal}/edit', [WithdrawalController::class, 'edit'])->name('withdrawals.edit');
    Route::put('/withdrawals/{withdrawal}', [WithdrawalController::class, 'update'])->name('withdrawals.update');
    Route::delete('/withdrawals/{withdrawal}', [WithdrawalController::class, 'destroy'])->name('withdrawals.destroy');
    
    // NEW: Manual entry with custom date
    Route::get('/withdrawals-manual/create', [WithdrawalController::class, 'createManual'])->name('withdrawals.create-manual');
    Route::post('/withdrawals-manual', [WithdrawalController::class, 'storeManual'])->name('withdrawals.store-manual');
    
    // Employee Import - HARUS SEBELUM resource
    Route::get('employees/import', [EmployeeImportController::class, 'showForm'])
        ->name('employees.import');
    Route::post('employees/import', [EmployeeImportController::class, 'import'])
        ->name('employees.import.process');

// Employees Resource
Route::resource('employees', EmployeeController::class);
    // Asset Types
    Route::resource('asset-types', AssetTypeController::class);
    
    // Asset Import - HARUS SEBELUM resource (NO TEMPLATE NEEDED)
    Route::get('assets/import', [AssetImportController::class, 'showForm'])->name('assets.import');
    Route::post('assets/import', [AssetImportController::class, 'import'])->name('assets.import.process');
    
    // Asset Export - HARUS SEBELUM resource
    Route::get('assets/export/excel', [AssetController::class, 'export'])->name('assets.export');
    
    // Assets Resource - HARUS TERAKHIR
    Route::resource('assets', AssetController::class);
    
    // Loan Log
    Route::resource('loan-log', LoanLogController::class);

    // Custom Route untuk Return Asset
    Route::put('/loan-log/{loanLog}/return', [LoanLogController::class, 'returnAsset'])
         ->name('loan-log.return');
});