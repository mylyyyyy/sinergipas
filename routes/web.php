<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Admin\SquadController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']); // Support both / and /login
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);

    // Announcements
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::patch('/announcements/{announcement}/toggle', [\App\Http\Controllers\AnnouncementController::class, 'toggle'])->name('announcements.toggle');

    // Document Management
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/employee/{employee}', [DocumentController::class, 'showEmployeeFolders'])->name('documents.employee');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documents/bulk-action', [DocumentController::class, 'bulkAction'])->name('documents.bulk-action');
    Route::post('/documents/category', [DocumentController::class, 'storeCategory'])->name('documents.category.store');
    Route::delete('/documents/category/{category}', [DocumentController::class, 'destroyCategory'])->name('documents.category.destroy');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/verify', [DocumentController::class, 'verify'])->name('documents.verify');
    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
    Route::post('/documents/{document}/revision', [DocumentController::class, 'storeRevision'])->name('documents.revision');
    Route::post('/documents/{document}/toggle-lock', [DocumentController::class, 'toggleLock'])->name('documents.toggle-lock');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
    Route::post('/profile/report', [ProfileController::class, 'report'])->name('profile.report');

    // Notifications
    Route::post('/notifications/mark-read', function() {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.mark-read');

    // System Settings (Superadmin)
    Route::middleware('can:superadmin')->group(function () {
        Route::get('/dashboard/export/pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf');
        Route::get('/dashboard/export/excel', [DashboardController::class, 'exportExcel'])->name('dashboard.export.excel');
        Route::get('/settings/health', [\App\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('settings.health');

        Route::post('/employees/import/excel', [EmployeeController::class, 'importExcel'])->name('employees.import.excel');
        Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
        Route::delete('/employees/bulk-destroy', [EmployeeController::class, 'bulkDestroy'])->name('employees.bulk-destroy');
        Route::delete('/employees/destroy-all', [EmployeeController::class, 'destroyAll'])->name('employees.destroy-all');
        Route::resource('employees', EmployeeController::class);

        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::delete('/audit/clear', [AuditController::class, 'destroyAll'])->name('audit.clear');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/running-text', [SettingController::class, 'getRunningText'])->name('settings.running-text');

        // Rank Management
        Route::delete('/admin/ranks/bulk', [\App\Http\Controllers\Admin\RankController::class, 'bulkDestroy'])->name('admin.ranks.bulk-destroy');
        Route::resource('/admin/ranks', \App\Http\Controllers\Admin\RankController::class)->names('admin.ranks');

        // Position & Work Unit Management
        Route::post('/settings/positions', [SettingController::class, 'storePosition'])->name('settings.positions.store');
        Route::delete('/settings/positions/bulk', [SettingController::class, 'bulkDestroyPosition'])->name('settings.positions.bulk-destroy');
        Route::delete('/settings/positions/{position}', [SettingController::class, 'destroyPosition'])->name('settings.positions.destroy');
        
        Route::post('/settings/work-units', [SettingController::class, 'storeWorkUnit'])->name('settings.work-units.store');
        Route::delete('/settings/work-units/bulk', [SettingController::class, 'bulkDestroyWorkUnit'])->name('settings.work-units.bulk-destroy');
        Route::delete('/settings/work-units/{workUnit}', [SettingController::class, 'destroyWorkUnit'])->name('settings.work-units.destroy');

        // Report Issues Management
        Route::get('/admin/report-issues', [\App\Http\Controllers\Admin\ReportIssueController::class, 'index'])->name('admin.report-issues.index');
        Route::delete('/admin/report-issues/destroy-all', [\App\Http\Controllers\Admin\ReportIssueController::class, 'destroyAll'])->name('admin.report-issues.destroy-all');
        Route::put('/admin/report-issues/{issue}', [\App\Http\Controllers\Admin\ReportIssueController::class, 'update'])->name('admin.report-issues.update');
        Route::delete('/admin/report-issues/{issue}', [\App\Http\Controllers\Admin\ReportIssueController::class, 'destroy'])->name('admin.report-issues.destroy');

        // --- HRIS & Attendance ---
        Route::prefix('admin/attendance')->name('admin.attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::post('/import', [AttendanceController::class, 'import'])->name('import');
            Route::post('/store-manual', [AttendanceController::class, 'storeManual'])->name('store-manual');
            Route::get('/export', [AttendanceController::class, 'export'])->name('export');
        });

        Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy');
            Route::post('/{category}/add-member', [\App\Http\Controllers\Admin\CategoryController::class, 'addMember'])->name('add-member');
            Route::post('/{category}/remove-member', [\App\Http\Controllers\Admin\CategoryController::class, 'removeMember'])->name('remove-member');
            Route::post('/{category}/remove-members-bulk', [\App\Http\Controllers\Admin\CategoryController::class, 'removeMembersBulk'])->name('remove-members-bulk');
        });

        Route::prefix('admin/squads')->name('admin.squads.')->group(function () {
            Route::get('/', [SquadController::class, 'index'])->name('index');
            Route::post('/', [SquadController::class, 'store'])->name('store');
            Route::delete('/bulk', [SquadController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::put('/{squad}', [SquadController::class, 'update'])->name('update');
            Route::delete('/{squad}', [SquadController::class, 'destroy'])->name('destroy');
            Route::post('/{squad}/add-member', [SquadController::class, 'addMember'])->name('add-member');
            Route::post('/{squad}/remove-member', [SquadController::class, 'removeMember'])->name('remove-member');
            Route::post('/{squad}/remove-members-bulk', [SquadController::class, 'removeMembersBulk'])->name('remove-members-bulk');
        });

        Route::prefix('admin/shifts')->name('admin.shifts.')->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->name('index');
            Route::post('/', [ShiftController::class, 'store'])->name('store');
            Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
            Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('admin/schedules')->name('admin.schedules.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::post('/', [ScheduleController::class, 'store'])->name('store');
            Route::post('/individual', [ScheduleController::class, 'storeIndividual'])->name('store-individual');
            Route::post('/generate', [ScheduleController::class, 'generateRoster'])->name('generate');
            Route::delete('/reset', [ScheduleController::class, 'reset'])->name('reset');
            Route::delete('/bulk-delete', [ScheduleController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/export', [ScheduleController::class, 'export'])->name('export');
        });
    });
});
