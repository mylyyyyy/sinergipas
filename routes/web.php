<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/reset-password', [\App\Http\Controllers\ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Employee Management (Superadmin)
    Route::post('/employees/import/excel', [EmployeeController::class, 'importExcel'])->name('employees.import.excel');
    Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export.pdf');
    Route::resource('employees', EmployeeController::class);

    // Document Management
    Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/employee/{employee}', [\App\Http\Controllers\DocumentController::class, 'showEmployeeFolders'])->name('documents.employee');
    
    // Audit Logs (Admin)
    Route::get('/audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
    Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    // Profile Settings
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
