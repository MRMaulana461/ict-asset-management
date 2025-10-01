<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\LoanLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Tambahkan sebelum Route::resource
Route::get('assets/export/excel', [AssetController::class, 'export'])->name('assets.export');
Route::get('loan-log/export/excel', [LoanLogController::class, 'export'])->name('loan-log.export');

Route::resource('assets', AssetController::class);
Route::resource('loan-log', LoanLogController::class);
