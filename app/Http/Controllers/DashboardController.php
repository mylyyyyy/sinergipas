<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Document;
use App\Models\WorkUnit;
use App\Models\DocumentCategory;
use App\Models\ReportIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // Storage Analytics (Simulation based on count)
            $storageUsed = $totalDocuments * 0.5; // Avg 0.5MB per file
            $storageLimit = 1024; // 1GB Limit
            $storagePercent = ($storageUsed / $storageLimit) * 100;

            // Unit Performance
            $unitPerformance = WorkUnit::withCount('employees')->get();

            $latestEmployees = (clone $employeeQuery)->with('user')->latest()->take(5)->get();
            $chartData = DocumentCategory::withCount('documents')->get();
            $workUnits = WorkUnit::all();

            return view('dashboard', compact(
                'totalEmployees', 'totalDocuments', 'docsToday', 'pendingDocs', 
                'openIssues', 'storagePercent', 'unitPerformance', 
                'latestEmployees', 'chartData', 'workUnits'
            ));
        } 
        
        // --- PEGAWAI VIEW DATA ---
        else {
            $employee = Employee::where('user_id', $user->id)->first();
            $myDocs = Document::where('employee_id', $employee?->id)->get();
            
            $myDocumentsCount = $myDocs->count();
            $verifiedDocs = $myDocs->where('status', 'verified')->count();
            
            // Career Progress Calculation (Dynamic based on categories)
            $totalCats = DocumentCategory::count();
            $uploadedCats = Document::where('employee_id', $employee?->id)
                ->distinct('document_category_id')
                ->count();
            $careerProgress = $totalCats > 0 ? ($uploadedCats / $totalCats) * 100 : 0;

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
