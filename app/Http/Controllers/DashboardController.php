<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Document;
use App\Models\WorkUnit;
use App\Models\DocumentCategory;
use App\Models\ReportIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller

{
    public function index(Request $request)
    {
        $user = auth()->user();
        $workUnitId = $request->work_unit_id;

        // --- ADMIN VIEW DATA ---
        if ($user->role === 'superadmin') {
            $employeeQuery = Employee::query();
            if ($workUnitId) { $employeeQuery->where('work_unit_id', $workUnitId); }

            $totalEmployees = $employeeQuery->count();
            $totalDocuments = Document::count();
            $docsToday = Document::whereDate('created_at', now())->count();
            $pendingDocs = Document::where('status', 'pending')->count();
            $openIssues = ReportIssue::where('status', 'open')->count();

            // Storage Analytics
            // Calculate actual storage used in MB
            $totalSizeBytes = 0;
            $disk = Storage::disk('private');
            if ($disk->exists('documents')) {
                $files = $disk->allFiles('documents');
                foreach ($files as $file) {
                    $totalSizeBytes += $disk->size($file);
                }
            }
            $storageUsed = round($totalSizeBytes / (1024 * 1024), 2); // MB
            $storageLimit = 1024; // 1GB Limit
            $storagePercent = min(($storageUsed / $storageLimit) * 100, 100);

            // Compliance Tracking: Find employees missing mandatory documents
            $mandatoryCategories = DocumentCategory::where('is_mandatory', true)->get();
            $nonCompliantEmployees = Employee::with('user')
                ->whereHas('user', function($q) { $q->where('role', 'pegawai'); })
                ->get()
                ->filter(function($emp) use ($mandatoryCategories) {
                    if ($mandatoryCategories->isEmpty()) return false;
                    
                    $uploadedCatIds = Document::where('employee_id', $emp->id)
                        ->where('status', 'verified')
                        ->pluck('document_category_id')
                        ->toArray();
                    
                    foreach ($mandatoryCategories as $cat) {
                        if (!in_array($cat->id, $uploadedCatIds)) return true;
                    }
                    return false;
                })->values()->take(5);

            // Unit Performance
            $unitPerformance = WorkUnit::withCount('employees')->get();

            $latestEmployees = (clone $employeeQuery)->with('user')->latest()->take(5)->get();
            $chartData = DocumentCategory::withCount('documents')->get();
            $workUnits = WorkUnit::all();
            
            // Widget Settings
            $widgets = \App\Models\Setting::where('key', 'like', 'widget_%')->pluck('value', 'key');

            return view('dashboard', compact(
                'totalEmployees', 'totalDocuments', 'docsToday', 'pendingDocs', 
                'openIssues', 'storagePercent', 'storageUsed', 'unitPerformance', 
                'latestEmployees', 'chartData', 'workUnits', 'nonCompliantEmployees',
                'widgets'
            ));
        } 
        
        // --- PEGAWAI VIEW DATA ---
        else {
            $employee = Employee::where('user_id', $user->id)->first();
            $myDocs = Document::where('employee_id', $employee?->id)->get();
            
            $myDocumentsCount = $myDocs->count();
            $verifiedDocs = $myDocs->where('status', 'verified')->count();
            
            // Career Progress: Percentage of mandatory categories uploaded
            $mandatoryCats = DocumentCategory::where('is_mandatory', true)->get();
            $totalMandatory = $mandatoryCats->count();
            
            if ($totalMandatory > 0) {
                $uploadedMandatory = Document::where('employee_id', $employee?->id)
                    ->whereIn('document_category_id', $mandatoryCats->pluck('id'))
                    ->where('status', 'verified')
                    ->distinct('document_category_id')
                    ->count();
                $careerProgress = ($uploadedMandatory / $totalMandatory) * 100;
            } else {
                $careerProgress = 100;
            }

            // Latest Salary
            $latestSalary = Document::where('employee_id', $employee?->id)
                ->whereHas('category', function($q) { $q->where('slug', 'like', '%gaji%'); })
                ->latest()
                ->first();

            return view('dashboard-pegawai', compact(
                'myDocumentsCount', 'verifiedDocs', 'careerProgress', 'latestSalary'
            ));
        }
    }
}
