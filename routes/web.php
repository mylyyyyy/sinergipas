<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// PWA Manifest
Route::get('/manifest.json', function() {
    return response()->json([
        "name" => "Sinergi PAS Jombang",
        "short_name" => "SinergiPAS",
        "start_url" => "/",
        "display" => "standalone",
        "background_color" => "#FCFBF9",
        "theme_color" => "#E85A4F",
        "icons" => [
            [
                "src" => asset('logo1.png'),
                "sizes" => "512x512",
                "type" => "image/png",
                "purpose" => "any maskable"
            ]
        ]
    ]);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Management (Superadmin)
    Route::post('/employees/import/excel', [EmployeeController::class, 'importExcel'])->name('employees.import.excel');
    Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export.pdf');
    Route::resource('employees', EmployeeController::class);

    // Document Management
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/employee/{employee}', [DocumentController::class, 'showEmployeeFolders'])->name('documents.employee');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documents/category', [DocumentController::class, 'storeCategory'])->name('documents.category.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Audit Logs (Admin)
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::delete('/audit/clear', [AuditController::class, 'destroyAll'])->name('audit.clear');
    
    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // System Settings (Superadmin)
    Route::middleware('can:superadmin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
