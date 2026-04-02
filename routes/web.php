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
    Route::get('/dashboard/export/pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');
    Route::get('/dashboard/export/excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');

    // Announcements
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::post('/announcements/{announcement}/toggle', [\App\Http\Controllers\AnnouncementController::class, 'toggle'])->name('announcements.toggle');

    // Employee Management (Superadmin)
    // Employee Management (Superadmin)
    Route::post('/employees/import/excel', [EmployeeController::class, 'importExcel'])->name('employees.import.excel');
    Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export.pdf');
    Route::delete('/employees/bulk-destroy', [EmployeeController::class, 'bulkDestroy'])->name('employees.bulk-destroy');
    Route::delete('/employees/{employee}/photo', [EmployeeController::class, 'deletePhoto'])->name('employees.photo.destroy');
    Route::resource('employees', EmployeeController::class);

    // Document Management
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/employee/{employee}', [DocumentController::class, 'showEmployeeFolders'])->name('documents.employee');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documents/bulk-store', [DocumentController::class, 'bulkStore'])->name('documents.bulk-store');
    Route::post('/documents/category', [DocumentController::class, 'storeCategory'])->name('documents.category.store');
    Route::delete('/documents/category/{category}', [DocumentController::class, 'destroyCategory'])->name('documents.category.destroy');
    Route::post('/documents/bulk-action', [DocumentController::class, 'bulkAction'])->name('documents.bulk-action');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/preview-version/{version}', [DocumentController::class, 'previewVersion'])->name('documents.preview-version');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
    Route::post('/documents/{document}/revision', [DocumentController::class, 'storeRevision'])->name('documents.revision');
    Route::post('/documents/{document}/toggle-lock', [DocumentController::class, 'toggleLock'])->name('documents.toggle-lock');

    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Audit Logs (Admin)
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::delete('/audit/clear', [AuditController::class, 'destroyAll'])->name('audit.clear');
    
    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.destroy');
    Route::post('/profile/report', [ProfileController::class, 'report'])->name('profile.report');

    // Notifications
    Route::post('/notifications/mark-read', function() {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.mark-read');

    // System Settings (Superadmin)
    Route::middleware('can:superadmin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Report Issues Management
        Route::get('/admin/report-issues', [\App\Http\Controllers\Admin\ReportIssueController::class, 'index'])->name('admin.report-issues.index');
        Route::put('/admin/report-issues/{issue}', [\App\Http\Controllers\Admin\ReportIssueController::class, 'update'])->name('admin.report-issues.update');
        Route::delete('/admin/report-issues/{issue}', [\App\Http\Controllers\Admin\ReportIssueController::class, 'destroy'])->name('admin.report-issues.destroy');
    });
});
