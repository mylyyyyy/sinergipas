<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Document;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Data dasar untuk dashboard
        $totalEmployees = Employee::count();
        $totalDocuments = Document::count();
        
        // Statistik khusus untuk pegawai yang login
        $myDocumentsCount = 0;
        if ($user->role === 'pegawai') {
            $employee = Employee::where('user_id', $user->id)->first();
            $myDocumentsCount = Document::where('employee_id', $employee?->id)->count();
        }

        $latestEmployees = Employee::with('user')->latest()->take(5)->get();

        // Chart Data: Documents by Category
        $chartData = \App\Models\DocumentCategory::withCount('documents')->get();

        return view('dashboard', compact(
            'totalEmployees', 
            'totalDocuments', 
            'latestEmployees',
            'myDocumentsCount',
            'chartData'
        ));
    }
}
